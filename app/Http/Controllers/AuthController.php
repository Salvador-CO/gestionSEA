<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin() {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // 1. Intentar autenticar al usuario
        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            // 2. Verificar si la cuenta está activa
            if ($user->activo == 0) {
                auth()->logout(); // Lo sacamos si está suspendido
                return back()->withErrors([
                    'suspendido' => 'Tu cuenta se encuentra suspendida. Contacta al administrador del SEA.',
                ])->withInput();
            }

            // 3. Si todo bien, al dashboard
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        // 4. Si falla la autenticación
        return back()->withErrors([
            'error_login' => 'El usuario o la contraseña son incorrectos.',
        ])->withInput();
    }

    public function logout() {
        Auth::logout();
        return redirect('/');
    }
}