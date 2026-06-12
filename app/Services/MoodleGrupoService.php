<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoodleGrupoService extends MoodleClient
{
    // Hereda token, url, getCall() y postCall() de MoodleClient

    // 1. Crear el grupo en Moodle si no existe
    public function crearGrupoMoodle($courseId, $nombreGrupo, $idNumber = '')
    {
        $params = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_group_create_groups',
            'moodlewsrestformat' => 'json',
        ];

        $bodyData = [
            'groups' => [
                [
                    'courseid'          => (int)$courseId,
                    'name'              => (string)$nombreGrupo, // Aquí llegará solo el código corto
                    'description'       => '',
                    'descriptionformat' => 1,
                    'idnumber'          => (string)$idNumber,    // Guardamos también el código aquí
                ]
            ]
        ];

        $queryString = http_build_query($params) . '&' . http_build_query($bodyData);

        try {
            $response = Http::withBody($queryString, 'application/x-www-form-urlencoded')->post($this->url);
            $res = $response->json();
            
            if (isset($res['exception']) || isset($res['errorcode'])) {
                $detalles = $res['message'] ?? $res['errorcode'];
                if (isset($res['debuginfo'])) {
                    $detalles .= ' | Detalle Técnico: ' . $res['debuginfo'];
                }
                return ['success' => false, 'message' => $detalles];
            }

            if (is_array($res) && isset($res[0]['id'])) {
                return ['success' => true, 'moodle_group_id' => $res[0]['id']];
            }

            return ['success' => false, 'message' => 'Respuesta inesperada de Moodle: ' . json_encode($res)];
        } catch (\Exception $e) {
            Log::error('Error creando grupo en Moodle: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // 2. Buscar un grupo por su Nombre (ya que no usaremos idnumber)
    public function obtenerGrupoPorCodigo($courseId, $codigoMoodle)
    {
        $params = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_group_get_course_groups',
            'moodlewsrestformat' => 'json',
            'courseid'           => $courseId
        ];

        try {
            $response = Http::asForm()->post($this->url, $params);
            $grupos = $response->json();

            if (is_array($grupos) && !isset($grupos['exception'])) {
                foreach ($grupos as $g) {
                    // Ahora comparamos contra el 'name' o si el 'name' empieza con tu código local
                    if (isset($g['name']) && (trim($g['name']) === trim($codigoMoodle) || str_starts_with(trim($g['name']), trim($codigoMoodle)))) {
                        return $g; 
                    }
                }
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // 3. Vincular al asesor al grupo (Inscribir en curso + Añadir al grupo) con Rol Dinámico
    public function vincularAsesorAGrupo($emailAsesor, $courseId, $moodleGroupId, $roleId = 3)
    {
        $paramsUser = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_user_get_users_by_field',
            'moodlewsrestformat' => 'json',
            'field'              => 'email',
            'values'             => [$emailAsesor]
        ];

        try {
            $resUser = Http::asForm()->post($this->url, $paramsUser)->json();
            if (empty($resUser) || isset($resUser['exception']) || count($resUser) == 0) {
                return ['success' => false, 'message' => 'El asesor no está registrado en Moodle con ese correo.'];
            }

            $moodleUserId = $resUser[0]['id'];
            $nombreCompletoMoodle = mb_strtoupper($resUser[0]['firstname'] . ' ' . $resUser[0]['lastname'], 'UTF-8');

            $paramsEnrol = [
                'wstoken'            => $this->token,
                'wsfunction'         => 'enrol_manual_enrol_users',
                'moodlewsrestformat' => 'json',
                'enrolments' => [
                    ['roleid' => (int)$roleId, 'userid' => $moodleUserId, 'courseid' => $courseId] 
                ]
            ];
            Http::asForm()->post($this->url, $paramsEnrol);

            $paramsGroup = [
                'wstoken'            => $this->token,
                'wsfunction'         => 'core_group_add_group_members',
                'moodlewsrestformat' => 'json',
                'members'            => [
                    ['groupid' => $moodleGroupId, 'userid' => $moodleUserId]
                ]
            ];
            
            $resGroup = Http::asForm()->post($this->url, $paramsGroup)->json();
            
            if (isset($resGroup['exception'])) {
                return ['success' => false, 'message' => 'Error al añadir al grupo: ' . $resGroup['message']];
            }

            // Devolvemos el nombre del asesor encontrado para pintarlo en tiempo real
            return ['success' => true, 'nombre_asesor' => $nombreCompletoMoodle];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Excepción al vincular asesor: ' . $e->getMessage()];
        }
    }

    public function obtenerCursoPorCodigoCorto($codigoAsignatura)
    {
        $params = [
            'wstoken'            => $this->token,
            'wsfunction'         => 'core_course_get_courses',
            'moodlewsrestformat' => 'json',
        ];

        try {
            $response = Http::asForm()->post($this->url, $params);
            $cursos = $response->json();

            if (is_array($cursos) && !isset($cursos['exception'])) {
                foreach ($cursos as $c) {
                    if (isset($c['shortname']) && trim($c['shortname']) == trim($codigoAsignatura)) {
                        return $c['id'];
                    }
                }
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Error buscando curso en MoodleGrupoService: ' . $e->getMessage());
            return null;
        }
    }
}