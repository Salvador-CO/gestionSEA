<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\Cargo;
use App\Models\Centro;
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
            // Redirecciona al index con mensaje de éxito
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
            return redirect()->route('asesores.index')->with('success', '¡Datos actualizados con éxito!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Asesor $asesore)
    {
        $asesore->delete();
        return back()->with('success', 'Asesor eliminado correctamente.');
    }
}