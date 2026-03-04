@extends('layouts.app')

@section('title', 'Book a Reservation')

@section('content')
<div class="p-6">


        @if (session('success'))
            <div id="success-alert"
                class="flex items-start justify-between bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg p-4 text-sm shadow-sm">
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
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Reserve a Spot</h1>
        <p class="text-sm text-gray-500">at {{ $parkingLocation->name }}</p>
    </div>

    @if ($vehicles->isEmpty())
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-bold text-red-800">No Vehicle Found</h3>
                    <p class="text-xs text-red-700">You need to register a vehicle before you can make a reservation.</p>
                </div>
            </div>
            <a href="{{ route('vehicles.index') }}"
               class="px-4 py-2 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition">
                Add Vehicle Now
            </a>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('reservations.store') }}" method="POST">
        @csrf
        <input type="hidden" name="parking_location_id" value="{{ $parkingLocation->id }}">

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select a Slot</label>
                    @if ($slots->isEmpty())
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-md text-gray-400 text-sm italic">
                            No available slots at this location.
                        </div>
                    @else
                        <select name="slot_id" required
                            class="w-full h-10 px-3 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($slots as $slot)
                                <option value="{{ $slot->id }}" {{ old('slot_id') == $slot->id ? 'selected' : '' }}>
                                    Slot #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Your Vehicle</label>
                    @if ($vehicles->isEmpty())
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-md text-gray-400 text-sm italic">
                            No vehicles available.
                        </div>
                    @else
                        <select name="vehicle_id" required
                            class="w-full h-10 px-3 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate_num }} - {{ $vehicle->model }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- Payment Method --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Method
                    </label>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach (['gcash' => 'GCash', 'maya' => 'Maya', 'card' => 'Card', 'cash' => 'Cash'] as $value => $label)
                            <label 
                                class="payment-label relative flex items-center justify-center gap-2 border-2 rounded-lg p-3 cursor-pointer transition
                                {{ old('payment_method') === $value ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}">

                                <input 
                                    type="radio" 
                                    name="payment_method" 
                                    value="{{ $value }}"
                                    class="hidden"
                                    {{ old('payment_method') === $value ? 'checked' : '' }}
                                    onchange="
                                        document.querySelectorAll('.payment-label').forEach(el => {
                                            el.classList.remove('border-blue-500','bg-blue-50');
                                            el.classList.add('border-gray-200');
                                        });
                                        this.closest('.payment-label').classList.remove('border-gray-200');
                                        this.closest('.payment-label').classList.add('border-blue-500','bg-blue-50');
                                    "
                                >

                                <span class="text-sm font-medium text-gray-700">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @error('payment_method')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    @if ($vehicles->isEmpty() || $slots->isEmpty()) disabled @endif
                    class="w-full py-3 rounded-lg font-bold transition cursor-pointer {{ $vehicles->isEmpty() || $slots->isEmpty() ? 'bg-gray-300 cursor-not-allowed text-gray-500' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                    Confirm Reservation
                </button>
            </div>

            <div class="bg-gray-50 p-6 rounded-xl border border-dashed border-gray-300 space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Pricing Information</h3>
                    <p class="text-sm text-gray-600 mb-4">You are booking at {{ $parkingLocation->name }}.</p>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Hourly Rate</span>
                            <span class="font-bold">₱{{ number_format(max(20.00, $parkingLocation->hourly_rate), 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-xs text-gray-500 italic">Minimum charge applied</span>
                            <span class="text-xs font-semibold text-gray-700">₱20.00</span>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-blue-50 text-blue-700 rounded-md text-[11px] leading-relaxed">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Your total is calculated based on duration. A minimum fee of <strong>₱20.00</strong> applies to all bookings at this location.
                    </div>
                </div>

                @if ($subscription->status === 'active')
                    @if ($subscription->plan_id !== 1)                   
                        @php $freeHoursLeft = $subscription->freeHoursLeft(); @endphp
                        <div class="p-3 rounded-md text-[11px] leading-relaxed {{ $freeHoursLeft > 0 ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            <i class="fa-solid fa-clock mr-1"></i>
                            @if ($freeHoursLeft > 0)
                                You have <strong>{{ number_format($freeHoursLeft, 1) }} hours for free</strong> remaining this week.
                                Parking within this limit will be free of charge.
                            @else
                                You have used all your free hours for this week. Normal rates apply.
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </form>
</div>
@endsection