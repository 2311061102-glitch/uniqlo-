<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Hiện form đăng ký.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Xử lý dữ liệu đăng ký được gửi lên.
     * RegisterRequest $request: Laravel tự validate trước khi vào hàm này,
     * nên trong này không cần viết lại if/else kiểm tra dữ liệu nữa.
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        // Mọi tài khoản tự đăng ký đều mặc định là "customer" (khách hàng)
        $customerRole = Role::where('name', 'customer')->first();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'], // Model User đã cast 'hashed' -> tự động bcrypt, không cần Hash::make() thủ công
            'role_id' => $customerRole?->id,
        ]);

        // Đăng ký xong thì đăng nhập luôn cho tiện, không bắt user đăng nhập lại
        Auth::login($user);

        return redirect()->route('home')->with('success', 'Đăng ký tài khoản thành công!');
    }

    /**
     * Hiện form đăng nhập.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Auth::attempt tự động so sánh password đã hash trong DB, trả về true/false
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ]);
        }

        // Chống session fixation attack: tạo session mới sau khi đăng nhập thành công
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Xử lý đăng xuất.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
