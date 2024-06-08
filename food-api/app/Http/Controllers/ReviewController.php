<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index($product_id)
    {
        $reviews = Review::with('customer')
            ->where('product_id', $product_id)
            ->orderBy('created_time', 'desc')
            ->get();
        if ($reviews->isEmpty()) {
            return response()->json(['message' => "This product has no reviews!"], 404);
        }
        return response()->json($reviews, 200);
    }
    function create(Request $request)
    {
        $validatedData = $request->validate([
            'orders_id' => 'required|exists:orders,id',
            'customer_id' => 'required|exists:customer,id',
            'product_id' => 'required|exists:product,id',
            'rate' => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:255',
        ]);

        // Tạo mới đối tượng Review và gán dữ liệu từ request
        $review = new Review;
        $review->orders_id = $validatedData['orders_id'];
        $review->customer_id = $validatedData['customer_id'];
        $review->product_id = $validatedData['product_id'];
        $review->rate = $validatedData['rate'];

        if (isset($validatedData['content'])) {
            $review->content = $validatedData['content'];
        }
        // Lưu đánh giá vào cơ sở dữ liệu
        $review->save();

        // Trả về thông báo thành công
        return response()->json(['message' => 'Review created successfully'], 201);
    }

    public function findByParameters(Request $request)
    {
        $validatedData = $request->validate([
            'orders_id' => 'required|exists:orders,id',
            'customer_id' => 'required|exists:customer,id',
            'product_id' => 'required|exists:product,id',
        ]);

        $review = Review::where([
            'orders_id' => $validatedData['orders_id'],
            'customer_id' => $validatedData['customer_id'],
            'product_id' => $validatedData['product_id'],
        ])->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        return response()->json($review, 200);
    }
}
