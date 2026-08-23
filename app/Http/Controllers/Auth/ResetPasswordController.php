<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password as PasswordBroker; // xử lý logic reset (tránh trùng tên với rule Password bên dưới)
use Illuminate\Validation\Rules\Password; // dùng để validate độ mạnh mật khẩu mới

class ResetPasswordController extends Controller
{
    /**
     * Hiện form đặt mật khẩu mới. $token lấy từ link trong email.
     * $request->email lấy từ query string (?email=...) mà Laravel tự đính kèm vào link.
     */
    public function show(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Xử lý đặt mật khẩu mới.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // PasswordBroker::reset kiểm tra token có đúng/còn hạn không (mặc định 60 phút),
        // nếu đúng thì chạy callback bên dưới để cập nhật mật khẩu mới.
        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password, // Model User đã cast 'hashed' -> tự động bcrypt
                ])->save();
            }
        );

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Đặt lại mật khẩu thành công, vui lòng đăng nhập.');
        }

        return back()->withErrors(['email' => 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.']);
    }
}
