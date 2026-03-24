<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'appointment_date' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'appointment_time' => fake()->randomElement(['08:00', '09:00', '10:00', '11:00']),
            'reason' => fake()->sentence(),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['pending', 'confirmed', 'completed']),
        ];
    }
}
