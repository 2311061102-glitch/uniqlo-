<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * true vì đã có middleware 'auth' chặn ở routes, và Controller sẽ tự kiểm tra
     * riêng việc user có phải chủ sở hữu địa chỉ này không (xem authorizeOwner()).
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^(0[3|5|7|8|9])+([0-9]{8})$/'],
            'province' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'ward' => ['required', 'string', 'max:255'],
            'address_detail' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Vui lòng nhập tên người nhận.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không hợp lệ (VD: 0912345678).',
            'province.required' => 'Vui lòng nhập Tỉnh/Thành phố.',
            'district.required' => 'Vui lòng nhập Quận/Huyện.',
            'ward.required' => 'Vui lòng nhập Phường/Xã.',
            'address_detail.required' => 'Vui lòng nhập địa chỉ cụ thể (số nhà, tên đường).',
        ];
    }
}
