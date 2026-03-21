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
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();

            // Relación con el Médico (que es un User con rol doctor)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Día de la semana (0 = Domingo, 1 = Lunes, ..., 6 = Sábado)
            $table->unsignedTinyInteger('day_of_week'); 

            // Rango de horas (Ej: 08:00:00 a 11:00:00)
            $table->time('start_time'); 
            $table->time('end_time');

            // Para saber si el horario está activo o el médico está de vacaciones/libre
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Evitar que un médico tenga dos horarios solapados el mismo día
            // Indexamos para que las consultas de "¿Atiende hoy?" sean ultra rápidas
            $table->index(['user_id', 'day_of_week', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
