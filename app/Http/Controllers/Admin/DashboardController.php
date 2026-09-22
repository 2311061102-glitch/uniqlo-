<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Bạn không có quyền truy cập khu vực quản trị.');

        $monthStart = now()->startOfMonth();
        $revenueThisMonth = (int) Order::where('order_status', 'completed')->whereBetween('created_at', [$monthStart, now()])->sum('total_amount');
        $revenueLastMonth = (int) Order::where('order_status', 'completed')->whereBetween('created_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->sum('total_amount');

        $stats = [
            ['label' => 'Doanh thu tháng này', 'value' => number_format($revenueThisMonth, 0, ',', '.') . '₫', 'trend' => $this->trend($revenueThisMonth, $revenueLastMonth), 'icon' => '₫', 'tone' => 'red'],
            ['label' => 'Đơn hàng hôm nay', 'value' => Order::whereDate('created_at', today())->count(), 'trend' => Order::whereDate('created_at', today())->where('order_status', 'pending')->count() . ' đơn chờ xử lý', 'icon' => '▣', 'tone' => 'blue'],
            ['label' => 'Khách hàng mới', 'value' => User::whereDate('created_at', today())->count(), 'trend' => User::where('created_at', '>=', $monthStart)->count() . ' khách trong tháng', 'icon' => '♙', 'tone' => 'green'],
            ['label' => 'Sản phẩm đang bán', 'value' => Product::where('is_active', true)->count(), 'trend' => Product::where('is_active', false)->count() . ' sản phẩm đang ẩn', 'icon' => '◈', 'tone' => 'orange'],
        ];

        $recentOrders = Order::with('user')->latest()->take(6)->get();
        $lowStockProducts = Product::with(['category', 'variants'])->where('is_active', true)->get()
            ->filter(fn (Product $product) => $product->variants->sum('stock_quantity') <= 10)
            ->sortBy(fn (Product $product) => $product->variants->sum('stock_quantity'))->take(5);
        $orderStatusCounts = Order::select('order_status', DB::raw('count(*) as total'))->groupBy('order_status')->pluck('total', 'order_status');

        return view('admin.dashboard', compact('stats', 'recentOrders', 'lowStockProducts', 'orderStatusCounts'));
    }

    private function trend(int $current, int $previous): string
    {
        if ($previous === 0) return $current > 0 ? 'Có doanh thu trong tháng' : 'Chưa có doanh thu';
        $percent = round((($current - $previous) / $previous) * 100);
        return ($percent >= 0 ? '+' : '') . $percent . '% so với tháng trước';
    }
}
