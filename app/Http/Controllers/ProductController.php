<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductCatalogService;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function suggestions(Request $request, ProductCatalogService $catalogService)
    {
        return $catalogService->suggestions((string) $request->string('q'));
    }

    /**
     * GET /san-pham — danh sách tất cả sản phẩm, có thể kèm query string:
     * ?category=ao-thun&size=M&color=Đen&min_price=100000&max_price=500000
     * &material=cotton&sort=price_asc&q=basic
     */
    public function index(Request $request, ProductCatalogService $catalogService)
    {
        return view('products.index', $catalogService->webList($request));
    }

    public function sale(ProductCatalogService $catalogService)
    {
        return view('products.index', $catalogService->saleList());
    }

    /**
     * GET /danh-muc/{category:slug} — danh sách sản phẩm theo 1 danh mục cụ thể.
     */
    public function byCategory(Request $request, Category $category, ProductCatalogService $catalogService)
    {
        return view('products.index', $catalogService->webList($request, $category));
    }

    /**
     * GET /san-pham/{product:slug} — trang chi tiết 1 sản phẩm.
     * "{product:slug}" nghĩa là Laravel tự tìm Product theo cột slug (không phải id) trên URL.
     */
    public function show(Product $product, ProductCatalogService $catalogService)
    {
        return view('products.show', $catalogService->details($product));
    }

    /**
     * GET /san-pham/{product:slug}/kiem-tra-ton-kho?size=M&color=Đen
     * Trả về JSON — được gọi bằng JavaScript (fetch) từ trang chi tiết sản phẩm
     * mỗi khi khách chọn xong CẢ size và màu, để kiểm tra tồn kho mà KHÔNG cần tải lại trang.
     */
    public function checkStock(Request $request, Product $product, ProductVariantService $variantService)
    {
        $request->validate([
            'size' => ['required', 'string'],
            'color' => ['required', 'string'],
        ]);

        $variant = $variantService->find($product, $request->string('size')->toString(), $request->string('color')->toString());

        if (! $variant) {
            return response()->json([
                'found' => false,
                'message' => 'Không có sẵn tổ hợp size và màu này.',
            ], 404);
        }

        return response()->json(['found' => true, ...$variantService->stockData($variant)]);
    }
}
