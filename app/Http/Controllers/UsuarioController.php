<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use App\Services\MoodleService;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UsuarioController extends Controller implements HasMiddleware
{
    protected $moodleService;

    public function __construct(MoodleService $moodleService)
    {
        $this->moodleService = $moodleService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function index() {
        $usuarios = User::with('rol')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create() {
        $roles = Rol::all();
        return view('usuarios.create', compact('roles'));
    }

    /**
     * Endpoint para validar correo contra Moodle via AJAX
     */
    public function verificarMoodle(Request $request) {
        $email = $request->get('email');
        $moodleUser = $this->moodleService->findUserByEmail($email);

        if ($moodleUser) {
            return response()->json(['success' => true, 'user' => $moodleUser]);
        }
        return response()->json(['success' => false, 'message' => 'Usuario no encontrado en Moodle']);
    }

    public function store(Request $request) {
        $request->validate([
            'username' => 'required|unique:usuarios',
            'email' => 'required|email|unique:usuarios',
            'password' => 'required|min:6',
            'rol_id' => 'required',
            'centro' => 'nullable|string'
        ]);

        User::create([
            'username' => $request->username,
            'nombre'   => $request->nombre,
            'apellido' => $request->apellido,
            'email'    => $request->email,
            'centro'   => $request->centro,
            'rol_id'   => $request->rol_id,
            'moodle_user_id' => $request->moodle_user_id,
            'password' => Hash::make($request->password),
            'activo'   => 1
        ]);
        AuditoriaService::registrar('CREAR_USUARIO', 'Usuarios', "Creó el usuario de sistema: {$request->username}");
        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit($id) {
        $usuario = User::findOrFail($id);
        $roles = Rol::all();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, $id) {
        $usuario = User::findOrFail($id);
        
        $request->validate([
            'email' => 'required|email|unique:usuarios,email,'.$id,
            'nombre' => 'required',
        ]);
        
        $data = $request->only('nombre', 'apellido', 'email', 'rol_id', 'activo', 'centro');
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);
        AuditoriaService::registrar('EDITAR_USUARIO', 'Usuarios', "Editó el usuario: {$usuario->username}");
        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado.');
    }

    public function toggleStatus($id) {
        $usuario = User::findOrFail($id);
        $usuario->activo = !$usuario->activo;
        $usuario->save();
        $estado = $usuario->activo ? 'activó' : 'suspendió';
        AuditoriaService::registrar('TOGGLE_USUARIO', 'Usuarios', "Se {$estado} la cuenta del usuario: {$usuario->username}");
        return back()->with('success', 'Estado de cuenta actualizado.');
    }
}