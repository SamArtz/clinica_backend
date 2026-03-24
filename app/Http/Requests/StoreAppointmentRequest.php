<?php

namespace App\Http\Requests;

use App\Support\AppointmentAvailability;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:users,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:' . AppointmentAvailability::today()->toDateString()],
            'appointment_time' => ['required', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.exists' => 'El paciente seleccionado no existe.',
            'doctor_id.exists' => 'El médico seleccionado no existe.',
            'appointment_date.after_or_equal' => 'La fecha de la cita no puede estar en el pasado.',
        ];
    }
}
