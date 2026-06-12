<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Correo;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CorreoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Correo::query();

        // 1. Lógica de Seguridad por Roles
        if (!in_array($user->rol_id, [1, 5])) {
            $query->where('plantel', $user->centro);
        }

        /**
         * NOTA: Eliminamos el ->paginate(50) y el filtrado por 'search' de Laravel
         * porque DataTables lo hará de forma mucho más rápida y fluida en la vista.
         * Traemos todos los registros filtrados por el plantel correspondiente.
         */
        $correos = $query->orderBy('created_at', 'desc')->get();

        // 2. Estadísticas Generales (Solo para Admin y Jefe)
        $stats = null;
        $statsPlanteles = null;

        if (in_array($user->rol_id, [1, 5])) {
            $stats = [
                'total' => Correo::count(),
                'entregados' => Correo::where('estatus', 'Entregado')->count(),
                'pendientes' => Correo::where('estatus', 'Pendiente')->count(),
            ];
            $stats['porcentaje'] = $stats['total'] > 0 
                ? round(($stats['entregados'] / $stats['total']) * 100, 2) 
                : 0;

            // 3. Resumen por Plantel (Para la tabla superior)
            $statsPlanteles = Correo::select('plantel', 
                DB::raw('count(*) as total'),
                DB::raw("SUM(CASE WHEN estatus = 'Entregado' THEN 1 ELSE 0 END) as entregados"),
                DB::raw("SUM(CASE WHEN estatus = 'Pendiente' THEN 1 ELSE 0 END) as pendientes")
            )
            ->groupBy('plantel')
            ->orderBy('plantel', 'asc')
            ->get();
        }

        return view('correo.index', compact('correos', 'stats', 'statsPlanteles'));
    }

    public function importar(Request $request)
    {
        if (Auth::user()->rol_id != 1) {
            return back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $request->validate([
            'archivo_csv' => 'required|mimes:csv,txt|max:10240' // Máximo 10MB
        ]);

        $file = $request->file('archivo_csv');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Omitir cabecera
        fgetcsv($handle, 2000, ","); 

        $insertados = 0;
        $errores = 0;

        while (($data = fgetcsv($handle, 2000, ",", '"')) !== FALSE) {
            // Validación de columnas mínimas
            if (count($data) < 7 || empty($data[1])) {
                $errores++;
                continue;
            }

            try {
                Correo::updateOrCreate(
                    ['matricula' => trim($data[1])],
                    [
                        'plantel'              => trim($data[0]),
                        'nombre'               => trim($data[2]),
                        'fecha_ingreso'        => trim($data[3]),
                        'correo_personal'      => trim($data[4]),
                        'correo_institucional' => trim($data[5]),
                        'clave_correo'         => $data[6],
                        'matricula_asesor'     => $data[7] ?? null,
                        'nombre_asesor'        => $data[8] ?? null,
                        'subido_por'           => Auth::user()->username,
                    ]
                );
                $insertados++;
            } catch (\Exception $e) {
                $errores++;
            }
        }
        fclose($handle);

        return back()->with('success', "¡Proceso terminado! Importados: {$insertados}, Errores/Omitidos: {$errores}");
    }

    public function toggleStatus($id)
    {
        $user = Auth::user();
        // El rol 5 (Jefe) suele ser solo de consulta según tu lógica anterior
        if ($user->rol_id == 5) {
            return response()->json(['success' => false, 'message' => 'El rol Jefe no puede marcar entregas'], 403);
        }

        $correo = Correo::findOrFail($id);
        
        if ($correo->estatus === 'Pendiente' || $correo->estatus === 'Enviado') {
            $correo->estatus = 'Entregado';
            $correo->fecha_entrega = Carbon::now();
        } else {
            // Permite revertir si hubo un error al marcar
            $correo->estatus = 'Pendiente';
            $correo->fecha_entrega = null;
        }

        $correo->save();

        return response()->json([
            'success' => true,
            'nuevo_estatus' => $correo->estatus,
            'fecha' => $correo->fecha_entrega ? $correo->fecha_entrega->format('d/m/Y H:i') : '---'
        ]);
    }

    public function descargarPlantilla()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=plantilla_correos.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columnas = ['PLANTEL', 'MATRICULA', 'NOMBRE', 'FECHA INGRESO', 'CORREO PERSONAL', 'CORREO INSTITUCIONAL', 'CLAVE_CORREO', 'MATRICULA_ASESOR', 'NOMBRE_ASESOR'];

        $callback = function() use ($columnas) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columnas);
            fputcsv($file, ['CENTRO 01', '20240001', 'JUAN PEREZ', '2024-02-15', 'juan@gmail.com', 'jperez@institucion.edu', 'Pass1234', 'AS-99', 'PROF. GARCIA']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportarPendientes($plantel = null)
    {
        $user = Auth::user();

        // Iniciamos la consulta de pendientes
        $query = Correo::where('estatus', 'Pendiente');

        // Seguridad: Si no es Admin/Jefe, solo puede bajar lo de su centro
        if (!in_array($user->rol_id, [1, 5])) {
            $query->where('plantel', $user->centro);
        } elseif ($plantel) {
            // Si es Admin y seleccionó un plantel específico
            $query->where('plantel', $plantel);
        }

        $pendientes = $query->orderBy('plantel', 'asc')->get();

        $fileName = 'pendientes_' . ($plantel ?? 'general') . '_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columnas = ['PLANTEL', 'MATRICULA', 'NOMBRE', 'FECHA INGRESO', 'CORREO PERSONAL', 'CORREO INSTITUCIONAL', 'ASESOR', 'ESTATUS'];

        $callback = function() use ($pendientes, $columnas) {
            $file = fopen('php://output', 'w');
            // Añadir BOM para que Excel reconozca tildes y ñ
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); 
            
            fputcsv($file, $columnas);

            foreach ($pendientes as $p) {
                fputcsv($file, [
                    $p->plantel,
                    $p->matricula,
                    $p->nombre,
                    $p->fecha_ingreso,
                    $p->correo_personal,
                    $p->correo_institucional,
                    $p->nombre_asesor,
                    $p->estatus
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function marcarComoEnviado($id)
    {
        $correo = Correo::findOrFail($id);
        
        // Solo permitimos cambiar a 'Enviado' si está en 'Pendiente'
        // para no arruinar el estatus de 'Entregado'
        if ($correo->estatus === 'Pendiente') {
            $correo->estatus = 'Enviado';
            $correo->save();
            
            return response()->json([
                'success' => true, 
                'message' => 'Marcado como enviado correctamente'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'El estatus actual no permite este cambio'], 400);
    }
}