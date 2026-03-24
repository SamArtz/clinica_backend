<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $doctor = User::find($request->doctor_id);

        if (! $doctor || ! $doctor->hasRole('doctor')) {
            return response()->json([
                'message' => 'El usuario seleccionado no es un médico válido.',
            ], 422);
        }

        $date = Carbon::parse($request->appointment_date);
        $time = $request->appointment_time;
        $dayOfWeek = $date->dayOfWeek;

        $isWithinSchedule = DoctorSchedule::query()
            ->where('doctor_id', $request->doctor_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->whereTime('start_time', '<=', $time)
            ->whereTime('end_time', '>=', $time)
            ->exists();

        if (! $isWithinSchedule) {
            return response()->json([
                'message' => 'El médico no tiene horario disponible en ese bloque de tiempo.',
            ], 422);
        }

        $isAlreadyBooked = Appointment::query()
            ->where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($isAlreadyBooked) {
            return response()->json([
                'message' => 'El médico ya tiene una cita programada para esta fecha y hora.',
            ], 422);
        }

        $appointment = Appointment::create([
            ...$request->validated(),
            'status' => $request->input('status', 'pending'),
            'notes' => $request->input('notes'),
        ]);

        return response()->json([
            'message' => 'Cita creada exitosamente.',
            'data' => $appointment->load(['doctor', 'patient']),
        ], 201);
    }
}
