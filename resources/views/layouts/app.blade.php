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
</head>

<body class="bg-gray-100 text-gray-900 min-h-screen flex flex-col font-sans">

@guest
    @if (!Route::is('login') && !Route::is('register') && !Route::is('password.request') && !Route::is('password.reset'))
        <header class="sticky top-0 z-50 bg-white shadow-sm p-2">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('landing') }}" class="flex items-center gap-2 font-semibold text-lg">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-500 text-white text-lg">P</span>
                    <span>Smart Parking</span>
                </a>
                <nav class="flex justify-end items-center gap-1 text-sm">
                    <a href="{{ route('login') }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm text-white bg-blue-600 hover:underline hover:bg-blue-400">Login</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-3 py-2 rounded-md text-blue-500 text-sm font-semibold hover:bg-gray-200 hover:underline">Register</a>
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
<div class="flex flex-1 overflow-hidden">

    {{-- SIDEBAR --}}
    <aside id="sidebar" class="w-64 min-h-screen bg-green-100 border-r border-dotted shadow-sm flex flex-col shrink-0 transition-all px-0 duration-300 overflow-x-hidden">

        <div class="h-16 flex items-center gap-2 px-4 border-b border-gray-200 shrink-0">
            <span class="sidebar-text font-semibold text-lg whitespace-nowrap transition-opacity duration-300">
                Smart Parking
            </span>
            <button id="sidebarToggle" class="ml-auto text-gray-500 hover:text-gray-900 focus:outline-none p-1 cursor-pointer"> <i class="fa-solid fa-bars"></i> </button>
        </div>

        <nav class="flex-1 px-2 py-4 space-y-6 overflow-y-auto">
            @if (auth()->user()->hasAdminAccess())
                <div class="space-y-1 pt-4 border-t border-gray-50">
                    <p class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider truncate sidebar-text">Administration</p>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium hover:text-orange-600 hover:bg-orange-50 transition sidebar-link">
                        <span class="sidebar-text truncate">Admin Panel</span>
                    </a>
                    <a href="{{ route('admin.parking-locations.index') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition sidebar-link">
                        <span class="sidebar-text truncate">Parking Locations</span>
                    </a>
                    <a href="{{ route('admin.parking-slots.index') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition sidebar-link">
                        <span class="sidebar-text truncate">Parking Slots</span>
                    </a>
                    <a href="{{ route('admin.reservations.index') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition sidebar-link">
                        <span class="sidebar-text truncate">Reservations</span>
                    </a>
                    <a href="{{ route('admin.subscriptions.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition sidebar-link">
                        <span class="sidebar-text truncate">Subscriptions</span>
                    </a>
                </div>

            @elseif (auth()->user()->hasOperatorAccess())
                <div class="space-y-1 pt-2">
                    <p class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider truncate sidebar-text">Operator</p>
                    <a href="{{ route('staff.dashboard') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium hover:text-orange-600 hover:bg-orange-50 transition sidebar-link">
                        <span class="sidebar-text truncate">Dashboard</span>
                    </a>
                    <a href="{{ route('staff.scan.page') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition sidebar-link">
                        <span class="sidebar-text truncate">Scan</span>
                    </a>
                    <a href="{{ route('staff.payments.index') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-emerald-200 hover:text-gray-900 transition-all sidebar-link">
                        <span class="sidebar-text truncate">Payments</span>
                    </a>
                </div>

            @else
                <div class="space-y-1">
                    <a href="{{ route('home') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-emerald-200 hover:text-gray-900 transition-all sidebar-link">
                        <span class="sidebar-text truncate">Dashboard</span>
                    </a>
                    <a href="{{ route('parking.index') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-emerald-200 hover:text-gray-900 transition-all sidebar-link">
                        <span class="sidebar-text truncate">Parking</span>
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-emerald-200 hover:text-gray-900 transition-all sidebar-link">
                        <span class="sidebar-text truncate">My vehicles</span>
                    </a>
                    <a href="{{ route('reservations.index') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-emerald-200 hover:text-gray-900 transition-all sidebar-link">
                        <span class="sidebar-text truncate">Reservations</span>
                    </a>
                    <a href="{{ route('subscription.index') }}" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-emerald-200 hover:text-gray-900 transition-all sidebar-link">
                        <span class="sidebar-text truncate">Subscription</span>
                    </a>
                </div>
            @endif
        </nav>

        <div class="border-t border-gray-100 shrink-0">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center px-4 py-4 text-sm font-medium text-gray-600 transition-all duration-200 hover:text-red-800 hover:bg-emerald-200 active:scale-95 cursor-pointer overflow-hidden">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="sidebar-text ml-3 truncate transition-opacity duration-300">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- CONTENT --}}
    <main class="flex-1 overflow-y-auto bg-gray-50">

        @if (!Route::is('profile.edit'))
            <header class="h-15 w-full bg-green-200 border-b border-dotted shadow-sm flex items-center justify-between px-4 shrink-0 transition-all duration-300">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-500 text-white text-lg">P</span>
                    <span class="text-2xl pl-2 font-semibold whitespace-nowrap transition-opacity duration-300">
                        @yield('title')
                    </span>
                </div>
                <div id="profile" class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <a href="{{ route('profile.edit') }}" class="block">
                        <img src="{{ auth()->user()->profile_picture_url }}"
                            class="h-8 w-8 rounded-full object-cover border hover:w-9 hover:h-9 transition-all duration-200"
                            alt="Profile Picture">
                    </a>
                </div>
            </header>
        @endif

        <div class="space-y-10">
            @yield('content')
        </div>
    </main>
</div>
@endauth

@if (!Route::is('login') && !Route::is('register') && !Route::is('password.request') && !Route::is('password.reset'))
    <footer class="bg-gray-40 mt-10 shadow-sm p-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-gray-500 flex items-center justify-between">
            <span>&copy; {{ date('Y') }} Smart Parking</span>
            <div class="flex gap-4">
                <a href="{{ route('privacy') }}" class="hover:text-gray-900 cursor-pointer">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="hover:text-gray-900 cursor-pointer">Terms of Service</a>
            </div>
        </div>
    </footer>
@endif

<script>
    // ── Sidebar ──
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    toggle?.addEventListener('click', () => {
        sidebar.classList.toggle('w-64');
        sidebar.classList.toggle('w-16');
        document.querySelectorAll('.sidebar-text, .sidebar-link')
            .forEach(el => el.classList.toggle('hidden'));
    });

    // ── Visibility ──
    function togglePassword(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById(iconId);
        if (!passwordInput || !eyeIcon) return;
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
        }
    }

    // ── Dismiss ──
    function closeAlert() {
        const alert = document.getElementById('success-alert');
        if (alert) alert.remove();
    }

    // ── Persist ──
    document.addEventListener('DOMContentLoaded', function () {
        const emailInput = document.querySelector('input[name="email"]');
        const rememberCheckbox = document.querySelector('input[name="remember"]');

        if (!emailInput || !rememberCheckbox) return;

        const savedEmail = localStorage.getItem('remembered_email');
        if (savedEmail) {
            emailInput.value = savedEmail;
            rememberCheckbox.checked = true;
        }

        document.querySelector('form').addEventListener('submit', function () {
            if (rememberCheckbox.checked) {
                localStorage.setItem('remembered_email', emailInput.value);
            } else {
                localStorage.removeItem('remembered_email');
            }
        });
    });

    // ── Validation ──
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.querySelector('input[name="name"]');
        const phoneInput = document.querySelector('input[name="phone_number"]');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');

        if (!nameInput || !passwordInput || !confirmInput) return;

        function showError(input, message) {
            removeError(input);
            const error = document.createElement('p');
            error.className = 'text-xs text-red-500 mt-1 dynamic-error';
            error.innerText = message;
            input.classList.add('border-red-500');
            input.parentElement.appendChild(error);
        }

        function removeError(input) {
            const oldError = input.parentElement.querySelector('.dynamic-error');
            if (oldError) oldError.remove();
            input.classList.remove('border-red-500');
        }

        nameInput.addEventListener('blur', function () {
            removeError(nameInput);
            if (nameInput.value.trim() === '') showError(nameInput, 'Full name is required.');
        });

        const emailInput = document.querySelector('input[name="email"]');
        if (emailInput) {
            emailInput.addEventListener('blur', function () {
                removeError(emailInput);
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailInput.value)) showError(emailInput, 'Please enter a valid email address.');
            });
        }

        if (phoneInput) {
            phoneInput.addEventListener('blur', function () {
                removeError(phoneInput);
                if (phoneInput.value.length < 10 || phoneInput.value.length > 11) showError(phoneInput, 'Phone number must be 10-11 digits.');
                else if (!/^\d+$/.test(phoneInput.value)) showError(phoneInput, 'Phone number must contain only digits.');
            });
        }

        passwordInput.addEventListener('blur', function () {
            removeError(passwordInput);
            if (passwordInput.value.length < 8) { showError(passwordInput, 'Password must be at least 8 characters.'); return; }
            if (!/[A-Z]/.test(passwordInput.value)) { showError(passwordInput, 'Password must contain at least one uppercase letter.'); return; }
            if (!/[0-9]/.test(passwordInput.value)) showError(passwordInput, 'Password must contain at least one number.');
        });

        confirmInput.addEventListener('blur', function () {
            removeError(confirmInput);
            if (confirmInput.value !== passwordInput.value) showError(confirmInput, 'Passwords do not match.');
        });
    });

    // ── Schedule ──
    document.addEventListener('DOMContentLoaded', function () {
        const startTime = document.getElementById('start_time');
        const endTime = document.getElementById('end_time');

        if (!startTime || !endTime) return;

        startTime.min = new Date().toISOString().slice(0, 16);

        startTime.addEventListener('change', function () {
            endTime.min = this.value;
            if (endTime.value && endTime.value <= this.value) endTime.value = '';
        });
    });
</script>

<style>
    .sidebar-link {
        @apply block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition;
    }
</style>

@stack('scripts')
@include('layouts.accessibility')
</body>
</html>