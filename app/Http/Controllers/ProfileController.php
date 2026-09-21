<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Hiện trang thông tin cá nhân của user đang đăng nhập.
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Cập nhật họ tên, số điện thoại, avatar.
     */
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            // Xóa ảnh avatar cũ trước (nếu có) để tránh tích rác file trên server
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Lưu ảnh mới vào storage/app/public/avatars, trả về đường dẫn dạng "avatars/ten-file.jpg"
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Hiện trang đổi mật khẩu.
     */
    public function editPassword()
    {
        return view('profile.change-password');
    }

    /**
     * Xử lý đổi mật khẩu (khi đã đăng nhập, khác với "quên mật khẩu" ở Giai đoạn 3).
     */
    public function updatePassword(ChangePasswordRequest $request)
    {
        $request->user()->update([
            'password' => $request->validated()['password'], // Model User đã cast 'hashed' -> tự bcrypt
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
}
