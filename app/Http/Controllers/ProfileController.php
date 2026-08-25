<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function editPassword()
    {
        return view('profile.change-password');
    }

    public function updatePassword(ChangePasswordRequest $request)
    {
        $request->user()->update([
            'password' => $request->validated()['password'],
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    /**
     * Tự xóa tài khoản (soft delete — xem migration + Model User Giai đoạn 9).
     * Thứ tự quan trọng: LẤY user ra biến trước, ĐĂNG XUẤT, rồi mới XÓA —
     * nếu xóa trước rồi mới đăng xuất, có thể gặp lỗi vì session vẫn đang
     * trỏ tới 1 user vừa bị coi như "không tồn tại" (do global scope SoftDeletes).
     */
    public function destroy(DeleteAccountRequest $request)
    {
        $user = $request->user();

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('home')->with('success', 'Tài khoản của bạn đã được xóa.');
    }
}
