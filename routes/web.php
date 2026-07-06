<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CalificacionesController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\AsesorController;
use App\Http\Controllers\CentroController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditoriaController;

/*
|--------------------------------------------------------------------------
| 1. RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| 2. RUTAS PROTEGIDAS (Requieren Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Panel principal tras iniciar sesión
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/limpiar-cache', [DashboardController::class, 'limpiarCache'])->name('dashboard.limpiarCache');

    /* |--- A. MÓDULO DE SEGURIDAD Y PERMISOS --- 
    | Solo usuarios con permiso para gestionar roles (Administradores)
    */
    Route::middleware(['checkpermiso:gestionar_roles_permisos'])->group(function () {
        Route::get('/roles', [ModuloController::class, 'gestionarRoles']);
        Route::post('/roles/update', [ModuloController::class, 'actualizarPermisos']);
        Route::post('/crear-modulo', [ModuloController::class, 'crear']);
        Route::post('/roles/store', [ModuloController::class, 'storeRol'])->name('roles.store');
    });

    /* |--- B. GESTIÓN DE USUARIOS DEL SISTEMA --- 
    | Control de acceso local y vinculación con Moodle
    */
    Route::middleware(['checkpermiso:gestionar_usuarios'])->group(function () {
        Route::get('/usuarios/verificar-moodle', [UsuarioController::class, 'verificarMoodle'])->name('usuarios.verificarMoodle');
        Route::resource('usuarios', UsuarioController::class);
        Route::post('/usuarios/toggle/{id}', [UsuarioController::class, 'toggleStatus'])->name('usuarios.toggle');
    });

    /* |--- C. CALIFICACIONES GENERALES --- 
    | Consulta básica de notas del sistema
    */
    Route::middleware(['checkpermiso:ver_calificaciones'])->group(function () {
        Route::resource('calificaciones', CalificacionesController::class);
        
        // Endpoints API para Revisión de Exámenes
        Route::post('/calificaciones/api/buscar-usuario', [CalificacionesController::class, 'buscarUsuario'])->name('calificaciones.api.buscarUsuario');
        Route::get('/calificaciones/api/examenes/{userid}', [CalificacionesController::class, 'obtenerExamenes'])->name('calificaciones.api.obtenerExamenes');
        Route::get('/calificaciones/api/intentos/{quizid}/{userid}', [CalificacionesController::class, 'obtenerIntentos'])->name('calificaciones.api.obtenerIntentos');
        Route::get('/calificaciones/api/revision/{attemptid}', [CalificacionesController::class, 'obtenerRevision'])->name('calificaciones.api.obtenerRevision');
    });

    /* |--- D. MÓDULO DE REGISTRO INICIAL --- 
    | Registro de usuarios nuevos en Moodle y primera inscripción a cursos normales
    */
    Route::middleware(['checkpermiso:ver_registro'])->group(function () {
    
            // Vistas principales
            Route::get('/registro', [RegistroController::class, 'index'])->name('registro.index');
            
            // Dejamos una sola ruta oficial para la vista del buscador
            Route::get('/registro/usuarios-lista', [RegistroController::class, 'listaUsuarios'])->name('registro.usuarios');

            // CORRECCIÓN AQUÍ: Le agregamos el prefijo /registro/ para que coincida con la estructura
            Route::post('/registro/buscar-estudiante', [RegistroController::class, 'buscarEstudiante'])->name('registro.buscarEstudiante');
                
            // Acciones de Formulario
            Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');
            Route::get('/registro/validar', [RegistroController::class, 'validarEmail'])->name('registro.validar');
                
            // Endpoints para selects dinámicos (Cursos y Grupos filtrados por centro)
            Route::get('/registro/obtener-cursos', [RegistroController::class, 'obtenerCursos'])->name('registro.obtenerCursos');
            Route::get('/registro/obtener-grupos/{courseId}', [RegistroController::class, 'obtenerGrupos'])->name('registro.obtenerGrupos');
            Route::post('/registro/asignar-curso', [RegistroController::class, 'inscribirCurso'])->name('registro.inscribir');
        });

    /* |--- E. NUEVO MÓDULO: ASIGNACIÓN DE EVALUACIONES (EVAL) --- 
    | Aquí separamos la lógica para manejar solo cursos de actividad única "EVAL"
    | Incluye validación estricta de pertenencia al centro.
    */
    /* |--- E. NUEVO MÓDULO: ASIGNACIÓN DE EVALUACIONES (EVAL) --- */
    Route::middleware(['auth', 'checkpermiso:ver_registro'])->group(function () {
        Route::get('/asignacion', [AsignacionController::class, 'index'])->name('asignacion.index');
        Route::get('/asignacion/validar-estudiante', [AsignacionController::class, 'validarEstudiante'])->name('asignacion.validar');
        Route::post('/asignacion/procesar', [AsignacionController::class, 'procesarAsignacion'])->name('asignacion.procesar');
        Route::post('/asignacion/nuevo-intento', [AsignacionController::class, 'reiniciarIntento'])->name('asignacion.reintentar');
        Route::post('/asignacion/quitar-intento', [AsignacionController::class, 'quitarIntento'])->name('asignacion.quitarIntento');
        Route::get('/asignacion/historial', [AsignacionController::class, 'verHistorial'])->name('asignacion.historial');
        
    });

    /* |--- F. REPORTES Y ESTADÍSTICAS --- 
    */
    Route::middleware(['checkpermiso:ver_reporte'])->group(function () {
        Route::get('/reporte', [ReporteController::class, 'index'])->name('reporte.index');
        Route::get('/reporte/ajax-cursos', [ReporteController::class, 'ajaxCursos'])->name('reporte.ajax.cursos');
        Route::get('/reporte/ajax-modal', [ReporteController::class, 'ajaxModal'])->name('reporte.ajax.modal');
    });


    /* |--- G. Gestionar catalogos y grupos --- 
    */
    Route::middleware(['checkpermiso:gestionar_asesores'])->group(function () {
        Route::resource('asesores', AsesorController::class);
        Route::get('/asesores/exportar', [AsesorController::class, 'exportar'])->name('asesores.exportar');
    });
    Route::middleware(['checkpermiso:gestionar_centros'])->group(function () {
        Route::resource('centros', CentroController::class);
    });
    Route::middleware(['checkpermiso:gestionar_asignaturas'])->group(function () {
        Route::resource('asignaturas', AsignaturaController::class);
    });
    Route::middleware(['checkpermiso:gestionar_cargos'])->group(function () {
        Route::resource('cargos', CargoController::class);
    });
    Route::middleware(['checkpermiso:gestionar_grupos'])->group(function () {
        Route::resource('grupos', GrupoController::class);
        Route::get('/grupos/exportar', [GrupoController::class, 'exportar'])->name('grupos.exportar');
        Route::post('grupos/{grupo}/sincronizar', [GrupoController::class, 'sincronizar'])->name('grupos.sincronizar');

        Route::get('tablero-moodle', [GrupoController::class, 'tableroMoodle'])->name('grupos.tableroMoodle');
        Route::get('tablero-moodle/grupos/{claveAsignatura}', [GrupoController::class, 'obtenerDetalleGruposMoodle']);
        Route::post('tablero-moodle/crear-remoto', [GrupoController::class, 'crearGrupoRemotoEnMoodle'])->name('grupos.crearRemoto');
        Route::post('/tablero-moodle/asignar-asesor', [GrupoController::class, 'asignarAsesorMoodle'])->name('grupos.asignarAsesorMoodle');
        Route::post('/tablero-moodle/desvincular-asesor', [GrupoController::class, 'desvincularAsesorMoodle'])->name('grupos.desvincularAsesorMoodle');
    });

    /* |--- I. AUDITORÍA --- */
    Route::middleware(['checkpermiso:gestionar_usuarios'])->group(function () {
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    });

    /* |--- H. Gestionar correo --- 
    */

    Route::middleware(['checkpermiso:ver_correo'])->group(function () {
         
        Route::get('/correo', [CorreoController::class, 'index'])->name('correo.index');
        Route::post('/correo/importar', [CorreoController::class, 'importar'])->name('correo.importar');
        Route::post('/correo/status/{id}', [CorreoController::class, 'toggleStatus']);
        Route::post('/correo/enviado/{id}', [CorreoController::class, 'marcarComoEnviado']);
        Route::get('/correos/plantilla', [CorreoController::class, 'descargarPlantilla'])->name('correos.plantilla');
        Route::get('/correos/exportar-pendientes/{plantel?}', [CorreoController::class, 'exportarPendientes'])->name('correos.exportar');
    });


});