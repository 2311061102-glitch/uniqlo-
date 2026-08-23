<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * true vì route này đã được bọc bởi middleware 'auth' rồi (xem routes/web.php),
     * nên chắc chắn chỉ user đã đăng nhập mới gọi tới đây được.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^(0[3|5|7|8|9])+([0-9]{8})$/'],
            // nullable: cho phép không chọn ảnh mới (giữ nguyên avatar cũ)
            'avatar' => ['nullable', 'image', 'max:2048'], // tối đa 2MB
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không hợp lệ (VD: 0912345678).',
            'avatar.image' => 'File phải là hình ảnh (jpg, png...).',
            'avatar.max' => 'Ảnh không được vượt quá 2MB.',
        ];
    }
}
