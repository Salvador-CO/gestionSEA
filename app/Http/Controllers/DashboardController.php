<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\Grupo;
use App\Models\Centro;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $rolName = $user->rol->nombre ?? '';

        // Solo Admin y Jefe ven el panel completo
        $esPrivilegiado = in_array(strtolower($rolName), ['administrador', 'jefe', 'admin']);

        if (!$esPrivilegiado) {
            // Usuarios operativos: solo ven bienvenida personal
            return view('dashboard', [
                'esPrivilegiado'         => false,
                'totalUsuarios'          => 0,
                'totalAsesores'          => 0,
                'totalGrupos'            => 0,
                'totalCorreosPendientes' => 0,
                'gruposPorCentro'        => collect(),
                'asesoresPorCargo'       => collect(),
                'gruposConAsesor'        => 0,
                'gruposSinAsesor'        => 0,
                'ultimosLogs'            => collect(),
                'cacheCursos'            => false,
                'cacheStats'             => false,
            ]);
        }

        // ── KPIs ───────────────────────────────────────────────────────────
        $totalUsuarios  = User::count();
        $totalAsesores  = Asesor::count();
        $totalGrupos    = Grupo::count();

        $totalCorreosPendientes = 0;
        try {
            $totalCorreosPendientes = DB::table('correos')->where('status', 0)->count();
        } catch (\Exception $e) { }

        // ── Gráfica 1: Grupos por Centro ────────────────────────────────────
        $gruposPorCentro = Centro::withCount('grupos')->get()->map(fn($c) => [
            'nombre' => $c->nombre,
            'total'  => $c->grupos_count,
        ])->filter(fn($c) => $c['total'] > 0)->values();

        // ── Gráfica 2: Asesores por Cargo ───────────────────────────────────
        $asesoresPorCargo = Asesor::with('cargo')
            ->get()
            ->groupBy(fn($a) => $a->cargo->nombre ?? 'Sin cargo')
            ->map(fn($g, $cargo) => ['cargo' => $cargo, 'total' => $g->count()])
            ->values();

        // ── Gráfica 3: Estado de Grupos ──────────────────────────────────────
        $gruposConAsesor = Grupo::whereNotNull('asesor_id')->count();
        $gruposSinAsesor = $totalGrupos - $gruposConAsesor;

        // ── Últimos logs ──────────────────────────────────────────────────────
        $ultimosLogs = ActivityLog::orderByDesc('created_at')->limit(8)->get();

        // ── Info de caché ─────────────────────────────────────────────────────
        $cacheCursos = Cache::has('moodle_todos_cursos');
        $cacheStats  = Cache::has('moodle_advanced_report_all');

        return view('dashboard', compact(
            'esPrivilegiado',
            'totalUsuarios', 'totalAsesores', 'totalGrupos', 'totalCorreosPendientes',
            'gruposPorCentro', 'asesoresPorCargo',
            'gruposConAsesor', 'gruposSinAsesor',
            'ultimosLogs', 'cacheCursos', 'cacheStats'
        ));
    }

    /**
     * Limpia todos los cachés de Moodle para forzar actualización.
     * Solo accesible para administradores.
     */
    public function limpiarCache(Request $request)
    {
        // Limpiar todas las keys conocidas de Moodle
        Cache::forget('moodle_todos_cursos');
        Cache::forget('moodle_advanced_report_all');
        Cache::forget('moodle_categories');

        // Limpiar cachés de grupos por curso (patrón)
        // Nota: Para driver database/file, no hay forget por patrón, borramos todo el caché de la app
        Cache::flush();

        return back()->with('success', '✅ Caché de Moodle limpiado. Los datos se actualizarán en la próxima consulta a la API.');
    }
}
