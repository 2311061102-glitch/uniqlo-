<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_gui_duoc_email_dat_lai_mat_khau(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_khong_gui_email_neu_khong_ton_tai_tai_khoan(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), ['email' => 'khong-ton-tai@example.com']);

        $response->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    }

    public function test_dat_lai_mat_khau_thanh_cong_voi_token_dung(): void
    {
        $user = User::factory()->create();

        // Tự tạo 1 token hợp lệ y hệt cách hệ thống thật tạo ra khi gửi email
        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'MatKhauMoi123',
            'password_confirmation' => 'MatKhauMoi123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('MatKhauMoi123', $user->fresh()->password));
    }

    public function test_khong_the_dat_lai_mat_khau_voi_token_sai(): void
    {
        $user = User::factory()->create(['password' => 'MatKhauCu123']);

        $response = $this->post(route('password.update'), [
            'token' => 'token-gia-mao-khong-hop-le',
            'email' => $user->email,
            'password' => 'MatKhauMoi123',
            'password_confirmation' => 'MatKhauMoi123',
        ]);

        $response->assertSessionHasErrors('email');

        // Mật khẩu KHÔNG được thay đổi vì token sai
        $this->assertTrue(Hash::check('MatKhauCu123', $user->fresh()->password));
    }
}
