@extends('layouts.app')

@section('title', 'My Reservations')

@section('content')
<section class="p-6">

    @if (session('success'))
        <div id="success-alert"
            class="flex items-start justify-between bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg p-4 text-sm shadow-sm mb-4">
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="closeAlert()" class="text-black text-lg cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg border border-gray-200 p-6">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">My Reservations</h2>
            <a href="{{ route('parking.index') }}"
               class="inline-flex items-center px-3 py-2 rounded-md bg-blue-600 text-white text-xs font-semibold shadow-sm hover:bg-blue-700">
                Browse Parking Locations
            </a>
        </div>

        @if($reservations->isEmpty())
            <p class="text-sm text-gray-500">You have no reservations yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-800 border-b">
                            <th class="py-2 pr-4">Location</th>
                            <th class="py-2 pr-4">Slot</th>
                            <th class="py-2 pr-4">Vehicle</th>
                            <th class="py-2 pr-4">Time</th>
                            <th class="py-2 pr-4">Total</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($reservations as $reservation)
                            @php
                                $slot     = $reservation->slot;
                                $location = $slot?->location;
                                $status   = $reservation->status;
                                $color    = [
                                    'pending'   => 'bg-yellow-50 text-yellow-700',
                                    'active'    => 'bg-blue-50 text-blue-700',
                                    'completed' => 'bg-green-100 text-green-700',
                                    'cancelled' => 'bg-red-50 text-red-700',
                                ][$status] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <tr class="align-top" id="reservation-row-{{ $reservation->id }}">
                                <td class="py-3 pr-4 text-gray-900">{{ $location?->name ?? '—' }}</td>
                                <td class="py-3 pr-4 text-gray-700">
                                    @if($slot) #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }}) @else — @endif
                                </td>
                                <td class="py-3 pr-4 text-gray-700">{{ $reservation->vehicle?->plate_num ?? '—' }}</td>
                                <td class="py-3 pr-4 text-gray-700 text-xs">
                                    <span class="text-gray-400 text-[10px]">From</span>
                                    <span class="block">{{ $reservation->start_time?->format('M d, Y h:i A') ?? '—' }}</span>
                                    <span class="text-gray-400 text-[10px]">To</span>
                                    <span class="block">{{ $reservation->end_time?->format('M d, Y h:i A') ?? '—' }}</span>
                                </td>
                                <td class="py-3 pr-4 text-xs">
                                    @if($status === 'completed')
                                        @if($reservation->is_free)
                                            <span class="font-semibold text-green-600">FREE</span>
                                        @elseif($reservation->total_amount !== null)
                                            <span class="font-semibold">₱{{ number_format($reservation->total_amount, 2) }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $color }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <div class="flex flex-col items-end gap-2">

                                        @if(in_array($status, ['pending', 'active']))
                                            <button onclick="showModal('qr-modal-{{ $reservation->id }}')"
                                                    class="text-sm text-blue-600 hover:text-blue-700 hover:underline font-medium">
                                                Show QR <i class="fa-solid fa-qrcode ml-1"></i>
                                            </button>
                                        @endif

                                        @if($status === 'completed')
                                            <button onclick="showModal('receipt-modal-{{ $reservation->id }}')"
                                                    class="text-sm text-green-600 hover:text-green-700 hover:underline font-medium">
                                                View Receipt <i class="fa-solid fa-receipt ml-1"></i>
                                            </button>
                                        @endif

                                        <a href="{{ route('reservations.show', $reservation) }}"
                                           class="text-sm text-gray-500 hover:text-gray-700 hover:underline font-medium">
                                            Details
                                        </a>
                                        <!--
                                        @if(in_array($status, ['pending', 'active']))
                                            <form method="POST"
                                                  action="{{ route('reservations.destroy', $reservation->id) }}"
                                                  onsubmit="return confirm('Cancel this reservation?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-sm text-red-500 hover:text-red-700 hover:underline font-medium">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif

                                        @if(in_array($status, ['completed', 'cancelled']))
                                            <form method="POST"
                                                  action="{{ route('reservations.destroy', $reservation->id) }}"
                                                  onsubmit="return confirm('Permanently delete this reservation?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-sm text-red-400 hover:text-red-600 hover:underline font-medium">
                                                    Delete <i class="fa-solid fa-trash ml-1"></i>
                                                </button>
                                            </form>
                                        @endif
                                        -->
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

@foreach($reservations as $reservation)
    @if(in_array($reservation->status, ['pending', 'active']))
        @php $slot = $reservation->slot; $location = $slot?->location; @endphp
        <div id="qr-modal-{{ $reservation->id }}" class="fixed inset-0 z-50" style="display:none">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-white font-bold text-base">Reservation #{{ $reservation->id }}</p>
                            <p class="text-blue-100 text-xs mt-0.5">{{ $location?->name ?? '—' }}</p>
                        </div>
                        <button onclick="hideModal('qr-modal-{{ $reservation->id }}')"
                                class="text-white hover:text-blue-200 text-xl font-bold">✕</button>
                    </div>
                    <div class="p-6 flex flex-col items-center gap-4">
                        <p class="text-xs text-gray-500 text-center">
                            Show this QR code to the parking staff to check in or out.
                        </p>
                        <div class="p-3 border-2 border-gray-100 rounded-xl bg-white shadow-inner">
                            {!! QrCode::size(200)->generate(
                                base64_encode(json_encode([
                                    'reservation_id' => $reservation->id,
                                    'token'          => hash_hmac('sha256', $reservation->id, config('app.key')),
                                ]))
                            ) !!}
                        </div>
                        <div class="w-full text-xs text-gray-600 space-y-1.5 border-t pt-4">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Slot</span>
                                <span class="font-medium">
                                    @if($slot) #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }}) @else — @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Vehicle</span>
                                <span class="font-medium">{{ $reservation->vehicle?->plate_num ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Start</span>
                                <span class="font-medium">{{ $reservation->start_time?->format('M d, Y h:i A') ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Status</span>
                                <span class="font-medium capitalize">{{ $reservation->status }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@foreach($reservations as $reservation)
    @if($reservation->status === 'completed')
        @php
            $slot     = $reservation->slot;
            $location = $slot?->location;
            $payment  = $reservation->payment;
        @endphp
        <div id="receipt-modal-{{ $reservation->id }}" class="fixed inset-0 z-50" style="display:none">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
                    <div class="bg-green-500 px-6 py-5 text-white text-center">
                        <div class="w-11 h-11 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-2 text-2xl">🅿</div>
                        <h2 class="text-base font-bold">Smart Parking</h2>
                        <p class="text-green-100 text-xs mt-0.5">Parking Receipt</p>
                    </div>
                    <div class="px-6 py-5 space-y-4 text-sm text-gray-700" id="receipt-body-{{ $reservation->id }}">
                        <div class="space-y-2">
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Reservation #</span>
                                <span class="font-medium">#{{ $reservation->id }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Location</span>
                                <span class="font-medium text-right">{{ $location?->name ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Slot</span>
                                <span class="font-medium">
                                    @if($slot) #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }}) @else — @endif
                                </span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Vehicle</span>
                                <span class="font-medium">{{ $reservation->vehicle?->plate_num ?? '—' }}</span>
                            </div>
                        </div>
                        <hr class="border-dashed border-gray-200">
                        <div class="space-y-2">
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Check-in</span>
                                <span class="font-medium text-right text-xs">{{ $reservation->start_time?->format('M d, Y h:i A') ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Check-out</span>
                                <span class="font-medium text-right text-xs">{{ $reservation->end_time?->format('M d, Y h:i A') ?? '—' }}</span>
                            </div>
                            @if($reservation->free_hours)
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Free hours</span>
                                <span class="font-medium">{{ $reservation->free_hours }} hr(s)</span>
                            </div>
                            @endif
                            @if($reservation->paid_hours)
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Paid hours</span>
                                <span class="font-medium">{{ $reservation->paid_hours }} hr(s)</span>
                            </div>
                            @endif
                        </div>
                        @if($payment)
                        <hr class="border-dashed border-gray-200">
                        <div class="space-y-2">
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Method</span>
                                <span class="font-medium">{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Payment</span>
                                <span class="font-medium">{{ ucfirst($payment->payment_status) }}</span>
                            </div>
                        </div>
                        @endif
                        <hr class="border-dashed border-gray-200">
                        <div class="flex justify-between items-center text-base font-bold">
                            <span>Total</span>
                            <span class="text-green-600">
                                @if($reservation->is_free) FREE
                                @else ₱{{ number_format($reservation->total_amount ?? 0, 2) }}
                                @endif
                            </span>
                        </div>
                        <p class="text-center text-xs text-gray-400">Thank you for using Smart Parking!</p>
                    </div>
                    <div class="px-6 pb-5 flex gap-3">
                        <button onclick="hideModal('receipt-modal-{{ $reservation->id }}')"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            Close
                        </button>
                        <button onclick="printReceiptFor({{ $reservation->id }})"
                                class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@push('scripts')
<script>
function showModal(id) {
    document.getElementById(id).style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function hideModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = '';
}

document.querySelectorAll('[id^="qr-modal-"], [id^="receipt-modal-"]').forEach(modal => {
    modal.addEventListener('click', function (e) {
        if (e.target === this) hideModal(this.id);
    });
});

document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('[id^="qr-modal-"], [id^="receipt-modal-"]')
        .forEach(el => { el.style.display = 'none'; });
    document.body.style.overflow = '';
});

function printReceiptFor(id) {
    const header = document.querySelector('#receipt-modal-' + id + ' .bg-green-500');
    const body   = document.getElementById('receipt-body-' + id);
    if (!header || !body) return;

    const win = window.open('', '_blank', 'width=420,height=680');
    win.document.write(`<!DOCTYPE html>
<html><head><title>Receipt #${id}</title><meta charset="utf-8">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:sans-serif;font-size:13px;color:#374151}
  .bg-green-500{background:#22c55e;color:white;padding:20px;text-align:center}
  .bg-green-500 p{color:#dcfce7;font-size:11px;margin-top:2px}
  .bg-green-500 h2{font-size:16px;font-weight:700}
  .px-6{padding-left:20px;padding-right:20px}.py-5{padding-top:16px;padding-bottom:16px}
  .space-y-2>*+*{margin-top:8px}.space-y-4>*+*{margin-top:14px}
  .flex{display:flex}.justify-between{justify-content:space-between}.gap-4{gap:16px}
  .text-gray-400{color:#9ca3af}.font-medium{font-weight:500}.text-right{text-align:right}
  .text-xs{font-size:11px}.text-base{font-size:15px}.font-bold{font-weight:700}
  .text-green-600{color:#16a34a}.text-center{text-align:center}
  hr{border:none;border-top:1px dashed #e5e7eb;margin:10px 0}
</style></head>
<body>${header.outerHTML}<div class="px-6 py-5">${body.innerHTML}</div></body></html>`);
    win.document.close();
    win.focus();
    win.print();
    win.close();
}

@php
    $activeIds = $reservations->filter(fn($r) => in_array($r->status, ['pending', 'active']))->pluck('id');
@endphp
@if($activeIds->isNotEmpty())
const watchedReservations = @json($activeIds->values());

const statusInterval = setInterval(async () => {
    try {
        const checks = watchedReservations.map(id =>
            fetch(`api/reservations/${id}/status`)
                .then(r => r.json())
                .then(data => ({ id, status: data.status }))
        );

        const results = await Promise.all(checks);
        const changed = results.some(r => {
            const row = document.getElementById('reservation-row-' + r.id);
            return row && !row.querySelector(`[class*="bg-yellow"], [class*="bg-blue"]`)
                ? false
                : r.status === 'completed' || r.status === 'cancelled';
        });

        if (changed) {
            clearInterval(statusInterval);
            window.location.reload();
        }
    } catch (e) {
        console.error('Polling error', e);
    }
}, 5000);
@endif

async function submitToBackend(qrData) {
    try {
        const response = await fetch('{{ route("staff.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept':       'application/json',
            },
            body: JSON.stringify({ qr_data: qrData }),
        });

        const result = await response.json();
        statusEl.textContent = result.success ? '✅ ' + result.message : '❌ ' + result.message;
        showModal(result);

    } catch (err) {
        console.error(err);
        statusEl.textContent = '❌ Network error. Please try again.';
        showModal({ success: false, message: 'Network error. Please try again.' });
        isProcessing = false;
    }
}
</script>
@endpush
@endsection