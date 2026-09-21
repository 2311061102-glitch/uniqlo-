<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Route webhook nhận POST từ server SePay, không có CSRF token của Laravel
        // -> phải loại trừ, nếu không Laravel sẽ chặn với lỗi 419 Page Expired.
        $middleware->validateCsrfTokens(except: [
            'webhooks/sepay',
        ]);

        // Khi chạy sau ngrok (hoặc bất kỳ proxy/CDN nào), Laravel cần được báo
        // "tin tưởng" header X-Forwarded-* từ proxy để tự nhận đúng là đang chạy
        // https, tránh sinh nhầm link http:// trong lúc demo qua ngrok.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();