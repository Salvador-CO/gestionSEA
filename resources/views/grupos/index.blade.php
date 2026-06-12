@extends('layouts.app')

@section('content')
<!-- Estilos CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

<!-- Script JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<div class="container-fluid">
            
    <!-- ALERTA DE ASESORES PENDIENTES -->
    @if($asesoresSinGrupo->count() > 0)
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <h5 class="alert-heading small fw-bold text-uppercase"><i class="bi bi-person-exclamation me-2"></i> Asesores sin carga académica</h5>
        <div class="d-flex flex-wrap gap-2 mt-2">
            @foreach($asesoresSinGrupo as $as)
                <span class="badge bg-white text-dark border p-2 shadow-sm">{{ $as->nombre }} {{ $as->apellidos }}</span>
            @endforeach
        </div>
    </div>
    @endif

    <div class="row">
        <!-- FORMULARIO DE CREACIÓN -->
        <div class="col-md-4">
            <div class="card shadow border-0 sticky-top" style="top: 20px;">
                <div class="card-header color_bacho1 text-white fw-bold">Generar Grupo (Moodle)</div>
                <div class="card-body">
                    <form action="{{ route('grupos.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="small fw-bold">Centro</label>
                            <select name="centro_id" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($centros as $c) <option value="{{ $c->id }}">{{ $c->clave }} - {{ $c->nombre }}</option> @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Asignatura</label>
                            <select name="asignatura_id" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($asignaturas as $mat) <option value="{{ $mat->id }}">(S{{ $mat->semestre }}) {{ $mat->nombre }}</option> @endforeach
                            </select>
                        </div>


                        <div class="mb-3">
                            <label class="small fw-bold">Asesor (Opcional)</label>
                            <select name="asesor_id" id="select-asesor" class="form-select" placeholder="Busca un asesor...">
                                <option value="">-- Dejar Pendiente --</option>
                                @foreach($asesores as $a) 
                                    <option value="{{ $a->id }}">{{ $a->nombre }} {{ $a->apellidos }}</option> 
                                @endforeach
                            </select>
                            <div class="form-text">Puedes buscar por nombre o apellido.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 color_bacho1 border-0">Crear Identificador</button>
                        <a href="{{ route('grupos.tableroMoodle') }}" class="btn btn-info text-white fw-bold shadow-sm mb-3">
                            <i class="bi bi-cloud-check-fill"></i> Ir al Tablero Real de Moodle
                        </a>
                    </form>
                </div>
            </div>
        </div>
          

        <!-- LISTADO DE GRUPOS -->
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white fw-bold">Control de Grupos y Nomenclatura
                </div>
                
                <div class="card-body">
                    <table class="table table-hover align-middle" id="tablaGrupos">
                        <thead>
                            <tr>
                                <th>Código Moodle</th>
                                <th>Asesor Asignado</th>
                                <th>Centro / Materia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grupos as $g)
                            <tr>
                                <td><strong class="text-primary">{{ $g->codigo_moodle }}</strong></td>
                                 
                                <td>
                                    @if($g->asesor)
                                        {{ $g->asesor->nombre }} {{ $g->asesor->apellidos }}
                                    @else
                                        <button class="btn btn-sm btn-outline-danger px-3 py-0" data-bs-toggle="modal" data-bs-target="#modalAsignar{{ $g->id }}">
                                            <i class="bi bi-person-plus"></i> PENDIENTE
                                        </button>

                                        <!-- Modal de Asignación Rápida -->
                                        <div class="modal fade" id="modalAsignar{{ $g->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-sm modal-dialog-centered">
                                                <form action="{{ route('grupos.update', $g->id) }}" method="POST" class="modal-content">
                                                    @csrf @method('PUT')
                                                    <div class="modal-body text-center">
                                                        <h6 class="mb-3">Asignar Profe a {{ $g->codigo_moodle }}</h6>
                                                        <select name="asesor_id" class="form-select mb-3" required>
                                                            @foreach($asesores as $a) <option value="{{ $a->id }}">{{ $a->nombre }}</option> @endforeach
                                                        </select>
                                                        <button class="btn btn-success btn-sm w-100">Confirmar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <small class="d-block"><b>Centro:</b> {{ $g->centro->clave }}</small>
                                    <small class="text-muted">{{ $g->asignatura->nombre }}</small>
                                </td>
                                <td>
                                    <form action="{{ route('grupos.destroy', $g->id) }}" method="POST" onsubmit="return confirm('¿Eliminar grupo?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
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
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#tablaGrupos').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }
        });
    });
</script>
<script>
    new TomSelect("#select-asesor", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        },
        allowEmptyOption: true
    });
</script>
@endpush
@endsection