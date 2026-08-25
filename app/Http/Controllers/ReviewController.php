<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Product;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * GET /san-pham/{product:slug}/danh-gia — trả JSON danh sách đánh giá.
     * Đây là API thuần (không trả về view HTML), đúng yêu cầu "GET /products/{id}/reviews"
     * trong bảng phân công — dùng khi cần lấy dữ liệu qua AJAX hoặc cho nơi khác gọi tới.
     */
    public function indexJson(Product $product)
    {
        $reviews = $product->reviews()
            ->with('user:id,name') // chỉ lấy id và name của user, không lộ email/sđt ra API công khai
            ->latest()
            ->get(['id', 'user_id', 'rating', 'comment', 'created_at']);

        return response()->json([
            'average_rating' => $product->average_rating,
            'total' => $reviews->count(),
            'reviews' => $reviews,
        ]);
    }

    /**
     * POST /san-pham/{product:slug}/danh-gia — thêm hoặc cập nhật đánh giá của user hiện tại.
     *
     * Dùng updateOrCreate thay vì create(): nếu user NÀY đã từng đánh giá sản phẩm NÀY rồi,
     * sẽ CẬP NHẬT lại đánh giá cũ thay vì tạo dòng mới — vừa tránh lỗi vi phạm ràng buộc
     * unique(product_id, user_id) đã đặt ở Giai đoạn 1, vừa tiện cho phép user sửa đánh giá.
     */
    public function store(ReviewRequest $request, Product $product)
    {
        $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated()
        );

        return back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }

    /**
     * DELETE /danh-gia/{review} — xóa đánh giá (chỉ chủ sở hữu đánh giá đó mới xóa được).
     */
    public function destroy(Review $review)
    {
        abort_if($review->user_id !== auth()->id(), 403, 'Bạn không có quyền xóa đánh giá này.');

        $review->delete();

        return back()->with('success', 'Đã xóa đánh giá.');
    }
}
