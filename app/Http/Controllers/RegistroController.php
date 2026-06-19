<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MoodleService;
use App\Services\RegistroService;
use App\Services\BusquedaService;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegistroController extends Controller
{
    protected $moodle;
    protected $registro;
    protected $busqueda;

    private $mapeo = [
        'CENTRO 1'     => '1', 'CENTRO 2'     => '2',
        'CENTRO 3'     => '3', 'CENTRO 4'     => '4',
        'CENTRO 5'     => '5', 'CAE LEON'     => '6',
        'CAE IRAPUATO' => '7',
    ];

    public function __construct(MoodleService $moodle, RegistroService $registro, BusquedaService $busqueda) {
        $this->moodle = $moodle;
        $this->registro = $registro;
        $this->busqueda = $busqueda;
    }

    public function index() {
        $user = Auth::user();
        $isAdmin = empty($user->centro);
        $centrosDisponibles = $this->mapeo;
        if (!$isAdmin) {
            $centroUsuario = strtoupper($user->centro);
            $centrosDisponibles = isset($this->mapeo[$centroUsuario]) ? [$centroUsuario => $this->mapeo[$centroUsuario]] : [];
        }
        return view('registro.index', ['user' => $user, 'isAdmin' => $isAdmin, 'centrosDisponibles' => $centrosDisponibles]);
    }

    public function store(Request $request) {
        $user = Auth::user();
        $datos = $request->all();
        if (!empty($user->centro)) { 
            $datos['centro'] = $user->centro; 
            $datos['cohort_id'] = $this->mapeo[strtoupper($user->centro)] ?? null;
        } else {
            $datos['centro'] = array_search($datos['cohort_id'], $this->mapeo);
        }

        $res = $this->registro->crearUsuarioMoodle($datos);

        if ($res['success']) {
            $nombreCompleto = mb_strtoupper($datos['firstname'] . ' ' . $datos['lastname'], 'UTF-8');
            AuditoriaService::registrar(
                'REGISTRAR_ALUMNO',
                'Registro',
                "Registró al alumno {$nombreCompleto} en Moodle (Centro: {$datos['centro']})"
            );
            return redirect()->route('registro.index')
                ->with('success', 'Usuario registrado con éxito.')
                ->with('nuevo_usuario_id', $res['id'])
                ->with('nuevo_usuario_nombre', $nombreCompleto)
                ->with('nuevo_usuario_centro', $datos['centro']);
        }
        return back()->withInput()->with('error', 'Error en Moodle: ' . $res['message']);
    }

    public function validarEmail(Request $request) {
        $userMoodle = $this->registro->buscarUsuarioPorEmail($request->email);

        if ($userMoodle) {
            $centro = '';
            if (isset($userMoodle['customfields'])) {
                foreach ($userMoodle['customfields'] as $field) {
                    if ($field['shortname'] == 'centro') { 
                        $centro = $field['value']; 
                        break; 
                    }
                }
            }

            $nombreCompleto = mb_strtoupper($userMoodle['firstname'].' '.$userMoodle['lastname'], 'UTF-8');
            $usuarioLogueado = auth()->user();

            $esJefeOAdmin = ($usuarioLogueado->rol === 'Administrador' || $usuarioLogueado->rol === 'Jefe' || empty($usuarioLogueado->centro));

            if (!$esJefeOAdmin && $centro !== $usuarioLogueado->centro) {
                return response()->json([
                    'exists' => true,
                    'acceso_denegado' => true,
                    'moodle_id' => $userMoodle['id'],
                    'nombre' => $nombreCompleto,
                    'centro' => $centro,
                    'message' => "Este usuario pertenece al centro: " . ($centro ?: 'No asignado') . ". No corresponde a tu subcentro."
                ]);
            }

            $cursosInscritosIds = [];
            if (method_exists($this->registro, 'obtenerCursosUsuario')) {
                $cursosUsuario = $this->registro->obtenerCursosUsuario($userMoodle['id']);
                if (is_array($cursosUsuario)) {
                    $cursosInscritosIds = array_column($cursosUsuario, 'id');
                }
            } elseif (method_exists($this->moodle, 'getEnrolledCourses')) {
                $cursosUsuario = $this->moodle->getEnrolledCourses($userMoodle['id']);
                if (is_array($cursosUsuario)) {
                    $cursosInscritosIds = array_column($cursosUsuario, 'id');
                }
            }

            return response()->json([
                'exists' => true, 
                'acceso_denegado' => false,
                'moodle_id' => $userMoodle['id'],
                'nombre' => $nombreCompleto,
                'centro' => $centro,
                'cursos_inscritos' => $cursosInscritosIds
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function obtenerCursos(Request $request) {
        $todosLosCursos = $this->registro->obtenerTodosCursos();
        $userId = $request->query('userId');
        
        $cursosInscritosIds = [];
        if ($userId) {
            if (method_exists($this->registro, 'obtenerCursosUsuario')) {
                $cursosUsuario = $this->registro->obtenerCursosUsuario($userId);
                if (is_array($cursosUsuario)) {
                    $cursosInscritosIds = array_column($cursosUsuario, 'id');
                }
            } elseif (method_exists($this->moodle, 'getEnrolledCourses')) {
                $cursosUsuario = $this->moodle->getEnrolledCourses($userId);
                if (is_array($cursosUsuario)) {
                    $cursosInscritosIds = array_column($cursosUsuario, 'id');
                }
            }
        }

        $cursosFiltrados = array_filter($todosLosCursos, function($c) use ($cursosInscritosIds) {
            $esVisible = isset($c['visible']) ? (int)$c['visible'] === 1 : true;
            $contieneEval = str_contains(strtoupper($c['fullname']), 'EVAL') || str_contains(strtoupper($c['shortname']), 'EVAL');
            $yaInscrito = in_array($c['id'], $cursosInscritosIds);

            return $esVisible && !$contieneEval && !$yaInscrito;
        });

        return response()->json(array_values($cursosFiltrados));
    }

    public function obtenerGrupos(Request $request, $courseId) {
        $grupos = $this->registro->obtenerGruposCurso($courseId);
        $user = Auth::user();
        
        $usuariosCurso = $this->registro->obtenerAsesoresCurso($courseId);
        
        $asesoresPorGrupo = [];
        if (is_array($usuariosCurso)) {
            foreach ($usuariosCurso as $u) {
                $esAsesor = false;
                
                if (!empty($u['roles'])) {
                    foreach ($u['roles'] as $role) {
                        if (in_array($role['shortname'], ['contenido', 'psicopeda', 'editingteacher', 'teacher'])) {
                            $esAsesor = true;
                            break;
                        }
                    }
                }

                if ($esAsesor && isset($u['groups'])) {
                    foreach ($u['groups'] as $gUser) {
                        $asesoresPorGrupo[$gUser['id']] = mb_strtoupper($u['firstname'] . ' ' . $u['lastname'], 'UTF-8');
                    }
                }
            }
        }

        $gruposProcesados = [];
        if (is_array($grupos)) {
            foreach ($grupos as $g) {
                $miembrosIds = $this->registro->obtenerMiembrosGrupo($g['id']);
                $totalParticipantes = count($miembrosIds);
                
                $nombreAsesor = $asesoresPorGrupo[$g['id']] ?? 'Por asignar';
                
                $g['total_participantes'] = $totalParticipantes;
                $g['nombre_asesor'] = $nombreAsesor;
                $gruposProcesados[] = $g;
            }
        } else {
            $gruposProcesados = $grupos;
        }

        if (empty($user->centro)) {
            return response()->json($gruposProcesados);
        }

        $centroEstudiante = strtoupper($request->query('centro', ''));
        if (!empty($centroEstudiante)) {
            $prefijo = ''; $prefijoCorto = '';
            if (str_contains($centroEstudiante, 'CENTRO')) {
                $numero = preg_replace('/[^0-9]/', '', $centroEstudiante);
                $prefijo = 'C' . str_pad($numero, 2, "0", STR_PAD_LEFT);
                $prefijoCorto = 'C' . (int)$numero;
            } elseif (str_contains($centroEstudiante, 'LEON')) {
                $prefijo = 'CAE01';
            } elseif (str_contains($centroEstudiante, 'IRAPUATO')) {
                $prefijo = 'CAE02';
            }

            if (!empty($prefijo)) {
                $gruposProcesados = array_values(array_filter($gruposProcesados, function($g) use ($prefijo, $prefijoCorto) {
                    $nombreGrupo = strtoupper($g['name']);
                    return str_starts_with($nombreGrupo, $prefijo) || ($prefijoCorto && str_starts_with($nombreGrupo, $prefijoCorto));
                }));
            }
        }
        
        return response()->json($gruposProcesados);
    }

    public function inscribirCurso(Request $request) {
        $resultado = $this->registro->inscribirUsuarioEnCurso($request->userId, $request->courseId, $request->groupId);
        if (!empty($resultado['success'])) {
            AuditoriaService::registrar(
                'INSCRIBIR_CURSO',
                'Registro',
                "Inscribió al usuario ID:{$request->userId} en el curso ID:{$request->courseId}" . ($request->groupId ? " / grupo ID:{$request->groupId}" : '')
            );
        }
        return response()->json($resultado);
    }

    public function listaUsuarios() {
        $user = Auth::user();
        return view('registro.usuarios', ['user' => $user]);
    }

    public function buscarEstudiante(Request $request) {
        try {
            $criterio = $request->input('query');
            if (empty($criterio)) {
                return response()->json(['success' => false, 'message' => 'Por favor, ingrese un criterio de búsqueda.']);
            }

            $usuariosMoodle = $this->busqueda->buscarUsuario($criterio);

            if (empty($usuariosMoodle)) {
                return response()->json(['success' => false, 'message' => 'No se encontró ningún estudiante con esos datos o la API de Moodle no respondió.']);
            }

            $usuarioLogueado = Auth::user();
            $esJefeOAdmin = ($usuarioLogueado->rol === 'Administrador' || $usuarioLogueado->rol === 'Jefe' || empty($usuarioLogueado->centro));
            $resultadosFiltrados = [];

            // Si viene directo de la API get_users_by_field, puede venir como array directo o embebido. Aseguramos iteración.
            $listaUsuarios = isset($usuariosMoodle['users']) ? $usuariosMoodle['users'] : $usuariosMoodle;

            foreach ($listaUsuarios as $u) {
                $centroUsuario = '';
                $rolUsuario = 'student';

                if (isset($u['customfields'])) {
                    foreach ($u['customfields'] as $field) {
                        if ($field['shortname'] === 'centro') {
                            $centroUsuario = $field['value'];
                        }
                        if ($field['shortname'] === 'tipoROL') {
                            $rolUsuario = $field['value'];
                        }
                    }
                }

                // Aplicar restricción de centro si no es Jefe o Administrador
                if (!$esJefeOAdmin && strtoupper($centroUsuario) !== strtoupper($usuarioLogueado->centro)) {
                    continue; 
                }

                // Obtener información académica detallada
                $cursosDeMoodle = $this->busqueda->obtenerCursosUsuario($u['id']);
                $cursosProcesados = [];

                if (is_array($cursosDeMoodle)) {
                    foreach ($cursosDeMoodle as $curso) {
                        $gruposDeUsuario = $this->busqueda->obtenerGruposUsuarioEnCurso($u['id'], $curso['id']);
                        $nombresGrupos = !empty($gruposDeUsuario) ? array_column($gruposDeUsuario, 'name') : ['Sin grupo asignado'];

                        $cursosProcesados[] = [
                            'id' => $curso['id'],
                            'fullname' => $curso['fullname'],
                            'shortname' => $curso['shortname'],
                            'grupos' => $nombresGrupos
                        ];
                    }
                }

                $resultadosFiltrados[] = [
                    'id' => $u['id'],
                    'username' => $u['username'],
                    'firstname' => $u['firstname'],
                    'lastname' => $u['lastname'],
                    'fullname' => mb_strtoupper($u['firstname'] . ' ' . $u['lastname'], 'UTF-8'),
                    'email' => $u['email'],
                    'centro' => $centroUsuario ?: 'No asignado',
                    'rol' => $rolUsuario,
                    'lastaccess' => $u['lastaccess'] ?? 0,
                    'cursos' => $cursosProcesados
                ];
            }

            if (empty($resultadosFiltrados)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'El usuario existe pero pertenece a otro centro o no tienes permisos para visualizarlo.'
                ]);
            }

            return response()->json([
                'success' => true,
                'usuarios' => $resultadosFiltrados
            ]);

        } catch (\Exception $e) {
            Log::error("Error crítico en buscarEstudiante: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}