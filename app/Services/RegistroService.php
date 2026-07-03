<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RegistroService extends MoodleClient
{
    // Hereda token, url, getCall() y postCall() de MoodleClient

    public function buscarUsuarioPorEmail($email)
    {
        $params = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_user_get_users_by_field',
            'moodlewsrestformat' => 'json',
            'field'              => 'email',
            'values'             => [$email]
        ];

        try {
            $response = Http::asForm()->post($this->url, $params);
            $res = $response->json();
            return (is_array($res) && count($res) > 0) ? $res[0] : null;
        } catch (\Exception $e) {
            Log::error('Error validando email: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca un usuario en Moodle por su username (matrícula).
     * El username en Moodle corresponde a la matrícula del estudiante.
     */
    public function buscarUsuarioPorUsername($username)
    {
        $params = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_user_get_users_by_field',
            'moodlewsrestformat' => 'json',
            'field'              => 'username',
            'values'             => [strtolower(trim($username))]
        ];

        try {
            $response = Http::asForm()->post($this->url, $params);
            $res = $response->json();
            return (is_array($res) && count($res) > 0) ? $res[0] : null;
        } catch (\Exception $e) {
            Log::error('Error buscando por username: ' . $e->getMessage());
            return null;
        }
    }

    public function crearUsuarioMoodle($datos)
    {
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $datos['username']));
        $email = strtolower(trim($datos['email']));

        $params = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_user_create_users',
            'moodlewsrestformat' => 'json',
            'users' => [
                [
                    'username'     => $username,
                    'password'     => $datos['password'],
                    'firstname'    => mb_strtoupper($datos['firstname'], 'UTF-8'),
                    'lastname'     => mb_strtoupper($datos['lastname'], 'UTF-8'),
                    'email'        => $email,
                    'auth'         => 'manual',
                    'customfields' => [
                        ['type' => 'tipoROL', 'value' => (string)$datos['rol']],
                        ['type' => 'centro',  'value' => (string)($datos['centro'] ?? '')],
                    ]
                ]
            ]
        ];

        try {
            $response = Http::asForm()->post($this->url, $params);
            $res = $response->json();

            if (isset($res['exception'])) {
                return ['success' => false, 'message' => 'Moodle Error: ' . $res['message']];
            }

            if (is_array($res) && isset($res[0]['id'])) {
                $moodleId = $res[0]['id'];
                if (!empty($datos['cohort_id'])) {
                    $this->asignarCohorte($moodleId, $datos['cohort_id']);
                }
                return ['success' => true, 'id' => $moodleId];
            }
            return ['success' => false, 'message' => 'Respuesta inesperada.'];
        } catch (\Exception $e) {
            Log::error('Excepción RegistroService: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function asignarCohorte($userId, $cohortId) {
        $params = [
            'wstoken' => $this->token,
            'wsfunction' => 'core_cohort_add_cohort_members',
            'moodlewsrestformat' => 'json',
            'members' => [['cohorttype' => ['type' => 'id', 'value' => (string)$cohortId], 'usertype' => ['type' => 'id', 'value' => (string)$userId]]]
        ];
        Http::asForm()->post($this->url, $params);
    }

    public function obtenerTodosCursos() {
        return Cache::remember('moodle_todos_cursos', 1800, function () {
            $params = [
                'wstoken'            => $this->token,
                'wsfunction'         => 'core_course_get_courses',
                'moodlewsrestformat' => 'json',
            ];
            $response = Http::timeout(60)->asForm()->post($this->url, $params);
            return $response->json() ?? [];
        });
    }

    public function obtenerGruposCurso($courseId) {
        return Cache::remember("moodle_grupos_curso_{$courseId}", 900, function () use ($courseId) {
            $params = [
                'wstoken'            => $this->token,
                'wsfunction'         => 'core_group_get_course_groups',
                'moodlewsrestformat' => 'json',
                'courseid'           => $courseId
            ];
            $response = Http::asForm()->post($this->url, $params);
            $res = $response->json();
            return (isset($res['exception'])) ? [] : $res;
        });
    }

    public function inscribirUsuarioEnCurso($userId, $courseId, $groupId = null) {
        $paramsEnrol = [
            'wstoken' => $this->token,
            'wsfunction' => 'enrol_manual_enrol_users',
            'moodlewsrestformat' => 'json',
            'enrolments' => [['roleid' => 5, 'userid' => $userId, 'courseid' => $courseId]]
        ];
        Http::asForm()->post($this->url, $paramsEnrol);

        if ($groupId) {
            $paramsGroup = [
                'wstoken' => $this->token,
                'wsfunction' => 'core_group_add_group_members',
                'moodlewsrestformat' => 'json',
                'members' => [['groupid' => $groupId, 'userid' => $userId]]
            ];
            Http::asForm()->post($this->url, $paramsGroup);
        }
        return ['success' => true];
    }

    public function obtenerUsuariosPorCohorte($cohortId) {
        $params = [
            'wstoken' => $this->token,
            'wsfunction' => 'core_cohort_get_cohort_members',
            'moodlewsrestformat' => 'json',
            'cohortids' => [(int)$cohortId]
        ];
        $response = Http::asForm()->post($this->url, $params);
        $res = $response->json();
        return (isset($res[0]['userids'])) ? $this->obtenerDetalleUsuarios($res[0]['userids']) : [];
    }

    private function obtenerDetalleUsuarios($userIds) {
        $params = [
            'wstoken' => $this->token,
            'wsfunction' => 'core_user_get_users_by_field',
            'moodlewsrestformat' => 'json',
            'field' => 'id',
            'values' => $userIds
        ];
        $response = Http::asForm()->post($this->url, $params);
        return $response->json();
    }


    public function obtenerMiembrosGrupo($groupId) {
        $params = [
            'wstoken' => $this->token,
            'wsfunction' => 'core_group_get_group_members',
            'moodlewsrestformat' => 'json',
            'groupids' => [$groupId]
        ];
        
        try {
            $response = Http::asForm()->post($this->url, $params);
            $res = $response->json();
            return (isset($res[0]['userids'])) ? $res[0]['userids'] : [];
        } catch (\Exception $e) {
            Log::error("Error obteniendo miembros del grupo {$groupId}: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerAsesoresCurso($courseId) {
        // 1. Intentamos obtener usuarios con capacidades de gestión (profesores/asesores)
        $params = [
            'wstoken' => $this->token,
            'wsfunction' => 'core_enrol_get_enrolled_users_with_capability',
            'moodlewsrestformat' => 'json',
            'coursecapabilities' => [
                [
                    'courseid' => $courseId, 
                    'capability' => 'moodle/course:manageactivities'
                ]
            ]
        ];

        try {
            $response = Http::asForm()->post($this->url, $params);
            $res = $response->json();
            
            // Si hay error o viene vacío, usamos el método tradicional pero asegurando los campos
            if (isset($res['exception']) || empty($res) || (isset($res[0]['users']) && empty($res[0]['users']))) {
                $paramsAlterno = [
                    'wstoken' => $this->token,
                    'wsfunction' => 'core_enrol_get_enrolled_users',
                    'moodlewsrestformat' => 'json',
                    'courseid' => $courseId,
                    // ES CRUCIAL: Agregar 'roles' en userfields para poder filtrar en el controller
                    'options' => [
                        ['name' => 'userfields', 'value' => 'id,firstname,lastname,groups,roles']
                    ]
                ];
                $response = Http::asForm()->post($this->url, $paramsAlterno);
                $res = $response->json();
            }

            // Si el resultado viene de 'core_enrol_get_enrolled_users_with_capability', 
            // los usuarios vienen dentro de $res[0]['users']. 
            // Si viene de 'core_enrol_get_enrolled_users', el array es directo.
            if (isset($res[0]['users'])) {
                return $res[0]['users'];
            }

            return (isset($res['exception'])) ? [] : $res;

        } catch (\Exception $e) {
            Log::error("Error obteniendo asesores del curso {$courseId}: " . $e->getMessage());
            return [];
        }
    }
    

}