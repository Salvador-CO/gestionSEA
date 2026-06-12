<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsignacionService
{
    private $token = '680539ac1455db058ae622f248d11006';
    private $url = 'https://plataformadigitalsea.cbachilleres.edu.mx/webservice/rest/server.php';

    /**
     * Obtiene cursos que contienen la palabra EVAL en su nombre
     */
    public function obtenerCursosEvaluacion()
    {
        try {
            $params = [
                'wstoken' => $this->token,
                'wsfunction' => 'core_course_get_courses',
                'moodlewsrestformat' => 'json',
            ];
            
            $response = Http::asForm()->post($this->url, $params);
            $cursos = $response->json();

            if (!is_array($cursos)) return [];

            $cursosFiltrados = [];

            foreach ($cursos as $curso) {
                $nombre = strtoupper($curso['fullname'] ?? '');
                
                // Filtro: Debe contener 'EVAL' y estar visible (1)
                if (str_contains($nombre, 'EVAL') && isset($curso['visible']) && $curso['visible'] == 1) {
                    $cursosFiltrados[] = [
                        'id'        => (int)$curso['id'],
                        'shortname' => $curso['shortname'] ?? '',
                        'fullname'  => $curso['fullname'] ?? ''
                    ];
                }
            }

            return $cursosFiltrados;

        } catch (\Exception $e) {
            \Log::error("Error en obtenerCursosEvaluacion: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los cursos donde el usuario ya está inscrito
     */
    public function obtenerCursosInscritos($userId)
    {
        $params = [
            'wstoken' => $this->token,
            'wsfunction' => 'core_enrol_get_users_courses',
            'moodlewsrestformat' => 'json',
            'userid' => $userId
        ];
        $response = Http::asForm()->post($this->url, $params);
        return $response->json() ?: [];
    }

    /**
     * Obtiene la nota final de un curso específico
     */
    public function obtenerNotaCurso($userId, $courseId)
    {
        try {
            $params = [
                'wstoken' => $this->token,
                'wsfunction' => 'gradereport_user_get_grade_items',
                'moodlewsrestformat' => 'json',
                'userid' => $userId,
                'courseid' => $courseId
            ];
            $response = Http::asForm()->post($this->url, $params);
            $data = $response->json();

            if (isset($data['usergrades'][0]['gradeitems'])) {
                foreach ($data['usergrades'][0]['gradeitems'] as $item) {
                    if ($item['itemtype'] === 'course') {
                        return $item['graderaw'] !== null ? number_format($item['graderaw'], 2) : 'N/A';
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error obteniendo nota: " . $e->getMessage());
        }
        return 'N/A';
    }

    /**
     * Obtiene el historial detallado de intentos
     */
    public function obtenerHistorialIntentos($userId, $courseId)
    {
        try {
            $resQuiz = Http::asForm()->post($this->url, [
                'wstoken' => $this->token,
                'wsfunction' => 'mod_quiz_get_quizzes_by_courses',
                'moodlewsrestformat' => 'json',
                'courseids' => [(int)$courseId]
            ]);
            $quizzes = $resQuiz->json()['quizzes'] ?? [];
            if (empty($quizzes)) return ['success' => false, 'message' => 'Sin examen.'];

            $quizId = $quizzes[0]['id'];

            $resAttempts = Http::asForm()->post($this->url, [
                'wstoken' => $this->token,
                'wsfunction' => 'mod_quiz_get_user_attempts',
                'moodlewsrestformat' => 'json',
                'quizid' => (int)$quizId,
                'userid' => (int)$userId,
                'status' => 'all'
            ]);
            
            $attemptsData = $resAttempts->json();
            return [
                'success' => true,
                'intentos' => $attemptsData['attempts'] ?? [],
                'conteo' => count($attemptsData['attempts'] ?? [])
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Habilitar intento extra gestionando excepciones existentes (Update vs Create)
     */
    /**
     * Habilitar intento extra gestionando excepciones existentes (Update vs Create)
     */
    public function habilitarIntentoExtra($userId, $courseId)
    {
        try {
            // 1. Obtener Quiz ID
            $resQuiz = Http::asForm()->post($this->url, [
                'wstoken' => $this->token,
                'wsfunction' => 'mod_quiz_get_quizzes_by_courses',
                'moodlewsrestformat' => 'json',
                'courseids' => [(int)$courseId]
            ]);

            $quizzes = $resQuiz->json()['quizzes'] ?? [];
            if (empty($quizzes)) {
                return ['success' => false, 'message' => 'No hay examen en este curso.'];
            }

            $quizId = $quizzes[0]['id'];

            // 2. Obtener overrides (CORRECTO en Moodle 4.x)
            $resEx = Http::asForm()->post($this->url, [
                'wstoken' => $this->token,
                'wsfunction' => 'mod_quiz_get_overrides',
                'moodlewsrestformat' => 'json',
                'quizid' => (int)$quizId
            ]);

            $overrides = $resEx->json()['overrides'] ?? [];

            $existingOverride = null;
            foreach ($overrides as $ov) {
                if (isset($ov['userid']) && (int)$ov['userid'] === (int)$userId) {
                    $existingOverride = $ov;
                    break;
                }
            }

            // 3. Calcular nuevo límite
            if ($existingOverride) {
                $nuevoLimite = (int)$existingOverride['attempts'] + 1;
            } else {
                // contar intentos reales
                $resAttempts = Http::asForm()->post($this->url, [
                    'wstoken' => $this->token,
                    'wsfunction' => 'mod_quiz_get_user_attempts',
                    'moodlewsrestformat' => 'json',
                    'quizid' => (int)$quizId,
                    'userid' => (int)$userId,
                    'status' => 'all'
                ]);

                $attempts = $resAttempts->json()['attempts'] ?? [];
                $conteo = count($attempts);

                // base: si ya hizo 1 → ahora puede 2
                $nuevoLimite = max(2, $conteo + 1);
            }

            // 4. Construir override
            $overrideData = [
                'userid'   => (int)$userId,
                'attempts' => (int)$nuevoLimite
            ];

            if ($existingOverride) {
                // IMPORTANTE: incluir ID para actualizar
                $overrideData['id'] = (int)$existingOverride['id'];
            } else {
                // SOLO en creación
                $overrideData['timeclose'] = time() + (7 * 24 * 3600);
            }

            // 5. Guardar (FORMATO CORRECTO)
            $params = [
                'wstoken' => $this->token,
                'wsfunction' => 'mod_quiz_save_overrides',
                'moodlewsrestformat' => 'json',
                'data' => [
                    'quizid' => (int)$quizId,
                    'overrides' => [$overrideData]
                ]
            ];

            $queryString = http_build_query($params);

            $finalRes = Http::withBody($queryString, 'application/x-www-form-urlencoded')
                ->post($this->url);

            $result = $finalRes->json();

            if (isset($result['exception']) || isset($result['errorcode'])) {
                return [
                    'success' => false,
                    'message' => 'Error Moodle: ' . ($result['message'] ?? 'Error desconocido')
                ];
            }

            return [
                'success' => true,
                'nuevo_limite' => $nuevoLimite,
                'mensaje' => "Intento habilitado correctamente. Nuevo límite: $nuevoLimite"
            ];

        } catch (\Exception $e) {
            Log::error("Error en intento extra: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Inscripción de usuario
     */
    public function inscribirUsuario($userId, $courseId, $groupId = null)
    {
        try {
            $paramsEnrol = [
                'wstoken' => $this->token,
                'wsfunction' => 'enrol_manual_enrol_users',
                'moodlewsrestformat' => 'json',
                'enrolments' => [['roleid' => 5, 'userid' => (int)$userId, 'courseid' => (int)$courseId]]
            ];
            Http::asForm()->post($this->url, $paramsEnrol);
            if ($groupId) {
                Http::asForm()->post($this->url, [
                    'wstoken' => $this->token,
                    'wsfunction' => 'core_group_add_group_members',
                    'moodlewsrestformat' => 'json',
                    'members' => [['groupid' => (int)$groupId, 'userid' => (int)$userId]]
                ]);
            }
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}