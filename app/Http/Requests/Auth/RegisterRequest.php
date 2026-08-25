<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Ai cũng được phép gọi request này vì đây là trang đăng ký công khai.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validate — Laravel tự động check các quy tắc này TRƯỚC KHI
     * chạy vào hàm register() trong Controller. Nếu sai, tự động quay lại
     * form kèm thông báo lỗi, Controller không cần tự viết if/else kiểm tra.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

            // Regex số điện thoại Việt Nam: bắt đầu 03/05/07/08/09, tổng 10 số
            'phone' => ['required', 'string', 'regex:/^(0[3|5|7|8|9])+([0-9]{8})$/'],

            // Mật khẩu mạnh: tối thiểu 8 ký tự, có chữ hoa+thường, có số
            'password' => [
                'required',
                'confirmed', // bắt buộc có field password_confirmation khớp giá trị
                Password::min(8)->max(20)->mixedCase()->numbers(),
            ],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt tùy chỉnh cho từng rule ở trên.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được đăng ký, vui lòng dùng email khác.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không hợp lệ (VD: 0912345678).',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ];
    }
}
