<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\Cargo;
use App\Models\Centro;
use App\Services\AuditoriaService;
use App\Exports\AsesoresExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class AsesorController extends Controller
{
    public function index()
    {
        $asesores = Asesor::with(['cargo', 'centro'])->get();
        return view('asesores.index', compact('asesores'));
    }

    public function create()
    {
        $cargos = Cargo::all();
        $centros = Centro::all();
        return view('asesores.create', compact('cargos', 'centros'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'matricula' => 'required|unique:asesores',
            'nombre' => 'required',
            'apellidos' => 'required',
            'correo' => 'required|email|unique:asesores',
            'cargo_id' => 'required',
            'centro_id' => 'required'
        ]);

        try {
            Asesor::create($request->all());
            AuditoriaService::registrar('CREAR_ASESOR', 'Asesores', "Registró al asesor: {$request->nombre} {$request->apellidos}");
            return redirect()->route('asesores.index')->with('success', '¡Asesor registrado correctamente!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Hubo un problema al registrar: ' . $e->getMessage());
        }
    }

    public function edit(Asesor $asesore)
    {
        // $asesore es el objeto inyectado por Laravel
        $asesor = $asesore;
        $cargos = Cargo::all();
        $centros = Centro::all();
        return view('asesores.edit', compact('asesor', 'cargos', 'centros'));
    }

    public function update(Request $request, Asesor $asesore)
    {
        $request->validate([
            'matricula' => 'required|unique:asesores,matricula,'.$asesore->id,
            'nombre' => 'required',
            'apellidos' => 'required',
            'correo' => 'required|email|unique:asesores,correo,'.$asesore->id,
            'cargo_id' => 'required',
            'centro_id' => 'required'
        ]);

        try {
            $asesore->update($request->all());
            AuditoriaService::registrar('EDITAR_ASESOR', 'Asesores', "Actualizó datos del asesor: {$asesore->nombre} {$asesore->apellidos}");
            return redirect()->route('asesores.index')->with('success', '¡Datos actualizados con éxito!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Asesor $asesore)
    {
        $nombre = "{$asesore->nombre} {$asesore->apellidos}";
        $asesore->delete();
        AuditoriaService::registrar('ELIMINAR_ASESOR', 'Asesores', "Eliminó al asesor: {$nombre}");
        return back()->with('success', 'Asesor eliminado correctamente.');
    }

    public function exportar()
    {
        AuditoriaService::registrar('EXPORTAR_EXCEL', 'Asesores', 'Exportó la lista de asesores a Excel');
        return Excel::download(new AsesoresExport(), 'asesores_sea_' . date('Y-m-d') . '.xlsx');
    }
}