<?php

use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'welcome'])->name('welcome');
Route::get('/firebase-messaging-sw.js', [PushSubscriptionController::class, 'serviceWorker'])->name('fcm.sw');

// Guests
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:auth');
    Route::post('/auth/firebase', [LoginController::class, 'firebase'])->middleware('throttle:auth')->name('auth.firebase');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:auth');

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:auth')->name('password.email');
});

// Signed in
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')->name('verification.send');

    // Signed in + verified
    Route::middleware('firebase.verified')->group(function () {
        Route::get('/home', [HomeController::class, 'dashboard'])->name('home');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::resource('notes', NoteController::class)->except('show');

        Route::post('/push/subscriptions', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
        Route::delete('/push/subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

        // Admins (custom claim admin=true)
        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::patch('/users/{uid}', [UserController::class, 'update'])->name('users.update');
            Route::post('/users/{uid}/toggle-disabled', [UserController::class, 'toggleDisabled'])->name('users.toggle-disabled');
            Route::post('/users/{uid}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');
            Route::post('/users/{uid}/password-reset', [UserController::class, 'sendPasswordReset'])->name('users.password-reset');

            Route::get('/notifications', [NotificationController::class, 'create'])->name('notifications.create');
            Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
        });
    });
});
