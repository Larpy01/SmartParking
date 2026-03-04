@extends('layouts.app')

@section('title', 'Subscription')

@section('content')
<section class="space-y-6 p-6">

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

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-semibold mb-4">Current Plan</h2>

        @if ($subscription && in_array($subscription->status, ['active', 'pending']))
            @php
                $subscriptionPlan = $subscription->plan;
                $isFreePlan = strtolower($subscriptionPlan?->name) === 'free plan';
            @endphp

            <div class="text-sm space-y-2">
                <p><strong>Plan:</strong> {{ $subscriptionPlan?->name ?? '—' }}</p>
                <p><strong>Status:</strong> {{ ucfirst($subscription->status) }}</p>
                <p><strong>Price:</strong> ₱{{ number_format($subscription->price, 2) }}</p>
                <p><strong>Duration:</strong> {{ $subscription->plan?->duration ?? '—' }} days</p>
                <p><strong>Start:</strong> {{ $subscription->starts_at?->format('F j, Y') }}</p>
                <p><strong>End:</strong> {{ $subscription->ends_at?->format('F j, Y') }}</p>
            </div>

            @if ($subscription->status === 'pending')
                <p class="text-sm text-yellow-600 mt-4">⏳ Please wait for payment confirmation.</p>
                <form method="POST" action="{{ route('subscription.cancel') }}" class="mt-3"
                    onsubmit="return confirm('Are you sure you want to cancel your subscription?')">
                    @csrf
                    <div class="text-right">
                        <button class="w-40 bg-red-600 hover:bg-red-400 text-white text-sm py-2 px-4 rounded cursor-pointer">
                            Cancel Subscription
                        </button>
                    </div>
                </form>

            @elseif ($subscription->status === 'active' && !$isFreePlan)
                <form method="POST" action="{{ route('subscription.cancel') }}" class="mt-4 mb-4"
                    onsubmit="return confirm('Are you sure you want to cancel your subscription?')">
                    @csrf
                    <div class="text-right">
                        <button class="w-40 bg-red-600 hover:bg-red-400 text-white text-sm py-2 px-4 rounded cursor-pointer">
                            Cancel Subscription
                        </button>
                    </div>
                </form>

            @elseif ($isFreePlan)
                <p class="text-sm text-gray-500 mt-4">You are currently on the free plan. No cancellation needed.</p>
            @endif
            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('parking.index') }}"
                   class="inline-flex px-4 py-2 rounded-md bg-blue-500 text-white text-sm font-semibold shadow-sm hover:bg-blue-600">
                    Browse parking locations
                </a>
            </div>

        @elseif (!$subscription || in_array($subscription->status, ['cancelled', 'expired']))

            @if ($subscription?->status === 'cancelled')
                <p class="text-sm text-gray-500 mb-5">Your subscription has been cancelled. Choose a new plan below.</p>
            @else
                <p class="text-sm text-gray-500 mb-5">No active subscription.</p>
            @endif

            <div id="plans-grid" class="grid md:grid-cols-3 gap-6">
                @foreach ($plans as $plan)
                    @if ($plan->price == 0 && $hasUsedFreePlan)
                        @continue
                    @endif

                    <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">

                        <div class="grid md:grid-cols-2 mb-2">
                            <h3 class="text-2xl font-semibold text-left">{{ $plan->name }}</h3>
                            @if (!empty($plan->trial))
                                <p class="text-xs text-right mt-2">{{ $plan->trial }}</p>
                            @endif
                        </div>

                        <p class="text-3xl text-right font-bold mb-4 text-gray-900">
                            ₱{{ $plan->price }}
                        </p>

                        <ul class="text-sm space-y-2">
                            @foreach ($plan->features as $feature)
                                <li>✔ {{ $feature }}</li>
                            @endforeach
                        </ul>

                        <div class="mt-5">
                            <form method="POST" action="{{ route('subscription.show') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer">
                                    Choose Plan
                                </button>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>

        @else
            <p class="text-sm text-gray-500">
                Your subscription status is currently: <strong>{{ ucfirst($subscription->status) }}</strong>.
                Please contact support for assistance.
            </p>
        @endif

    </div>

</section>
@endsection