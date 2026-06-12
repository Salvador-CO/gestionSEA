<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Asesor;
use App\Models\Centro;
use App\Models\Asignatura;
use App\Services\MoodleGrupoService;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    protected $moodleGrupo;

    public function __construct(MoodleGrupoService $moodleGrupo)
    {
        $this->moodleGrupo = $moodleGrupo;
    }

    public function index()
    {
        $gruposRaw = Grupo::with(['centro', 'asignatura', 'asesor'])->get();
        $asesoresSinGrupo = Asesor::doesntHave('grupos')->get();
        $centros = Centro::all();
        $asignaturas = Asignatura::all();
        $asesores = Asesor::all();

        $grupos = $gruposRaw->map(function($grupo) {
            $courseId = $grupo->asignatura->moodle_course_id ?? null; 
            
            $grupoMoodle = null;
            if ($courseId) {
                $grupoMoodle = $this->moodleGrupo->obtenerGrupoPorCodigo($courseId, $grupo->codigo_moodle);
            }

            $grupo->existe_en_moodle = !is_null($grupoMoodle);
            $grupo->moodle_group_id = $grupoMoodle['id'] ?? null;

            return $grupo;
        });

        return view('grupos.index', compact('grupos', 'asesoresSinGrupo', 'centros', 'asignaturas', 'asesores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'centro_id' => 'required',
            'asignatura_id' => 'required',
            'asesor_id' => 'nullable',
        ]);

        $centro = Centro::find($request->centro_id);
        $asignatura = Asignatura::find($request->asignatura_id);

        $consecutivo = Grupo::where('centro_id', $request->centro_id)
                            ->where('asignatura_id', $request->asignatura_id)
                            ->count() + 1;

        $p_numero = str_pad($consecutivo, 2, '0', STR_PAD_LEFT);
        $codigoMoodle = $centro->clave . 'S' . $asignatura->semestre . $asignatura->clave . 'P' . $p_numero;

        Grupo::create([
            'codigo_moodle' => $codigoMoodle,
            'centro_id' => $request->centro_id,
            'asignatura_id' => $request->asignatura_id,
            'asesor_id' => $request->asesor_id,
            'p_numero' => $consecutivo
        ]);

        return back()->with('success', "Grupo $codigoMoodle generado localmente.");
    }

    public function sincronizar(Grupo $grupo)
    {
        $codigoAsignatura = $grupo->asignatura->clave; 
        $courseId = $this->moodleGrupo->obtenerCursoPorCodigoCorto($codigoAsignatura);

        if (!$courseId) {
            return back()->with('error', "No se encontró ningún curso en Moodle cuyo 'shortname' sea exactamente: '{$codigoAsignatura}'.");
        }

        $grupoMoodle = $this->moodleGrupo->obtenerGrupoPorCodigo($courseId, $grupo->codigo_moodle);
        
        if (!$grupoMoodle) {
            $nombreGrupo = $grupo->codigo_moodle . ' - ' . $grupo->centro->nombre;
            $resGrupo = $this->moodleGrupo->crearGrupoMoodle($courseId, $nombreGrupo);
            
            if (!$resGrupo['success']) {
                return back()->with('error', 'Error creando grupo en Moodle: ' . $resGrupo['message']);
            }
            $moodleGroupId = $resGrupo['moodle_group_id'];
        } else {
            $moodleGroupId = $grupoMoodle['id'];
        }

        return back()->with('success', "¡Excelente! El grupo {$grupo->codigo_moodle} se sincronizó correctamente en Moodle.");
    }

    public function update(Request $request, Grupo $grupo)
    {
        $grupo->update([
            'asesor_id' => $request->asesor_id
        ]);

        return back()->with('success', 'Asesor asignado localmente al grupo ' . $grupo->codigo_moodle);
    }

    public function destroy(Grupo $grupo)
    {
        $grupo->delete();
        return back()->with('success', 'Asignación eliminada.');
    }

    public function tableroMoodle()
    {
        $asignaturas = Asignatura::all();
        return view('grupos.moodle', compact('asignaturas'));
    }

    public function obtenerDetalleGruposMoodle(Request $request, $claveAsignatura)
    {
        $registroService = app(\App\Services\RegistroService::class);
        
        $asignaturaLocal = Asignatura::where('clave', $claveAsignatura)->first();
        if (!$asignaturaLocal) {
            return response()->json(['error' => 'Asignatura no encontrada en el sistema local.'], 404);
        }

        $todosLosCursos = $registroService->obtenerTodosCursos();
        $courseId = null;

        if (is_array($todosLosCursos)) {
            foreach ($todosLosCursos as $c) {
                if (isset($c['shortname']) && trim($c['shortname']) == trim($claveAsignatura)) {
                    $courseId = $c['id'];
                    break;
                }
            }
        }

        if (!$courseId) {
            return response()->json(['error' => 'No se encontró este curso en Moodle.'], 404);
        }

        $gruposMoodle = $registroService->obtenerGruposCurso($courseId) ?? [];
        $usuariosCurso = $registroService->obtenerAsesoresCurso($courseId) ?? [];
        
        $asesoresPorGrupoMoodle = [];
        if (is_array($usuariosCurso)) {
            foreach ($usuariosCurso as $u) {
                $esAsesor = false;
                $rolNombre = '';
                if (!empty($u['roles'])) {
                    foreach ($u['roles'] as $role) {
                        // Cambiamos la validación de nombres cortos para que coincida con los tuyos personalizados
                        if (in_array($role['shortname'], ['contenido', 'psicopeda', 'respplantel'])) {
                            $esAsesor = true;
                            if ($role['shortname'] == 'contenido') $rolNombre = 'CONTENIDO';
                            elseif ($role['shortname'] == 'psicopeda') $rolNombre = 'PSICO';
                            elseif ($role['shortname'] == 'respplantel') $rolNombre = 'RESP. CENTRO';
                            break;
                        }
                    }
                }
                if ($esAsesor && isset($u['groups'])) {
                    foreach ($u['groups'] as $gUser) {
                        $nombreCompleto = mb_strtoupper($u['firstname'] . ' ' . $u['lastname'], 'UTF-8') . " ($rolNombre)";
                        if (!isset($asesoresPorGrupoMoodle[$gUser['id']])) {
                            $asesoresPorGrupoMoodle[$gUser['id']] = [];
                        }
                        $asesoresPorGrupoMoodle[$gUser['id']][] = $nombreCompleto;
                    }
                }
            }
        }

        $gruposLocales = Grupo::with(['centro', 'asesor'])
                              ->where('asignatura_id', $asignaturaLocal->id)
                              ->get();

        $tablaFinal = [];
        foreach ($gruposLocales as $gl) {
            $grupoMoodleEncontrado = collect($gruposMoodle)->first(function($gm) use ($gl) {
                return trim($gm['name']) == trim($gl->codigo_moodle) || str_starts_with(trim($gm['name']), trim($gl->codigo_moodle));
            });

            if ($grupoMoodleEncontrado) {
                $miembrosIds = $registroService->obtenerMiembrosGrupo($grupoMoodleEncontrado['id']) ?? [];
                $totalAlumnos = count($miembrosIds);
                
                if (isset($asesoresPorGrupoMoodle[$grupoMoodleEncontrado['id']])) {
                    $asesorMoodle = implode('<br>', $asesoresPorGrupoMoodle[$grupoMoodleEncontrado['id']]);
                } else {
                    $asesorMoodle = 'No asignado en Moodle';
                }
                
                $existeEnMoodle = true;
                $idMoodle = $grupoMoodleEncontrado['id'];
            } else {
                $totalAlumnos = 0;
                $asesorMoodle = 'N/A';
                $existeEnMoodle = false;
                $idMoodle = null;
            }

            $correoDetectado = null;
            if ($gl->asesor && isset($gl->asesor->email)) {
                $correoDetectado = trim($gl->asesor->email);
            } elseif ($gl->asesor && isset($gl->asesor->correo)) {
                $correoDetectado = trim($gl->asesor->correo);
            }

            $tablaFinal[] = [
                'id_local' => $gl->id,
                'codigo_moodle' => $gl->codigo_moodle,
                'centro_nombre' => $gl->centro->nombre ?? 'Sin Centro',
                'asesor_local' => $gl->asesor ? mb_strtoupper($gl->asesor->nombre . ' ' . $gl->asesor->apellidos, 'UTF-8') : 'POR ASIGNAR',
                'correo_asesor' => $correoDetectado, 
                'existe_en_moodle' => $existeEnMoodle,
                'id_moodle' => $idMoodle,
                'total_alumnos' => $totalAlumnos,
                'asesor_moodle' => $asesorMoodle
            ];
        }

        return response()->json([
            'course_id_moodle' => $courseId,
            'grupos' => $tablaFinal
        ]);
    }

    public function crearGrupoRemotoEnMoodle(Request $request)
    {
        $request->validate([
            'course_id' => 'required',
            'codigo_moodle' => 'required',
        ]);

        $grupoLocal = Grupo::with(['centro'])->where('codigo_moodle', $request->codigo_moodle)->first();
        
        if (!$grupoLocal) {
            return response()->json(['success' => false, 'message' => 'El grupo no existe en la base de datos local.'], 404);
        }

        $nombreGrupoMoodle = $grupoLocal->codigo_moodle;
        $courseId = (int)$request->course_id;

        $resGrupo = $this->moodleGrupo->crearGrupoMoodle($courseId, $nombreGrupoMoodle, $grupoLocal->codigo_moodle);

        if (!$resGrupo['success']) {
            return response()->json(['success' => false, 'message' => $resGrupo['message']], 400);
        }

        return response()->json([
            'success' => true,
            'id_moodle' => $resGrupo['moodle_group_id']
        ]);
    }

    // MODIFICADO: Mapeo exacto de tus IDs de Rol de Moodle
    public function asignarAsesorMoodle(Request $request)
    {
        $request->validate([
            'course_id' => 'required',
            'moodle_group_id' => 'required',
            'email' => 'required|email',
            'role_type' => 'required|in:contenido,psicopeda,responsable'
        ]);

        // Mapeo dinámico usando tus roleid exactos de Moodle
        $roleId = 12; // Por defecto Asesor de Contenido
        $tagRol = 'CONTENIDO';

        if ($request->role_type == 'psicopeda') {
            $roleId = 9; 
            $tagRol = 'PSICO';
        } elseif ($request->role_type == 'responsable') {
            $roleId = 14; 
            $tagRol = 'RESP. CENTRO';
        }

        $res = $this->moodleGrupo->vincularAsesorAGrupo(
            strtolower(trim($request->email)),
            (int)$request->course_id,
            (int)$request->moodle_group_id,
            $roleId
        );

        if (!$res['success']) {
            return response()->json(['success' => false, 'message' => $res['message']], 400);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Asesor vinculado exitosamente en la plataforma Moodle.',
            'nombre_completo' => $res['nombre_asesor'] . " ($tagRol)"
        ]);
    }
}