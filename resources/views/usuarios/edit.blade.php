@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow col-md-8 mx-auto">
        <div class="card-header bg-warning text-dark fw-bold">
            <i class="bi bi-pencil-square me-2"></i> Editar Usuario: {{ $usuario->username }}
        </div>
        <div class="card-body">
            <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                @csrf
                @method('PUT') 

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="{{ $usuario->nombre }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" value="{{ $usuario->apellido }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $usuario->email }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Centro</label>
                        <input type="text" name="centro" class="form-control" value="{{ $usuario->centro }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol_id" class="form-select">
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" {{ $usuario->rol_id == $rol->id ? 'selected' : '' }}>
                                    {{ $rol->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Estado</label>
                        <select name="activo" class="form-select">
                            <option value="1" {{ $usuario->activo == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ $usuario->activo == 0 ? 'selected' : '' }}>Suspendido</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                        <input type="password" name="password" class="form-control" placeholder="********">
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-between">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar Datos</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection