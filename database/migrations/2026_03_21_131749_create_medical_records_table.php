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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();

            // Relación 1:1 con Pacientes
            // Usamos unique() para asegurar que un paciente solo tenga UN expediente
            $table->foreignId('patient_id')
                ->unique() 
                ->constrained('patients')
                ->onDelete('cascade');

            // Información Médica Base
            $table->string('blood_type')->nullable(); // Ej: A+, O-, etc.
            $table->text('allergies')->nullable();    // Alergias conocidas
            $table->text('chronic_diseases')->nullable(); // Enfermedades crónicas
            $table->text('family_history')->nullable();   // Antecedentes familiares
            
            // Observaciones generales iniciales
            $table->text('current_medications')->nullable();
            $table->decimal('height', 5, 2)->nullable(); // En metros (ej: 1.75)
            $table->decimal('weight', 5, 2)->nullable(); // En kg (ej: 70.50)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
