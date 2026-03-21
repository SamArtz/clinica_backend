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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            
            // Datos Personales
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique(); // El correo debe ser único por paciente
            $table->string('phone')->nullable();
            $table->date('birth_date'); // Necesario para calcular edad en el expediente
            
            // Dirección (opcional pero común en clínicas)
            $table->text('address')->nullable();
            
            // Identificación (DNI, Cédula, Pasaporte, etc.)
            $table->string('document_number')->unique()->nullable(); 

            $table->timestamps();
            
            // Índice para búsquedas rápidas por nombre/apellido
            $table->index(['first_name', 'last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
