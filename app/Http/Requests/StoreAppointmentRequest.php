<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cambiar a auth()->check() si usas Sanctum
    }

    public function rules(): array
    {
        return [
            'patient_id'       => 'required|exists:patients,id',
            'doctor_id'        => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'reason'           => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.exists' => 'El paciente seleccionado no existe.',
            'doctor_id.exists'  => 'El médico seleccionado no existe.',
            'appointment_date.after_or_equal' => 'La fecha de la cita no puede ser en el pasado.',
        ];
    }
}