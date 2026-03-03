@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="grid gap-8 lg:grid-cols-[3fr,2fr] items-start">
        <section class="space-y-6 p-6">
            <div class=" bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h1 class="text-2xl font-semibold mb-2">
                    Hassle Free Smart Parking
                </h1>
                <p class="text-sm text-gray-600 mb-4">
                    Find and reserve parking spots in a minute! Browse nearby locations,
                    see availability in real time, and keep track of your reservations.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('parking.index') }}"
                       class="inline-flex items-center px-4 py-2 rounded-md bg-blue-500 text-white text-sm font-semibold shadow-sm hover:bg-blue-600">
                        Browse parking locations
                    </a>
                    <p class="text-xs text-gray-500">
                        {{ $locations->count() }} featured locations available right now.
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">
                        Featured locations
                    </h2>
                    <a href="{{ route('parking.index') }}" class="text-sm text-gray-600 hover:text-gray-700">
                        View all
                    </a>
                </div>

                @if ($locations->isEmpty())
                    <div class="bg-white rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500">
                        No parking locations are available yet. Check back soon.
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($locations as $location)
                            <a href="{{ route('parking.show', $location) }}"
                               class="group bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:border-blue-400 hover:shadow-md transition">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600">
                                            {{ $location->name }}
                                        </h3>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $location->address }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium
                                        {{ $location->is_available ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $location->is_available ? 'Available' : 'Unavailable' }}
                                    </span>
                                </div>

                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-sm">
                                        Capacity: {{ $location->capacity->count() }} slots
                                    </span>
                                    <span class="text-xl font-semibold text-gray-900">
                                        ₱ {{ number_format($location->hourly_rate, 2) }}/hour
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="space-y-4 p-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">
                    Contact us
                </h2>
                <p class="text-xs text-gray-500 mb-3">
                    Have questions or feedback about the smart parking system? Send us a message.
                </p>

                <form action="{{ route('contact.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1" for="email">
                            Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            required
                            class="block w-full min-h-[30px] px-3 rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                            placeholder="you@example.com"
                            value="{{ old('email') }}"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="message">
                            Message
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows="4"
                            required
                            class="block w-full min-h-[100px] px-3 py-2 rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                            placeholder="Tell us how we can help..."
                        >{{ old('message') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-3 py-2 rounded-md bg-gray-900 text-white text-sm font-semibold shadow-sm hover:bg-black cursor-pointer">
                        Send message
                    </button>
                </form>
            </div>
        </section>
    </div>
@endsection

