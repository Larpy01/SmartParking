@extends('layouts.app')

@section('title', 'Reservation details')

@section('content')
<section class="p-6">
    @php
        $slot     = $reservation->slot;
        $location = $slot?->location;
        $status   = $reservation->status;
        $color    = [
            'pending'   => 'bg-yellow-50 text-yellow-700',
            'active'    => 'bg-green-50 text-green-700',
            'completed' => 'bg-gray-100 text-gray-700',
            'cancelled' => 'bg-red-50 text-red-700',
        ][$status] ?? 'bg-gray-100 text-gray-700';
    @endphp

    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Reservation #{{ $reservation->id }}</h1>
            <p class="text-xs text-gray-500">{{ $location?->name ?? 'Unknown location' }}</p>
        </div>
        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $color }}">
            {{ ucfirst($status) }}
        </span>
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
                            @if($slot) #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }}) @else — @endif
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
                </dl>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Payment</h2>
                @if($reservation->payment)
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-gray-700">
                        <div>
                            <dt class="text-gray-500">Amount</dt>
                            <dd class="font-medium">
                                @if($reservation->is_free)
                                    <span class="text-green-600 font-semibold">FREE</span>
                                @else
                                    ₱{{ number_format($reservation->payment->amount, 2) }}
                                @endif
                            </dd>
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

            <a href="{{ route('reservations.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Back to reservations
            </a>
        </section>

        <section class="space-y-4">
            <div class="bg-white rounded-lg border border-gray-200 p-4 text-xs text-gray-700 space-y-2">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">Status</h2>
                <p class="text-gray-500">
                    @if($status === 'pending')
                        Your reservation is confirmed. Show your QR code at the entrance.
                    @elseif($status === 'active')
                        You are currently parked. Your timer is running.
                    @elseif($status === 'completed')
                        This reservation has been completed.
                    @else
                        This reservation has been {{ $status }}.
                    @endif
                </p>
            </div>
        </section>
    </div>
</section>
@endsection

@push('scripts')
@if(in_array($reservation->status, ['pending', 'active']))
<script>
    const reservationId = {{ $reservation->id }};
    const currentStatus = '{{ $reservation->status }}';

    const interval = setInterval(async () => {
        try {
            const res  = await fetch(`/reservations/${reservationId}/status`);
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
@endpush