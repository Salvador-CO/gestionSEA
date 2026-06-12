<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna centro_id a la tabla asesores.
     * Esta columna ya estaba referenciada en el modelo Asesor.php ($fillable y relación)
     * pero faltaba en la migración original.
     */
    public function up(): void
    {
        Schema::table('asesores', function (Blueprint $table) {
            // Solo agrega la columna si no existe (compatible con BD nueva y existente)
            if (!Schema::hasColumn('asesores', 'centro_id')) {
                $table->foreignId('centro_id')
                      ->nullable()
                      ->after('cargo_id')
                      ->constrained('centros')
                      ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesores', function (Blueprint $table) {
            $table->dropForeign(['centro_id']);
            $table->dropColumn('centro_id');
        });
    }
};
