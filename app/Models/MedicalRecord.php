<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecord extends Model
{
    use HasFactory;

    // Campos que el seeder y el API pueden llenar
    protected $fillable = [
        'patient_id',
        'blood_type',
        'allergies',
        'chronic_diseases',
        'family_history',
        'current_medications',
        'height',
        'weight'
    ];

    /**
     * Relación 1:1 con Pacientes
     * Cada expediente clínico pertenece a un único paciente
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}