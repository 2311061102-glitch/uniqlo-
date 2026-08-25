<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Bắt buộc gõ lại mật khẩu hiện tại trước khi xóa — tránh trường hợp
            // ai đó ngồi cạnh máy đang đăng nhập sẵn của bạn bấm nhầm/cố ý xóa tài khoản.
            'current_password' => ['required', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Vui lòng nhập mật khẩu để xác nhận.',
            'current_password.current_password' => 'Mật khẩu không đúng.',
        ];
    }
}
