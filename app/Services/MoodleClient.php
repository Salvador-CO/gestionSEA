<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Clase base para todos los servicios de integración con Moodle.
 *
 * Centraliza:
 *  - Credenciales (token y URL) leídas desde config/services.php → .env
 *  - Helper getCall()  — para peticiones GET  (usado por MoodleService)
 *  - Helper postCall() — para peticiones POST (usado por el resto de Services)
 *  - Manejo uniforme de errores: HTTP fallido, excepciones Moodle, timeouts
 */
abstract class MoodleClient
{
    protected string $token;
    protected string $url;

    public function __construct()
    {
        $this->token = config('services.moodle.token', '');
        $this->url   = config('services.moodle.url', '');
    }

    /**
     * Llamada a Moodle usando GET (para consultas ligeras con caché).
     * Retorna null ante cualquier error.
     */
    protected function getCall(string $function, array $params = []): mixed
    {
        if (!$this->credencialesConfiguradas($function)) return null;

        $allParams = array_merge([
            'wstoken'            => $this->token,
            'wsfunction'         => $function,
            'moodlewsrestformat' => 'json',
        ], $params);

        try {
            $response = Http::timeout(120)->get($this->url, $allParams);

            if ($response->failed()) {
                Log::warning("Moodle GET [{$function}]: HTTP {$response->status()} — {$this->url}");
                return null;
            }

            $data = $response->json();

            if (isset($data['exception'])) {
                Log::warning("Moodle GET [{$function}]: Excepción — " . ($data['message'] ?? 'Sin mensaje'));
                return null;
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Moodle GET [{$function}]: Sin conexión — " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error("Moodle GET [{$function}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Llamada a Moodle usando POST (para la mayoría de operaciones).
     * Retorna null ante cualquier error.
     */
    protected function postCall(string $function, array $params = []): mixed
    {
        if (!$this->credencialesConfiguradas($function)) return null;

        $allParams = array_merge([
            'wstoken'            => $this->token,
            'wsfunction'         => $function,
            'moodlewsrestformat' => 'json',
        ], $params);

        try {
            $response = Http::timeout(60)->asForm()->post($this->url, $allParams);

            if ($response->failed()) {
                Log::warning("Moodle POST [{$function}]: HTTP {$response->status()}");
                return null;
            }

            $data = $response->json();

            if (isset($data['exception'])) {
                Log::warning("Moodle POST [{$function}]: Excepción — " . ($data['message'] ?? 'Sin mensaje'));
                return null;
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Moodle POST [{$function}]: Sin conexión — " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error("Moodle POST [{$function}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica que el token y la URL estén configurados.
     * Si faltan, registra el error y retorna false.
     */
    private function credencialesConfiguradas(string $funcion): bool
    {
        if (empty($this->token) || empty($this->url)) {
            Log::error("MoodleClient [{$funcion}]: MOODLE_TOKEN o MOODLE_URL no están configurados en .env");
            return false;
        }
        return true;
    }
}
