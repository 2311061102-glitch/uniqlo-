<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    // RefreshDatabase: tự tạo lại database RỖNG trước MỖI test, đảm bảo test này
    // không bị ảnh hưởng bởi dữ liệu để lại từ test khác chạy trước đó.
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Đăng ký cần có sẵn role "customer" để gán cho user mới -> seed trước mỗi test
        $this->seed(RoleSeeder::class);
    }

    public function test_trang_dang_ky_hien_thi_duoc(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    public function test_dang_ky_thanh_cong_voi_du_lieu_hop_le(): void
    {
        // Notification::fake(): chặn KHÔNG gửi email thật khi chạy test (test chạy nhanh hơn,
        // không cần internet, không làm đầy hộp thư Mailtrap) — chỉ kiểm tra CÓ được gọi gửi hay không.
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'name' => 'Nguyen Van A',
            'email' => 'a@example.com',
            'phone' => '0912345678',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', ['email' => 'a@example.com']);

        $user = User::where('email', 'a@example.com')->firstOrFail();

        $this->assertSame('customer', $user->role->name);
        $this->assertNotEquals('Password123', $user->password); // phải đã hash, không lưu thô
        $this->assertAuthenticatedAs($user); // đăng ký xong phải tự động đăng nhập

        Notification::assertSentTo($user, VerifyEmail::class); // phải có gửi email xác thực
    }

    public function test_khong_the_dang_ky_voi_email_da_ton_tai(): void
    {
        User::factory()->create(['email' => 'a@example.com']);

        $response = $this->post(route('register.store'), [
            'name' => 'Nguyen Van B',
            'email' => 'a@example.com',
            'phone' => '0912345678',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_khong_the_dang_ky_voi_mat_khau_yeu(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Nguyen Van C',
            'email' => 'c@example.com',
            'phone' => '0912345678',
            'password' => '123456', // không hoa, không đủ mạnh
            'password_confirmation' => '123456',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_khong_the_dang_ky_voi_so_dien_thoai_sai_dinh_dang(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Nguyen Van D',
            'email' => 'd@example.com',
            'phone' => '123',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertSessionHasErrors('phone');
    }
}
