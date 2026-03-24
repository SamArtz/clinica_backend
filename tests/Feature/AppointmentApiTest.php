<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppointmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'doctor']);
        Role::create(['name' => 'assistant']);
        Role::create(['name' => 'admin']);
    }

    public function test_it_creates_a_valid_appointment(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        $patient = Patient::factory()->create();

        DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($doctor, 'sanctum')
            ->postJson('/api/appointments', [
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_date' => now()->addDay()->format('Y-m-d'),
                'appointment_time' => '10:00',
                'reason' => 'Consulta general',
            ]);

        $response->assertCreated();
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_it_rejects_duplicate_appointments_for_same_doctor_and_time(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        $patient = Patient::factory()->create();

        DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'is_active' => true,
        ]);

        Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'appointment_time' => '10:00',
            'reason' => 'Primera cita',
            'status' => 'confirmed',
        ]);

        $response = $this
            ->actingAs($doctor, 'sanctum')
            ->postJson('/api/appointments', [
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_date' => now()->addDay()->format('Y-m-d'),
                'appointment_time' => '10:00',
                'reason' => 'Segunda cita',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('message', 'El médico ya tiene una cita programada para esta fecha y hora.');
    }
}
