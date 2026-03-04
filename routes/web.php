<?php

use App\Http\Controllers\User\IndexController;
use App\Http\Controllers\User\ParkLocController;
use App\Http\Controllers\User\ReservationController;
use App\Http\Controllers\User\VehicleController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\SubscriptionController;
use App\Http\Controllers\User\ContactsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminParkLocController;
use App\Http\Controllers\Admin\AdminParkingSlotController;
use App\Http\Controllers\Admin\AdminReservationController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Staff\StaffPaymentController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Admin\AdminPaymentController;

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
Route::view('/', 'landing')->name('landing');

Route::get('/login', [RegisteredUserController::class, 'showLogin'])->name('login');
Route::post('/login', [RegisteredUserController::class, 'login'])->name('login.submit');

Route::get('/register', [RegisteredUserController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'register'])->name('register.submit');

Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::view('/privacy', 'legalsht.privacy')->name('privacy');
Route::view('/terms', 'legalsht.terms')->name('terms');



//Auth User
Route::middleware('auth')->group(function () {
    


    Route::get('/logout', [RegisteredUserController::class, 'logout'])->name('logout');

    Route::post('/logout', [RegisteredUserController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
    
    Route::get('/home', [IndexController::class, 'index'])->name('home');

    Route::get('/parking', [ParkLocController::class, 'index'])
        ->name('parking.index');

    Route::get('/parking/{parkingLocation}', [ParkLocController::class, 'show'])
        ->name('parking.show');

    Route::post('/contact', [ContactsController::class, 'store'])
        ->name('contact.store');

    // Vehicles
    Route::resource('vehicles', VehicleController::class)->only(['index', 'store', 'destroy']);

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index'])
        ->name('reservations.index');

    Route::get('/reservations/create/{parkingLocation}', [ReservationController::class, 'create'])
        ->name('reservations.create');

    Route::get('/reservations', [ReservationController::class, 'show'])
        ->name('reservations.show');

    Route::post('/reservations', [ReservationController::class, 'store'])
        ->name('reservations.store');

    Route::post('/reservations/{reservation}/start', [ReservationController::class, 'start'])
        ->name('reservations.start');

    Route::post('/reservations/{reservation}/end', [ReservationController::class, 'end'])
        ->name('reservations.end');

    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])
        ->name('reservations.destroy');
        
    Route::get('payment/{plan}', [PaymentController::class, 'paymentForm'])
        ->name('payment.form');

    Route::post('payment', [PaymentController::class, 'store'])
        ->name('payment.store');

    // Subscriptions
    Route::get('/subscription',
        [SubscriptionController::class, 'index'])
        ->name('subscription.index');

    Route::post('/subscription/show',
        [SubscriptionController::class, 'show'])
        ->name('subscription.show');

    Route::post('/subscription/cancel',
        [SubscriptionController::class, 'cancel'])
        ->name('subscription.cancel');

    //Proflile
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');

    Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('/scan', function () {return view('staff.scan');})->name('scan.page');
        Route::post('/scan', [StaffDashboardController::class, 'scan'])->name('scan');
        Route::post('/reservations/{reservation}/status', [StaffDashboardController::class, 'updateStatus'])->name('reservations.status');
        Route::get('/payments', [StaffPaymentController::class, 'index'])->name('payments.index');
        Route::patch('/payments/{reservation}/mark-paid', [StaffPaymentController::class, 'markPaid'])->name('payments.markPaid');
        Route::patch('/payments/{reservation}/refund', [StaffPaymentController::class, 'refund'])->name('payments.refund');

    });
});

//Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard',
            [AdminDashboardController::class, 'index'])
            ->name('dashboard');

       Route::get('/charts',
            [AdminDashboardController::class, 'charts'])
            ->name('charts');
        
        // Parking Locations
        Route::resource('parking-locations',
            AdminParkLocController::class);

        // Parking Slots
        Route::resource('parking-slots',
            AdminParkingSlotController::class);

        // Reservations
        Route::resource('reservations',
            AdminReservationController::class)
            ->only(['index', 'show', 'destroy']);
        
        Route::get('/reservations/scan/{id}/{token}', [AdminReservationController::class, 'scan'])
        ->name('admin.reservations.scan');

        // Users
        Route::resource('users',
            AdminUserController::class)
            ->only(['index', 'show', 'update', 'destroy']);

        // Subscriptions
        Route::get('/subscriptions', [AdminPaymentController::class, 'index'])->name('subscriptions.index');
        Route::patch('/subscriptions/{subscription}/approve', [AdminPaymentController::class, 'approve'])->name('subscriptions.approve');
        Route::patch('/subscriptions/{subscription}/reject', [AdminPaymentController::class, 'reject'])->name('subscriptions.reject');

});



