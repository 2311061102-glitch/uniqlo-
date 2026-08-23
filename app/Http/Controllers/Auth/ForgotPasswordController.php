<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Hiện form nhập email để gửi link đặt lại mật khẩu.
     */
    public function show()
    {
        return view('auth.forgot-password');
    }

    /**
     * Gửi email chứa link đặt lại mật khẩu.
     *
     * Password::sendResetLink() là hàm có sẵn của Laravel:
     * - Tự tạo 1 token ngẫu nhiên, lưu vào bảng password_reset_tokens
     * - Tự gửi email cho user (dùng cấu hình SMTP trong .env)
     * - Không cần tự viết code gửi mail thủ công
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Đã gửi email đặt lại mật khẩu. Vui lòng kiểm tra hộp thư.');
        }

        return back()->withErrors(['email' => 'Không tìm thấy tài khoản với email này.']);
    }
}
