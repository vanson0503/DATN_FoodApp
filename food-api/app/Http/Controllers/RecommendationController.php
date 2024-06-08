<?php
namespace App\Http\Controllers;

use App\Services\CollaborativeFilteringService;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    protected $collaborativeFilteringService;

    public function __construct(CollaborativeFilteringService $collaborativeFilteringService)
    {
        $this->collaborativeFilteringService = $collaborativeFilteringService;
    }

    public function recommend(Request $request)
    {
        $userId = $request->id;
        $recommendations = $this->collaborativeFilteringService->recommendProducts($userId);

        $recommendedProducts = Product::with(['category', 'images'])
            ->whereIn('id', $recommendations)
            ->orderByDesc('created_time')
            ->get();
            foreach ($recommendedProducts as $product) {
                // Lấy thông tin đánh giá cho sản phẩm hiện tại
                $review = DB::table('review')
                    ->select(DB::raw('COALESCE(COUNT(*), 0) as total_reviews, COALESCE(AVG(rate), 0) as average_rating'))
                    ->where('product_id', $product->id)
                    ->first();
    
                // Thêm thông tin đánh giá vào đối tượng sản phẩm
                $product->total_reviews = $review->total_reviews;
                $product->average_rating = $review->average_rating;
                // Fetch total sold data
                $totalSold = DB::table('order_detail')
                    ->join('orders', 'orders.id', '=', 'order_detail.orders_id')
                    ->where('orders.status', 'completed')
                    ->where('order_detail.product_id', $product->id)
                    ->sum('order_detail.quantity');
    
                $product->total_sold = $totalSold;
            }

        // Trả về danh sách sản phẩm gợi ý
        return response()->json($recommendedProducts);
    }
}
