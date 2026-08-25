<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_moi_dang_ky_chua_xac_thuc_email(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertFalse($user->hasVerifiedEmail());
    }

    public function test_xac_thuc_thanh_cong_voi_link_dung_chu_ky(): void
    {
        Event::fake();

        $user = User::factory()->unverified()->create();

        // Tự tạo 1 "signed URL" hợp lệ, y hệt link thật mà email xác thực sẽ gửi
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('home'));
    }

    public function test_khong_xac_thuc_duoc_voi_link_khong_co_chu_ky_hop_le(): void
    {
        $user = User::factory()->unverified()->create();

        // Gọi thẳng route mà KHÔNG qua URL::temporarySignedRoute() -> thiếu chữ ký hợp lệ
        $invalidUrl = route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]);

        $response = $this->actingAs($user)->get($invalidUrl);

        $response->assertStatus(403);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
