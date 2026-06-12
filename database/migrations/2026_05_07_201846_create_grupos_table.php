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
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_moodle')->unique(); // El ID final generado
            $table->foreignId('centro_id')->constrained('centros');
            $table->foreignId('asignatura_id')->constrained('asignaturas');
            $table->foreignId('asesor_id')->constrained('asesores');
            $table->integer('p_numero'); // El consecutivo (01, 02...)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
