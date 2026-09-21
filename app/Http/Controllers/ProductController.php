<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /san-pham — danh sách tất cả sản phẩm, có thể kèm query string:
     * ?category=ao-thun&size=M&color=Đen&min_price=100000&max_price=500000
     * &material=cotton&sort=price_asc&q=basic
     */
    public function index(Request $request)
    {
        return $this->renderList($request);
    }

    /**
     * GET /danh-muc/{category:slug} — danh sách sản phẩm theo 1 danh mục cụ thể.
     */
    public function byCategory(Request $request, Category $category)
    {
        return $this->renderList($request, $category);
    }

    /**
     * Hàm dùng chung cho cả 2 route ở trên, tránh viết trùng code lọc/sắp xếp 2 lần.
     */
    private function renderList(Request $request, ?Category $category = null)
    {
        $query = Product::query()->active()->with(['images', 'variants', 'reviews']);

        if ($category) {
            $query->where('category_id', $category->id);
        } elseif ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('size')) {
            $query->whereHas('variants', fn ($q) => $q->where('size', $request->size));
        }

        if ($request->filled('color')) {
            $query->whereHas('variants', fn ($q) => $q->where('color', $request->color));
        }

        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', (int) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', (int) $request->max_price);
        }

        if ($request->filled('material')) {
            $query->where('material', 'like', '%'.$request->material.'%');
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%');
        }

        match ($request->input('sort')) {
            'price_asc' => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'best_selling' => $query->orderByDesc('sold_count'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::where('is_active', true)->whereNull('parent_id')->get(),
            'sizes' => ProductVariant::query()->select('size')->distinct()->pluck('size'),
            'colors' => ProductVariant::query()->select('color', 'color_hex')->distinct()->get(),
            'currentCategory' => $category,
        ]);
    }

    /**
     * GET /san-pham/{product:slug} — trang chi tiết 1 sản phẩm.
     * "{product:slug}" nghĩa là Laravel tự tìm Product theo cột slug (không phải id) trên URL.
     */
    public function show(Product $product)
    {
        $product->load(['images', 'variants', 'category', 'reviews.user']);

        // Danh sách size/màu DUY NHẤT của sản phẩm này (dùng để hiện nút chọn ngoài giao diện)
        $sizes = $product->variants->pluck('size')->unique()->values();
        $colors = $product->variants->unique('color')->values();

        return view('products.show', compact('product', 'sizes', 'colors'));
    }

    /**
     * GET /san-pham/{product:slug}/kiem-tra-ton-kho?size=M&color=Đen
     * Trả về JSON — được gọi bằng JavaScript (fetch) từ trang chi tiết sản phẩm
     * mỗi khi khách chọn xong CẢ size và màu, để kiểm tra tồn kho mà KHÔNG cần tải lại trang.
     */
    public function checkStock(Request $request, Product $product)
    {
        $request->validate([
            'size' => ['required', 'string'],
            'color' => ['required', 'string'],
        ]);

        $variant = $product->variants()
            ->where('size', $request->size)
            ->where('color', $request->color)
            ->first();

        if (! $variant) {
            return response()->json([
                'found' => false,
                'message' => 'Không có sẵn tổ hợp size và màu này.',
            ], 404);
        }

        return response()->json([
            'found' => true,
            'variant_id' => $variant->id,
            'sku' => $variant->sku,
            'in_stock' => $variant->in_stock,
            'stock_quantity' => $variant->stock_quantity,
            'price' => $variant->final_price,
        ]);
    }
}
