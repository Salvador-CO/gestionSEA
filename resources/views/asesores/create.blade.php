@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i> Registro de Nuevo Asesor</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('asesores.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase">Matrícula (Moodle)</label>
                            <input type="text" name="matricula" class="form-control bg-light" placeholder="Ej: 2023001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase">Centro de Adscripción</label>
                            <select name="centro_id" class="form-select border-primary shadow-sm" required>
                                <option value="">-- Seleccionar Centro --</option>
                                @foreach($centros as $c)
                                    <option value="{{ $c->id }}">{{ $c->clave }} - {{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase">Cargo / Puesto</label>
                            <select name="cargo_id" class="form-select border-primary shadow-sm" required>
                                <option value="">-- Seleccione Función --</option>
                                @foreach($cargos as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mt-4">
                            <label class="form-label fw-bold small text-uppercase">Nombre(s)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4">
                            <label class="form-label fw-bold small text-uppercase">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">Correo Institucional</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="correo" class="form-control" placeholder="nombre@bachillerato.edu.mx" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">Teléfono de Contacto</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="contacto" class="form-control" placeholder="10 dígitos">
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 d-flex justify-content-end gap-2">
                        <a href="{{ route('asesores.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success px-5 shadow">
                            <i class="bi bi-check-circle me-1"></i> Guardar Asesor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection