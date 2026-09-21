<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $now = now();

            foreach ([
                ['name' => 'admin', 'display_name' => 'Quản trị viên'],
                ['name' => 'staff', 'display_name' => 'Nhân viên'],
                ['name' => 'customer', 'display_name' => 'Khách hàng'],
            ] as $role) {
                DB::table('roles')->updateOrInsert(['name' => $role['name']], $role + ['created_at' => $now, 'updated_at' => $now]);
            }

            $roleIds = DB::table('roles')->pluck('id', 'name');
            $users = [
                ['email' => 'demo.admin@example.test', 'name' => 'Demo Quản trị viên', 'phone' => '0901000001', 'role_id' => $roleIds['admin']],
                ['email' => 'demo.staff@example.test', 'name' => 'Demo Nhân viên', 'phone' => '0901000002', 'role_id' => $roleIds['staff']],
            ];
            for ($i = 1; $i <= 12; $i++) {
                $users[] = [
                    'email' => sprintf('demo.customer%02d@example.test', $i),
                    'name' => 'Khách hàng mẫu '.$i,
                    'phone' => '091'.str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                    'role_id' => $roleIds['customer'],
                ];
            }
            foreach ($users as $user) {
                DB::table('users')->updateOrInsert(
                    ['email' => $user['email']],
                    $user + ['password' => Hash::make('Demo@123456'), 'email_verified_at' => $now, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $categories = [];
            foreach ([
                ['ao-thun', 'Áo thun'], ['ao-so-mi', 'Áo sơ mi'], ['ao-khoac', 'Áo khoác'],
                ['quan-jean', 'Quần jean'], ['quan-kaki', 'Quần kaki'], ['quan-short', 'Quần short'],
                ['do-mac-nha', 'Đồ mặc nhà'], ['phu-kien', 'Phụ kiện'],
            ] as [$slug, $name]) {
                $slug = 'demo-'.$slug;
                DB::table('categories')->updateOrInsert(
                    ['slug' => $slug],
                    ['name' => $name.' mẫu', 'slug' => $slug, 'description' => 'Danh mục dữ liệu mẫu để kiểm thử website.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
                $categories[] = DB::table('categories')->where('slug', $slug)->value('id');
            }

            $sizes = ['S', 'M', 'L', 'XL'];
            $colors = [['Đen', '#111111'], ['Trắng', '#ffffff'], ['Xanh navy', '#243b64']];
            $variants = [];
            foreach ($categories as $categoryIndex => $categoryId) {
                for ($productIndex = 1; $productIndex <= 4; $productIndex++) {
                    $code = sprintf('DEMO-%02d-%02d', $categoryIndex + 1, $productIndex);
                    $slug = strtolower($code);
                    DB::table('products')->updateOrInsert(
                        ['slug' => $slug],
                        [
                            'category_id' => $categoryId,
                            'name' => 'Sản phẩm mẫu '.$code,
                            'slug' => $slug,
                            'description' => 'Sản phẩm mẫu dùng để kiểm thử danh sách, tìm kiếm, chi tiết và giỏ hàng.',
                            'material' => $productIndex % 2 ? 'Cotton 100%' : 'Polyester cao cấp',
                            'base_price' => 199000 + ($categoryIndex * 70000) + ($productIndex * 25000),
                            'is_featured' => $productIndex === 1,
                            'is_active' => true,
                            'sold_count' => $productIndex * 13,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                    $productId = DB::table('products')->where('slug', $slug)->value('id');
                    DB::table('product_images')->updateOrInsert(
                        ['product_id' => $productId, 'image_path' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=900&q=80'],
                        ['is_primary' => true, 'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now]
                    );
                    foreach ($sizes as $sizeIndex => $size) {
                        foreach ($colors as $colorIndex => [$color, $hex]) {
                            $sku = $code.'-'.$size.'-'.($colorIndex + 1);
                            DB::table('product_variants')->updateOrInsert(
                                ['sku' => $sku],
                                ['product_id' => $productId, 'size' => $size, 'color' => $color, 'color_hex' => $hex, 'price_override' => null, 'stock_quantity' => 10 + $sizeIndex + $colorIndex, 'created_at' => $now, 'updated_at' => $now]
                            );
                            $variants[] = DB::table('product_variants')->where('sku', $sku)->value('id');
                        }
                    }
                }
            }

            $customerIds = DB::table('users')->where('email', 'like', 'demo.customer%@example.test')->pluck('id');
            $addressIds = [];
            foreach ($customerIds as $index => $userId) {
                $address = [
                    'user_id' => $userId, 'recipient_name' => 'Khách mẫu '.($index + 1), 'phone' => '091'.str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT),
                    'province' => $index % 2 ? 'Hà Nội' : 'Thành phố Hồ Chí Minh', 'district' => $index % 2 ? 'Cầu Giấy' : 'Quận 1',
                    'ward' => $index % 2 ? 'Dịch Vọng' : 'Bến Nghé', 'address_detail' => ($index + 1).' Đường Mẫu', 'is_default' => true,
                    'created_at' => $now, 'updated_at' => $now,
                ];
                $existing = DB::table('addresses')->where('user_id', $userId)->where('address_detail', $address['address_detail'])->first();
                if ($existing) {
                    $addressIds[] = $existing->id;
                } else {
                    $addressIds[] = DB::table('addresses')->insertGetId($address);
                }
            }

            foreach (array_values($customerIds->all()) as $index => $userId) {
                $cartId = DB::table('carts')->where('user_id', $userId)->value('id');
                if (! $cartId) {
                    $cartId = DB::table('carts')->insertGetId(['user_id' => $userId, 'created_at' => $now, 'updated_at' => $now]);
                }
                foreach (array_slice($variants, ($index * 3) % max(count($variants), 1), 3) as $variantId) {
                    DB::table('cart_items')->updateOrInsert(['cart_id' => $cartId, 'product_variant_id' => $variantId], ['quantity' => ($index % 3) + 1, 'created_at' => $now, 'updated_at' => $now]);
                }
            }

            foreach (array_slice($customerIds->all(), 0, 10) as $index => $userId) {
                foreach (array_slice($variants, $index, 3) as $variantId) {
                    $productId = DB::table('product_variants')->where('id', $variantId)->value('product_id');
                    DB::table('wishlists')->insertOrIgnore(['user_id' => $userId, 'product_id' => $productId, 'created_at' => $now, 'updated_at' => $now]);
                    DB::table('reviews')->insertOrIgnore(['user_id' => $userId, 'product_id' => $productId, 'rating' => 3 + ($index % 3), 'comment' => 'Sản phẩm mẫu dùng rất ổn, giao hàng nhanh.', 'created_at' => $now, 'updated_at' => $now]);
                }
            }

            foreach (range(1, 20) as $index) {
                $code = sprintf('DEMO%02d', $index);
                DB::table('vouchers')->updateOrInsert(
                    ['code' => $code],
                    ['type' => $index % 2 ? 'percent' : 'fixed', 'value' => $index % 2 ? 10 + ($index % 4) * 5 : 30000 + $index * 5000, 'min_order_amount' => 300000, 'max_discount_amount' => $index % 2 ? 100000 : null, 'usage_limit' => 100, 'used_count' => $index % 5, 'start_date' => now()->toDateString(), 'end_date' => now()->addMonths(3)->toDateString(), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            foreach (array_slice(array_values($customerIds->all()), 0, 10) as $index => $userId) {
                $orderCode = sprintf('DEMO-ORDER-%03d', $index + 1);
                $variantId = $variants[$index % count($variants)];
                $product = DB::table('products')->join('product_variants', 'products.id', '=', 'product_variants.product_id')->where('product_variants.id', $variantId)->select('products.name', 'products.id as product_id', 'product_variants.size', 'product_variants.color', 'product_variants.price_override', 'products.base_price')->first();
                $price = $product->price_override ?: $product->base_price;
                $quantity = ($index % 3) + 1;
                $subtotal = $price * $quantity;
                $shipping = 30000;
                $order = [
                    'order_code' => $orderCode, 'user_id' => $userId, 'voucher_id' => DB::table('vouchers')->where('code', 'DEMO'.str_pad((string) (($index % 20) + 1), 2, '0', STR_PAD_LEFT))->value('id'), 'address_id' => $addressIds[$index],
                    'recipient_name' => 'Khách mẫu '.($index + 1), 'recipient_phone' => '091'.str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT), 'province' => 'Hà Nội', 'district' => 'Cầu Giấy', 'ward' => 'Dịch Vọng', 'address_detail' => ($index + 1).' Đường Mẫu',
                    'subtotal_amount' => $subtotal, 'shipping_fee' => $shipping, 'discount_amount' => 0, 'total_amount' => $subtotal + $shipping, 'payment_method' => ['cod', 'vietqr', 'vnpay'][$index % 3], 'payment_status' => $index < 5 ? 'paid' : 'pending', 'order_status' => ['completed', 'shipping', 'confirmed', 'pending'][$index % 4], 'created_at' => $now->copy()->subDays($index), 'updated_at' => $now,
                ];
                DB::table('orders')->updateOrInsert(['order_code' => $orderCode], $order);
                $orderId = DB::table('orders')->where('order_code', $orderCode)->value('id');
                DB::table('order_items')->where('order_id', $orderId)->delete();
                DB::table('order_items')->insert(['order_id' => $orderId, 'product_variant_id' => $variantId, 'product_name' => $product->name, 'variant_label' => 'Size '.$product->size.' - '.$product->color, 'price' => $price, 'quantity' => $quantity, 'subtotal' => $subtotal, 'created_at' => $now, 'updated_at' => $now]);
                DB::table('payments')->updateOrInsert(['order_id' => $orderId, 'payment_method' => $order['payment_method']], ['amount' => $order['total_amount'], 'status' => $index < 5 ? 'success' : 'pending', 'transaction_code' => $index < 5 ? 'DEMO-TXN-'.$index : null, 'paid_at' => $index < 5 ? $now : null, 'created_at' => $now, 'updated_at' => $now]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $userIds = DB::table('users')->where('email', 'like', 'demo.%@example.test')->pluck('id');
            $orderIds = DB::table('orders')->where('order_code', 'like', 'DEMO-ORDER-%')->pluck('id');
            DB::table('payments')->whereIn('order_id', $orderIds)->delete();
            DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
            DB::table('orders')->whereIn('id', $orderIds)->delete();
            DB::table('reviews')->whereIn('user_id', $userIds)->delete();
            DB::table('wishlists')->whereIn('user_id', $userIds)->delete();
            DB::table('cart_items')->whereIn('cart_id', DB::table('carts')->whereIn('user_id', $userIds)->pluck('id'))->delete();
            DB::table('carts')->whereIn('user_id', $userIds)->delete();
            DB::table('addresses')->whereIn('user_id', $userIds)->delete();
            DB::table('users')->whereIn('id', $userIds)->delete();
            DB::table('vouchers')->where('code', 'like', 'DEMO%')->delete();
            $productIds = DB::table('products')->where('slug', 'like', 'demo-%')->pluck('id');
            DB::table('product_images')->whereIn('product_id', $productIds)->delete();
            DB::table('cart_items')->whereIn('product_variant_id', DB::table('product_variants')->whereIn('product_id', $productIds)->pluck('id'))->delete();
            DB::table('product_variants')->whereIn('product_id', $productIds)->delete();
            DB::table('wishlists')->whereIn('product_id', $productIds)->delete();
            DB::table('reviews')->whereIn('product_id', $productIds)->delete();
            DB::table('products')->whereIn('id', $productIds)->delete();
            DB::table('categories')->where('slug', 'like', 'demo-%')->delete();
        });
    }
};
