<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ParkingLocation;
use App\Models\Reservation;
use App\Models\ParkingSlot;
use App\Models\Payment;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    private function hasActiveSubscription(): bool
    {
        $subscription = auth()->user()->subscription;
        return $subscription
            && $subscription->status === 'active'
            && $subscription->ends_at->isFuture();
    }

    public function index()
    {
        $reservations = auth()->user()->reservations()
            ->with(['slot.location', 'vehicle', 'payment'])
            ->latest()
            ->get();
        return view('reservations.index', compact('reservations'));
    }

    public function create($parkingLocation)
    {
        if (!$this->hasActiveSubscription()) {
            return redirect()->route('subscription.index')
                ->with('error', 'You need an active subscription to make a reservation.');
        }

        $hasActiveReservation = auth()->user()->reservations()
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($hasActiveReservation) {
            return redirect()->route('reservations.index')
                ->with('error', 'You already have an active reservation. Please complete or cancel it before making a new one.');
        }

        $parkingLocation = ParkingLocation::findOrFail($parkingLocation);
        $slots           = $parkingLocation->slots()->where('status', 'available')->get();
        $vehicles        = auth()->user()->vehicles()->get();
        $subscription    = auth()->user()->subscription;

        return view('reservations.create', compact('parkingLocation', 'slots', 'vehicles', 'subscription'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'slot_id'             => 'required|exists:parking_slots,id',
            'vehicle_id'          => 'required|exists:vehicle,id',
            'parking_location_id' => 'required|exists:parking_locations,id',
            'payment_method'      => 'required|in:gcash,maya,card,cash',
        ]);

        $slot = ParkingSlot::findOrFail($request->slot_id);
        if ($slot->status !== 'available') {
            return back()->withErrors(['slot_id' => 'This slot is no longer available.']);
        }

        $reservation = Reservation::create([
            'user_id'    => auth()->id(),
            'slot_id'    => $request->slot_id,
            'vehicle_id' => $request->vehicle_id,
            'status'     => 'pending',
            'is_free'    => false,
        ]);

        Payment::create([
            'reservation_id' => $reservation->id,
            'amount'         => 0,
            'payment_method' => $request->payment_method,
            'payment_status' => 'unpaid',
        ]);

        $slot->update(['status' => 'reserved']);

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation created. Show your QR code at the entrance.');
    }

    public function show(Reservation $reservation)
    {
        abort_if($reservation->user_id !== auth()->id(), 403);
        $reservation->load(['slot.location', 'vehicle', 'payment']);
        return view('reservations.show', compact('reservation'));
    }

    public function start(Reservation $reservation)
    {
        $reservation->update(['status' => 'active', 'start_time' => now()]);
        $reservation->slot->update(['status' => 'active']);
        return back();
    }

    public function end(Reservation $reservation)
    {
        $reservation->update(['status' => 'completed']);
        $reservation->slot->update(['status' => 'available']);
        return back();
    }

    public function destroy(Reservation $reservation)
    {
        abort_if($reservation->user_id !== auth()->id(), 403);

        // ── Pending / active → cancel (soft) ─────────────────────────────────
        if (in_array($reservation->status, ['pending', 'active'])) {
            $reservation->update(['status' => 'cancelled']);
            $reservation->slot->update(['status' => 'available']);
            // Hard-delete so slot record is freed; change to soft-delete if needed
            $reservation->delete();
            return back()->with('success', 'Reservation cancelled.');
        }

        // ── Completed / cancelled → hard delete ───────────────────────────────
        if (in_array($reservation->status, ['completed', 'cancelled'])) {
            $reservation->delete();
            return back()->with('success', 'Reservation deleted.');
        }

        return back()->with('error', 'This reservation cannot be deleted.');
    }
}