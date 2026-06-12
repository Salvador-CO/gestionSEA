@extends('layouts.app')

@section('content')
<div class="card shadow border-0">
    <div class="card-header color_bacho2 text-white d-flex justify-content-between">
        <h5 class="mb-0">Listado de Asignaturas</h5>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsignatura">Nueva Asignatura</button>
    </div>
    <div class="card-body">
        <table class="table" id="tabla">
            <thead>
                <tr>
                    <th>Clave</th>
                    <th>Nombre</th>
                    <th>Semestre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asignaturas as $a)
                <tr>
                    <td>{{ $a->clave }}</td>
                    <td>{{ $a->nombre }}</td>
                    <td><span class="badge bg-primary">Semestre {{ $a->semestre }}</span></td>
                    <td>
                        <form action="{{ route('asignaturas.destroy', $a->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalAsignatura" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('asignaturas.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5>Agregar Asignatura</h5></div>
            <div class="modal-body">
                <div class="mb-3"><label>Clave</label><input type="text" name="clave" class="form-control" required></div>
                <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                <div class="mb-3">
                    <label>Semestre</label>
                    <select name="semestre" class="form-select">
                        @for($i=1; $i<=6; $i++) <option value="{{$i}}">{{$i}}° Semestre</option> @endfor
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-success">Guardar</button></div>
        </form>
    </div>
</div>
@endsection