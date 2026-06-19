@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#0F8A7A;">
                <i class="bi bi-speedometer2 me-2"></i>Panel de Control
            </h4>
            <small class="text-muted">Resumen general del sistema gestionSEA</small>
        </div>
        <form action="{{ route('dashboard.limpiarCache') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Fuerza actualización de datos de Moodle">
                <i class="bi bi-arrow-clockwise me-1"></i> Actualizar datos Moodle
                @if($cacheCursos)
                    <span class="badge bg-success ms-1" title="Caché activo — datos cargados rápido">●</span>
                @else
                    <span class="badge bg-secondary ms-1" title="Sin caché — primera carga tardará">○</span>
                @endif
            </button>
        </form>
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0F8A7A !important; border-left-style: solid !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(15,138,122,0.12);">
                        <i class="bi bi-people-fill fs-4" style="color:#0F8A7A;"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold lh-1" style="color:#0F8A7A;">{{ $totalUsuarios }}</div>
                        <div class="text-muted small">Usuarios del sistema</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #1FB3A1 !important; border-left-style: solid !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(31,179,161,0.12);">
                        <i class="bi bi-person-badge-fill fs-4" style="color:#1FB3A1;"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold lh-1" style="color:#1FB3A1;">{{ $totalAsesores }}</div>
                        <div class="text-muted small">Asesores registrados</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0B6E61 !important; border-left-style: solid !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(11,110,97,0.12);">
                        <i class="bi bi-collection-fill fs-4" style="color:#0B6E61;"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold lh-1" style="color:#0B6E61;">{{ $totalGrupos }}</div>
                        <div class="text-muted small">Grupos creados</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #5F666B !important; border-left-style: solid !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(95,102,107,0.12);">
                        <i class="bi bi-envelope-fill fs-4" style="color:#5F666B;"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold lh-1" style="color:#5F666B;">{{ $totalCorreosPendientes }}</div>
                        <div class="text-muted small">Correos pendientes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRÁFICAS --}}
    <div class="row g-3 mb-4">
        {{-- Grupos por Centro --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-building me-2" style="color:#0F8A7A;"></i>Grupos por Centro
                    </h6>
                    <small class="text-muted">Distribución de grupos en cada plantel</small>
                </div>
                <div class="card-body">
                    <div id="chartGruposCentro" style="min-height:270px;"></div>
                </div>
            </div>
        </div>

        {{-- Asesores por Cargo --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-briefcase me-2" style="color:#1FB3A1;"></i>Asesores por Cargo
                    </h6>
                    <small class="text-muted">Distribución por tipo de cargo</small>
                </div>
                <div class="card-body">
                    <div id="chartAsesoresCargo" style="min-height:270px;"></div>
                </div>
            </div>
        </div>

        {{-- Estado de Grupos --}}
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-diagram-3 me-2" style="color:#0B6E61;"></i>Estado de Grupos
                    </h6>
                    <small class="text-muted">Con y sin asesor asignado</small>
                </div>
                <div class="card-body">
                    <div id="chartEstadoGrupos" style="min-height:270px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- LOGS + ACCESOS RÁPIDOS --}}
    <div class="row g-3">
        {{-- Últimos logs --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-journal-text me-2" style="color:#0F8A7A;"></i>Actividad reciente
                        </h6>
                        <small class="text-muted">Últimas acciones registradas</small>
                    </div>
                    @if(auth()->user()->tienePermiso('ver_auditoria') || auth()->user()->tienePermiso('gestionar_usuarios'))
                    <a href="{{ route('auditoria.index') }}" class="btn btn-sm btn-outline-secondary">
                        Ver todo <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($ultimosLogs->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-25"></i>
                            Aún no hay acciones registradas.<br>
                            <small>Los logs aparecerán conforme se usen los módulos del sistema.</small>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead style="background:#f8f9fa;">
                                    <tr>
                                        <th class="ps-3 py-2 text-muted small fw-semibold">USUARIO</th>
                                        <th class="py-2 text-muted small fw-semibold">MÓDULO</th>
                                        <th class="py-2 text-muted small fw-semibold">ACCIÓN</th>
                                        <th class="py-2 text-muted small fw-semibold">DESCRIPCIÓN</th>
                                        <th class="pe-3 py-2 text-muted small fw-semibold">HORA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ultimosLogs as $log)
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <span class="badge rounded-pill" style="background:rgba(15,138,122,0.1);color:#0F8A7A;font-size:0.75rem;">
                                                {{ $log->username }}
                                            </span>
                                        </td>
                                        <td class="py-2">
                                            <small class="text-secondary">{{ $log->modulo }}</small>
                                        </td>
                                        <td class="py-2">
                                            <code class="small" style="font-size:0.72rem;">{{ $log->accion }}</code>
                                        </td>
                                        <td class="py-2">
                                            <small class="text-truncate d-block" style="max-width:250px;" title="{{ $log->descripcion }}">
                                                {{ $log->descripcion }}
                                            </small>
                                        </td>
                                        <td class="pe-3 py-2">
                                            <small class="text-muted">{{ $log->created_at->timezone('America/Mexico_City')->format('d/m H:i') }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Accesos rápidos --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-lightning-charge me-2" style="color:#0F8A7A;"></i>Accesos rápidos
                    </h6>
                    <small class="text-muted">Ir a módulos del sistema</small>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    @if(auth()->user()->tienePermiso('ver_registro'))
                    <a href="/registro" class="btn btn-light text-start d-flex align-items-center gap-2 border">
                        <i class="bi bi-person-plus-fill" style="color:#0F8A7A;"></i>
                        <span>Registrar alumno en Moodle</span>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </a>
                    @endif
                    @if(auth()->user()->tienePermiso('gestionar_grupos'))
                    <a href="/grupos" class="btn btn-light text-start d-flex align-items-center gap-2 border">
                        <i class="bi bi-collection-fill" style="color:#1FB3A1;"></i>
                        <span>Administrar grupos</span>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </a>
                    <a href="/tablero-moodle" class="btn btn-light text-start d-flex align-items-center gap-2 border">
                        <i class="bi bi-diagram-3-fill" style="color:#0B6E61;"></i>
                        <span>Tablero Moodle</span>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </a>
                    @endif
                    @if(auth()->user()->tienePermiso('gestionar_asesores'))
                    <a href="/asesores" class="btn btn-light text-start d-flex align-items-center gap-2 border">
                        <i class="bi bi-person-badge-fill" style="color:#0F8A7A;"></i>
                        <span>Administrar asesores</span>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </a>
                    @endif
                    @if(auth()->user()->tienePermiso('ver_reporte'))
                    <a href="/reporte" class="btn btn-light text-start d-flex align-items-center gap-2 border">
                        <i class="bi bi-graph-up" style="color:#5F666B;"></i>
                        <span>Ver reportes de calificaciones</span>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </a>
                    @endif
                    @if(auth()->user()->tienePermiso('gestionar_usuarios'))
                    <a href="{{ route('auditoria.index') }}" class="btn btn-light text-start d-flex align-items-center gap-2 border">
                        <i class="bi bi-journal-check" style="color:#0F8A7A;"></i>
                        <span>Logs de auditoría</span>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const SEA_COLORS = ['#0F8A7A','#1FB3A1','#0B6E61','#5F666B','#8A9297','#C5CBCD','#2DC9B5','#087060'];

    // ── Gráfica 1: Grupos por Centro (Barras horizontal) ──────────────────
    @php
        $centroLabels = $gruposPorCentro->pluck('nombre')->toJson();
        $centroData   = $gruposPorCentro->pluck('total')->toJson();
    @endphp

    if (document.getElementById('chartGruposCentro') && {!! $gruposPorCentro->count() !!} > 0) {
        new ApexCharts(document.getElementById('chartGruposCentro'), {
            chart: { type: 'bar', height: 270, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Grupos', data: {!! $centroData !!} }],
            xaxis: { categories: {!! $centroLabels !!}, labels: { style: { fontSize: '11px' } } },
            colors: SEA_COLORS,
            plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '55%',
                distributed: true } },
            legend: { show: false },
            dataLabels: { enabled: true, style: { fontSize: '11px' } },
            grid: { borderColor: '#f1f1f1' },
            yaxis: { title: { text: 'Grupos' } }
        }).render();
    } else if (document.getElementById('chartGruposCentro')) {
        document.getElementById('chartGruposCentro').innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-bar-chart fs-1 d-block opacity-25 mb-2"></i>Sin datos aún</div>';
    }

    // ── Gráfica 2: Asesores por Cargo (Donut) ─────────────────────────────
    @php
        $cargoLabels = $asesoresPorCargo->pluck('cargo')->toJson();
        $cargoData   = $asesoresPorCargo->pluck('total')->toJson();
    @endphp

    if (document.getElementById('chartAsesoresCargo') && {!! $asesoresPorCargo->count() !!} > 0) {
        new ApexCharts(document.getElementById('chartAsesoresCargo'), {
            chart: { type: 'donut', height: 270, toolbar: { show: false }, fontFamily: 'inherit' },
            series: {!! $cargoData !!},
            labels: {!! $cargoLabels !!},
            colors: SEA_COLORS,
            legend: { position: 'bottom', fontSize: '11px' },
            dataLabels: { enabled: true },
            plotOptions: { pie: { donut: { size: '65%',
                labels: { show: true, total: { show: true, label: 'Total', fontSize: '13px',
                    color: '#0F8A7A', fontWeight: 700 } } } } }
        }).render();
    } else if (document.getElementById('chartAsesoresCargo')) {
        document.getElementById('chartAsesoresCargo').innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-pie-chart fs-1 d-block opacity-25 mb-2"></i>Sin datos aún</div>';
    }

    // ── Gráfica 3: Estado de Grupos (Pie) ─────────────────────────────────
    if (document.getElementById('chartEstadoGrupos') && {{ $totalGrupos }} > 0) {
        new ApexCharts(document.getElementById('chartEstadoGrupos'), {
            chart: { type: 'pie', height: 270, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{{ $gruposConAsesor }}, {{ $gruposSinAsesor }}],
            labels: ['Con asesor', 'Sin asesor'],
            colors: ['#0F8A7A', '#C5CBCD'],
            legend: { position: 'bottom', fontSize: '11px' },
            dataLabels: { enabled: true, formatter: (v, o) => o.w.config.series[o.seriesIndex] + ' (' + Math.round(v) + '%)' },
            plotOptions: { pie: { expandOnClick: false } }
        }).render();
    } else if (document.getElementById('chartEstadoGrupos')) {
        document.getElementById('chartEstadoGrupos').innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-pie-chart fs-1 d-block opacity-25 mb-2"></i>Sin datos aún</div>';
    }
});
</script>
@endpush