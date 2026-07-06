<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MoodleService;
use Illuminate\Support\Facades\Auth;

class CalificacionesController extends Controller
{
    protected $moodle;

    public function __construct(MoodleService $moodle) {
        $this->moodle = $moodle;
    }

    public function index(Request $request) {
        // 1. Obtener centro del usuario logueado para filtrar
        $centroUsuario = Auth::user()->centro;
        $filtroCentro = empty($centroUsuario) ? null : $centroUsuario;

        // 2. Obtener filtros del request
        $categoryId = $request->input('category_id');
        $courseId = $request->input('course_id');
        $roleName = $request->input('role_name');

        // 3. Obtener categorías (Ahora sí existe el método)
        $categorias = $this->moodle->getCategorias();

        // 4. Obtener resultados pasando el filtro de centro
        $resultado = $this->moodle->getCalificacionesFiltradas($categoryId, $courseId, $roleName, $filtroCentro);

        $rolesDisponibles = ['Estudiante', 'Docente', 'Admin'];

        return view('calificaciones.index', [
            'categorias' => $categorias,
            'cursos' => $resultado['cursos_select'],
            'calificaciones' => $resultado['data'],
            'roles' => $rolesDisponibles,
            'fuente' => $resultado['fuente'],
            'selectedCategory' => $categoryId,
            'selectedCourse' => $courseId,
            'selectedRole' => $roleName,
            'centroFiltrado' => $filtroCentro
        ]);
    }

    public function create()
    {
        $rolNombre = strtolower(auth()->user()->rol->nombre ?? '');
        $isAdminOrJefe = in_array($rolNombre, ['administrador', 'jefe', 'admin']);
        
        return view('calificaciones.create', compact('isAdminOrJefe'));
    }

    public function buscarUsuario(Request $request)
    {
        $criterio = $request->input('query');
        if (empty($criterio)) {
            return response()->json(['success' => false, 'message' => 'Criterio vacío']);
        }

        // Buscar primero por email
        $user = $this->moodle->findUserByEmail($criterio);

        // Si no se encuentra por email, buscar por username (matricula)
        if (!$user) {
            $response = $this->moodle->getCall('core_user_get_users_by_field', [
                'field'  => 'username',
                'values' => [strtolower($criterio)]
            ]);
            
            if ($response && is_array($response) && count($response) > 0 && !isset($response['exception'])) {
                $u = $response[0];
                $centro = 'Sin Centro';
                if (!empty($u['customfields'])) {
                    foreach ($u['customfields'] as $f) {
                        if ($f['shortname'] == 'centro') $centro = trim($f['value']);
                    }
                }
                $user = [
                    'moodle_id' => $u['id'],
                    'username'  => $u['username'],
                    'firstname' => $u['firstname'],
                    'lastname'  => $u['lastname'],
                    'email'     => $u['email'],
                    'centro'    => $centro
                ];
            }
        }

        if ($user) {
            return response()->json(['success' => true, 'usuario' => $user]);
        }
        
        return response()->json(['success' => false, 'message' => 'Usuario no encontrado']);
    }

    public function obtenerExamenes($userid)
    {
        // 1. Obtener cursos en los que está matriculado
        $cursos = $this->moodle->getUsersCourses($userid);
        if (empty($cursos) || (isset($cursos['exception']))) {
            return response()->json(['success' => false, 'message' => 'No se encontraron cursos para este usuario.']);
        }

        // 2. Extraer los IDs de los cursos
        $courseIds = array_column($cursos, 'id');

        // 3. Obtener los quizzes de esos cursos
        $quizzes = $this->moodle->getQuizzesByCourses($courseIds);

        if (empty($quizzes)) {
            return response()->json(['success' => false, 'message' => 'El usuario no tiene evaluaciones en sus cursos.']);
        }

        return response()->json(['success' => true, 'quizzes' => $quizzes]);
    }

    public function obtenerIntentos($quizid, $userid)
    {
        $attempts = $this->moodle->getUserAttempts($quizid, $userid);
        
        if (empty($attempts)) {
            return response()->json(['success' => false, 'message' => 'No hay intentos finalizados para esta evaluación.']);
        }

        return response()->json(['success' => true, 'attempts' => $attempts]);
    }

    public function obtenerRevision($attemptid)
    {
        $review = $this->moodle->getAttemptReview($attemptid);
        
        if (empty($review) || isset($review['exception'])) {
            return response()->json(['success' => false, 'message' => 'No se pudo cargar la revisión del intento.']);
        }

        return response()->json(['success' => true, 'review' => $review]);
    }
}