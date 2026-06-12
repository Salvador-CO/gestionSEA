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
        Schema::create('estudiante_correos', function (Blueprint $table) {
        $table->id();
        // Datos extraídos del Excel/CSV
        $table->string('plantel'); 
        $table->string('matricula')->unique(); // La matrícula debe ser única para no duplicar alumnos
        $table->string('nombre');
        $table->string('fecha_ingreso')->nullable();
        $table->string('correo_personal')->nullable();
        $table->string('correo_institucional');
        $table->string('clave_correo');
        $table->string('matricula_asesor')->nullable();
        $table->string('nombre_asesor')->nullable();
        
        // Columnas de control interno
        $table->enum('estatus', ['Pendiente', 'Entregado'])->default('Pendiente');
        $table->timestamp('fecha_entrega')->nullable();
        $table->string('subido_por')->nullable(); // Para saber qué Admin hizo la carga
        
        $table->timestamps(); // Crea created_at y updated_at automáticamente
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiante_correos');
    }
};
