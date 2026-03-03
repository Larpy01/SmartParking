<?php

use App\Http\Controllers\Api\{
    ParkingLocationController as ApiParkingLocationController,
    ParkingSlotController as ApiParkingSlotController,
    ReservationController as ApiReservationController
};

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/parking-locations',
        [ApiParkingLocationController::class, 'index']);

    Route::get('/parking-locations/{id}/slots',
        [ApiParkingSlotController::class, 'index']);

    Route::post('/reservations',
        [ApiReservationController::class, 'store']);
});

Route::get('/api/slot/{id}/{token}', function ($id, $token) {

    if ($token !== 'ESP32_SECRET') {
        abort(403);
    }

    $slot = \App\Models\ParkingSlot::findOrFail($id);

    return response()->json([
        'status' => $slot->status
    ]);
});
