<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class MoodleService extends MoodleClient
{
    // Hereda token, url y getCall() de MoodleClient

    public function findUserByEmail($email)
    {
        $params = ['criteria' => [['key' => 'email', 'value' => $email]]];
        $response = $this->getCall('core_user_get_users', $params);
        if (!$response || empty($response['users'])) return null;

        $u = $response['users'][0];
        $centro = 'Sin Centro';
        if (!empty($u['customfields'])) {
            foreach ($u['customfields'] as $f) {
                if ($f['shortname'] == 'centro') $centro = trim($f['value']);
            }
        }

        return [
            'moodle_id' => $u['id'],
            'username'  => $u['username'],
            'firstname' => $u['firstname'],
            'lastname'  => $u['lastname'],
            'email'     => $u['email'],
            'centro'    => $centro
        ];
    }

    /**
     * Obtiene las categorías de Moodle (Necesario para el select)
     */
    public function getCategorias()
    {
        return Cache::remember('moodle_categories', 3600, function () {
            return $this->getCall('core_course_get_categories') ?? [];
        });
    }

    public function getAdvancedStats($forceRefresh = false, $filtroCentro = null)
    {
        $cacheSuffix = $filtroCentro ? '_' . str_replace(' ', '_', $filtroCentro) : '_all';
        $cacheKey = 'moodle_advanced_report' . $cacheSuffix;
        
        if ($forceRefresh) Cache::forget($cacheKey);

        return Cache::remember($cacheKey, 3600, function () use ($filtroCentro) {
            $usersResponse = $this->getCall('core_user_get_users', ['criteria' => [['key' => 'deleted', 'value' => '0']]]);
            $rawUsers = $usersResponse['users'] ?? [];
            $coursesRaw = $this->getCall('core_course_get_courses') ?? [];
            
            $haceUnaSemana = strtotime('-7 days');
            
            $stats = [
                'total' => 0,
                'acceso' => ['nunca' => 0, 'alguna_vez' => 0, 'reciente' => 0],
                'roles' => [], 'centros' => [], 'matriz_centro_rol' => [],
                'raw_users' => [],
                'KPI' => ['tasa_inactividad' => 0, 'inscritos' => 0, 'activos' => 0]
            ];

            foreach ($rawUsers as $u) {
                $rol = 'Sin Rol'; 
                $centro = 'Sin Centro';
                if (!empty($u['customfields'])) {
                    foreach ($u['customfields'] as $f) {
                        if ($f['shortname'] == 'tipoROL' && !empty($f['value'])) $rol = trim($f['value']);
                        if ($f['shortname'] == 'centro' && !empty($f['value'])) $centro = trim($f['value']);
                    }
                }

                if ($filtroCentro && strtolower($centro) !== strtolower($filtroCentro)) continue;

                $stats['total']++;
                $stats['raw_users'][] = $u;
                $last = (int)($u['lastaccess'] ?? 0);
                if ($last === 0) { $stats['acceso']['nunca']++; } 
                else {
                    $stats['acceso']['alguna_vez']++;
                    if ($last > $haceUnaSemana) $stats['acceso']['reciente']++;
                }
                $stats['roles'][$rol] = ($stats['roles'][$rol] ?? 0) + 1;
                $stats['centros'][$centro] = ($stats['centros'][$centro] ?? 0) + 1;
                $stats['matriz_centro_rol'][$centro][$rol] = ($stats['matriz_centro_rol'][$centro][$rol] ?? 0) + 1;
            }

            return [
                'stats' => $stats,
                'courses' => $coursesRaw,
                'fuente' => now()->timezone('America/Mexico_City')->format('d/m/Y H:i')
            ];
        });
    }

    /**
     * MODIFICADO: Ahora acepta $filtroCentro
     */
    public function getCalificacionesFiltradas($categoryId = null, $courseId = null, $roleFilter = null, $filtroCentro = null)
    {
        $allCourses = $this->getCall('core_course_get_courses') ?? [];
        $cursosFiltrados = $allCourses;
        
        if ($categoryId) {
            $cursosFiltrados = array_filter($allCourses, function($c) use ($categoryId) {
                return $c['categoryid'] == $categoryId;
            });
        }

        $reporte = [];
        $cursosAProcesar = [];
        if ($courseId && $courseId !== 'all') {
            $cursosAProcesar[] = $courseId;
        } elseif ($courseId === 'all' && $categoryId) {
            foreach ($cursosFiltrados as $cf) { $cursosAProcesar[] = $cf['id']; }
        }

        foreach ($cursosAProcesar as $idCurso) {
            $users = $this->getCall('core_enrol_get_enrolled_users', ['courseid' => $idCurso]);
            if ($users && is_array($users)) {
                foreach ($users as $u) {
                    $centro = 'Sin Centro';
                    $tipoRol = 'Sin Rol';
                    if (!empty($u['customfields'])) {
                        foreach ($u['customfields'] as $f) {
                            if ($f['shortname'] == 'centro') $centro = trim($f['value']);
                            if ($f['shortname'] == 'tipoROL') $tipoRol = trim($f['value']);
                        }
                    }

                    // FILTRO DE CENTRO: Si el usuario del sistema tiene un centro, solo ve alumnos de ese centro
                    if ($filtroCentro && strtolower($centro) !== strtolower($filtroCentro)) continue;
                    
                    // FILTRO DE ROL (del select de la vista)
                    if ($roleFilter && $tipoRol !== $roleFilter) continue;

                    $gradesData = $this->getCall('gradereport_user_get_grade_items', [
                        'courseid' => $idCurso,
                        'userid'   => $u['id']
                    ]);

                    $calificacion = 'Sin Nota';
                    $fecha = 'Sin Fecha';

                    if (isset($gradesData['usergrades'][0]['gradeitems'])) {
                        foreach ($gradesData['usergrades'][0]['gradeitems'] as $item) {
                            if ($item['itemtype'] == 'course') {
                                $calificacion = $item['graderaw'] ?? 'Sin Nota';
                                if (isset($item['gradedatesubmitted']) && $item['gradedatesubmitted'] > 0) {
                                    $fecha = date('Y-m-d H:i:s', $item['gradedatesubmitted']);
                                }
                                break;
                            }
                        }
                    }

                    $reporte[] = [
                        'estudiante' => $u['fullname'],
                        'Usuario' => $u['username'],
                        'Centro' => $centro,
                        'Fecha' => $fecha,
                        'Calificacion' => $calificacion,
                        'Clave' => $idCurso,
                        'Tipo_Rol' => $tipoRol
                    ];
                }
            }
        }

        return [
            'data' => $reporte,
            'cursos_select' => $cursosFiltrados,
            'fuente' => now()->timezone('America/Mexico_City')->format('d/m/Y H:i')
        ];
    }

    /**
     * Obtiene los cursos en los que está matriculado un usuario
     */
    public function getUsersCourses($userId)
    {
        return $this->getCall('core_enrol_get_users_courses', ['userid' => $userId]);
    }

    /**
     * Obtiene los exámenes de un conjunto de cursos
     */
    public function getQuizzesByCourses(array $courseIds)
    {
        $params = [];
        foreach ($courseIds as $index => $id) {
            $params["courseids[$index]"] = $id;
        }
        $response = $this->getCall('mod_quiz_get_quizzes_by_courses', $params);
        return $response['quizzes'] ?? [];
    }

    /**
     * Obtiene los intentos de un usuario en un quiz (solo finalizados)
     */
    public function getUserAttempts($quizId, $userId)
    {
        $params = [
            'quizid' => $quizId,
            'userid' => $userId,
            'status' => 'finished'
        ];
        $response = $this->getCall('mod_quiz_get_user_attempts', $params);
        return $response['attempts'] ?? [];
    }

    /**
     * Obtiene la revisión detallada de un intento (preguntas, estado, calificación y html con feedback)
     */
    public function getAttemptReview($attemptId)
    {
        return $this->getCall('mod_quiz_get_attempt_review', ['attemptid' => $attemptId]);
    }
}