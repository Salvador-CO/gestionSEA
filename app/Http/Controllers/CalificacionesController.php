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
}