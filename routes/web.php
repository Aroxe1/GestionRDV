<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MerchantPlanningController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/reserver', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/reserver', [BookingController::class, 'store'])->name('booking.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/mes-reservations', [BookingController::class, 'index'])->name('booking.index');
    Route::patch('/mes-reservations/{reservation}/annuler', [BookingController::class, 'cancel'])->name('booking.cancel');
});

Route::middleware(['auth', 'merchant'])->prefix('commercant')->group(function () {
    Route::get('/planning', [MerchantPlanningController::class, 'index'])->name('merchant.planning');
    Route::get('/planning/events', [MerchantPlanningController::class, 'events'])->name('merchant.events');
    Route::patch('/planning/{reservation}/annuler', [MerchantPlanningController::class, 'cancel'])->name('merchant.cancel');
});

require __DIR__.'/auth.php';
