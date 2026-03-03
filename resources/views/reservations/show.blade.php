@extends('layouts.app')

@section('title', 'Reservation details')

@section('content')
    @php
        $slot     = $reservation->slot;
        $location = $slot?->location;
        $payment  = $reservation->payment;
        $status   = $reservation->status;
    @endphp

    {{-- ── Scan notification toast ────────────────────────────────────────── --}}
    <div id="scan-toast"
         class="hidden fixed top-5 right-5 z-50 max-w-sm w-full bg-white border border-green-300 rounded-xl shadow-lg p-4 flex items-start gap-3 transition-all">
        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center shrink-0 text-green-600 text-xl">✓</div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 text-sm">QR Code Scanned!</p>
            <p id="scan-toast-msg" class="text-xs text-gray-500 mt-0.5">Your parking timer will start in 5 minutes.</p>
            <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                <div id="scan-toast-bar" class="h-full bg-green-500 rounded-full" style="width:100%"></div>
            </div>
        </div>
        <button onclick="closeToast()" class="text-gray-400 hover:text-gray-600 text-lg leading-none shrink-0">×</button>
    </div>

    {{-- ── Receipt modal ───────────────────────────────────────────────────── --}}
    @if($status === 'completed')
    <div id="receipt-modal" class="hidden fixed inset-0 bg-black/50 items-center justify-center z-50">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-xl overflow-hidden">

            {{-- Header --}}
            <div class="bg-green-500 px-6 py-5 text-white text-center">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-2 text-2xl">🅿</div>
                <h2 class="text-lg font-bold">Smart Parking</h2>
                <p class="text-green-100 text-xs mt-0.5">Parking Receipt</p>
            </div>

            {{-- Receipt body --}}
            <div id="receipt-body" class="px-6 py-5 space-y-4 text-sm text-gray-700">

                {{-- Reservation info --}}
                <div class="space-y-2">
                    @php
                        function receiptRow(string $label, string $value): string {
                            return '<div class="flex justify-between gap-4">
                                <span class="text-gray-400">'.$label.'</span>
                                <span class="font-medium text-right">'.$value.'</span>
                            </div>';
                        }
                    @endphp

                    {!! receiptRow('Reservation #',  '#' . $reservation->id) !!}
                    {!! receiptRow('Location',        $location?->name ?? '—') !!}
                    {!! receiptRow('Slot',            $slot ? '#'.$slot->slot_number.' ('.ucfirst($slot->type).')' : '—') !!}
                    {!! receiptRow('Vehicle',         optional($reservation->vehicle)->plate_num ?? '—') !!}
                </div>

                <hr class="border-dashed border-gray-200">

                {{-- Time --}}
                <div class="space-y-2">
                    {!! receiptRow('Check-in',  $reservation->start_time?->format('M d, Y h:i A') ?? '—') !!}
                    {!! receiptRow('Check-out', $reservation->end_time?->format('M d, Y h:i A')   ?? '—') !!}
                    @if($reservation->free_hours)
                        {!! receiptRow('Free hours',  $reservation->free_hours . ' hr(s) (subscription)') !!}
                    @endif
                    @if($reservation->paid_hours)
                        {!! receiptRow('Paid hours',  $reservation->paid_hours . ' hr(s)') !!}
                    @endif
                </div>

                <hr class="border-dashed border-gray-200">

                {{-- Payment --}}
                @if($payment)
                <div class="space-y-2">
                    {!! receiptRow('Payment method', str_replace('_', ' ', ucfirst($payment->payment_method))) !!}
                    {!! receiptRow('Payment status', ucfirst($payment->payment_status)) !!}
                </div>
                <hr class="border-dashed border-gray-200">
                @endif

                {{-- Total --}}
                <div class="flex justify-between items-center text-base font-bold">
                    <span>Total</span>
                    <span class="text-green-600">
                        @if($reservation->is_free)
                            FREE
                        @else
                            ₱{{ number_format($reservation->total_amount ?? 0, 2) }}
                        @endif
                    </span>
                </div>

                <p class="text-center text-xs text-gray-400 pt-1">Thank you for using Smart Parking!</p>
            </div>

            {{-- Actions --}}
            <div class="px-6 pb-5 flex gap-3">
                <button onclick="closeReceiptModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                    Close
                </button>
                <button onclick="printReceipt()"
                        class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4H8v4a1 1 0 001 1zm1-12V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1"/>
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Page header ─────────────────────────────────────────────────────── --}}
    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Reservation #{{ $reservation->id }}</h1>
            <p class="text-xs text-gray-500">{{ $location?->name ?? 'Unknown location' }}</p>
        </div>

        <div class="flex items-center gap-2">
            @php
                $color = [
                    'pending'   => 'bg-yellow-50 text-yellow-700',
                    'active'    => 'bg-green-50 text-green-700',
                    'completed' => 'bg-gray-100 text-gray-700',
                    'cancelled' => 'bg-red-50 text-red-700',
                ][$status] ?? 'bg-gray-100 text-gray-700';
            @endphp
            <span id="reservation-status-badge"
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $color }}">
                {{ ucfirst($status) }}
            </span>

            @if($status === 'completed')
                <button onclick="openReceiptModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-500 hover:bg-green-600 text-white text-xs font-medium transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    View Receipt
                </button>
            @endif
        </div>
    </div>

    {{-- ── Main grid ───────────────────────────────────────────────────────── --}}
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
                            @if($slot) #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }})
                            @else —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Vehicle</dt>
                        <dd class="font-medium">{{ optional($reservation->vehicle)->plate_num ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Check-in</dt>
                        <dd class="font-medium">{{ $reservation->start_time?->format('M d, Y h:i A') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Check-out</dt>
                        <dd class="font-medium">{{ $reservation->end_time?->format('M d, Y h:i A') ?? '—' }}</dd>
                    </div>
                    @if($reservation->free_hours)
                    <div>
                        <dt class="text-gray-500">Free hours</dt>
                        <dd class="font-medium">{{ $reservation->free_hours }} hr(s)</dd>
                    </div>
                    @endif
                    @if($reservation->paid_hours)
                    <div>
                        <dt class="text-gray-500">Paid hours</dt>
                        <dd class="font-medium">{{ $reservation->paid_hours }} hr(s)</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Payment</h2>
                @if($payment)
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-gray-700">
                        <div>
                            <dt class="text-gray-500">Amount</dt>
                            <dd class="font-medium">
                                @if($reservation->is_free)
                                    <span class="text-green-600 font-semibold">FREE</span>
                                @else
                                    ₱{{ number_format($reservation->total_amount ?? $payment->amount, 2) }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Method</dt>
                            <dd class="font-medium">{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Status</dt>
                            <dd class="font-medium">{{ ucfirst($payment->payment_status) }}</dd>
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
// ── Laravel Echo — real-time scan notification ────────────────────────────────
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
            if (parseInt(data.reservation_id) !== reservationId) return;

            showToast(data.message ?? 'Your QR code was scanned! Timer starts in 5 minutes.');

            const badge = document.getElementById('reservation-status-badge');
            if (badge) {
                badge.textContent = 'Active';
                badge.className = 'inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-green-50 text-green-700';
            }
        });
});

// ── Toast ─────────────────────────────────────────────────────────────────────
let toastTimer = null;

function showToast(message) {
    const toast = document.getElementById('scan-toast');
    const msgEl = document.getElementById('scan-toast-msg');
    const bar   = document.getElementById('scan-toast-bar');

    msgEl.textContent = message;
    toast.classList.remove('hidden');
    toast.classList.add('flex');

    bar.style.transition = 'none';
    bar.style.width = '100%';
    requestAnimationFrame(() => requestAnimationFrame(() => {
        bar.style.transition = 'width 6s linear';
        bar.style.width = '0%';
    }));

    clearTimeout(toastTimer);
    toastTimer = setTimeout(closeToast, 6000);
}

function closeToast() {
    const toast = document.getElementById('scan-toast');
    toast.classList.add('hidden');
    toast.classList.remove('flex');
    clearTimeout(toastTimer);
}

// ── Receipt modal ─────────────────────────────────────────────────────────────
function openReceiptModal() {
    const modal = document.getElementById('receipt-modal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReceiptModal() {
    const modal = document.getElementById('receipt-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function printReceipt() {
    const body    = document.getElementById('receipt-body');
    const header  = body?.previousElementSibling;
    const content = (header?.outerHTML ?? '') + (body?.outerHTML ?? '');

    const win = window.open('', '_blank', 'width=420,height=680');
    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Parking Receipt #{{ $reservation->id }}</title>
            <meta charset="utf-8">
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: sans-serif; font-size: 13px; color: #374151; }
                .bg-green-500 { background: #22c55e; color: white; padding: 20px; text-align: center; }
                .bg-green-500 p { color: #dcfce7; font-size: 11px; margin-top: 2px; }
                .bg-green-500 h2 { font-size: 16px; font-weight: 700; }
                #receipt-body { padding: 20px; }
                .flex { display: flex; }
                .justify-between { justify-content: space-between; }
                .gap-4 { gap: 16px; }
                .space-y-2 > * + * { margin-top: 8px; }
                .space-y-4 > * + * { margin-top: 16px; }
                .text-gray-400 { color: #9ca3af; }
                .font-medium { font-weight: 500; }
                .text-right { text-align: right; }
                hr { border: none; border-top: 1px dashed #e5e7eb; margin: 12px 0; }
                .text-base { font-size: 15px; }
                .font-bold { font-weight: 700; }
                .text-green-600 { color: #16a34a; }
                .text-center { text-align: center; }
                .text-xs { font-size: 11px; }
                .pt-1 { padding-top: 4px; }
            </style>
        </head>
        <body>${content}</body>
        </html>
    `);
    win.document.close();
    win.focus();
    win.print();
    win.close();
}

// Close receipt modal on backdrop click
document.getElementById('receipt-modal')?.addEventListener('click', function (e) {
    if (e.target === this) closeReceiptModal();
});
</script>
@endpush