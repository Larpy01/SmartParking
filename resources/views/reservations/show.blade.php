@extends('layouts.app')

@section('title', 'Reservation details')

@section('content')
    @php
        $slot = $reservation->slot;
        $location = $slot?->location;
    @endphp

    {{-- ── Scan notification toast (hidden by default) ───────────────────── --}}
    <div id="scan-toast"
         class="hidden fixed top-5 right-5 z-50 max-w-sm w-full bg-white border border-green-300 rounded-xl shadow-lg p-4 flex items-start gap-3 transition-all">
        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center shrink-0 text-green-600 text-xl">
            ✓
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 text-sm">QR Code Scanned!</p>
            <p id="scan-toast-msg" class="text-xs text-gray-500 mt-0.5">Your parking timer will start in 5 minutes.</p>
            {{-- Countdown bar --}}
            <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                <div id="scan-toast-bar" class="h-full bg-green-500 rounded-full transition-none" style="width:100%"></div>
            </div>
        </div>
        <button onclick="closeToast()" class="text-gray-400 hover:text-gray-600 text-lg leading-none shrink-0">×</button>
    </div>

    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">
                Reservation #{{ $reservation->id }}
            </h1>
            <p class="text-xs text-gray-500">
                {{ $location?->name ?? 'Unknown location' }}
            </p>
        </div>

        <div class="text-right">
            @php
                $status = $reservation->status;
                $color = [
                    'pending'   => 'bg-yellow-50 text-yellow-700',
                    'active'    => 'bg-green-50 text-green-700',
                    'completed' => 'bg-gray-100 text-gray-700',
                    'canceled'  => 'bg-red-50 text-red-700',
                ][$status] ?? 'bg-gray-100 text-gray-700';
            @endphp
            <span id="reservation-status-badge"
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $color }}">
                {{ ucfirst($status) }}
            </span>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[3fr,2fr] items-start">
        <section class="bg-white rounded-lg border border-gray-200 p-4 space-y-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Reservation details</h2>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-gray-700">
                    <div>
                        <dt class="text-gray-500">Location</dt>
                        <dd class="font-medium">{{ $location?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Slot</dt>
                        <dd class="font-medium">
                            @if ($slot)
                                #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }})
                            @else —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Vehicle</dt>
                        <dd class="font-medium">{{ optional($reservation->vehicle)->plate_num ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Time</dt>
                        <dd class="font-medium">
                            {{ $reservation->start_time }} – {{ $reservation->end_time }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Payment</h2>
                @if ($reservation->payment)
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-gray-700">
                        <div>
                            <dt class="text-gray-500">Amount</dt>
                            <dd class="font-medium">₱{{ number_format($reservation->payment->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Method</dt>
                            <dd class="font-medium">{{ str_replace('_', ' ', ucfirst($reservation->payment->payment_method)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Status</dt>
                            <dd class="font-medium">{{ ucfirst($reservation->payment->payment_status) }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-xs text-gray-500">No payment recorded yet.</p>
                @endif
            </div>
        </section>

        <section class="space-y-4">
            <div class="bg-white rounded-lg border border-gray-200 p-4 text-xs text-gray-700 space-y-2">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">Actions</h2>
                <p class="text-gray-500">
                    Status changes are handled by the existing routes
                    (<code>/reservations/{reservation}/start</code> and <code>/reservations/{reservation}/end</code>).
                </p>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
// ── Laravel Echo — listens on the private channel for this user ───────────────
// Requires: Laravel Echo + Pusher (or Reverb) configured in resources/js/bootstrap.js
// and @vite(['resources/js/app.js']) present in the layout (it already is).

document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.Echo === 'undefined') {
        console.warn('Laravel Echo not available — real-time notifications disabled.');
        return;
    }

    const userId        = {{ auth()->id() }};
    const reservationId = {{ $reservation->id }};

    window.Echo
        .private(`user.${userId}`)
        .listen('.reservation.scanned', (data) => {
            // Only show the toast for this specific reservation
            if (parseInt(data.reservation_id) !== reservationId) return;

            showToast(data.message ?? 'Your QR code was scanned! Timer starts in 5 minutes.');

            // Update the status badge in place without a page reload
            const badge = document.getElementById('reservation-status-badge');
            if (badge) {
                badge.textContent = 'Active';
                badge.className = 'inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-green-50 text-green-700';
            }
        });
});

// ── Toast helpers ─────────────────────────────────────────────────────────────
let toastTimer = null;

function showToast(message) {
    const toast   = document.getElementById('scan-toast');
    const msgEl   = document.getElementById('scan-toast-msg');
    const bar     = document.getElementById('scan-toast-bar');

    msgEl.textContent = message;
    toast.classList.remove('hidden');
    toast.classList.add('flex');

    // Animate the progress bar draining over 6 seconds
    bar.style.transition = 'none';
    bar.style.width = '100%';

    // Kick off drain on next paint
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            bar.style.transition = 'width 6s linear';
            bar.style.width = '0%';
        });
    });

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => closeToast(), 6000);
}

function closeToast() {
    const toast = document.getElementById('scan-toast');
    toast.classList.add('hidden');
    toast.classList.remove('flex');
    clearTimeout(toastTimer);
}
</script>
@endpush