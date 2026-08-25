<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UNIQLO Men - Đồ án')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/uniqlo-full-combined.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body>

    <header class="site-header">
        <a href="{{ route('home') }}" class="site-header__logo" aria-label="UNIQLO trang chủ">
            <span class="site-header__logo-mark">ユ</span><span class="site-header__logo-word">UNI<br>QLO</span>
        </a>

        <form method="GET" action="{{ route('products.index') }}" class="site-header__search" data-search-form>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Bạn đang tìm gì?" aria-label="Tìm kiếm sản phẩm">
            <button type="submit" aria-label="Tìm kiếm">⌕</button>
        </form>
        <div class="search-panel" data-search-panel>
            <div class="search-panel__top">
                <span class="search-panel__back" data-search-close aria-label="Đóng tìm kiếm">‹</span>
                <form method="GET" action="{{ route('products.index') }}" class="search-panel__form">
                    <input type="search" name="q" placeholder="Bạn đang tìm gì?" aria-label="Tìm kiếm sản phẩm" data-search-input>
                    <button type="submit" aria-label="Tìm kiếm">⌕</button>
                </form>
                <button type="button" class="search-panel__close" data-search-close aria-label="Đóng tìm kiếm">×</button>
            </div>
            <div class="search-panel__body">
                <p class="search-panel__label">Gợi ý tìm kiếm</p>
                <div class="search-suggestions">
                    <button type="button" data-search-term="Áo thun">⌕ Áo thun</button>
                    <button type="button" data-search-term="Áo sơ mi">⌕ Áo sơ mi</button>
                    <button type="button" data-search-term="Áo khoác">⌕ Áo khoác</button>
                    <button type="button" data-search-term="Quần jean">⌕ Quần jean</button>
                    <button type="button" data-search-term="Quần short">⌕ Quần short</button>
                    <button type="button" data-search-term="Phụ kiện">⌕ Phụ kiện</button>
                </div>
                <div class="search-history__heading"><span>Lịch sử tìm kiếm</span><button type="button" data-search-clear aria-label="Xóa lịch sử">⌫</button></div>
                <p class="search-history__empty" data-search-empty>Bạn có thể xem lịch sử tìm kiếm tại đây.</p>
                <div class="search-history" data-search-history></div>
            </div>
        </div>

        <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox">
        <label for="nav-toggle" class="nav-toggle-button" aria-label="Mở menu">☰</label>

        <nav class="site-header__nav">
            <a href="{{ route('products.index') }}">Sản phẩm</a>
            <a href="{{ route('categories.index') }}">Danh mục</a>

            @auth
                <a href="{{ route('cart.index') }}" class="site-header__action" aria-label="Giỏ hàng" title="Giỏ hàng">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6M10 20a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm9 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/></svg>
                    @if ($cartCount = auth()->user()->cartItems()->sum('quantity'))
                        <span class="site-header__badge">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.edit') }}" class="site-header__action" aria-label="Tài khoản" title="Tài khoản">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/></svg>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="link-button">Đăng xuất</button>
                </form>
            @else
                <a href="{{ route('login.google') }}" class="site-header__icon" aria-label="Đăng nhập Google">G</a>
                <a href="{{ route('login') }}" class="site-header__action" aria-label="Đăng nhập" title="Đăng nhập">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/></svg>
                </a>
                <a href="{{ route('register') }}">Đăng ký</a>
            @endauth
        </nav>
    </header>

    <main class="site-main">
        @if (session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif

        @auth
            @unless (auth()->user()->hasVerifiedEmail())
                <div class="alert alert--warning">
                    Email của bạn chưa được xác thực.
                    <a href="{{ route('verification.notice') }}">Xem chi tiết</a> hoặc
                    <form method="POST" action="{{ route('verification.send') }}" style="display:inline">
                        @csrf
                        <button type="submit" class="link-button">gửi lại email xác thực</button>
                    </form>.
                </div>
            @endunless
        @endauth

        @yield('content')
    </main>

    @stack('scripts')
    <script>
        (function () {
            const form = document.querySelector('[data-search-form]');
            const panel = document.querySelector('[data-search-panel]');
            const input = panel?.querySelector('[data-search-input]');
            const history = panel?.querySelector('[data-search-history]');
            const empty = panel?.querySelector('[data-search-empty]');
            const storageKey = 'uniqlo-men-search-history';

            if (!form || !panel || !input || !history) return;

            function getHistory() {
                try { return JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch (_) { return []; }
            }

            function saveTerm(term) {
                term = term.trim();
                if (term) localStorage.setItem(storageKey, JSON.stringify([term, ...getHistory().filter(item => item !== term)].slice(0, 6)));
            }

            function renderHistory() {
                const terms = getHistory();
                history.replaceChildren(...terms.map(term => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.searchTerm = term;
                    button.textContent = '⌕ ' + term;
                    return button;
                }));
                empty.hidden = terms.length > 0;
            }

            function openPanel() {
                panel.classList.add('search-panel--open');
                input.value = form.querySelector('input[name="q"]').value;
                renderHistory();
                input.focus();
            }

            form.addEventListener('focusin', openPanel);
            form.addEventListener('submit', function () {
                saveTerm(form.querySelector('input[name="q"]').value);
            });
            panel.querySelector('form').addEventListener('submit', function () {
                saveTerm(input.value);
                panel.classList.remove('search-panel--open');
            });
            panel.addEventListener('click', function (event) {
                const close = event.target.closest('[data-search-close]');
                const termButton = event.target.closest('[data-search-term]');
                if (close) { panel.classList.remove('search-panel--open'); return; }
                if (termButton) {
                    input.value = termButton.dataset.searchTerm;
                    saveTerm(input.value);
                    input.closest('form').submit();
                }
                if (event.target.closest('[data-search-clear]')) { localStorage.removeItem(storageKey); renderHistory(); }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') panel.classList.remove('search-panel--open');
            });
            renderHistory();
        })();
    </script>
    <script>
        document.querySelectorAll('.password-toggle').forEach(function (toggle) {
            const input = toggle.parentElement.querySelector('input[type="password"]');
            let locked = false;

            function setVisible(visible) {
                input.type = visible ? 'text' : 'password';
                toggle.setAttribute('aria-label', visible ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
                toggle.setAttribute('title', visible ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
            }

            toggle.addEventListener('mouseenter', function () {
                if (!locked) setVisible(true);
            });
            toggle.addEventListener('mouseleave', function () {
                if (!locked) setVisible(false);
            });
            toggle.addEventListener('click', function () {
                locked = !locked;
                setVisible(locked);
            });
            toggle.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    locked = !locked;
                    setVisible(locked);
                }
            });
        });
    </script>
</body>
</html>
