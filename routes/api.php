<?php

/**use App\Http\Controllers\Api\{
    ParkingLocationController as ApiParkingLocationController,
    ParkingSlotController as ApiParkingSlotController,
    ReservationController as ApiReservationController
}; */


use Illuminate\Http\Request;
use App\Models\ParkingSlot;
use Illuminate\Support\Facades\Route;


Route::get('/slot/{id}/{token}', function ($id, $token) {

    $secret = env('ESP32_SECRET');
    if (!$secret || $token !== $secret) {
        return response()->json([
            'error' => 'Unauthorized'
        ], 403);
    }

    $slot = ParkingSlot::find($id);
    if (!$slot) {
        return response()->json([
            'error' => 'Slot not found'
        ], 404);
    }

    return response()->json([
        'slot_id' => $slot->id,
        'status' => $slot->status,
        'last_updated' => $slot->updated_at->toDateTimeString()
    ]);
});
