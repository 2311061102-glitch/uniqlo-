<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_khach_chua_dang_nhap_bi_chuyen_ve_trang_dang_nhap(): void
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_cap_nhat_ten_va_so_dien_thoai_thanh_cong(): void
    {
        $user = User::factory()->create(['name' => 'Ten Cu', 'phone' => '0911111111']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Ten Moi',
            'phone' => '0922222222',
        ]);

        $response->assertRedirect();
        $this->assertSame('Ten Moi', $user->fresh()->name);
        $this->assertSame('0922222222', $user->fresh()->phone);
    }

    public function test_upload_avatar_thanh_cong(): void
    {
        // Storage::fake(): tạo ổ đĩa GIẢ chỉ tồn tại trong lúc test, không tạo file thật
        // trong storage/app/public — test chạy xong tự dọn sạch, không để lại rác.
        Storage::fake('public');

        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'phone' => '0933333333',
            'avatar' => $avatar,
        ]);

        $response->assertRedirect();
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_khong_the_cap_nhat_neu_thieu_ho_ten(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => '',
            'phone' => '0933333333',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_doi_mat_khau_that_bai_neu_sai_mat_khau_hien_tai(): void
    {
        $user = User::factory()->create(['password' => 'MatKhauCu123']);

        $response = $this->actingAs($user)->put(route('password.change'), [
            'current_password' => 'SaiMatKhau',
            'password' => 'MatKhauMoi123',
            'password_confirmation' => 'MatKhauMoi123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('MatKhauCu123', $user->fresh()->password)); // mật khẩu KHÔNG đổi
    }

    public function test_doi_mat_khau_thanh_cong(): void
    {
        $user = User::factory()->create(['password' => 'MatKhauCu123']);

        $response = $this->actingAs($user)->put(route('password.change'), [
            'current_password' => 'MatKhauCu123',
            'password' => 'MatKhauMoi123',
            'password_confirmation' => 'MatKhauMoi123',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('MatKhauMoi123', $user->fresh()->password));
    }
}
