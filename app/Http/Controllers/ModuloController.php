<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Models\Permiso;
use App\Models\Rol;
use App\Services\AuditoriaService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ModuloController extends Controller implements HasMiddleware
{
    /**
     * Definimos el middleware para el controlador
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function crear(Request $request)
    {
        // Validación de seguridad
        if (!auth()->user()->tienePermiso('gestionar_roles_permisos')) {
            abort(403, 'No tienes autorización para realizar esta acción.');
        }

        $nombre = ucfirst($request->nombre);
        $nombreLower = strtolower($nombre);
        
        // 1. Crear Controlador y Modelo usando Artisan
        Artisan::call('make:controller', [
            'name' => "{$nombre}Controller",
            '--resource' => true,
            '--no-interaction' => true,
        ]);

        Artisan::call('make:model', [
            'name' => $nombre,
            '--no-interaction' => true,
        ]);

        // --- MEJORA: INYECTAR CÓDIGO AL CONTROLADOR CREADO ---
        $controllerPath = app_path("Http/Controllers/{$nombre}Controller.php");
        
        $controllerTemplate = "<?php\n\n" .
            "namespace App\Http\Controllers;\n\n" .
            "use Illuminate\Http\Request;\n" .
            "use App\Models\\$nombre;\n\n" .
            "class {$nombre}Controller extends Controller\n" .
            "{\n" .
            "    public function index()\n" .
            "    {\n" .
            "        return view('{$nombreLower}.index');\n" .
            "    }\n\n" .
            "    public function create()\n" .
            "    {\n" .
            "        return view('{$nombreLower}.create');\n" .
            "    }\n\n" .
            "    public function store(Request \$request)\n" .
            "    {\n" .
            "        // Lógica para guardar\n" .
            "    }\n\n" .
            "    public function show(string \$id)\n" .
            "    {\n" .
            "        return view('{$nombreLower}.show');\n" .
            "    }\n\n" .
            "    public function edit(string \$id)\n" .
            "    {\n" .
            "        return view('{$nombreLower}.edit');\n" .
            "    }\n\n" .
            "    public function update(Request \$request, string \$id)\n" .
            "    {\n" .
            "        // Lógica para actualizar\n" .
            "    }\n\n" .
            "    public function destroy(string \$id)\n" .
            "    {\n" .
            "        // Lógica para eliminar\n" .
            "    }\n" .
            "}";

        File::put($controllerPath, $controllerTemplate);
        // ----------------------------------------------------

        // 2. Crear Carpeta de Vista e Index
        $path = resource_path("views/$nombreLower");
        if(!File::exists($path)){
            File::makeDirectory($path, 0755, true);
            
            // Template mejorado para la vista inicial
            $template = "@extends('layouts.app')\n\n" .
                        "@section('content')\n" .
                        "<div class='container-fluid'>\n" .
                        "    <div class='card shadow'>\n" .
                        "        <div class='card-header bg-primary text-white d-flex justify-content-between align-items-center'>\n" .
                        "            <h3 class='mb-0'><i class='bi bi-layers me-2'></i> Módulo de $nombre</h3>\n" .
                        "            <a href='{{ route('{$nombreLower}.create') }}' class='btn btn-light btn-sm text-primary fw-bold'>Pagina en blanco</a>\n" .
                        "        </div>\n" .
                        "        <div class='card-body'>\n" .
                        "            <p>Contenido generado automáticamente para el módulo <strong>$nombre</strong>.</p>\n" .
                        "            <div class='alert alert-info'>\n" .
                        "                Esta es la vista principal. Puedes empezar a editarla en <code>resources/views/{$nombreLower}/index.blade.php</code>\n" .
                        "            </div>\n" .
                        "        </div>\n" .
                        "    </div>\n" .
                        "</div>\n" .
                        "@endsection";
            
            File::put("$path/index.blade.php", $template);
            
            // También creamos un archivo create.blade.php básico para evitar errores al navegar
            File::put("$path/create.blade.php", "@extends('layouts.app')\n@section('content')\n<h1>Nuevo $nombre</h1>\n@endsection");
        }

        // 3. Crear Permiso y asignarlo al Administrador (ID 1)
        $permiso = Permiso::firstOrCreate(
            ['nombre' => "ver_$nombreLower"],
            ['descripcion' => "Acceso al módulo de $nombre"]
        );

        $admin = Rol::find(1); 
        if($admin) {
            $admin->permisos()->syncWithoutDetaching([$permiso->id]);
        }

        return back()->with('success', "¡Módulo $nombre creado exitosamente con sus controladores y vistas!");
    }

    public function gestionarRoles() {
        if (!auth()->user()->tienePermiso('gestionar_roles_permisos')) {
            abort(403);
        }
        
        $roles = Rol::with('permisos')->get();
        $permisos = Permiso::all();
        return view('roles.index', compact('roles', 'permisos'));
    }

    public function actualizarPermisos(Request $request) {
        if (!auth()->user()->tienePermiso('gestionar_roles_permisos')) {
            abort(403);
        }

        $roles = Rol::all();

        foreach($roles as $rol) {
            if (isset($request->roles[$rol->id])) {
                $rol->permisos()->sync($request->roles[$rol->id]);
            } else {
                $rol->permisos()->sync([]);
            }
        }

        AuditoriaService::registrar('ACTUALIZAR_PERMISOS', 'Roles y Permisos', 'Actualizó la matriz de permisos de todos los roles');
        return back()->with('success', 'Seguridad actualizada. El sistema se refrescará en breve.');
    }

    
    // --- NUEVA FUNCIÓN PARA AGREGAR ROL ---
    public function storeRol(Request $request) {
        if (!auth()->user()->tienePermiso('gestionar_roles_permisos')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombre' => 'required|unique:roles,nombre|max:100',
            'descripcion' => 'nullable|string'
        ]);

        Rol::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion ?? 'Sin descripción',
            'activo'      => 1
        ]);
        AuditoriaService::registrar('CREAR_ROL', 'Roles y Permisos', "Creó el rol: {$request->nombre}");
        return response()->json(['success' => '¡Rol creado correctamente!']);
    }


}