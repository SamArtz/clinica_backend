<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            // Relaciones principales
            // El médico es un usuario con el rol 'doctor'. Usamos user_id o doctor_id.
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            
            // Datos de la cita
            $table->date('appointment_date'); // Ejemplo: 2024-05-20
            $table->time('appointment_time'); // Ejemplo: 10:00:00
            
            // Información adicional
            $table->text('reason')->nullable(); // Motivo de la consulta
            $table->text('notes')->nullable();  // Notas del médico
            
            // Estado de la cita
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');

            $table->timestamps();

            // Índice para acelerar la búsqueda de disponibilidad
            $table->index(['doctor_id', 'appointment_date', 'appointment_time'], 'check_availability_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
