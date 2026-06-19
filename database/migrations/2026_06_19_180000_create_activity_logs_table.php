<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('username', 100)->nullable(); // Guardado directo por si el user se elimina
            $table->string('accion', 100);               // REGISTRAR_ALUMNO, CREAR_GRUPO, etc.
            $table->string('modulo', 50);                // Registro, Grupos, Usuarios, etc.
            $table->text('descripcion');                 // Descripción legible
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('accion');
            $table->index('modulo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
