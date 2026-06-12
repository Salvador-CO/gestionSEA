<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BusquedaService
{
    private $token;
    private $url;

    public function __construct()
    {
        $this->token = env('MOODLE_TOKEN');
        $this->url   = env('MOODLE_URL');
    }

    /**
     * Busca usuarios por múltiples criterios alternativos
     */
    public function buscarUsuario($criterio)
    {
        $criterio = trim($criterio);
        
        // Determinar qué campo usar para la búsqueda inicial en Moodle
        $field = 'username'; // Por defecto matrícula/username
        if (filter_var($criterio, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $criterio)) {
            // Si contiene espacios o caracteres especiales, asumimos búsqueda por nombre/apellido
            return $this->buscarPorNombreCompleto($criterio);
        }

        $params = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_user_get_users_by_field',
            'moodlewsrestformat' => 'json',
            'field'              => $field,
            'values'             => [$criterio]
        ];

        try {
            $response = Http::asForm()->post($this->url, $params);
            
            if ($response->failed()) {
                Log::error('BusquedaService: La petición HTTP a Moodle falló.', ['status' => $response->status()]);
                return [];
            }

            $res = $response->json();
            
            // Validar si Moodle regresó una excepción en el JSON
            if (isset($res['exception'])) {
                Log::error('Moodle devolvió una excepción: ' . ($res['message'] ?? 'Sin mensaje'));
                return [];
            }

            return (is_array($res) && count($res) > 0) ? $res : [];
        } catch (\Exception $e) {
            Log::error('Error en BusquedaService (buscarUsuario): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Búsqueda por similitud de texto en nombres/apellidos
     */
    private function buscarPorNombreCompleto($texto)
    {
        $params = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_user_get_users',
            'moodlewsrestformat' => 'json',
            'criteria' => [
                ['key' => 'firstname', 'value' => '%' . $texto . '%'],
            ]
        ];

        try {
            $response = Http::asForm()->post($this->url, $params);
            $res = $response->json();
            
            if (isset($res['exception'])) {
                Log::error('Moodle devolvió una excepción en buscarPorNombreCompleto: ' . ($res['message'] ?? ''));
                return [];
            }
            
            if (isset($res['users']) && is_array($res['users']) && count($res['users']) > 0) {
                return $res['users'];
            }
            
            // Reintentar por apellido si no encontró por nombre
            $params['criteria'][0]['key'] = 'lastname';
            $response = Http::asForm()->post($this->url, $params);
            $res = $response->json();
            
            return (isset($res['users'])) ? $res['users'] : [];
        } catch (\Exception $e) {
            Log::error('Error en BusquedaService (buscarPorNombreCompleto): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los cursos inscritos de un usuario
     */
    public function obtenerCursosUsuario($userId)
    {
        $params = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_enrol_get_users_courses',
            'moodlewsrestformat' => 'json',
            'userid'             => $userId
        ];

        try {
            $response = Http::asForm()->post($this->url, $params);
            return $response->json();
        } catch (\Exception $e) {
            Log::error("Error obteniendo cursos del usuario {$userId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los grupos del curso a los que pertenece el usuario
     */
    public function obtenerGruposUsuarioEnCurso($userId, $courseId)
    {
        $params = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_group_get_course_user_groups',
            'moodlewsrestformat' => 'json',
            'userid'             => $userId,
            'courseid'           => $courseId
        ];

        try {
            $response = Http::asForm()->post($this->url, $params);
            $res = $response->json();
            return (isset($res['groups'])) ? $res['groups'] : [];
        } catch (\Exception $e) {
            Log::error("Error obteniendo grupos del usuario {$userId} en curso {$courseId}: " . $e->getMessage());
            return [];
        }
    }
}