<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    public function index() {
        $asignaturas = Asignatura::all();
        return view('asignaturas.index', compact('asignaturas'));
    }

    public function store(Request $request) {
        $request->validate(['clave' => 'required|unique:asignaturas', 'nombre' => 'required', 'semestre' => 'required']);
        Asignatura::create($request->all());
        return back()->with('success', 'Asignatura creada');
    }

    public function destroy(Asignatura $asignatura) {
        $asignatura->delete();
        return back()->with('success', 'Asignatura eliminada');
    }
}