<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Aplikasi Kasir')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

    <style>
        /* Custom Gradient Animations */
        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient 3s ease infinite;
        }

        /* Smooth Transitions */
        * {
            transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }

        /* Active Sidebar Link */
        .sidebar-link.active {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.3);
            transform: scale(1.05);
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 100px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border-radius: 100px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #9333ea 100%);
        }

        /* Mobile Menu Animation */
        @media (max-width: 1023px) {
            .mobile-sidebar-enter {
                transform: translateX(-100%);
            }

            .mobile-sidebar-enter-active {
                transform: translateX(0);
                transition: transform 300ms ease-out;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-indigo-50/30 min-h-screen">

    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left: Logo & Mobile Menu Toggle -->
                <div class="flex items-center gap-4">
                    <button id="mobile-menu-toggle" class="lg:hidden p-2 rounded-xl hover:bg-gray-100 transition-all">
                        <i class="fa-solid fa-bars text-xl text-gray-600"></i>
                    </button>
                    <a href="/home" class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg overflow-hidden">
                            <img src="{{ asset('logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <h1
                                class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                Enuma Vault
                            </h1>
                            <p class="text-xs text-gray-500">Inventory System</p>
                        </div>
                    </a>
                </div>

                <!-- Center: Search (only on certain pages) -->
                @if (!request()->is('home') && !request()->is('/') && !request()->is('profile') && !request()->is('borrow') && !request()->is('return') )
                    <div class="hidden md:block flex-1 max-w-xl mx-8">
                        <form action="{{ url()->current() }}" method="GET" class="relative">
                            <i
                                class="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" placeholder="Cari..." value="{{ request('search') }}"
                                class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
                        </form>
                    </div>
                @endif

                <!-- Right: Profile -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <img class="w-10 h-10 rounded-xl object-cover shadow-md"
                            src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=6366f1&color=fff' }}"
                            alt="{{ Auth::user()->name }}">
                        <div class="hidden md:block">
                            <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="w-full mx-auto p-4 sm:px-6 lg:p-2">
        <div class="flex gap-6">
            <!-- Sidebar -->
            <aside id="sidebar"
                class="fixed lg:sticky top-16 lg:top-0 left-0 h-screen lg:h-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-4 z-40 transition-transform duration-300 w-64 -translate-x-full lg:translate-x-0">
                <!-- Close Button (Mobile Only) -->
                <button id="sidebar-close" class="lg:hidden absolute top-4 right-4 p-2 rounded-lg hover:bg-gray-100">
                    <i class="fa-solid fa-times text-gray-600"></i>
                </button>

                <nav class="space-y-2 mt-[70px] lg:mt-0">
                    <a href="/home"
                        class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('home') || request()->is('/') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-house text-lg"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    {{-- Menu Peminjaman untuk semua user --}}
                    <a href="{{ route('loan.borrow') }}"
                        class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('borrow') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-box text-lg"></i>
                        <span class="font-medium">Pinjam Barang</span>
                    </a>

                    <a href="{{ route('loan.return') }}"
                        class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('return') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-rotate-left text-lg"></i>
                        <span class="font-medium">Kembalikan Barang</span>
                    </a>

                    @if (in_array(Auth::user()->role, ['kasir', 'pengelola']))
                        <a href="/manage"
                            class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('manage') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fa-solid fa-pen-to-square text-lg"></i>
                            <span class="font-medium">Kelola Barang</span>
                        </a>

                        <a href="{{ route('admin.loans') }}"
                            class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('admin/loans') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fa-solid fa-clipboard-list text-lg"></i>
                            <span class="font-medium">Kelola Pinjaman</span>
                        </a>
                    @endif

                    <a href="/history"
                        class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('history') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-file text-lg"></i>
                        <span class="font-medium">Riwayat</span>
                    </a>

                    @if (Auth::user()->role == 'pengelola')
                        <a href="{{ route('users.index') }}"
                            class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('manage-users') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fa-solid fa-users-cog text-lg"></i>
                            <span class="font-medium">Pengguna</span>
                        </a>

                        <a href="{{ route('segments.index') }}"
                            class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('segments*') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fa-solid fa-map-location-dot text-lg"></i>
                            <span class="font-medium">Segmen</span>
                        </a>
                    @endif

                    <a href="/profile"
                        class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('profile') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-user text-lg"></i>
                        <span class="font-medium">Profil</span>
                    </a>

                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-all w-full">
                                <i class="fa-solid fa-right-from-bracket text-lg"></i>
                                <span class="font-medium">Keluar</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 pt-16 lg:pt-8">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="lg:hidden fixed inset-0 bg-black/50 z-30 hidden"></div>

    <script>
        // Mobile Menu Toggle
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('✅ SW registered'))
                    .catch(err => console.log('❌ SW failed:', err));
            });
        }
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarClose = document.getElementById('sidebar-close');
        const mobileOverlay = document.getElementById('mobile-overlay');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            mobileOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', openSidebar);
        }

        if (sidebarClose) {
            sidebarClose.addEventListener('click', closeSidebar);
        }

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', closeSidebar);
        }

        // Close sidebar when clicking a link (mobile)
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    closeSidebar();
                }
            });
        });
    </script>

</body>

</html>
