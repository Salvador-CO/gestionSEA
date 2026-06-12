@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow border-0">
            <div class="card-header color_bacho3 text-white">Nuevo Centro</div>
            <div class="card-body">
                <form action="{{ route('centros.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Clave (Ej: C01)</label>
                        <input type="text" name="clave" class="form-control" placeholder="C01" required>
                    </div>
                    <div class="mb-3">
                        <label>Nombre del Centro</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control"></textarea>
                    </div>
                    <button class="btn btn-success w-100">Guardar Centro</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow border-0">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($centros as $c)
                        <tr>
                            <td><strong>{{ $c->clave }}</strong></td>
                            <td>{{ $c->nombre }}</td>
                            <td>
                                <form action="{{ route('centros.destroy', $c->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection