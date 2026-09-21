# Khôi phục cơ sở dữ liệu

Toàn bộ cấu trúc bảng của ứng dụng được quản lý bằng các file trong `database/migrations`.
Sau khi tạo lại database MySQL rỗng và cập nhật thông tin kết nối trong `.env`, chạy:

```bash
php artisan migrate --seed
```

Nếu muốn dựng lại hoàn toàn trong môi trường phát triển (lệnh này xóa toàn bộ bảng hiện có):

```bash
php artisan migrate:fresh --seed
```

`--seed` sẽ tạo role, danh mục và sản phẩm mẫu. Migration `2026_09_21_130000_seed_demo_catalog_and_orders` cũng tạo dữ liệu demo mở rộng (voucher, địa chỉ, giỏ hàng và đơn mẫu) khi chạy trên database mới.

Trước khi xóa database thật, nên xuất một bản backup bằng phpMyAdmin hoặc `mysqldump`. Migration khôi phục cấu trúc và dữ liệu mẫu, nhưng không thể khôi phục các đơn hàng/dữ liệu người dùng phát sinh sau đó nếu không có file backup.
