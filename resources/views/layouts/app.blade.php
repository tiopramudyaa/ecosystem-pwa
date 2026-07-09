<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - EcoSystem</title>
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

        body {
            background-color: #f9fafb;
            color: #111827;
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
    </style>
    @stack('styles')
</head>
<body class="text-gray-900 min-h-screen">
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
            <header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3">
                    <div class="flex items-center gap-3">
                        <button onclick="openSidebar()" class="icon-btn lg:hidden">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="min-w-0">
                            <h1 id="page-title" class="text-lg font-semibold text-gray-900 leading-tight truncate max-w-[60vw] sm:max-w-md">@yield('title', 'Dashboard')</h1>
                            @hasSection('subtitle')
                                <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-0.5 truncate max-w-[60vw] sm:max-w-md">@yield('subtitle')</div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <span class="hidden sm:block text-xs text-gray-400 font-mono" id="live-clock"></span>

                        <span class="icon-btn relative">
                            @include('partials.notifications-bell')
                        </span>

                        <a href="{{ route('profile.index') }}" class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl border border-gray-200 hover:border-gray-300 transition-colors">
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
                        <button type="button" class="icon-btn" title="Logout" onclick="openLogoutModal('header-logout-form')">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:pb-6 @hasSection('hideBottomNav') pb-4 @else pb-24 @endif">
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
            var clock = document.getElementById('live-clock');
            if (!clock) return;

            function tick() {
                clock.textContent = new Date().toLocaleTimeString('en-GB');
            }

            tick();
            setInterval(tick, 1000);
        })();
    </script>
    @stack('scripts')
</body>
</html>
