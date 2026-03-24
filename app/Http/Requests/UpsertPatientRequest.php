<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()?->hasAnyRole(['admin', 'assistant', 'doctor']);
    }

    public function rules(): array
    {
        $patientId = $this->route('patient')?->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('patients', 'email')->ignore($patientId)],
            'phone' => ['required', 'string', 'max:50', Rule::unique('patients', 'phone')->ignore($patientId)],
            'birth_date' => ['required', 'date'],
            'address' => ['nullable', 'string'],
            'document_number' => ['required', 'string', 'max:100', Rule::unique('patients', 'document_number')->ignore($patientId)],
            'medical_record.blood_type' => ['nullable', 'string', 'max:10'],
            'medical_record.allergies' => ['nullable', 'string'],
            'medical_record.chronic_diseases' => ['nullable', 'string'],
            'medical_record.family_history' => ['nullable', 'string'],
            'medical_record.current_medications' => ['nullable', 'string'],
            'medical_record.height' => ['nullable', 'numeric', 'gt:1'],
            'medical_record.weight' => ['nullable', 'numeric', 'gt:1'],
        ];
    }
}
