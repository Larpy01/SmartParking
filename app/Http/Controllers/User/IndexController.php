<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ParkingLocation;
use App\Models\Reservation;

class IndexController extends Controller
{
    public function index()
    {
        $locations = ParkingLocation::where('is_available', true)
            ->limit(6)
            ->get();

        $activeReservation = auth()->user()
            ->reservations()
            ->with(['slot.location', 'vehicle'])
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->first();

        $subscription = auth()->user()->subscription()->with('plan')->first();

        return view('home', compact('locations', 'activeReservation', 'subscription'));
    }
}