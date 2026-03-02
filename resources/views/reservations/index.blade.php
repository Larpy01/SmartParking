@extends('layouts.app')

@section('title', 'My Reservations')

@section('content')
<section class="p-6">
    <div class="bg-white rounded-lg border border-gray-200 p-6">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">My Reservations</h2>
            <a href="{{ route('parking.index') }}"
                class="inline-flex items-center px-3 py-2 rounded-md bg-blue-600 text-white text-xs font-semibold shadow-sm hover:bg-blue-700">
                Browse Parking Locations
            </a>
        </div>

        @if ($reservations->isEmpty())
            <p class="text-sm text-gray-500">You have no reservations yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-800 border-b">
                            <th class="py-2 pr-4 text-lg">Location</th>
                            <th class="py-2 pr-4 text-lg">Slot</th>
                            <th class="py-2 pr-4 text-lg">Vehicle</th>
                            <th class="py-2 pr-4 text-lg">Time</th>
                            <th class="py-2 pr-4 text-lg">Status</th>
                            <th class="py-2 text-lg text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($reservations as $reservation)
                            @php
                                $slot = $reservation->slot;
                                $location = $slot?->location;
                                $status = $reservation->status;
                                $color = [
                                    'pending'   => 'bg-yellow-50 text-yellow-700',
                                    'active'    => 'bg-blue-50 text-blue-700',
                                    'completed' => 'bg-green-100 text-green-700',
                                    'cancelled' => 'bg-red-50 text-red-700',
                                ][$status] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <tr class="align-top">
                                <td class="py-3 pr-4 text-gray-900">{{ $location?->name ?? '—' }}</td>
                                <td class="py-3 pr-4 text-gray-700">
                                    @if ($slot) #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }}) @else — @endif
                                </td>
                                <td class="py-3 pr-4 text-gray-700">{{ $reservation->vehicle?->plate_num ?? '—' }}</td>
                                <td class="py-3 pr-4 text-gray-700 text-xs">
                                    <span class="text-gray-400 text-[10px]">From</span>
                                    <span class="block">{{ $reservation->start_time?->format('M d, Y h:i A') }}</span>
                                    <span class="text-gray-400 text-[10px]">to</span>
                                    <span class="block">{{ $reservation->end_time?->format('M d, Y h:i A') }}</span>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $color }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    @if (in_array($status, ['pending', 'active']))
                                        <div class="flex flex-col items-end gap-2">
                                            <button
                                                onclick="showQr('qr-modal-{{ $reservation->id }}')"
                                                class="text-md text-blue-600 hover:text-blue-700 hover:underline font-medium cursor-pointer">
                                                Show QR <i class="fa-solid fa-qrcode ml-1"></i>
                                            </button>
                                            <form method="POST" action="{{ route('reservations.destroy', $reservation->id) }}"
                                                onsubmit="return confirm('Are you sure you want to cancel your reservation?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-md text-red-500 hover:text-red-700 hover:underline font-medium cursor-pointer">
                                                    Cancel
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

{{-- Modals --}}
@foreach ($reservations as $reservation)
    @if (in_array($reservation->status, ['pending', 'active']))
        @php
            $slot = $reservation->slot;
            $location = $slot?->location;
            $status = $reservation->status;
        @endphp
        <div id="qr-modal-{{ $reservation->id }}" class="fixed inset-0 z-50" style="display:none">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-white font-bold text-base">Reservation #{{ $reservation->id }}</p>
                            <p class="text-blue-100 text-xs mt-0.5">{{ $location?->name ?? '—' }}</p>
                        </div>
                        <button onclick="hideQr('qr-modal-{{ $reservation->id }}')"
                            class="text-white hover:text-blue-200 transition text-xl font-bold">✕</button>
                    </div>

                    <div class="p-6 flex flex-col items-center gap-4">
                        <p class="text-xs text-gray-500 text-center">Show this QR code to the parking staff to check in or out.</p>
                    
                    <div class="p-3 border-2 border-gray-100 rounded-xl bg-white shadow-inner">
                        {!! QrCode::size(200)->generate(
                            json_encode([
                                'reservation_id' => $reservation->id,
                                'token' => hash_hmac('sha256', $reservation->id, config('app.key')),
                            ])
                        ) !!}
                    </div>

                        <div class="w-full text-xs text-gray-600 space-y-1.5 border-t pt-4">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Slot</span>
                                <span class="font-medium">
                                    @if ($slot) #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }}) @else — @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Vehicle</span>
                                <span class="font-medium">{{ $reservation->vehicle?->plate_num ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Start</span>
                                <span class="font-medium">{{ $reservation->start_time?->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">End</span>
                                <span class="font-medium">{{ $reservation->end_time?->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Status</span>
                                <span class="font-medium capitalize">{{ $status }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<script>
    function showQr(id) {
        document.getElementById(id).style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function hideQr(id) {
        document.getElementById(id).style.display = 'none';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id^="qr-modal-"]').forEach(el => el.style.display = 'none');
            document.body.style.overflow = '';
        }
    });
</script>
@endsection