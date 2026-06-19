@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#0F8A7A;">
                <i class="bi bi-journal-check me-2"></i>Logs de Auditoría
            </h4>
            <small class="text-muted">Registro de todas las acciones realizadas en el sistema</small>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('auditoria.index') }}" class="row g-2 align-items-end">
                <div class="col-sm-3">
                    <label class="form-label small fw-semibold text-muted">MÓDULO</label>
                    <select name="modulo" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($modulos as $m)
                            <option value="{{ $m }}" {{ request('modulo') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label small fw-semibold text-muted">USUARIO</label>
                    <input type="text" name="usuario" class="form-control form-control-sm"
                           placeholder="nombre de usuario" value="{{ request('usuario') }}">
                </div>
                <div class="col-sm-3">
                    <label class="form-label small fw-semibold text-muted">FECHA</label>
                    <input type="date" name="fecha" class="form-control form-control-sm"
                           value="{{ request('fecha') }}">
                </div>
                <div class="col-sm-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm w-100" style="background:#0F8A7A;color:white;">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('auditoria.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-x me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-list-ul me-2" style="color:#0F8A7A;"></i>
                {{ $logs->total() }} registros encontrados
            </h6>
            <small class="text-muted">Mostrando {{ $logs->firstItem() }}–{{ $logs->lastItem() }}</small>
        </div>
        <div class="card-body p-0">
            @if($logs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-1 d-block opacity-25 mb-3"></i>
                    <p>No hay registros con los filtros actuales.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th class="ps-3 py-2 text-muted small fw-semibold">FECHA / HORA</th>
                                <th class="py-2 text-muted small fw-semibold">USUARIO</th>
                                <th class="py-2 text-muted small fw-semibold">MÓDULO</th>
                                <th class="py-2 text-muted small fw-semibold">ACCIÓN</th>
                                <th class="py-2 text-muted small fw-semibold">DESCRIPCIÓN</th>
                                <th class="pe-3 py-2 text-muted small fw-semibold">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td class="ps-3 py-2">
                                    <div class="fw-semibold small">{{ $log->created_at->timezone('America/Mexico_City')->format('d/m/Y') }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $log->created_at->timezone('America/Mexico_City')->format('H:i:s') }}</div>
                                </td>
                                <td class="py-2">
                                    <span class="badge rounded-pill" style="background:rgba(15,138,122,0.1);color:#0F8A7A;">
                                        {{ $log->username ?? '—' }}
                                    </span>
                                </td>
                                <td class="py-2">
                                    <small class="text-secondary">{{ $log->modulo }}</small>
                                </td>
                                <td class="py-2">
                                    @php
                                        $colorAccion = match(true) {
                                            str_contains($log->accion, 'ELIMINAR') => 'danger',
                                            str_contains($log->accion, 'CREAR')    => 'success',
                                            str_contains($log->accion, 'EDITAR') || str_contains($log->accion, 'ACTUALIZAR') => 'warning',
                                            str_contains($log->accion, 'LOGIN') || str_contains($log->accion, 'LOGOUT') => 'info',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $colorAccion }} bg-opacity-10 text-{{ $colorAccion }}" style="font-size:0.72rem;">
                                        {{ $log->accion }}
                                    </span>
                                </td>
                                <td class="py-2">
                                    <small>{{ $log->descripcion }}</small>
                                </td>
                                <td class="pe-3 py-2">
                                    <small class="text-muted font-monospace">{{ $log->ip_address }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-center pt-2 pb-3">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
