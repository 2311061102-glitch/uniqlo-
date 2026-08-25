<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_xoa_tai_khoan_that_bai_neu_sai_mat_khau(): void
    {
        $user = User::factory()->create(['password' => 'MatKhau123']);

        $response = $this->actingAs($user)->delete(route('profile.destroy'), [
            'current_password' => 'SaiMatKhau',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertNotSoftDeleted($user);
    }

    public function test_xoa_tai_khoan_thanh_cong(): void
    {
        $user = User::factory()->create(['password' => 'MatKhau123']);

        $response = $this->actingAs($user)->delete(route('profile.destroy'), [
            'current_password' => 'MatKhau123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertSoftDeleted($user); // vẫn còn trong DB, chỉ có deleted_at khác null
        $this->assertGuest(); // phải tự động đăng xuất
    }

    public function test_tai_khoan_da_xoa_khong_the_dang_nhap_lai(): void
    {
        $user = User::factory()->create(['password' => 'MatKhau123']);
        $user->delete();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'MatKhau123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
