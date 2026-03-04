<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\ParkingSlot;
use App\Mail\ParkingReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Luigel\Paymongo\Facades\Paymongo;

class StaffDashboardController extends Controller
{
    public function index()
    {
        $totalReserved  = Reservation::whereIn('status', ['pending', 'active'])->count();
        $totalActive    = Reservation::where('status', 'active')->count();
        $totalCompleted = Reservation::where('status', 'completed')->whereDate('updated_at', today())->count();
        $totalCancelled = Reservation::where('status', 'cancelled')->whereDate('updated_at', today())->count();

        $slotStats = ParkingSlot::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $reservations = Reservation::with(['slot.location', 'vehicle', 'user'])
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->get();

        $slotTypes = ParkingSlot::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        return view('staff.dashboard', compact(
            'totalReserved', 'totalActive', 'totalCompleted',
            'totalCancelled', 'slotStats', 'reservations', 'slotTypes',
        ));
    }

    public function scan(Request $request)
    {
        $request->validate(['qr_data' => 'required|string']);

        // Accept either raw JSON or base64-encoded JSON payloads from QR codes.
        $payload = $request->qr_data;
        $decoded = @base64_decode($payload, true);
        if($decoded !== false && json_decode($decoded, true) !== null) {
            $data = json_decode($decoded, true);
        } else {
            $data = json_decode($payload, true);
        }

        if (!$data || !isset($data['reservation_id'], $data['token'])) {
            return response()->json(['success' => false, 'message' => 'Invalid QR code.'], 422);
        }

        $expected = hash_hmac('sha256', $data['reservation_id'], config('app.key'));
        if (!hash_equals($expected, $data['token'])) {
            return response()->json(['success' => false, 'message' => 'QR code verification failed.'], 403);
        }

        $reservation = Reservation::with(['slot.location', 'vehicle', 'user.subscription.plan', 'payment'])
            ->find($data['reservation_id']);

        if (!$reservation) {
            return response()->json(['success' => false, 'message' => 'Reservation not found.'], 404);
        }

        // First scan = check in
        if ($reservation->status === 'pending') {
            $reservation->update([
                'start_time' => now()->addMinutes(5),
                'status'     => 'active',
            ]);
            $reservation->slot->update(['status' => 'occupied']);

            return response()->json([
                'success' => true,
                'action'  => 'checked_in',
                'message' => 'Checked in. Parking timer starts in 5 minutes.',
                'reservation' => $this->formatReservation($reservation),
            ]);
        }

        // Second scan = check out
        if ($reservation->status === 'active') {
            return $this->checkout($reservation);
        }

        return response()->json(['success' => false, 'message' => 'Reservation is already ' . $reservation->status . '.'], 422);
    }

    private function checkout(Reservation $reservation)
    {
        $endTime      = now();
        $start        = $reservation->start_time;
        $totalMinutes = max(60, $start->diffInMinutes($endTime)); // minimum 1 hour
        $totalHours   = ceil($totalMinutes / 60);
        $rate         = $reservation->slot->location->hourly_rate ?? 20;

        // Subscription free hours logic
        $user         = $reservation->user;
        $subscription = $user->subscription;
        $freeHours    = 0;
        $paidHours    = 0;
        $totalAmount  = 0;
        $isFree       = false;

        if ($subscription && $subscription->status === 'active' && ($subscription->plan->free_hours_per_week ?? 0) > 0) {
            $freeHoursLeft = $subscription->freeHoursLeft();

            if ($freeHoursLeft >= $totalHours) {
                // Fully covered by subscription
                $freeHours   = $totalHours;
                $paidHours   = 0;
                $totalAmount = 0;
                $isFree      = true;
            } elseif ($freeHoursLeft > 0) {
                // Partially covered
                $freeHours   = $freeHoursLeft;
                $paidHours   = $totalHours - $freeHoursLeft;
                $totalAmount = $paidHours * $rate;
            } else {
                // No free hours left
                $paidHours   = $totalHours;
                $totalAmount = $totalHours * $rate;
            }
        } else {
            // No subscription or free plan
            $paidHours   = $totalHours;
            $totalAmount = $totalHours * $rate;
        }

        // Update reservation
        $reservation->update([
            'end_time'     => $endTime,
            'total_amount' => $totalAmount,
            'free_hours'   => $freeHours,
            'paid_hours'   => $paidHours,
            'is_free'      => $isFree,
            'status'       => 'completed',
        ]);

        $reservation->slot->update(['status' => 'available']);

        $payment = $reservation->payment;

        // Free — no charge needed
        if ($totalAmount == 0) {
            $payment->update(['payment_status' => 'paid', 'amount' => 0]);
            Mail::to($user->email)->send(new ParkingReceipt($reservation->fresh(['payment', 'slot.location', 'vehicle', 'user'])));

            return response()->json([
                'success' => true,
                'action'  => 'checked_out',
                'message' => "Free parking! {$freeHours} free hour(s) used from subscription.",
                'reservation' => $this->formatReservation($reservation),
            ]);
        }

        // Cash payment — mark as paid immediately
        if ($payment->payment_method === 'cash') {
            $payment->update(['payment_status' => 'paid', 'amount' => $totalAmount]);
            Mail::to($user->email)->send(new ParkingReceipt($reservation->fresh(['payment', 'slot.location', 'vehicle', 'user'])));

            return response()->json([
                'success' => true,
                'action'  => 'checked_out',
                'message' => "Cash payment of ₱{$totalAmount}. Receipt sent to {$user->email}.",
                'reservation' => $this->formatReservation($reservation),
            ]);
        }

        /**Online payment via PayMongo
        try {
            $paymentIntent = Paymongo::paymentIntent()->create([
                'amount'               => $totalAmount,
                'currency'             => 'PHP',
                'payment_method_types' => [$payment->payment_method],
                'description'          => 'Smart Parking Reservation #' . $reservation->id,
            ]);

            $payment->update([
                'amount'                      => $totalAmount,
                'payment_status'              => 'processing',
                'paymongo_payment_intent_id'  => $paymentIntent->id,
            ]);

            Mail::to($user->email)->send(new ParkingReceipt($reservation->fresh(['payment', 'slot.location', 'vehicle', 'user'])));

            return response()->json([
                'success' => true,
                'action'  => 'checked_out',
                'message' => "₱{$totalAmount} charged via {$payment->payment_method}. Receipt sent.",
                'reservation' => $this->formatReservation($reservation),
            ]);

        } catch (\Exception $e) {
            $payment->update(['payment_status' => 'failed', 'amount' => $totalAmount]);

            return response()->json([
                'success' => true,
                'action'  => 'payment_failed',
                'message' => "Online payment failed. Please collect ₱{$totalAmount} cash.",
                'reservation' => $this->formatReservation($reservation),
            ]);
        } */ 
       $payment->update(['payment_status' => 'paid', 'amount' => $totalAmount]);
            Mail::to($user->email)->send(new ParkingReceipt($reservation->fresh(['payment', 'slot.location', 'vehicle', 'user'])));

            return response()->json([
                'success' => true,
                'action'  => 'checked_out',
                'message' => "Payment of ₱{$totalAmount} via {$payment->payment_method} recorded. Receipt sent to {$user->email}.",
                'reservation' => $this->formatReservation($reservation),
            ]);
    }

    private function formatReservation(Reservation $reservation): array
    {
        return [
            'id'             => $reservation->id,
            'status'         => $reservation->status,
            'user'           => $reservation->user?->name,
            'vehicle'        => $reservation->vehicle?->plate_num,
            'slot'           => $reservation->slot ? '#' . $reservation->slot->slot_number . ' (' . ucfirst($reservation->slot->type) . ')' : '—',
            'location'       => $reservation->slot?->location?->name,
            'start_time'     => $reservation->start_time?->format('M d, Y h:i A'),
            'end_time'       => $reservation->end_time?->format('M d, Y h:i A'),
            'is_free'        => $reservation->is_free,
            'free_hours'     => $reservation->free_hours,
            'paid_hours'     => $reservation->paid_hours,
            'total_amount'   => $reservation->total_amount,
            'payment_method' => $reservation->payment?->payment_method,
            'payment_status' => $reservation->payment?->payment_status,
        ];
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $request->validate(['status' => 'required|in:active,completed']);

        $reservation->update(['status' => $request->status]);

        if ($request->status === 'completed') {
            $reservation->slot->update(['status' => 'available']);
        } elseif ($request->status === 'active') {
            $reservation->slot->update(['status' => 'occupied']);
        }

        return response()->json(['success' => true, 'status' => $request->status]);
    }
}