@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="grid gap-8 lg:grid-cols-[3fr,2fr] items-start">

    {{-- ── Left column ──────────────────────────────────────────────────────── --}}
    <section class="space-y-6 p-6">

        {{-- Hero --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h1 class="text-2xl font-semibold mb-2">Hassle Free Smart Parking</h1>
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

        {{-- Active reservation card --}}
        @if($activeReservation)
        @php
            $rSlot     = $activeReservation->slot;
            $rLocation = $rSlot?->location;
            $rStatus   = $activeReservation->status;
            $rColor    = $rStatus === 'active'
                ? 'bg-green-50 text-green-700'
                : 'bg-yellow-50 text-yellow-700';
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-semibold text-gray-900">Active Reservation</span>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $rColor }}">
                            {{ ucfirst($rStatus) }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-500 mb-1">
                        {{ $rLocation?->name ?? '—' }}
                        @if($rSlot)
                            · Slot #{{ $rSlot->slot_number }} ({{ ucfirst($rSlot->type) }})
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mb-3">
                        Vehicle: {{ optional($activeReservation->vehicle)->plate_num ?? '—' }}
                    </p>

                    @if($activeReservation->start_time)
                    <p class="text-xs text-gray-400 mb-3">
                        Timer starts: {{ $activeReservation->start_time->format('M d, Y h:i A') }}
                    </p>
                    @endif

                    <a href="{{ route('reservations.show', $activeReservation) }}"
                       class="text-xs px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 font-medium">
                        View details
                    </a>
                </div>

                {{-- QR Code thumbnail --}}
                <div class="shrink-0 text-center">
                    <div id="qr-container"
                         class="w-24 h-24 flex items-center justify-center bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                        <svg class="animate-spin h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Show at entrance</p>
                    <button onclick="openQrModal()"
                            class="mt-0.5 text-[10px] text-blue-500 hover:underline">
                        Enlarge
                    </button>
                </div>
            </div>
        </div>

        {{-- QR enlarged modal --}}
        <div id="qr-modal" class="hidden fixed inset-0 bg-black/50 items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 max-w-xs w-full mx-4 text-center shadow-xl">
                <h3 class="font-semibold text-gray-900 mb-1">Your QR Code</h3>
                <p class="text-xs text-gray-500 mb-4">Show this to the parking staff at the entrance.</p>
                <div id="qr-modal-code" class="flex justify-center mb-4"></div>
                <p class="text-xs text-gray-400">Reservation #{{ $activeReservation->id }}</p>
                <button onclick="closeQrModal()"
                        class="mt-4 w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm text-gray-700">
                    Close
                </button>
            </div>
        </div>

        @else
        <div class="bg-white rounded-xl shadow-sm border border-dashed border-gray-300 p-5 text-center">
            <p class="text-sm text-gray-500 mb-3">You have no active reservation.</p>
            <a href="{{ route('parking.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-md bg-blue-500 text-white text-sm font-semibold hover:bg-blue-600">
                Reserve a spot
            </a>
        </div>
        @endif

        {{-- Featured locations --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Featured locations</h2>
                <a href="{{ route('parking.index') }}" class="text-sm text-gray-600 hover:text-gray-700">View all</a>
            </div>

            @if($locations->isEmpty())
                <div class="bg-white rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500">
                    No parking locations are available yet. Check back soon.
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($locations as $location)
                        <a href="{{ route('parking.show', $location) }}"
                           class="group bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:border-blue-400 hover:shadow-md transition">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600">
                                        {{ $location->name }}
                                    </h3>
                                    <p class="mt-1 text-xs text-gray-500">{{ $location->address }}</p>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium
                                    {{ $location->is_available ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $location->is_available ? 'Available' : 'Unavailable' }}
                                </span>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                {{-- Use slots() relation count — safe regardless of what "capacity" is --}}
                                <span class="text-sm">
                                    {{ $location->slots()->count() }} slots
                                </span>
                                <span class="text-xl font-semibold text-gray-900">
                                    ₱{{ number_format($location->hourly_rate, 2) }}/hour
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
            <h2 class="text-sm font-semibold text-gray-900 mb-3">My Subscription</h2>

            @if($subscription && $subscription->status === 'active')
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-lg">★</div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $subscription->plan?->name ?? 'Active Plan' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Expires {{ $subscription->ends_at?->format('M d, Y') ?? '—' }}
                        </p>
                    </div>
                    <span class="ml-auto inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-green-50 text-green-700">
                        Active
                    </span>
                </div>
                @if(($subscription->plan?->free_hours_per_week ?? 0) > 0)
                <div class="bg-blue-50 rounded-lg p-3 text-xs text-blue-700">
                    <span class="font-semibold">{{ $subscription->plan->free_hours_per_week }} free hours/week</span>
                    included with your plan.
                </div>
                @endif
                <a href="{{ route('subscription.index') }}"
                   class="mt-3 block text-center text-xs text-gray-500 hover:text-gray-700">
                    Manage subscription →
                </a>
            @else
                <p class="text-xs text-gray-500 mb-3">
                    Subscribe to get free parking hours and priority reservations.
                </p>
                <a href="{{ route('subscription.index') }}"
                   class="block w-full text-center px-4 py-2 rounded-md bg-blue-500 text-white text-sm font-semibold hover:bg-blue-600">
                    View plans
                </a>
            @endif
        </div>

        {{-- Contact --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-1">Contact us</h2>
            <p class="text-xs text-gray-500 mb-3">Have questions or feedback? Send us a message.</p>

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1" for="contact_email">Email</label>
                    <input id="contact_email" type="email" name="email" required
                           class="block w-full min-h-[30px] px-3 rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                           placeholder="you@example.com"
                           value="{{ old('email') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="contact_message">Message</label>
                    <textarea id="contact_message" name="message" rows="4" required
                              class="block w-full min-h-[100px] px-3 py-2 rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                              placeholder="Tell us how we can help...">{{ old('message') }}</textarea>
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

@push('scripts')
@if($activeReservation)
<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
@if($activeReservation && in_array($activeReservation->status, ['pending', 'active']))
<script>
    const reservationId = {{ $activeReservation->id }};
    const currentStatus = '{{ $activeReservation->status }}';

    const interval = setInterval(async () => {
        try {
            const res = await fetch(`/reservations/${reservationId}/status`);
            const data = await res.json();

            if (data.status !== currentStatus) {
                clearInterval(interval);
                window.location.reload();
            }
        } catch (e) {
            console.error('Status check failed', e);
        }
    }, 5000);   
</script>

@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reservationId = '{{ $activeReservation->id }}';
    const token         = '{{ hash_hmac("sha256", (string) $activeReservation->id, config("app.key")) }}';
    const payload       = btoa(JSON.stringify({ reservation_id: reservationId, token: token }));

    QRCode.toCanvas(document.createElement('canvas'), payload, { width: 96, margin: 1 }, function (err, c) {
        if (err) return console.error('QR small:', err);
        const container = document.getElementById('qr-container');
        container.innerHTML = '';
        container.appendChild(c);
    });

    QRCode.toCanvas(document.createElement('canvas'), payload, { width: 220, margin: 2 }, function (err, c) {
        if (err) return console.error('QR large:', err);
        document.getElementById('qr-modal-code').appendChild(c);
    });
});

function openQrModal() {
    const m = document.getElementById('qr-modal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeQrModal() {
    const m = document.getElementById('qr-modal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}
document.getElementById('qr-modal').addEventListener('click', e => {
    if (e.target === document.getElementById('qr-modal')) closeQrModal();
});
</script>
@endif
@endpush