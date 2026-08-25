<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    private function validAddressData(array $overrides = []): array
    {
        return array_merge([
            'recipient_name' => 'Nguyen Van A',
            'phone' => '0912345678',
            'province' => 'Ha Noi',
            'district' => 'Cau Giay',
            'ward' => 'Dich Vong',
            'address_detail' => 'So 1 Duong ABC',
        ], $overrides);
    }

    public function test_them_dia_chi_dau_tien_tu_dong_thanh_mac_dinh(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('addresses.store'), $this->validAddressData());

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'is_default' => true,
        ]);
    }

    public function test_dat_dia_chi_moi_lam_mac_dinh_se_bo_mac_dinh_dia_chi_cu(): void
    {
        $user = User::factory()->create();

        $first = $user->addresses()->create($this->validAddressData(['is_default' => true]));

        $this->actingAs($user)->post(
            route('addresses.store'),
            $this->validAddressData(['is_default' => 1])
        );

        $this->assertFalse((bool) $first->fresh()->is_default);
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());
    }

    /**
     * ĐÂY LÀ TEST BẢO MẬT QUAN TRỌNG NHẤT của toàn bộ phần Thành viên 1:
     * chứng minh 1 user KHÔNG THỂ sửa địa chỉ của user khác dù đoán đúng ID trên URL.
     */
    public function test_khong_the_sua_dia_chi_cua_nguoi_khac(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $address = $owner->addresses()->create($this->validAddressData());

        $response = $this->actingAs($attacker)->get(route('addresses.edit', $address));

        $response->assertStatus(403);
    }

    public function test_khong_the_xoa_dia_chi_cua_nguoi_khac(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $address = $owner->addresses()->create($this->validAddressData());

        $response = $this->actingAs($attacker)->delete(route('addresses.destroy', $address));

        $response->assertStatus(403);
        $this->assertDatabaseHas('addresses', ['id' => $address->id]); // vẫn còn, chưa bị xóa
    }

    public function test_xoa_dia_chi_mac_dinh_tu_dong_gan_mac_dinh_cho_dia_chi_con_lai(): void
    {
        $user = User::factory()->create();

        $first = $user->addresses()->create($this->validAddressData(['is_default' => true]));
        $second = $user->addresses()->create($this->validAddressData(['is_default' => false]));

        $this->actingAs($user)->delete(route('addresses.destroy', $first));

        $this->assertTrue((bool) $second->fresh()->is_default);
    }
}
