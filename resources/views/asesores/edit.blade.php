@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Editar Información del Asesor</h5>
            </div>
            <div class="card-body p-4">
                <!-- Mostramos errores de validación si existen -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('asesores.update', $asesor->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase">Matrícula (Moodle)</label>
                            <input type="text" name="matricula" class="form-control bg-light" value="{{ old('matricula', $asesor->matricula) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase">Centro de Adscripción</label>
                            <select name="centro_id" class="form-select border-primary shadow-sm" required>
                                @foreach($centros as $c)
                                    <option value="{{ $c->id }}" {{ $asesor->centro_id == $c->id ? 'selected' : '' }}>
                                        {{ $c->clave }} - {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase">Cargo / Puesto</label>
                            <select name="cargo_id" class="form-select border-primary shadow-sm" required>
                                @foreach($cargos as $ca)
                                    <option value="{{ $ca->id }}" {{ $asesor->cargo_id == $ca->id ? 'selected' : '' }}>
                                        {{ $ca->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mt-4">
                            <label class="form-label fw-bold small text-uppercase">Nombre(s)</label>
                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $asesor->nombre) }}" required>
                        </div>
                        <div class="col-md-6 mt-4">
                            <label class="form-label fw-bold small text-uppercase">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos', $asesor->apellidos) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">Correo Institucional</label>
                            <input type="email" name="correo" class="form-control" value="{{ old('correo', $asesor->correo) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">Teléfono de Contacto</label>
                            <input type="text" name="contacto" class="form-control" value="{{ old('contacto', $asesor->contacto) }}">
                        </div>
                    </div>

                    <div class="mt-5 d-flex justify-content-end gap-2">
                        <a href="{{ route('asesores.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-5 shadow">
                            <i class="bi bi-save me-1"></i> Actualizar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection