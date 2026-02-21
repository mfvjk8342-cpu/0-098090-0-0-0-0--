<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\TimeSlotResource;
use App\Models\Appointment;
use App\Models\TimeSlot;
use App\Services\Appointment\AppointmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    use ApiResponse;

    public function __construct(protected AppointmentService $appointmentService) {}

    // Get All Time Slots
    public function index()
    {
        $timeslots = TimeSlot::all();
        return $this->success(TimeSlotResource::collection($timeslots), 'Time Slots', 200);
    }

    // Book Appointment
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'slot_id' => ['required', 'integer', 'exists:time_slots,id'],
            ]);

            $result = $this->appointmentService->bookAppointment($request->user(), $validated['slot_id']);
            return $this->success([
                'appointment' => new AppointmentResource($result['appointment']),
                'checkout_url' => $result['checkout_url'],
            ], 'Appointment Created', 201);
        } catch (ValidationException $e) {
            return $this->error('Validation failed', $e->errors(), 422);
        } catch (\Throwable $e) {
            $message = $e->getMessage() ?: 'Failed to create appointment';
            $status = match (true) {
                $e->getCode() >= 400 && $e->getCode() <= 599 => $e->getCode(),
                str_contains(strtolower($message), 'already have an active appointment') => 409,
                str_contains(strtolower($message), 'slot not available') => 409,
                default => 422,
            };

            return $this->error($message, [], $status);
        }
    }

    // Cancel Appointment
    public function destroy(Appointment $appointment)
    {
        $this->appointmentService->cancelAppointment($appointment);
        return $this->success(null, 'Appointment Cancelled', 200);
    }
}
