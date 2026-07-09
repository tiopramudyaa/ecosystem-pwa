<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ config('services.vapid.public_key') }}">
    <title>@yield('title', 'Dashboard') - EcoSystem</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#991b1b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="EcoSystem">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png">
    <link rel="icon" href="/images/icons/icon-192.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        :root {
            --primary-color: #991b1b;
            --primary-rgb: 153, 27, 27;
            --primary-dark-rgb: 113, 0, 0;
        }

        html {
            overflow-x: hidden;
        }

        body {
            background-color: #f9fafb;
            color: #111827;
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
        }

        img, svg, video {
            max-width: 100%;
        }

        table {
            max-width: 100%;
        }

        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .primary-gradient {
            background: linear-gradient(135deg,
                rgb(var(--primary-dark-rgb)),
                rgb(var(--primary-rgb))) !important;
        }

        .primary-text {
            color: var(--primary-color) !important;
        }

        .primary-focus:focus,
        .primary-focus:focus-visible {
            outline: none;
            border-color: rgb(var(--primary-rgb));
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c62828;
            border-radius: 10px;
            border: 2px solid #f1f1f1;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #991b1b;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .nav-link {
            animation: slideIn 0.3s ease-out;
        }

        .nav-link:hover {
            transform: translateX(4px);
        }

        .nav-link.active {
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
        }

        .icon-btn {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4b5563;
            transition: all 0.2s ease;
        }

        .icon-btn:hover {
            border-color: rgb(var(--primary-rgb));
            color: rgb(var(--primary-rgb));
        }

        .bottom-nav {
            padding-bottom: env(safe-area-inset-bottom);
        }

        .bottom-nav-link {
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .bottom-nav-link.active {
            color: rgb(var(--primary-rgb));
        }

        .bottom-nav-link.active i {
            transform: translateY(-1px);
        }

        #page-loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(249, 250, 251, 0.6);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        #page-loading-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        #page-loading-overlay .loading-logo-wrap {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 9999px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem;
        }

        #page-loading-overlay .loading-logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            animation: spin 1.1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .spinner {
            display: inline-block;
            width: 1em;
            height: 1em;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        .spinner-dark {
            border-color: rgba(0, 0, 0, 0.15);
            border-top-color: rgb(var(--primary-rgb));
        }
    </style>
    @stack('styles')
</head>
<body class="text-gray-900 min-h-screen">
    <div id="page-loading-overlay">
        <div class="loading-logo-wrap">
            <img src="/images/icons/icon-512.png" alt="Loading">
        </div>
    </div>
    @php
        $authUser = session('lite_api_user', []);
        $authUserName = $authUser['name'] ?? 'User';
        $authUserRole = $authUser['role']['name'] ?? '';
        $initials = collect(explode(' ', trim($authUserName)))->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
    @endphp
    <div class="flex min-h-screen">

        <!-- Mobile sidebar backdrop -->
        <div id="sidebarOverlay" onclick="closeSidebar()" class="fixed inset-0 z-40 hidden lg:hidden" style="background-color: rgba(0,0,0,0.5);"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar-transition fixed inset-y-0 left-0 h-screen overflow-y-auto primary-gradient text-white shadow-2xl z-50 w-64 -translate-x-full lg:translate-x-0">
            <div class="p-5 pb-2 flex items-center justify-center">
                <div class="w-full rounded-xl p-3 backdrop-blur-sm">
                    <img src="/images/eclectic_logo_nobg.png" alt="EcoSystem Logo" class="w-full h-auto"/>
                </div>
            </div>

            <nav class="py-6 px-4">
                <div class="mb-2">
                    <a href="{{ route('dashboard') }}" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl {{ Request::is('dashboard') ? 'active bg-white bg-opacity-20 text-white font-semibold' : 'text-white text-opacity-80 hover:bg-white hover:bg-opacity-10 hover:text-white' }} transition-all">
                        <span class="w-5 h-5 flex items-center justify-center">
                            <i class="fas fa-home"></i>
                        </span>
                        <span class="font-medium">Home</span>
                    </a>
                </div>

                <div class="mb-2">
                    <a href="{{ route('tickets.index') }}" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl {{ Request::is('tickets*') ? 'active bg-white bg-opacity-20 text-white font-semibold' : 'text-white text-opacity-80 hover:bg-white hover:bg-opacity-10 hover:text-white' }} transition-all">
                        <span class="w-5 h-5 flex items-center justify-center">
                            <i class="fas fa-ticket-alt"></i>
                        </span>
                        <span class="font-medium">Tickets</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Topbar -->
            <header id="app-header" class="fixed top-0 inset-x-0 lg:left-64 z-30 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center justify-between gap-2 px-3 sm:px-6 py-3 max-w-full overflow-hidden">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                        @hasSection('backUrl')
                            <a href="@yield('backUrl')" class="icon-btn shrink-0 lg:hidden">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        @else
                            <button onclick="openSidebar()" class="icon-btn shrink-0 lg:hidden">
                                <i class="fas fa-bars"></i>
                            </button>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h1 id="page-title" class="text-base sm:text-lg font-semibold text-gray-900 leading-tight truncate">@yield('title', 'Dashboard')</h1>
                            @hasSection('subtitle')
                                <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-0.5 truncate">@yield('subtitle')</div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-1 sm:gap-3 shrink-0">
                        <span class="hidden sm:block text-xs text-gray-400 font-mono" id="live-clock"></span>

                        <span class="icon-btn relative shrink-0">
                            @include('partials.notifications-bell')
                        </span>

                        <a href="{{ route('profile.index') }}" class="flex items-center gap-2 sm:pl-2 sm:pr-3 py-1.5 rounded-xl sm:border sm:border-gray-200 hover:border-gray-300 transition-colors shrink-0">
                            <span class="w-8 h-8 rounded-full primary-gradient text-white flex items-center justify-center text-xs font-semibold shrink-0">
                                {{ $initials ?: 'U' }}
                            </span>
                            <span class="hidden sm:block text-left leading-tight">
                                <span class="block text-sm font-medium text-gray-900">{{ $authUserName }}</span>
                                @if ($authUserRole)
                                    <span class="block text-[11px] text-gray-500">{{ $authUserRole }}</span>
                                @endif
                            </span>
                        </a>

                        <form id="header-logout-form" method="POST" action="{{ route('logout') }}">
                            @csrf
                        </form>
                        <button type="button" class="icon-btn shrink-0" title="Logout" onclick="openLogoutModal('header-logout-form')">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:pb-6 @hasSection('hideBottomNav') pb-4 @else pb-24 @endif" style="padding-top: calc(var(--header-h, 64px) + 1rem);">
                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('status_warning'))
                    <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm flex items-start gap-2">
                        <i class="fas fa-triangle-exclamation mt-0.5"></i>
                        <span>Pesan tersimpan, tetapi <strong>tidak terkirim ke email customer</strong>: {{ session('status_warning') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bottom navigation (mobile only) -->
    @hasSection('hideBottomNav')
    @else
        <nav class="bottom-nav fixed inset-x-0 bottom-0 z-40 lg:hidden bg-white border-t border-gray-200 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
            <div class="grid grid-cols-4">
                <a href="{{ route('dashboard') }}" class="bottom-nav-link flex flex-col items-center justify-center gap-1 py-2.5 {{ Request::is('dashboard') ? 'active font-semibold' : 'text-gray-500' }}">
                    <i class="fas fa-home text-lg"></i>
                    <span class="text-[11px]">Home</span>
                </a>
                <a href="{{ route('tickets.index') }}" class="bottom-nav-link flex flex-col items-center justify-center gap-1 py-2.5 {{ Request::is('tickets*') ? 'active font-semibold' : 'text-gray-500' }}">
                    <i class="fas fa-ticket-alt text-lg"></i>
                    <span class="text-[11px]">Tickets</span>
                </a>
                <a href="{{ route('notifications.index') }}" class="bottom-nav-link flex flex-col items-center justify-center gap-1 py-2.5 {{ Request::is('notifications*') ? 'active font-semibold' : 'text-gray-500' }}">
                    <i class="fas fa-bell text-lg"></i>
                    <span class="text-[11px]">Notifications</span>
                </a>
                <a href="{{ route('profile.index') }}" class="bottom-nav-link flex flex-col items-center justify-center gap-1 py-2.5 {{ Request::is('profile*') ? 'active font-semibold' : 'text-gray-500' }}">
                    <i class="fas fa-user text-lg"></i>
                    <span class="text-[11px]">Profile</span>
                </a>
            </div>
        </nav>
    @endif

    <!-- Logout confirmation modal -->
    <div id="logoutModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="closeLogoutModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
            <div class="mx-auto mb-4 w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center">
                <i class="fas fa-sign-out-alt text-lg"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-900 mb-1">Konfirmasi Logout</h2>
            <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin logout?</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeLogoutModal()" class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="button" id="logoutModalConfirm" class="flex-1 rounded-xl bg-red-600 py-2.5 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                    Logout
                </button>
            </div>
        </div>
    </div>

    <script>
        function openLogoutModal(formId) {
            document.getElementById('logoutModal').classList.remove('hidden');
            document.getElementById('logoutModalConfirm').onclick = function () {
                document.getElementById(formId).submit();
            };
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }

        function openSidebar() {
            document.getElementById('sidebar').classList.remove('-translate-x-full');
            document.getElementById('sidebarOverlay').classList.remove('hidden');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebarOverlay').classList.add('hidden');
        }

        (function () {
            var header = document.getElementById('app-header');
            if (!header) return;

            function syncHeaderHeight() {
                document.documentElement.style.setProperty('--header-h', header.offsetHeight + 'px');
            }

            syncHeaderHeight();
            window.addEventListener('resize', syncHeaderHeight);
            if (window.ResizeObserver) {
                new ResizeObserver(syncHeaderHeight).observe(header);
            }
        })();

        (function () {
            var clock = document.getElementById('live-clock');
            if (!clock) return;

            function tick() {
                clock.textContent = new Date().toLocaleTimeString('en-GB');
            }

            tick();
            setInterval(tick, 1000);
        })();

        (function () {
            var VAPID_PUBLIC_KEY = document.querySelector('meta[name="vapid-public-key"]').content;
            var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
            var SUBSCRIBE_URL = '{{ route('push-subscriptions.store') }}';

            function urlBase64ToUint8Array(base64String) {
                var padding = '='.repeat((4 - base64String.length % 4) % 4);
                var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                var rawData = window.atob(base64);
                var outputArray = new Uint8Array(rawData.length);
                for (var i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }

            window.ecosystemSubscribeToPush = function () {
                if (!VAPID_PUBLIC_KEY || !('serviceWorker' in navigator) || !('PushManager' in window)) {
                    return Promise.resolve();
                }
                if (Notification.permission !== 'granted') {
                    return Promise.resolve();
                }

                return navigator.serviceWorker.ready.then(function (reg) {
                    return reg.pushManager.getSubscription().then(function (existing) {
                        return existing || reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
                        });
                    });
                }).then(function (subscription) {
                    return fetch(SUBSCRIBE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        },
                        body: JSON.stringify(subscription),
                    });
                }).catch(function (err) {
                    console.warn('Push subscription failed:', err);
                });
            };

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('/sw.js').then(function () {
                        window.ecosystemSubscribeToPush();
                    }).catch(function (err) {
                        console.warn('Service worker registration failed:', err);
                    });
                });
            }
        })();

        (function () {
            var overlay = document.getElementById('page-loading-overlay');
            if (!overlay) return;

            var showTimer = null;

            function show() {
                clearTimeout(showTimer);
                showTimer = setTimeout(function () {
                    overlay.classList.add('active');
                }, 100);
            }

            function hide() {
                clearTimeout(showTimer);
                overlay.classList.remove('active');
            }

            window.AppLoading = { show: show, hide: hide };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('a[href]');
                if (!link) return;
                if (link.hasAttribute('data-no-loading')) return;
                if (link.target && link.target !== '' && link.target !== '_self') return;
                if (event.defaultPrevented || event.button !== 0) return;
                if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

                var href = link.getAttribute('href') || '';
                if (href === '' || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return;

                show();
            });

            document.addEventListener('submit', function (event) {
                var form = event.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (form.hasAttribute('data-no-loading')) return;
                if (event.defaultPrevented) return;

                show();
            });

            window.addEventListener('pageshow', hide);
            window.addEventListener('pagehide', show);
        })();
    </script>
    @stack('scripts')
</body>
</html>
