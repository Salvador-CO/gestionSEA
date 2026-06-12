<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use Illuminate\Http\Request;

class CentroController extends Controller
{
    public function index() {
        $centros = Centro::all();
        return view('centros.index', compact('centros'));
    }

    public function store(Request $request) {
        $request->validate(['clave' => 'required|unique:centros', 'nombre' => 'required']);
        Centro::create($request->all());
        return back()->with('success', 'Centro creado con éxito');
    }

    public function update(Request $request, Centro $centro) {
        $centro->update($request->all());
        return back()->with('success', 'Centro actualizado');
    }

    public function destroy(Centro $centro) {
        $centro->delete();
        return back()->with('success', 'Centro eliminado');
    }
}