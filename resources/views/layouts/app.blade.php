<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UNIS Men - Đồ án')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/uniqlo-full-combined.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vouchers.css') }}">
    <link rel="stylesheet" href="{{ asset('css/address-map.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home-overrides.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cart-overrides.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stores.css') }}">
</head>
<body>

    <div class="promo-bar"><button type="button" aria-label="Đóng thông báo" class="promo-bar__close">×</button><span>Khám phá bộ sưu tập LifeWear mới — thiết kế cho mỗi ngày.</span><a href="{{ route('products.index') }}">Mua sắm ngay <span aria-hidden="true">→</span></a></div>
    <script>try { if (localStorage.getItem('unis-promo-dismissed') === '1') document.documentElement.classList.add('promo-dismissed'); } catch (_) {}</script>

    <header class="site-header">
        <a href="{{ route('home') }}" class="site-header__logo" aria-label="UNIS trang chủ">
            <span class="site-header__logo-mark" aria-hidden="true"></span>
            <span class="site-header__logo-word" aria-label="UNIS"><span>U</span><span>N</span><span>I</span><span>S</span></span>
        </a>

        <nav class="site-header__gender" aria-label="Bộ sưu tập">
            @php($routeCategory = request()->route('category'))
            @php($activeCategorySlug = is_object($routeCategory) ? $routeCategory->slug : ($routeCategory ?: request('category')))
            @php($activeCategorySlug = str_starts_with((string) $activeCategorySlug, 'demo-') ? substr((string) $activeCategorySlug, 5) : $activeCategorySlug)
            <a href="{{ route('products.category', ['category' => 'ao-thun']) }}" class="{{ $activeCategorySlug === 'ao-thun' ? 'is-current' : '' }}">ÁO PHÔNG</a>
            <a href="{{ route('products.category', ['category' => 'ao-so-mi']) }}" class="{{ $activeCategorySlug === 'ao-so-mi' ? 'is-current' : '' }}">ÁO SƠ MI</a>
            <a href="{{ route('products.category', ['category' => 'quan-jean']) }}" class="{{ $activeCategorySlug === 'quan-jean' || $activeCategorySlug === 'quan-kaki' ? 'is-current' : '' }}">QUẦN DÀI</a>
            <a href="{{ route('products.category', ['category' => 'ao-khoac']) }}" class="{{ $activeCategorySlug === 'ao-khoac' ? 'is-current' : '' }}">ÁO KHOÁC</a>
            <a href="{{ route('products.category', ['category' => 'phu-kien']) }}" class="{{ $activeCategorySlug === 'phu-kien' ? 'is-current' : '' }}">PHỤ KIỆN</a>
        </nav>

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
            <a href="{{ route('products.index') }}" class="site-header__legacy-link">Sản phẩm</a>
            <a href="{{ route('categories.index') }}" class="site-header__legacy-link">Danh mục</a>
            <a href="{{ route('stores.index') }}" class="site-header__legacy-link">Hệ thống cửa hàng</a>
            @auth
                <a href="{{ route('wishlist.index') }}" class="site-header__wishlist-link" title="Sản phẩm yêu thích">♡ Yêu thích</a>
            @endauth

            @guest
                <a href="{{ route('cart.index') }}" class="site-header__action" aria-label="Cart" title="Cart">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6"/></svg>
                    @if ($cartCount = \App\Services\CartCounter::count())
                        <span class="site-header__badge">{{ $cartCount }}</span>
                    @endif
                </a>
            @endguest

            @auth
                <a href="{{ route('cart.index') }}" class="site-header__action" aria-label="Giỏ hàng" title="Giỏ hàng">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6M10 20a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm9 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/></svg>
                    @if ($cartCount = \App\Services\CartCounter::count())
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
                @if (Route::has('login.google'))
                    <a href="{{ route('login.google') }}" class="site-header__icon" aria-label="Đăng nhập Google">G</a>
                @endif
                <a href="{{ route('login') }}" class="site-header__action" aria-label="Đăng nhập" title="Đăng nhập">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/></svg>
                </a>
                <a href="{{ route('register') }}">Đăng ký</a>
            @endauth
        </nav>
    </header>

    @auth
        @if (auth()->user()->isAdmin())
            <div class="admin-access-bar" role="status">
                <span><b>Chế độ quản trị</b> · Bạn đang đăng nhập bằng tài khoản Admin</span>
                <a href="{{ route('admin.dashboard') }}">Mở trang quản trị <span aria-hidden="true">→</span></a>
            </div>
        @endif
    @endauth

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
        document.querySelector('.promo-bar__close')?.addEventListener('click', function () {
            this.closest('.promo-bar')?.remove();
            document.body.classList.add('promo-hidden');
            document.documentElement.classList.add('promo-dismissed');
            try { localStorage.setItem('unis-promo-dismissed', '1'); } catch (_) {}
        });
    </script>
    <script>
        // Gợi ý tìm kiếm sản phẩm theo dữ liệu thật, có debounce để không gọi API liên tục.
        (function () {
            const panel = document.querySelector('[data-search-panel]');
            const input = panel?.querySelector('[data-search-input]');
            const body = panel?.querySelector('.search-panel__body');
            if (!panel || !input || !body) return;
            const results = document.createElement('div');
            results.className = 'search-autocomplete';
            body.prepend(results);
            let timer;
            input.addEventListener('input', function () {
                clearTimeout(timer);
                const term = input.value.trim();
                if (term.length < 2) { results.replaceChildren(); return; }
                timer = setTimeout(() => fetch('{{ route('search.suggestions') }}?q=' + encodeURIComponent(term), { headers: { Accept: 'application/json' } })
                    .then(response => response.json()).then(payload => {
                        results.replaceChildren();
                        payload.data.forEach(item => {
                            const link = document.createElement('a'); link.href = item.url; link.className = 'search-autocomplete__item';
                            if (item.image) { const image = document.createElement('img'); image.src = item.image; image.alt = ''; link.append(image); }
                            const info = document.createElement('span'); const name = document.createElement('strong'); const price = document.createElement('small'); name.textContent = item.name; price.textContent = item.price; info.append(name, price); link.append(info);
                            results.append(link);
                        });
                    }).catch(() => {}), 220);
            });
        })();
    </script>
    <script>
        // Nút yêu thích trên trang chi tiết sản phẩm dùng chung cho cả thêm và bỏ.
        (function () {
            const button = document.querySelector('.product-actions .btn-secondary');
            if (!button || !location.pathname.startsWith('/san-pham/')) return;
            button.addEventListener('click', function () {
                const slug = location.pathname.split('/').filter(Boolean)[1];
                const active = button.textContent.trim() === '♥';
                fetch('/yeu-thich/' + encodeURIComponent(slug), { method: active ? 'DELETE' : 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, Accept: 'application/json' } })
                    .then(response => response.json()).then(data => { button.textContent = data.wishlisted ? '♥' : '♡'; button.classList.toggle('btn-secondary--active', data.wishlisted); });
            });
        })();
    </script>
    <script>
        (function () {
            const form = document.querySelector('[data-search-form]');
            const panel = document.querySelector('[data-search-panel]');
            const input = panel?.querySelector('[data-search-input]');
            const history = panel?.querySelector('[data-search-history]');
            const empty = panel?.querySelector('[data-search-empty]');
            const storageKey = 'unis-men-search-history';

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
