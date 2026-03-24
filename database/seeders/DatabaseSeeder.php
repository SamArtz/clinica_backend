<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $assistantRole = Role::firstOrCreate(['name' => 'assistant']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@clinica.com'],
            ['name' => 'Admin Sistema', 'password' => Hash::make('password')]
        );
        $admin->syncRoles([$adminRole]);

        $assistant = User::firstOrCreate(
            ['email' => 'assistant@clinica.com'],
            ['name' => 'Asistente Clínica', 'password' => Hash::make('password')]
        );
        $assistant->syncRoles([$assistantRole]);

        $doctorOne = User::firstOrCreate(
            ['email' => 'house@clinica.com'],
            ['name' => 'Dr. Gregory House', 'password' => Hash::make('password')]
        );
        $doctorOne->syncRoles([$doctorRole]);

        $doctorTwo = User::firstOrCreate(
            ['email' => 'wilson@clinica.com'],
            ['name' => 'Dr. James Wilson', 'password' => Hash::make('password')]
        );
        $doctorTwo->syncRoles([$doctorRole]);

        foreach ([$doctorOne, $doctorTwo] as $doctor) {
            foreach ([1, 2, 3, 4, 5] as $day) {
                DoctorSchedule::firstOrCreate([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                    'start_time' => '08:00:00',
                    'end_time' => '12:00:00',
                ], [
                    'is_active' => true,
                ]);
            }

            DoctorSchedule::firstOrCreate([
                'doctor_id' => $doctor->id,
                'day_of_week' => 6,
                'start_time' => '15:00:00',
                'end_time' => '17:00:00',
            ], [
                'is_active' => true,
            ]);
        }

        $patients = Patient::factory()->count(20)->create();

        foreach ($patients as $patient) {
            MedicalRecord::factory()->create([
                'patient_id' => $patient->id,
            ]);
        }

        $appointmentSlots = ['08:00', '09:00', '10:00', '11:00'];
        $currentDate = Carbon::now()->startOfDay()->addDay();
        $doctors = [$doctorOne, $doctorTwo];
        $createdAppointments = 0;
        $patientIndex = 0;

        while ($createdAppointments < 20) {
            if (in_array($currentDate->dayOfWeek, [1, 2, 3, 4, 5], true)) {
                foreach ($appointmentSlots as $slot) {
                    if ($createdAppointments >= 20) {
                        break;
                    }

                    $patient = $patients[$patientIndex % $patients->count()];
                    $doctor = $doctors[$createdAppointments % count($doctors)];

                    Appointment::create([
                        'doctor_id' => $doctor->id,
                        'patient_id' => $patient->id,
                        'appointment_date' => $currentDate->format('Y-m-d'),
                        'appointment_time' => $slot,
                        'reason' => 'Consulta de control',
                        'notes' => 'Cita generada por seeder',
                        'status' => $createdAppointments % 2 === 0 ? 'confirmed' : 'pending',
                    ]);

                    $createdAppointments++;
                    $patientIndex++;
                }
            }

            $currentDate->addDay();
        }

        $this->command?->info('Datos de prueba generados correctamente.');
        $this->command?->info('Usuarios de prueba: admin@clinica.com / house@clinica.com / assistant@clinica.com');
        $this->command?->info('Contraseña por defecto: password');
    }
}
