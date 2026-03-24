<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Support\AppointmentAvailability;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $doctor = User::find($request->integer('doctor_id'));

        if (! $doctor || ! $doctor->hasRole('doctor')) {
            return response()->json([
                'message' => 'El usuario seleccionado no es un médico válido.',
            ], 422);
        }

        $date = $request->string('appointment_date')->toString();
        $time = $request->string('appointment_time')->toString();

        if (AppointmentAvailability::isPastDate($date)) {
            return response()->json([
                'message' => 'La fecha de la cita no puede estar en el pasado.',
            ], 422);
        }

        if (! AppointmentAvailability::isWithinDoctorSchedule($doctor->id, $date, $time)) {
            return response()->json([
                'message' => 'El médico no tiene horario disponible en ese bloque de tiempo.',
            ], 422);
        }

        if (AppointmentAvailability::hasConflict($doctor->id, $date, $time)) {
            return response()->json([
                'message' => 'Esa hora no está disponible.',
            ], 422);
        }

        try {
            $appointment = Appointment::create([
                ...$request->validated(),
                'appointment_time' => AppointmentAvailability::normalizeTime($time),
                'status' => $request->input('status', 'pending'),
                'notes' => $request->input('notes'),
            ]);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23505') {
                return response()->json([
                    'message' => 'Esa hora no está disponible.',
                ], 422);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Cita creada exitosamente.',
            'data' => $appointment->load(['doctor', 'patient']),
        ], 201);
    }
}
