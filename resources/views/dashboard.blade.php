@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">

    @if($esPrivilegiado)
    {{-- ╔══════════════════════════════════════════════════════════════╗ --}}
    {{-- VISTA ADMIN / JEFE — Panel completo con KPIs y gráficas      --}}
    {{-- ╚══════════════════════════════════════════════════════════════╝ --}}

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
                <span class="badge bg-success ms-1" title="Caché activo">●</span>
                @else
                <span class="badge bg-secondary ms-1" title="Sin caché">○</span>
                @endif
            </button>
        </form>
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0F8A7A !important;">
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
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #1FB3A1 !important;">
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
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0B6E61 !important;">
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
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #5F666B !important;">
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
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-building me-2" style="color:#0F8A7A;"></i>Grupos por Centro</h6>
                    <small class="text-muted">Distribución de grupos en cada plantel</small>
                </div>
                <div class="card-body">
                    <div id="chartGruposCentro" 
                         data-labels='@json($gruposPorCentro->pluck("nombre"))' 
                         data-values='@json($gruposPorCentro->pluck("total"))'
                         data-count="{{ $gruposPorCentro->count() }}"
                         style="min-height:270px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-briefcase me-2" style="color:#1FB3A1;"></i>Asesores por Cargo</h6>
                    <small class="text-muted">Distribución por tipo de cargo</small>
                </div>
                <div class="card-body">
                    <div id="chartAsesoresCargo" 
                         data-labels='@json($asesoresPorCargo->pluck("cargo"))' 
                         data-values='@json($asesoresPorCargo->pluck("total"))'
                         data-count="{{ $asesoresPorCargo->count() }}"
                         style="min-height:270px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2" style="color:#0B6E61;"></i>Estado de Grupos</h6>
                    <small class="text-muted">Con y sin asesor asignado</small>
                </div>
                <div class="card-body">
                    <div id="chartEstadoGrupos" 
                         data-total="{{ $totalGrupos }}" 
                         data-con-asesor="{{ $gruposConAsesor }}" 
                         data-sin-asesor="{{ $gruposSinAsesor }}"
                         style="min-height:270px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- LOGS + ACCESOS RÁPIDOS --}}
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-2" style="color:#0F8A7A;"></i>Actividad reciente</h6>
                        <small class="text-muted">Últimas acciones registradas</small>
                    </div>
                    <a href="{{ route('auditoria.index') }}" class="btn btn-sm btn-outline-secondary">
                        Ver todo <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($ultimosLogs->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-25"></i>
                        Aún no hay acciones registradas.
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
                                    <td class="py-2"><small class="text-secondary">{{ $log->modulo }}</small></td>
                                    <td class="py-2"><code class="small" style="font-size:0.72rem;">{{ $log->accion }}</code></td>
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
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-lightning-charge me-2" style="color:#0F8A7A;"></i>Accesos rápidos</h6>
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

    @else
    {{-- ╔══════════════════════════════════════════════════════════════╗ --}}
    {{-- VISTA OPERATIVA — Bienvenida personal sin datos sensibles     --}}
    {{-- ╚══════════════════════════════════════════════════════════════╝ --}}

    <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 70vh;">
        <div class="card border-0 shadow-sm text-center" style="max-width: 520px; width: 100%;">
            <div class="card-body py-5 px-4">

                {{-- Logo / Escudo --}}
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle shadow"
                        style="width:100px;height:100px;background:linear-gradient(135deg,#0F8A7A,#1FB3A1);">
                        <i class="bi bi-mortarboard-fill text-white" style="font-size:2.8rem;"></i>
                    </div>
                </div>

                {{-- Nombre del sistema --}}
                <p class="fw-semibold mb-1" style="color:#0F8A7A;letter-spacing:2px;font-size:0.8rem;">
                    SISTEMA DE GESTIÓN
                </p>
                <h3 class="fw-bold mb-1" style="color:#212529;">Bachillerato Digital</h3>
                <p class="text-muted small mb-4">Plataforma de Gestión de Asesores y Alumnos (SEA)</p>

                <hr class="my-3" style="border-color:#0F8A7A;opacity:0.2;">

                {{-- Mensaje de bienvenida personalizado --}}
                <div class="mb-3">
                    <p class="text-muted small mb-1">Bienvenido(a) al sistema,</p>
                    <h5 class="fw-bold mb-0" style="color:#0F8A7A;">
                        {{ mb_strtoupper(auth()->user()->nombre . ' ' . auth()->user()->apellido, 'UTF-8') }}
                    </h5>
                </div>

                <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
                    <span class="badge rounded-pill px-3 py-2" style="background:rgba(15,138,122,0.1);color:#0F8A7A;font-size:0.85rem;">
                        <i class="bi bi-shield-check me-1"></i>{{ auth()->user()->rol->nombre ?? 'Usuario' }}
                    </span>
                    @if(auth()->user()->centro)
                    <span class="badge rounded-pill px-3 py-2" style="background:rgba(11,110,97,0.1);color:#0B6E61;font-size:0.85rem;">
                        <i class="bi bi-building me-1"></i>{{ strtoupper(auth()->user()->centro) }}
                    </span>
                    @endif
                </div>

                <p class="text-muted small mb-4">
                    Usa el menú de la izquierda para acceder a los módulos<br>que tienes asignados.
                </p>

                {{-- Accesos rápidos según permisos --}}
                @php $tieneAlgunModulo = false; @endphp
                <div class="d-flex flex-column gap-2">
                    @if(auth()->user()->tienePermiso('ver_registro'))
                    @php $tieneAlgunModulo = true; @endphp
                    <a href="/registro" class="btn btn-light text-start d-flex align-items-center gap-2 border">
                        <i class="bi bi-person-plus-fill" style="color:#0F8A7A;"></i>
                        <span>Registrar alumno</span>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </a>
                    @endif
                    @if(auth()->user()->tienePermiso('ver_reporte'))
                    @php $tieneAlgunModulo = true; @endphp
                    <a href="/reporte" class="btn btn-light text-start d-flex align-items-center gap-2 border">
                        <i class="bi bi-graph-up" style="color:#5F666B;"></i>
                        <span>Ver reportes</span>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </a>
                    @endif
                    @if(auth()->user()->tienePermiso('ver_correo'))
                    @php $tieneAlgunModulo = true; @endphp
                    <a href="/correo" class="btn btn-light text-start d-flex align-items-center gap-2 border">
                        <i class="bi bi-envelope-fill" style="color:#1FB3A1;"></i>
                        <span>Gestión de correos</span>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </a>
                    @endif
                </div>
            </div>

            <div class="card-footer bg-white border-0 pb-4">
                <small class="text-muted">
                    <i class="bi bi-calendar3 me-1"></i>{{ now()->timezone('America/Mexico_City')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </small>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection

@if($esPrivilegiado)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const SEA_COLORS = ['#0F8A7A', '#1FB3A1', '#0B6E61', '#5F666B', '#8A9297', '#C5CBCD', '#2DC9B5', '#087060'];

        // ── Gráfica 1: Grupos por Centro ──────────────────────────────────────
        const chartGruposDom = document.getElementById('chartGruposCentro');
        if (chartGruposDom) {
            const count = parseInt(chartGruposDom.getAttribute('data-count') || '0');
            if (count > 0) {
                const labels = JSON.parse(chartGruposDom.getAttribute('data-labels') || '[]');
                const values = JSON.parse(chartGruposDom.getAttribute('data-values') || '[]');

                new ApexCharts(chartGruposDom, {
                    chart: { type: 'bar', height: 270, toolbar: { show: false }, fontFamily: 'inherit' },
                    series: [{ name: 'Grupos', data: values }],
                    xaxis: { categories: labels, labels: { style: { fontSize: '11px' } } },
                    colors: SEA_COLORS,
                    plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '55%', distributed: true } },
                    legend: { show: false },
                    dataLabels: { enabled: true, style: { fontSize: '11px' } },
                    grid: { borderColor: '#f1f1f1' },
                    yaxis: { title: { text: 'Grupos' } }
                }).render();
            } else {
                chartGruposDom.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-bar-chart fs-1 d-block opacity-25 mb-2"></i>Sin datos aún</div>';
            }
        }

        // ── Gráfica 2: Asesores por Cargo ─────────────────────────────────────
        const chartAsesoresDom = document.getElementById('chartAsesoresCargo');
        if (chartAsesoresDom) {
            const count = parseInt(chartAsesoresDom.getAttribute('data-count') || '0');
            if (count > 0) {
                const labels = JSON.parse(chartAsesoresDom.getAttribute('data-labels') || '[]');
                const values = JSON.parse(chartAsesoresDom.getAttribute('data-values') || '[]');

                new ApexCharts(chartAsesoresDom, {
                    chart: { type: 'donut', height: 270, toolbar: { show: false }, fontFamily: 'inherit' },
                    series: values,
                    labels: labels,
                    colors: SEA_COLORS,
                    legend: { position: 'bottom', fontSize: '11px' },
                    dataLabels: { enabled: true },
                    plotOptions: { pie: { donut: { size: '65%',
                        labels: { show: true, total: { show: true, label: 'Total', fontSize: '13px',
                            color: '#0F8A7A', fontWeight: 700 } } } } }
                }).render();
            } else {
                chartAsesoresDom.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-pie-chart fs-1 d-block opacity-25 mb-2"></i>Sin datos aún</div>';
            }
        }

        // ── Gráfica 3: Estado de Grupos ───────────────────────────────────────
        const chartEstadoDom = document.getElementById('chartEstadoGrupos');
        if (chartEstadoDom) {
            const total = parseInt(chartEstadoDom.getAttribute('data-total') || '0');
            if (total > 0) {
                const conAsesor = parseInt(chartEstadoDom.getAttribute('data-con-asesor') || '0');
                const sinAsesor = parseInt(chartEstadoDom.getAttribute('data-sin-asesor') || '0');

                new ApexCharts(chartEstadoDom, {
                    chart: { type: 'pie', height: 270, toolbar: { show: false }, fontFamily: 'inherit' },
                    series: [conAsesor, sinAsesor],
                    labels: ['Con asesor', 'Sin asesor'],
                    colors: ['#0F8A7A', '#C5CBCD'],
                    legend: { position: 'bottom', fontSize: '11px' },
                    dataLabels: { enabled: true, formatter: (v, o) => o.w.config.series[o.seriesIndex] + ' (' + Math.round(v) + '%)' },
                    plotOptions: { pie: { expandOnClick: false } }
                }).render();
            } else {
                chartEstadoDom.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-pie-chart fs-1 d-block opacity-25 mb-2"></i>Sin datos aún</div>';
            }
        }
    });
</script>
@endpush
@endif