<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function paymentForm(Request $request, $plan)
    {
        $plan = SubscriptionPlan::findOrFail($plan);
        return view('payment.index', compact('plan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan_id'        => 'required|exists:subscription_plans,id',
            'amount'         => 'required|numeric|min:0',
            'payment_method' => 'required|in:gcash,maya,card,cash',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        Payment::create([
            'reservation_id' => null,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_status' => 'paid',
        ]);

        Subscription::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'plan_id'   => $plan->id,
                'price'     => $plan->price,
                'starts_at' => now(),
                'ends_at'   => now()->addDays($plan->duration),
                'status'    => 'active',
            ]
        );

        return redirect()->route('subscription.index')
            ->with('success', 'Subscription activated successfully.');
    }
}