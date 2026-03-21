<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $date = Carbon::parse($request->appointment_date);
        $time = $request->appointment_time;
        $dayOfWeek = $date->dayOfWeek; // 0 (Dom) a 6 (Sab)

        // 1. VALIDACIÓN: ¿El médico atiende ese día y a esa hora?
        $isWithinSchedule = DoctorSchedule::where('user_id', $request->doctor_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->whereTime('start_time', '<=', $time)
            ->whereTime('end_time', '>=', $time)
            ->exists();

        if (!$isWithinSchedule) {
            return response()->json([
                'message' => 'El médico no tiene horario disponible en ese bloque de tiempo.'
            ], 422);
        }

        // 2. VALIDACIÓN: ¿Ya existe otra cita a la misma hora para ese médico?
        $isAlreadyBooked = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled']) // No contar si la cita fue cancelada
            ->exists();

        if ($isAlreadyBooked) {
            return response()->json([
                'message' => 'El médico ya tiene una cita programada para esta fecha y hora.'
            ], 422);
        }

        // 3. PERSISTENCIA: Todo está bien, creamos la cita
        $appointment = Appointment::create($request->validated());

        return response()->json([
            'message' => 'Cita creada exitosamente.',
            'data' => $appointment
        ], 201);
    }
}