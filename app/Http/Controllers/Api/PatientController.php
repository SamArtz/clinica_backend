<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertPatientRequest;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $patients = Patient::query()
            ->with('medicalRecord')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->paginate($request->integer('per_page', 15));

        return response()->json($patients);
    }

    public function store(UpsertPatientRequest $request): JsonResponse
    {
        if (! $request->user()?->hasAnyRole(['admin', 'assistant'])) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $patient = Patient::create($request->safe()->except('medical_record'));

        $medicalRecord = $request->validated('medical_record');
        if (! empty($medicalRecord)) {
            $patient->medicalRecord()->create($medicalRecord);
        }

        return response()->json([
            'message' => 'Paciente creado exitosamente.',
            'data' => $patient->fresh()->load('medicalRecord'),
        ], 201);
    }

    public function show(Patient $patient): JsonResponse
    {
        return response()->json([
            'data' => $patient->load(['medicalRecord', 'appointments.doctor:id,name']),
        ]);
    }

    public function update(UpsertPatientRequest $request, Patient $patient): JsonResponse
    {
        if (! $request->user()?->hasAnyRole(['admin', 'assistant', 'doctor'])) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if ($request->user()?->hasRole('doctor')) {
            $patient->medicalRecord()->updateOrCreate(
                ['patient_id' => $patient->id],
                $request->validated('medical_record', [])
            );
        } else {
            $patient->update($request->safe()->except('medical_record'));
            $patient->medicalRecord()->updateOrCreate(
                ['patient_id' => $patient->id],
                $request->validated('medical_record', [])
            );
        }

        return response()->json([
            'message' => 'Paciente actualizado exitosamente.',
            'data' => $patient->fresh()->load('medicalRecord'),
        ]);
    }

    public function destroy(Request $request, Patient $patient): JsonResponse
    {
        if (! $request->user()?->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $patient->delete();

        return response()->json(['message' => 'Paciente eliminado exitosamente.']);
    }
}
