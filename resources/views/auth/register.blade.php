@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-5xl space-y-10 mt-0">

        {{-- Hero --}}
        <header class="text-center space-y-4 mb-4 mt-0">
            <p class="inline-flex mx-auto items-center rounded-full bg-orange-400 px-4 py-1 text-xs font-semibold text-white">
                Smart Parking System
            </p>

            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900">
                Park smarter, not harder.
            </h1>

            <p class="text-gray-600 max-w-2xl mx-auto text-sm">
                Reserve parking slots, view real-time availability, and manage your account — all in one place.
            </p>
        </header>

        {{-- Sign Up --}}
        <div id="signup" class="rounded-xl border border-orange-300 bg-white p-6 shadow-sm">


            <a href="{{ route('landing') }}" 
            class="inline-flex items-center gap-2 text-gray-200 hover:text-red-600 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <h2 class="text-3xl text-center font-semibold text-gray-900 mb-1">Sign up</h2>
            <p class="text-xs text-center text-gray-700 mb-4 mt-1">
                Create a driver account to start your stress free parking experience!.
            </p>

            <form action="/register" method="POST" class="space-y-4">
                @csrf
                
                {{-- Full Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Full name
                    </label>
                    <input type="text" name="name" required
                           class="w-full h-10 rounded-md px-3 border border-gray-400 text-sm focus:border-orange-500 focus:ring-orange-500"
                           placeholder="Juan Dela Cruz">
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email address
                    </label>
                    <input type="email" name="email" required
                           class="w-full h-10 rounded-md px-3 border border-gray-400 text-sm focus:border-orange-500 focus:ring-orange-500"
                           placeholder="sample@gmail.com">
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Phone Number
                    </label>

                    <div class="relatve flex items-center">
                        <span class="inline-flex h-10 items-center px-3 rounded-l-md border border-r-0 border-gray-400 bg-gray-100 text-sm text-gray-600">
                            +63
                        </span>
                        <input type="tel" name="phone_number" required
                            class="w-full h-10 rounded-r-md px-3 border border-gray-400 text-sm focus:border-orange-500 focus:ring-orange-500"
                            placeholder="9123456789">  
                    </div>

                    @error('phone_number')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>


               {{-- Password --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Password
                    </label>

                    <div class="relative">
                        <input type="password"
                            name="password"
                            id="password"
                            required
                            class="w-full h-10 rounded-md px-3 pr-10 border border-gray-400 text-sm focus:border-orange-500 focus:ring-orange-500">

                        <button type="button"
                                onclick="togglePassword('password', 'eyeIconPassword')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-600">
                            <i id="eyeIconPassword" class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>

                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>


                {{-- Confirm Password --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Confirm password
                    </label>

                    <div class="relative">
                        <input type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            required
                            class="w-full h-10 rounded-md px-3 pr-10 border border-gray-400 text-sm focus:border-orange-500 focus:ring-orange-500">

                        <button type="button"
                                onclick="togglePassword('password_confirmation', 'eyeIconConfirm')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-600">
                            <i id="eyeIconConfirm" class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>

                    @error('password_confirmation')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Privacy Policy & Terms --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" name="terms" type="checkbox" required
                               class="h-4 w-4 rounded border-gray-400 text-orange-500 focus:ring-orange-500 cursor-pointer">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="font-medium text-gray-700">
                            I agree to the 
                            <a href="privacy" class="text-blue-500 hover:underline">Privacy Policy</a> 
                            and 
                            <a href="terms" class="text-blue-500 hover:underline">Terms of Service</a>.
                        </label>
                        @error('terms')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <a href="{{ route('login') }}"
                    class="text-sm font-semibold text-blue-500 hover:underline hover:text-blue-700 transition">
                        Already have an account? Log in
                    </a>

                    <button type="submit"
                            class="px-5 py-2 rounded-md bg-blue-500 text-sm font-semibold text-white hover:bg-blue-600 transition cursor-pointer">
                        Create account
                    </button>
                </div>


                </div>
            </form>
        </div>
    </div>
</div>

@endsection