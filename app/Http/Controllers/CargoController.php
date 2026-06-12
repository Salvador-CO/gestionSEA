<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    public function index()
    {
        $cargos = Cargo::all();
        return view('cargos.index', compact('cargos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:cargos|max:255',
        ]);

        Cargo::create($request->all());

        return redirect()->route('cargos.index')->with('success', 'Puesto/Cargo registrado exitosamente.');
    }

    public function destroy(Cargo $cargo)
    {
        // Verificar si hay asesores usando este cargo antes de borrar
        if ($cargo->asesores()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el cargo porque tiene asesores asociados.');
        }

        $cargo->delete();
        return redirect()->route('cargos.index')->with('success', 'Cargo eliminado correctamente.');
    }
}