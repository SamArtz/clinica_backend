<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition(): array
    {
        return [
            'blood_type' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'allergies' => fake()->randomElement(['Ninguna', 'Penicilina', 'Polen', 'Mariscos']),
            'chronic_diseases' => fake()->randomElement(['Ninguna', 'Hipertensión', 'Diabetes']),
            'family_history' => fake()->sentence(),
            'current_medications' => fake()->randomElement(['Ninguno', 'Ibuprofeno', 'Losartán', 'Metformina']),
            'height' => fake()->randomFloat(2, 1.45, 1.95),
            'weight' => fake()->randomFloat(2, 45, 110),
        ];
    }
}
