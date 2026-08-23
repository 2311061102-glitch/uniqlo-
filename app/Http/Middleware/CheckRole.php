<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Middleware này nhận thêm tham số là danh sách role được phép, ví dụ dùng như sau
     * trong routes/web.php:
     *
     *   Route::middleware(['auth', 'role:admin'])->group(...)
     *   Route::middleware(['auth', 'role:admin,staff'])->group(...)  // cho phép cả 2 role
     *
     * Nếu user chưa đăng nhập hoặc role không nằm trong danh sách cho phép -> chặn (403).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role?->name, $roles, true)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
