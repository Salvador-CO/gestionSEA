<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Servicio centralizado de auditoría.
 * Registra las acciones de los usuarios en la tabla activity_logs.
 * Si falla el registro, solo lo loguea en el log de Laravel — nunca interrumpe la operación principal.
 */
class AuditoriaService
{
    /**
     * Registra una acción en el log de auditoría.
     *
     * @param string $accion     Código de acción en MAYÚSCULAS (ej: REGISTRAR_ALUMNO)
     * @param string $modulo     Nombre del módulo (ej: Registro, Grupos)
     * @param string $descripcion Descripción legible de la acción
     */
    public static function registrar(string $accion, string $modulo, string $descripcion): void
    {
        try {
            $user = Auth::user();

            ActivityLog::create([
                'user_id'     => $user?->id,
                'username'    => $user?->username ?? 'sistema',
                'accion'      => $accion,
                'modulo'      => $modulo,
                'descripcion' => $descripcion,
                'ip_address'  => Request::ip(),
                'created_at'  => now(),
            ]);
        } catch (\Exception $e) {
            // El log de auditoría nunca debe interrumpir la operación principal
            Log::warning("AuditoriaService: No se pudo registrar la acción [{$accion}]: " . $e->getMessage());
        }
    }
}
