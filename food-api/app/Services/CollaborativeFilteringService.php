<?php
namespace App\Services;

use App\Models\Review;
use App\Models\Cart;
use App\Models\Favorite;
use App\Models\Customer;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;

class CollaborativeFilteringService
{
    public function getSimilarUsers($userId, $numSimilarUsers = 10)
    {
        $userRatings = $this->getUserRatings($userId);
        $similarities = [];
        foreach (Customer::all() as $user) {
            if ($user->id == $userId)
                continue;
            $otherUserRatings = $this->getUserRatings($user->id);
            $similarity = $this->calculateSimilarity($userRatings, $otherUserRatings);
            $similarities[$user->id] = $similarity;
        }
        arsort($similarities);
        return array_slice($similarities, 0, $numSimilarUsers, true);
    }

    private function getUserRatings($userId)
    {
        $reviews = Review::where('customer_id', $userId)->get();
        $cartItems = Cart::where('customer_id', $userId)->get();
        $favorites = Favorite::where('customer_id', $userId)->get();
        $ratings = $reviews->map(function ($item) {
            return (object) ['product_id' => $item->product_id, 'score' => $item->rate];
        })->concat(
                $cartItems->map(function ($item) {
                    return (object) ['product_id' => $item->product_id, 'score' => 1];
                })
            )->concat(
                $favorites->map(function ($item) {
                    return (object) ['product_id' => $item->product_id, 'score' => 2];
                })
            );

        return $ratings;
    }

    private function calculateSimilarity($userRatings, $otherUserRatings)
    {
        $commonRatings = $userRatings->whereIn('product_id', $otherUserRatings->pluck('product_id')->all());
        if ($commonRatings->isEmpty()) {
            return 0;
        }
        $sum = 0;
        foreach ($commonRatings as $rating) {
            $otherRating = $otherUserRatings->where('product_id', $rating->product_id)->first();
            $sum += pow($rating->score - $otherRating->score, 2);
        }
        return 1 / (1 + sqrt($sum));
    }

    public function recommendProducts($userId, $numRecommendations = 10)
    {
        $similarUsers = $this->getSimilarUsers($userId);
        $recommendations = collect();
        foreach ($similarUsers as $similarUserId => $similarity) {
            $ratings = $this->getUserRatings($similarUserId);
            foreach ($ratings as $rating) {
                if (
                    !Review::where('customer_id', $userId)
                        ->where('product_id', $rating->product_id)
                        ->exists() &&
                    !Cart::where('customer_id', $userId)
                        ->where('product_id', $rating->product_id)
                        ->exists() &&
                    !Favorite::where('customer_id', $userId)
                        ->where('product_id', $rating->product_id)
                        ->exists()
                ) {
                    $recommendations->push($rating->product_id);
                }
            }
        }
        $bestSellers = $this->getBestSellingProducts($numRecommendations);
        $recommendations = $recommendations->merge($bestSellers);
        return $recommendations->unique()->take($numRecommendations);
    }

    private function getBestSellingProducts($numProducts)
    {
        return OrderDetail::select('product_id')
            ->join('orders', 'orders.id', '=', 'order_detail.orders_id')
            ->where('orders.status', 'completed')
            ->groupBy('product_id')
            ->orderByDesc(DB::raw('SUM(order_detail.quantity)'))
            ->pluck('product_id')
            ->take($numProducts);
    }
}
