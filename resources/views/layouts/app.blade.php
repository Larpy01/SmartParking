<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/svg+xml"
          href="data:image/svg+xml,<svg xmlns='https://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='50' fill='%23f97316'/><text x='50%25' y='50%25' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='bold' font-size='60' dy='.3em'>P</text></svg>">

    <title>@yield('title', 'Smart Parking')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        #sidebar { transition: width 0.25s ease, transform 0.25s ease; }

        #drawer-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.4);
            z-index: 40;
        }
        #drawer-overlay.open { display: block; }

        #mobile-drawer {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: 72vw; max-width: 280px;
            background: #dcfce7;
            z-index: 50;
            transform: translateX(-100%);
            transition: transform 0.25s ease;
            display: flex; flex-direction: column;
            border-right: 1px dashed #d1d5db;
        }
        #mobile-drawer.open { transform: translateX(0); }

        .sidebar-link {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            text-decoration: none;
            transition: background-color 0.15s, color 0.15s;
        }
        .sidebar-link:hover {
            background-color: #a7f3d0;
            color: #111827;
        }

        #bottom-nav a.tab-active,
        #bottom-nav button.tab-active { color: #16a34a; }
        #bottom-nav a.tab-active svg,
        #bottom-nav button.tab-active svg { stroke: #16a34a; }
    </style>
</head>

<body class="bg-gray-100 text-gray-900 min-h-screen flex flex-col font-sans">

@guest
    @if (!Route::is('login') && !Route::is('register') && !Route::is('password.request') && !Route::is('password.reset'))
        <header class="sticky top-0 z-50 bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
                <a href="{{ route('landing') }}" class="flex items-center gap-2 font-semibold text-lg">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-500 text-white text-lg">P</span>
                    <span class="hidden sm:inline">Smart Parking</span>
                </a>
                <nav class="flex items-center gap-1 text-sm">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-md text-sm text-white bg-blue-600 hover:bg-blue-500">Login</a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-md text-blue-500 text-sm font-semibold hover:bg-gray-100">Register</a>
                </nav>
            </div>
        </header>
    @endif

    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </div>
    </main>
@endguest

@auth

<div id="drawer-overlay"></div>

<div id="mobile-drawer">
    <div class="h-14 flex items-center gap-2 px-4 border-b border-gray-200 shrink-0">
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-500 text-white text-lg">P</span>
        <span class="font-semibold text-base">Smart Parking</span>
        <button id="drawerClose" class="ml-auto text-gray-400 hover:text-gray-700 p-1 cursor-pointer">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
    <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
        @include('layouts.nav-links')
    </nav>
    <div class="border-t border-gray-100 shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center px-4 py-4 text-sm font-medium text-gray-600 hover:text-red-700 hover:bg-emerald-100 transition cursor-pointer">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="ml-3">Logout</span>
            </button>
        </form>
    </div>
</div>

<div class="flex flex-1 overflow-hidden">

    <aside id="sidebar"
           class="hidden lg:flex w-64 min-h-screen bg-green-100 border-r border-dotted shadow-sm flex-col shrink-0 overflow-x-hidden">

        <div class="h-16 flex items-center gap-2 px-4 border-b border-gray-200 shrink-0">
            <span class="sidebar-text font-semibold text-lg whitespace-nowrap">Smart Parking</span>
            <button id="sidebarToggle"
                    class="ml-auto text-gray-500 hover:text-gray-900 focus:outline-none p-1 cursor-pointer">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
            @include('layouts.nav-links')
        </nav>

        <div class="border-t border-gray-100 shrink-0">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center px-4 py-4 text-sm font-medium text-gray-600 hover:text-red-700 hover:bg-emerald-200 transition cursor-pointer overflow-hidden">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="sidebar-text ml-3 truncate">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto bg-gray-50 pb-20 lg:pb-0">

        @if (!Route::is('profile.edit'))
        <header class="sticky top-0 z-30 h-14 w-full bg-green-200 border-b border-dotted shadow-sm
                        flex items-center justify-between px-4 shrink-0">
            <div class="flex items-center gap-2">
                <button id="drawerOpen"
                        class="lg:hidden mr-1 text-gray-600 hover:text-gray-900 focus:outline-none p-1 cursor-pointer">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-500 text-white text-lg">P</span>
                <span class="text-lg font-semibold whitespace-nowrap hidden sm:inline">
                    @yield('title')
                </span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600 hidden sm:inline">{{ auth()->user()->name }}</span>
                <a href="{{ route('profile.edit') }}" class="block">
                    <img src="{{ auth()->user()->profile_picture_url }}"
                         class="h-8 w-8 rounded-full object-cover border hover:scale-110 transition-transform duration-200"
                         alt="Profile">
                </a>
            </div>
        </header>
        @endif

        <div class="space-y-6 p-0">
            @yield('content')
        </div>
    </main>
</div>

@endauth

@if (!Route::is('login') && !Route::is('register') && !Route::is('password.request') && !Route::is('password.reset'))
<footer class="bg-white border-t border-gray-200 mt-auto hidden lg:block">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-sm text-gray-500 flex flex-col sm:flex-row items-center justify-between gap-2">
        <span>&copy; {{ date('Y') }} Smart Parking</span>
        <div class="flex gap-4">
            <a href="{{ route('privacy') }}" class="hover:text-gray-900">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-gray-900">Terms of Service</a>
        </div>
    </div>
</footer>
@endif

@stack('scripts')
@include('layouts.accessibility')
</body>
</html>