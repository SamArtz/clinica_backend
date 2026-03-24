<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertAppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Appointment::query()->with(['doctor:id,name', 'patient:id,first_name,last_name']);
        $user = $request->user();

        if ($user?->hasRole('doctor')) {
            $query->where('doctor_id', $user->id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->integer('doctor_id'));
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->integer('patient_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('appointment_date')) {
            $query->whereDate('appointment_date', $request->date('appointment_date'));
        }

        $appointments = $query
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate($request->integer('per_page', 15));

        return response()->json($appointments);
    }

    public function store(UpsertAppointmentRequest $request): JsonResponse
    {
        $validationError = $this->validateAvailability($request->validated());

        if ($validationError) {
            return $validationError;
        }

        $appointment = Appointment::create([
            ...$request->validated(),
            'status' => $request->input('status', 'pending'),
            'notes' => $request->input('notes'),
        ]);

        return response()->json([
            'message' => 'Cita creada exitosamente.',
            'data' => $appointment->load(['doctor:id,name', 'patient:id,first_name,last_name']),
        ], 201);
    }

    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        if ($request->user()?->hasRole('doctor') && $appointment->doctor_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json([
            'data' => $appointment->load(['doctor:id,name,email', 'patient.medicalRecord']),
        ]);
    }

    public function update(UpsertAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        if (! $request->user()?->hasAnyRole(['admin', 'assistant'])) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validationError = $this->validateAvailability($request->validated(), $appointment->id);

        if ($validationError) {
            return $validationError;
        }

        $appointment->update($request->validated());

        return response()->json([
            'message' => 'Cita actualizada exitosamente.',
            'data' => $appointment->fresh()->load(['doctor:id,name', 'patient:id,first_name,last_name']),
        ]);
    }

    public function destroy(Request $request, Appointment $appointment): JsonResponse
    {
        if (! $request->user()?->hasAnyRole(['admin', 'assistant'])) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $appointment->delete();

        return response()->json(['message' => 'Cita eliminada exitosamente.']);
    }

    private function validateAvailability(array $payload, ?int $ignoreAppointmentId = null): ?JsonResponse
    {
        $doctor = User::find($payload['doctor_id']);

        if (! $doctor || ! $doctor->hasRole('doctor')) {
            return response()->json([
                'message' => 'El usuario seleccionado no es un médico válido.',
            ], 422);
        }

        $date = Carbon::parse($payload['appointment_date']);
        $time = $payload['appointment_time'];
        $dayOfWeek = $date->dayOfWeek;

        $isWithinSchedule = DoctorSchedule::query()
            ->where('doctor_id', $payload['doctor_id'])
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->whereTime('start_time', '<=', $time)
            ->whereTime('end_time', '>=', $time)
            ->exists();

        if (! $isWithinSchedule) {
            return response()->json([
                'message' => 'Esa hora no está disponible.',
            ], 422);
        }

        $isAlreadyBooked = Appointment::query()
            ->where('doctor_id', $payload['doctor_id'])
            ->where('appointment_date', $payload['appointment_date'])
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled'])
            ->when($ignoreAppointmentId, fn (Builder $query) => $query->where('id', '!=', $ignoreAppointmentId))
            ->exists();

        if ($isAlreadyBooked) {
            return response()->json([
                'message' => 'Esa hora no está disponible.',
            ], 422);
        }

        return null;
    }
}
