<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParkingLocation;
use App\Models\Reservation;

class ParkLocController extends Controller
{
    public function index(Request $request)
    {
        $query = ParkingLocation::where('is_available', true);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('address', 'like', '%' . $request->q . '%');
            });
        }

        $locations = $query->get();
        return view('parking.index', compact('locations'));
    }
    public function show(ParkingLocation $parkingLocation)
    {
        $parkingLocation->load('slots');
        return view('parking.show', compact('parkingLocation'));
    }
}