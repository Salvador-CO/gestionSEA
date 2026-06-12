@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card shadow border-0">
            <div class="card-header color_bacho5 text-white fw-bold">
                <i class="bi bi-briefcase me-2"></i> Registrar Nuevo Cargo/Puesto
            </div>
            <div class="card-body">
                <form action="{{ route('cargos.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre del Cargo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Asesor de Contenido" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 color_bacho1 border-0">
                        <i class="bi bi-save me-1"></i> Guardar Cargo
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow border-0">
            <div class="card-header bg-white fw-bold text_bacho_primary">
                Lista de Cargos Existentes
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Descripción del Puesto</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cargos as $cargo)
                        <tr>
                            <td>{{ $cargo->id }}</td>
                            <td><span class="fw-bold">{{ $cargo->nombre }}</span></td>
                            <td class="text-end">
                                <form action="{{ route('cargos.destroy', $cargo->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
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