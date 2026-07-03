<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AsignacionService;
use App\Services\RegistroService;
use Illuminate\Support\Facades\Auth;

class AsignacionController extends Controller
{
    protected $asignacion;
    protected $registro;

    public function __construct(AsignacionService $asignacion, RegistroService $registro)
    {
        $this->asignacion = $asignacion;
        $this->registro = $registro;
    }

    public function index() 
    { 
        return view('asignacion.index', ['user' => Auth::user()]); 
    }

    /**
     * Valida y busca un estudiante por correo electrónico O por username (matrícula).
     * El campo que llegue en el request determina el tipo de búsqueda.
     */
    public function validarEstudiante(Request $request)
    {
        // Determinar tipo de búsqueda
        if ($request->filled('username')) {
            $moodleUser = $this->registro->buscarUsuarioPorUsername($request->username);
            $tipoBusqueda = 'matrícula';
        } elseif ($request->filled('email')) {
            $moodleUser = $this->registro->buscarUsuarioPorEmail($request->email);
            $tipoBusqueda = 'correo';
        } else {
            return response()->json(['success' => false, 'message' => 'Proporcione un correo o matrícula.']);
        }

        if (!$moodleUser) {
            return response()->json(['success' => false, 'message' => "Usuario no encontrado por $tipoBusqueda."]);
        }

        $centroEstudiante = 'SIN CENTRO';
        $rol = 'ESTUDIANTE';

        if (isset($moodleUser['customfields'])) {
            foreach ($moodleUser['customfields'] as $f) {
                if ($f['shortname'] == 'centro') $centroEstudiante = strtoupper($f['value']);
                if ($f['shortname'] == 'tipoROL') $rol = strtoupper($f['value']);
            }
        }

        $userAdmin = Auth::user();
        if (!empty($userAdmin->centro) && strtoupper($userAdmin->centro) !== 'ADMIN' && strtoupper($userAdmin->centro) !== $centroEstudiante) {
            return response()->json(['success' => false, 'message' => "El alumno pertenece al plantel: $centroEstudiante."]);
        }

        $inscritos = $this->asignacion->obtenerCursosInscritos($moodleUser['id']);
        if (is_array($inscritos)) {
            foreach ($inscritos as &$curso) {
                if (str_contains(strtoupper($curso['fullname'] ?? ''), 'EVAL')) {
                    $curso['nota'] = $this->asignacion->obtenerNotaCurso($moodleUser['id'], $curso['id']);
                }
            }
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id'       => $moodleUser['id'],
                'nombre'   => mb_strtoupper($moodleUser['firstname'].' '.$moodleUser['lastname'], 'UTF-8'),
                'email'    => $moodleUser['email'],
                'username' => $moodleUser['username'] ?? '',
                'centro'   => $centroEstudiante,
                'rol'      => $rol,
                'foto'     => $moodleUser['profileimageurlsmall'] ?? null
            ],
            'cursosEval' => $this->asignacion->obtenerCursosEvaluacion(),
            'inscritos'  => $inscritos ?: []
        ]);
    }

    public function verHistorial(Request $request)
    {
        return response()->json($this->asignacion->obtenerHistorialIntentos($request->userId, $request->courseId));
    }

    public function procesarAsignacion(Request $request)
    {
        return response()->json($this->asignacion->inscribirUsuario($request->userId, $request->courseId, $request->groupId));
    }

    public function reiniciarIntento(Request $request)
    {
        return response()->json($this->asignacion->habilitarIntentoExtra($request->userId, $request->courseId));
    }

    public function quitarIntento(Request $request)
    {
        return response()->json($this->asignacion->quitarIntentoExtra($request->userId, $request->courseId));
    }
}