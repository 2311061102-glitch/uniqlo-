<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_dang_nhap_thanh_cong_voi_dung_thong_tin(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_dang_nhap_that_bai_voi_sai_mat_khau(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'SaiMatKhau',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_bi_khoa_tam_thoi_sau_5_lan_dang_nhap_sai(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);

        // Cố tình đăng nhập sai đúng 5 lần
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'SaiMatKhau',
            ]);
        }

        // Lần thứ 6: dù gõ ĐÚNG mật khẩu vẫn phải bị chặn vì đã vượt ngưỡng 5 lần thử sai
        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_dang_nhap_sai_o_email_nay_khong_lam_khoa_email_khac(): void
    {
        $userA = User::factory()->create(['email' => 'a@example.com', 'password' => 'Password123']);
        $userB = User::factory()->create(['email' => 'b@example.com', 'password' => 'Password123']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), [
                'email' => $userA->email,
                'password' => 'SaiMatKhau',
            ]);
        }

        // userB đăng nhập đúng ngay lần đầu -> KHÔNG được bị ảnh hưởng bởi việc userA bị khóa
        $response = $this->post(route('login.store'), [
            'email' => $userB->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($userB);
    }

    public function test_dang_xuat_thanh_cong(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
