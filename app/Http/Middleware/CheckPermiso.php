<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermiso
{
    public function handle(Request $request, Closure $next, $permiso): Response
    {
        // 1. Verificar si está logueado
        if (!auth()->check()) {
            return redirect('/');
        }

        // 2. Verificar permiso (usando el método del modelo User)
        if (!auth()->user()->tienePermiso($permiso)) {
            // Debug opcional: Descomenta la línea de abajo para ver qué permiso está fallando exactamente
            // dd("Falta el permiso: " . $permiso); 
            abort(403, 'No tienes autorización para acceder a: ' . $permiso);
        }

        return $next($request);
    }
}