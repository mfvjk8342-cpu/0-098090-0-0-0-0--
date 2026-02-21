<?php

use App\Http\Controllers\Admin\{
    AppointmentController as AdminAppointmentController,
    AvailabilityController,
    TimeSlotController
};
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\User\{
    AppointmentController,
    ProfileController,
    UserTimeSlotController
};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'isAdmin'])
    ->group(function () {
        // Availabilities
        Route::apiResource('availabilities', AvailabilityController::class);
        // Time Slots
        Route::apiResource('time-slots', TimeSlotController::class);
        // Appointments
        Route::apiResource('appointments', AdminAppointmentController::class);
    });

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // User Time SLot
    Route::get('user/time-slots', [UserTimeSlotController::class, 'index']);
    // User profile
    Route::get('user/profile', [ProfileController::class, 'show']);
    Route::put('user/profile', [ProfileController::class, 'update']);
    // Appointment -> book & cancel
    Route::post('appointments', [AppointmentController::class, 'store']);
    Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
// Get All Time Slots
Route::get('time-slots', [AppointmentController::class, 'index']);

// Strip Webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

require __DIR__ . '/auth.php';
