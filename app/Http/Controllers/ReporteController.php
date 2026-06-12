<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MoodleService;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    protected $moodle;

    public function __construct(MoodleService $moodle) {
        $this->moodle = $moodle;
    }

    public function index(Request $request) {
        // Obtenemos el centro del usuario logueado
        $centroUsuario = Auth::user()->centro; 
        
        // Si es admin o no tiene centro, ve todo
        $filtroCentro = empty($centroUsuario) ? null : $centroUsuario;

        $data = $this->moodle->getAdvancedStats($request->has('refresh'), $filtroCentro);
        
        return view('reporte.index', [
            'stats' => $data['stats'],
            'fuente' => $data['fuente'],
            'centroFiltrado' => $filtroCentro
        ]);
    }

    public function ajaxCursos() {
        $centroUsuario = Auth::user()->centro;
        $filtroCentro = empty($centroUsuario) ? null : $centroUsuario;
        
        $data = $this->moodle->getAdvancedStats(false, $filtroCentro);
        return response()->json($data['courses'] ?? []);
    }

    public function ajaxModal(Request $request) {
        $centroUsuario = Auth::user()->centro;
        $filtroCentro = empty($centroUsuario) ? null : $centroUsuario;

        $data = $this->moodle->getAdvancedStats(false, $filtroCentro);
        $criterio = $request->criterio;
        $valor = $request->valor;
        $valorExtra = $request->valor_extra; 

        $usuarios = $data['stats']['raw_users'] ?? [];
        $resultado = [];

        foreach ($usuarios as $u) {
            $rol = 'Sin Rol';
            $centro = 'Sin Centro';

            if (!empty($u['customfields'])) {
                foreach ($u['customfields'] as $f) {
                    if ($f['shortname'] == 'tipoROL' && !empty($f['value'])) $rol = trim($f['value']);
                    if ($f['shortname'] == 'centro' && !empty($f['value'])) $centro = trim($f['value']);
                }
            }

            $match = false;
            switch ($criterio) {
                case 'rol': 
                    $match = ($rol === $valor); 
                    break;
                case 'centro': 
                    $match = ($centro === $valor); 
                    break;
                case 'centro_rol': 
                    // Comparamos que coincida el centro Y el rol enviado como valorExtra
                    $match = ($centro === $valor && $rol === $valorExtra);
                    break;
                case 'acceso':
                    if ($valor === 'Total') $match = true;
                    if ($valor === 'Han Accedido' && (int)$u['lastaccess'] > 0) $match = true;
                    if ($valor === 'Nunca Ingresaron' && (int)$u['lastaccess'] == 0) $match = true;
                    if ($valor === 'Activos 7 días' && (int)$u['lastaccess'] > strtotime('-7 days')) $match = true;
                    break;
            }

            if ($match) {
                $resultado[] = [
                    'nombre' => $u['fullname'],
                    'email' => $u['email'],
                    'rol' => $rol,
                    'centro' => $centro
                ];
            }
        }
        return response()->json($resultado);
    }
}