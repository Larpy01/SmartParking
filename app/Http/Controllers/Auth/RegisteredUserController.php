<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    
    public function showRegister(Request $request) {

        $selectedPlanId = $request->query('plan');
         
        $selectedPlan = null;

    if ($selectedPlanId) {
        $selectedPlan = $selectedPlanId ? Subscription::find($selectedPlanId) : null;
    }

        return view('auth.register', [
        'selectedPlan' => $selectedPlan,
        'selectedPlanId' => $selectedPlanId,
        ]);

    }
 
 
public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'terms' => 'accepted',
        'phone_number' => [
            'required',
            'digits_between:10',
            'unique:users,phone_number'
        ],
        'password' => [
            'required',
            'min:8',
            'max:24',
            'confirmed',
            'regex:/[A-Z]/', 
            'regex:/[0-9]/',
        ],
    ], [
        'terms.accepted' => 'You must agree to the Privacy Policy and Terms of Service.', // <--- Custom message
        'password.regex' => 'Password must contain at least one uppercase letter and a number.',
        'password.confirmed' => 'Passwords do not match.',
        'phone_number.digits_between' => 'Phone number must be only 10 digits.',
        'email.unique' => 'This email is already registered.',
        'phone_number.unique' => 'This phone number is already registered.',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone_number' => $request->phone_number,
        'password' => Hash::make($request->password),
    ]);


    if ($request->filled('plan_id')) {
        $plan = Subscription::find($request->plan_id);

        if ($plan) {
            Auth::login($user);

            return redirect()
                ->route('subscription.payment.form', ['plan' => $plan->id])
                ->with('success', 'Please upload your payment proof to activate your subscription.');
        }
    }

    $subscription = $user->subscription()->latest()->first();

    if ($subscription && $subscription->status === 'pending') {
        Auth::login($user);
        return redirect()->route('landing')->with('success', 'Thank you for registering. Your subscription is pending approval.');
    }

    if ($subscription && $subscription->status === null) {
        Auth::login($user);
        return redirect()->route('subscription.payment.form');
    }


    return redirect('login')->with('success', 'You have been registered successfully. Please log in to continue.');
}

    public function showLogin() {
        return view('auth.login');
    }
 
 
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $credentials = $request->only('email', 'password');
    $remember = $request->boolean('remember');

    if (Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate();
        $user = Auth::user();   

        if ($user->hasAdminAccess()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasOperatorAccess()) {
            return redirect()->route('staff.dashboard');
        }

        $subscription = $user->subscription()->latest()->first();

        if ($subscription && $subscription->status === 'pending') {
            return redirect()->route('landing')
                ->with('success', 'Your subscription is pending approval.');
        }

        session([
            'subscription_status' => $subscription ? $subscription->status : 'none'
        ]);

        return redirect()->route('home')->with('success', 'You have logged in successfully.');
    }

    return back()->withErrors([
        'email' => 'Invalid email or password.',
    ])->onlyInput('email');
}
 
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing');
    }

}