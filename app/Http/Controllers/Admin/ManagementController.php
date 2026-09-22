<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManagementController extends Controller
{
    private function guard(): void { abort_unless(auth()->user()?->isAdmin(), 403); }

    public function products(Request $request)
    {
        $this->guard();
        $query = Product::with(['category', 'variants'])->withCount('reviews')->latest();
        if ($request->filled('q')) $query->where('name', 'like', '%'.$request->q.'%');
        if ($request->filled('category')) $query->where('category_id', $request->category);
        if ($request->filled('status')) $query->where('is_active', $request->status === 'active');
        return view('admin.products.index', ['products' => $query->paginate(15)->withQueryString(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function productCreate() { $this->guard(); return view('admin.products.form', ['product' => new Product, 'categories' => Category::orderBy('name')->get(), 'variants' => collect()]); }

    public function productStore(Request $request)
    {
        $this->guard(); $data = $this->validateProduct($request); $product = Product::create($data); $this->syncVariants($product, $request);
        return redirect()->route('admin.products.index')->with('success', 'Đã tạo sản phẩm thành công.');
    }

    public function productEdit(Product $product) { $this->guard(); return view('admin.products.form', ['product' => $product->load('variants'), 'categories' => Category::orderBy('name')->get(), 'variants' => $product->variants]); }

    public function productUpdate(Request $request, Product $product)
    {
        $this->guard(); $product->update($this->validateProduct($request, $product)); $this->syncVariants($product, $request);
        return redirect()->route('admin.products.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function productToggle(Product $product) { $this->guard(); $product->update(['is_active' => ! $product->is_active]); return back()->with('success', 'Đã cập nhật trạng thái sản phẩm.'); }
    public function productDestroy(Product $product) { $this->guard(); $product->delete(); return back()->with('success', 'Đã xóa sản phẩm.'); }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $data = $request->validate(['category_id' => 'required|exists:categories,id', 'name' => 'required|string|max:255', 'description' => 'nullable|string', 'material' => 'nullable|string|max:255', 'base_price' => 'required|integer|min:0', 'is_featured' => 'nullable|boolean', 'is_active' => 'nullable|boolean']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    private function syncVariants(Product $product, Request $request): void
    {
        $rows = $request->input('variants', []); $keep = [];
        foreach ($rows as $row) {
            if (blank($row['size'] ?? null) || blank($row['color'] ?? null) || blank($row['sku'] ?? null)) continue;
            $variant = $product->variants()->updateOrCreate(['id' => $row['id'] ?? null], ['size' => $row['size'], 'color' => $row['color'], 'color_hex' => $row['color_hex'] ?? null, 'sku' => strtoupper($row['sku']), 'price_override' => $row['price_override'] ?: null, 'stock_quantity' => max(0, (int) ($row['stock_quantity'] ?? 0))]);
            $keep[] = $variant->id;
        }
        if ($product->exists && count($rows)) $product->variants()->whereNotIn('id', $keep)->delete();
    }

    public function categories() { $this->guard(); return view('admin.categories.index', ['categories' => Category::withCount('products')->with('parent')->latest()->paginate(20)]); }
    public function categoryCreate() { $this->guard(); return view('admin.categories.form', ['category' => new Category, 'parents' => Category::whereNull('parent_id')->orderBy('name')->get()]); }
    public function categoryStore(Request $request) { $this->guard(); $data = $request->validate(['name' => 'required|max:120', 'parent_id' => 'nullable|exists:categories,id', 'description' => 'nullable|string', 'is_active' => 'nullable|boolean']); $data['is_active'] = $request->boolean('is_active'); Category::create($data); return redirect()->route('admin.categories.index')->with('success', 'Đã tạo danh mục.'); }
    public function categoryEdit(Category $category) { $this->guard(); return view('admin.categories.form', ['category' => $category, 'parents' => Category::whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('name')->get()]); }
    public function categoryUpdate(Request $request, Category $category) { $this->guard(); $data = $request->validate(['name' => 'required|max:120', 'parent_id' => 'nullable|exists:categories,id', 'description' => 'nullable|string', 'is_active' => 'nullable|boolean']); $data['is_active'] = $request->boolean('is_active'); $category->update($data); return redirect()->route('admin.categories.index')->with('success', 'Đã cập nhật danh mục.'); }
    public function categoryToggle(Category $category) { $this->guard(); $category->update(['is_active' => ! $category->is_active]); return back()->with('success', 'Đã cập nhật trạng thái danh mục.'); }
    public function categoryDestroy(Category $category) { $this->guard(); if ($category->products()->exists()) return back()->with('error', 'Không thể xóa danh mục đang có sản phẩm.'); $category->delete(); return back()->with('success', 'Đã xóa danh mục.'); }

    public function orders(Request $request) { $this->guard(); $query = Order::with('user')->latest(); if ($request->filled('q')) $query->where(fn ($q) => $q->where('order_code', 'like', '%'.$request->q.'%')->orWhere('recipient_name', 'like', '%'.$request->q.'%')); if ($request->filled('status')) $query->where('order_status', $request->status); return view('admin.orders.index', ['orders' => $query->paginate(20)->withQueryString()]); }
    public function orderShow(Order $order) { $this->guard(); return view('admin.orders.show', ['order' => $order->load(['user', 'items.variant', 'payments'])]); }
    public function orderStatus(Request $request, Order $order) { $this->guard(); $data = $request->validate(['order_status' => 'required|in:pending,confirmed,processing,shipping,completed,cancelled', 'payment_status' => 'required|in:unpaid,pending,paid,failed,refunded']); $order->update($data); return back()->with('success', 'Đã cập nhật đơn hàng '.$order->order_code.'.'); }

    public function customers(Request $request) { $this->guard(); $query = User::with('role')->withCount('orders')->latest(); if ($request->filled('q')) $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('email', 'like', '%'.$request->q.'%')); return view('admin.customers.index', ['customers' => $query->paginate(20)->withQueryString()]); }
}
