<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::orderByDesc('created_at');

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }
        if ($request->filled('accion')) {
            $query->where('accion', 'like', '%' . $request->accion . '%');
        }
        if ($request->filled('usuario')) {
            $query->where('username', 'like', '%' . $request->usuario . '%');
        }
        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        $logs    = $query->paginate(25)->withQueryString();
        $modulos = ActivityLog::distinct()->orderBy('modulo')->pluck('modulo');

        return view('auditoria.index', compact('logs', 'modulos'));
    }
}
