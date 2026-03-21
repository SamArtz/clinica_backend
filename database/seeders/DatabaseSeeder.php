<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\DoctorSchedule;
use Spatie\Permission\Models\Role;
use App\Models\MedicalRecord;
use App\Models\Appointment;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear los roles PRIMERO
        $adminRole = Role::create(['name' => 'admin']);
        $doctorRole = Role::create(['name' => 'doctor']);
        $assistantRole = Role::create(['name' => 'assistant']);

        // 2. Crear el Paciente PRIMERO (para que el ID 1 exista antes que el expediente)
        $patient = Patient::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'birth_date' => '1990-01-01',
        ]);

        // 3. Crear el Expediente (ahora que el patient_id 1 existe)
        MedicalRecord::create([
            'patient_id' => $patient->id,
            'blood_type' => 'O+',
            'allergies' => 'Ninguna',
            'chronic_diseases' => 'Ninguna',
        ]);

        // 4. Crear el Médico
        $doctor = User::create([
            'name' => 'Dr. Gregory House',
            'email' => 'house@clinica.com',
            'password' => bcrypt('password'),
        ]);
        $doctor->assignRole($doctorRole); // Se asigna DESPUÉS de crear al $doctor

        // 5. Crear el Horario del médico
        DoctorSchedule::create([
            'user_id' => $doctor->id,
            'day_of_week' => 1, 
            'start_time' => '08:00:00',
            'end_time' => '14:00:00',
            'is_active' => true,
        ]);

        // 6. Crear Administrador
        $admin = User::create([
            'name' => 'Admin Sistema',
            'email' => 'admin@clinica.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($adminRole);

        // 7. Crear Asistente
        $assistant = User::create([
            'name' => 'Asistente Clínica',
            'email' => 'assistant@clinica.com',
            'password' => bcrypt('password'),
        ]);
        $assistant->assignRole($assistantRole);
        
        $this->command->info('¡Datos de prueba creados con éxito!');

        Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => now()->addDay()->format('Y-m-d'),
        'appointment_time' => '10:00',
        'reason' => 'Chequeo rutinario de ejemplo',
        'status' => 'confirmed'
        ]);
    }
}