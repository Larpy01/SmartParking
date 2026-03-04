<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ParkingLocation;

class IndexController extends Controller
{
    public function index()
    {
        $user = auth()->user();

    $locations = ParkingLocation::where('is_available', true)
        ->whereHas('slots', function ($query) {
            $query->where('status', 'available');
        })
        ->withCount(['slots as available_slots_count' => function ($query) {
            $query->where('status', 'available');
        }])
        ->limit(6)
        ->get();

        $activeReservation = $user
            ->reservations()
            ->with(['slot.location', 'vehicle'])
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->first();

        $subscription = $user->subscription()->with('plan')->first();

        return view('home', compact('locations', 'activeReservation', 'subscription'));
    }
}