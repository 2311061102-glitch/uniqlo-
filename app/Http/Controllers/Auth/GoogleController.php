<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use App\Services\CartService;

class GoogleController extends Controller
{
    public function redirect()
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->with('error', 'Đăng nhập Google chưa được cấu hình. Vui lòng dùng email và mật khẩu.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            // Một số trình duyệt chặn/đổi cookie session khi quay về từ Google.
            // Thử lấy user không state để tránh màn hình 500; callback URL vẫn phải
            // khớp GOOGLE_REDIRECT_URI trong Google Cloud Console.
            $googleUser = Socialite::driver('google')->stateless()->user();
        }
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User',
                'email' => $googleUser->getEmail(),
                'password' => Str::random(40),
                'avatar' => $googleUser->getAvatar(),
                'role_id' => Role::where('name', 'customer')->value('id'),
                'google_id' => $googleUser->getId(),
            ]);
        } else {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $user->email_verified_at ?: now(),
                'avatar' => $user->avatar ?: $googleUser->getAvatar(),
            ])->save();
        }

        Auth::login($user, true);
        request()->session()->regenerate();
        CartService::mergeGuestCartIntoUser($user);

        return redirect()->intended(route('home'));
    }
}
