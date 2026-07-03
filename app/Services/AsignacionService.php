<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsignacionService extends MoodleClient
{
    // Hereda token, url, getCall() y postCall() de MoodleClient

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
            Log::error("Error en obtenerCursosEvaluacion: " . $e->getMessage());
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

            $quizId   = $quizzes[0]['id'];
            $limiteQuiz = (int)($quizzes[0]['attempts'] ?? 0); // 0 = ilimitado

            $resAttempts = Http::asForm()->post($this->url, [
                'wstoken' => $this->token,
                'wsfunction' => 'mod_quiz_get_user_attempts',
                'moodlewsrestformat' => 'json',
                'quizid' => (int)$quizId,
                'userid' => (int)$userId,
                'status' => 'all'
            ]);

            $attemptsData  = $resAttempts->json();
            $todosIntentos = $attemptsData['attempts'] ?? [];

            // Contar solo los finalizados (no "inprogress")
            $finalizados = count(array_filter($todosIntentos, fn($a) => ($a['state'] ?? '') === 'finished'));

            // Obtener override del usuario en este quiz
            $resOverrides = Http::asForm()->post($this->url, [
                'wstoken' => $this->token,
                'wsfunction' => 'mod_quiz_get_overrides',
                'moodlewsrestformat' => 'json',
                'quizid' => (int)$quizId
            ]);
            $overrides = $resOverrides->json()['overrides'] ?? [];

            $limiteOverride = null;
            foreach ($overrides as $ov) {
                if (isset($ov['userid']) && (int)$ov['userid'] === (int)$userId) {
                    $limiteOverride = (int)$ov['attempts'];
                    break;
                }
            }

            // Calcular límite efectivo y pendientes
            $limiteEfectivo = $limiteOverride ?? $limiteQuiz;
            // Si limiteEfectivo = 0 significa ilimitado → no mostramos pendientes
            if ($limiteEfectivo > 0) {
                $pendientes = max(0, $limiteEfectivo - $finalizados);
            } else {
                // Quiz ilimitado: hay pendiente si no tienen intentos en progreso pero sí pueden entrar
                $enProgreso = count(array_filter($todosIntentos, fn($a) => ($a['state'] ?? '') === 'inprogress'));
                $pendientes = $enProgreso > 0 ? 1 : 0; // en progreso = ya está dentro del examen
            }

            return [
                'success'     => true,
                'intentos'    => $todosIntentos,
                'conteo'      => count($todosIntentos),
                'finalizados' => $finalizados,
                'pendientes'  => $pendientes,
                'limite'      => $limiteEfectivo,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Habilitar intento extra gestionando excepciones existentes (Update vs Create).
     * REGLA: Solo se puede habilitar si el alumno NO tiene intentos pendientes (pendientes = 0).
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

            $quizId     = $quizzes[0]['id'];
            $limiteQuiz = (int)($quizzes[0]['attempts'] ?? 0);

            // 2. Obtener overrides existentes
            $resEx    = Http::asForm()->post($this->url, [
                'wstoken'            => $this->token,
                'wsfunction'         => 'mod_quiz_get_overrides',
                'moodlewsrestformat' => 'json',
                'quizid'             => (int)$quizId
            ]);
            $overrides = $resEx->json()['overrides'] ?? [];

            $existingOverride = null;
            foreach ($overrides as $ov) {
                if (isset($ov['userid']) && (int)$ov['userid'] === (int)$userId) {
                    $existingOverride = $ov;
                    break;
                }
            }

            // 3. Contar intentos finalizados para validar que no haya pendientes
            $resAttempts = Http::asForm()->post($this->url, [
                'wstoken'            => $this->token,
                'wsfunction'         => 'mod_quiz_get_user_attempts',
                'moodlewsrestformat' => 'json',
                'quizid'             => (int)$quizId,
                'userid'             => (int)$userId,
                'status'             => 'all'
            ]);
            $todosIntentos = $resAttempts->json()['attempts'] ?? [];
            $finalizados   = count(array_filter($todosIntentos, fn($a) => ($a['state'] ?? '') === 'finished'));

            // 4. VALIDACIÓN DE SEGURIDAD: No habilitar si ya tiene pendientes
            $limiteActual = $existingOverride ? (int)$existingOverride['attempts'] : $limiteQuiz;
            if ($limiteActual > 0 && $limiteActual > $finalizados) {
                return [
                    'success' => false,
                    'message' => 'El alumno ya tiene un intento habilitado sin presentar. Espere a que lo presente antes de habilitar otro.'
                ];
            }

            // 5. Calcular nuevo límite
            if ($existingOverride) {
                $nuevoLimite = (int)$existingOverride['attempts'] + 1;
            } else {
                $nuevoLimite = max(2, $finalizados + 1);
            }

            // 6. Construir override
            $overrideData = [
                'userid'   => (int)$userId,
                'attempts' => (int)$nuevoLimite
            ];

            if ($existingOverride) {
                $overrideData['id'] = (int)$existingOverride['id'];
            } else {
                $overrideData['timeclose'] = time() + (7 * 24 * 3600);
            }

            // 7. Guardar override
            $params      = [
                'wstoken'            => $this->token,
                'wsfunction'         => 'mod_quiz_save_overrides',
                'moodlewsrestformat' => 'json',
                'data'               => [
                    'quizid'    => (int)$quizId,
                    'overrides' => [$overrideData]
                ]
            ];
            $queryString = http_build_query($params);
            $finalRes    = Http::withBody($queryString, 'application/x-www-form-urlencoded')->post($this->url);
            $result      = $finalRes->json();

            if (isset($result['exception']) || isset($result['errorcode'])) {
                return [
                    'success' => false,
                    'message' => 'Error Moodle: ' . ($result['message'] ?? 'Error desconocido')
                ];
            }

            return [
                'success'      => true,
                'nuevo_limite' => $nuevoLimite,
                'mensaje'      => "Intento habilitado correctamente. Nuevo límite: $nuevoLimite"
            ];

        } catch (\Exception $e) {
            Log::error("Error en intento extra: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    /**
     * Quita el último intento extra habilitado de un usuario.
     * Si el override queda igual a los finalizados (pendientes=0), elimina el override.
     * Si reduce el override pero sigue por encima de los finalizados, actualiza.
     */
    public function quitarIntentoExtra($userId, $courseId)
    {
        try {
            // 1. Obtener Quiz
            $resQuiz = Http::asForm()->post($this->url, [
                'wstoken'            => $this->token,
                'wsfunction'         => 'mod_quiz_get_quizzes_by_courses',
                'moodlewsrestformat' => 'json',
                'courseids'          => [(int)$courseId]
            ]);
            $quizzes = $resQuiz->json()['quizzes'] ?? [];
            if (empty($quizzes)) {
                return ['success' => false, 'message' => 'No hay examen en este curso.'];
            }
            $quizId = $quizzes[0]['id'];

            // 2. Obtener override del usuario
            $resEx     = Http::asForm()->post($this->url, [
                'wstoken'            => $this->token,
                'wsfunction'         => 'mod_quiz_get_overrides',
                'moodlewsrestformat' => 'json',
                'quizid'             => (int)$quizId
            ]);
            $overrides        = $resEx->json()['overrides'] ?? [];
            $existingOverride = null;
            foreach ($overrides as $ov) {
                if (isset($ov['userid']) && (int)$ov['userid'] === (int)$userId) {
                    $existingOverride = $ov;
                    break;
                }
            }

            if (!$existingOverride) {
                return ['success' => false, 'message' => 'No hay intento extra que quitar para este alumno.'];
            }

            // 3. Contar intentos finalizados
            $resAttempts   = Http::asForm()->post($this->url, [
                'wstoken'            => $this->token,
                'wsfunction'         => 'mod_quiz_get_user_attempts',
                'moodlewsrestformat' => 'json',
                'quizid'             => (int)$quizId,
                'userid'             => (int)$userId,
                'status'             => 'all'
            ]);
            $todosIntentos = $resAttempts->json()['attempts'] ?? [];
            $finalizados   = count(array_filter($todosIntentos, fn($a) => ($a['state'] ?? '') === 'finished'));

            $limiteActual = (int)$existingOverride['attempts'];
            $nuevoLimite  = $limiteActual - 1;

            // 4a. Si el nuevo límite es <= finalizados → eliminar el override completo
            if ($nuevoLimite <= $finalizados) {
                $params      = [
                    'wstoken'            => $this->token,
                    'wsfunction'         => 'mod_quiz_delete_overrides',
                    'moodlewsrestformat' => 'json',
                    'quizid'             => (int)$quizId,
                    'overrides'          => [(int)$existingOverride['id']]
                ];
                $queryString = http_build_query($params);
                Http::withBody($queryString, 'application/x-www-form-urlencoded')->post($this->url);

                return [
                    'success' => true,
                    'mensaje' => 'Intento extra eliminado. El override fue removido correctamente.'
                ];
            }

            // 4b. Si aún quedarían pendientes válidos → actualizar override con nuevo límite
            $overrideData = [
                'id'       => (int)$existingOverride['id'],
                'userid'   => (int)$userId,
                'attempts' => $nuevoLimite
            ];
            $params      = [
                'wstoken'            => $this->token,
                'wsfunction'         => 'mod_quiz_save_overrides',
                'moodlewsrestformat' => 'json',
                'data'               => [
                    'quizid'    => (int)$quizId,
                    'overrides' => [$overrideData]
                ]
            ];
            $queryString = http_build_query($params);
            $result      = Http::withBody($queryString, 'application/x-www-form-urlencoded')->post($this->url)->json();

            if (isset($result['exception']) || isset($result['errorcode'])) {
                return ['success' => false, 'message' => 'Error Moodle: ' . ($result['message'] ?? 'Error desconocido')];
            }

            return [
                'success' => true,
                'mensaje' => "Intento retirado. Nuevo límite: $nuevoLimite"
            ];

        } catch (\Exception $e) {
            Log::error("Error en quitarIntentoExtra: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
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