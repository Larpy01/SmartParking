@extends('layouts.app')

@section('title', 'Reservation details')

@section('content')
    @php
        $slot = $reservation->slot;
        $location = $slot?->location;
    @endphp

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
                    'pending' => 'bg-yellow-50 text-yellow-700',
                    'active' => 'bg-green-50 text-green-700',
                    'completed' => 'bg-gray-100 text-gray-700',
                    'canceled' => 'bg-red-50 text-red-700',
                ][$status] ?? 'bg-gray-100 text-gray-700';
            @endphp
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $color }}">
                {{ ucfirst($status) }}
            </span>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[3fr,2fr] items-start">
        <section class="bg-white rounded-lg border border-gray-200 p-4 space-y-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">
                    Reservation details
                </h2>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-gray-700">
                    <div>
                        <dt class="text-gray-500">Location</dt>
                        <dd class="font-medium">
                            {{ $location?->name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Slot</dt>
                        <dd class="font-medium">
                            @if ($slot)
                                #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }})
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Vehicle</dt>
                        <dd class="font-medium">
                            {{ optional($reservation->vehicle)->plate_num ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Time</dt>
                        <dd class="font-medium">
                            {{ $reservation->start_time }} - {{ $reservation->end_time }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">
                    Payment
                </h2>
                @if ($reservation->payment)
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-gray-700">
                        <div>
                            <dt class="text-gray-500">Amount</dt>
                            <dd class="font-medium">
                                ₱{{ number_format($reservation->payment->amount, 2) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Method</dt>
                            <dd class="font-medium">
                                {{ str_replace('_', ' ', ucfirst($reservation->payment->payment_method)) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Status</dt>
                            <dd class="font-medium">
                                {{ ucfirst($reservation->payment->status) }}
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="text-xs text-gray-500">
                        No payment has been recorded for this reservation yet.
                    </p>
                @endif
            </div>
        </section>

        <section class="space-y-4">
            <div class="bg-white rounded-lg border border-gray-200 p-4 text-xs text-gray-700 space-y-2">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">
                    Actions
                </h2>
                <p class="text-gray-500">
                    Status changes are handled by the existing routes
                    (<code>/reservations/{reservation}/start</code> and <code>/reservations/{reservation}/end</code>).
                </p>
            </div>
        </section>
    </div>
@endsection

