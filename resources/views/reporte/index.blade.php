@extends('layouts.app')

@section('content')
<style>
    .card-hover { transition: transform 0.2s; cursor: pointer; border: none !important; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .bg-gradient-primary { background: linear-gradient(45deg, #4e73df, #224abe); }
    .bg-gradient-success { background: linear-gradient(45deg, #1cc88a, #13855c); }
    .bg-gradient-danger { background: linear-gradient(45deg, #e74a3b, #be2617); }
    .bg-gradient-info { background: linear-gradient(45deg, #36b9cc, #258391); }
    .table-clickable tbody tr { cursor: pointer; }
    /* Contenedor */
    .nav_bacho {
      background-color: #FFFFFF;
      border-radius: 10px;
      padding: 6px;
    }

    /* Botones normales */
    .nav_bacho .nav-link {
      color: #000;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    /* Hover */
    .nav_bacho .nav-link:hover {
      color: #0F8A7A;

    }

    /* Activo */
    .nav_bacho .nav-link.active {
      background: linear-gradient(45deg, #0F8A7A, #1FB3A1);
      color: #FFFFFF;
      box-shadow: 0 4px 10px rgba(15, 138, 122, 0.3);
    }
    
</style>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h2><i class="bi bi-pie-chart-fill text_bacho_primary"></i> Reporte Avanzado de Usuarios</h2>
            <span class="badge bg-light text-dark border"><i class="bi bi-clock"></i> Última actualización: {{ $fuente }}</span>
            
            @if($centroFiltrado)
                <span class="badge bg-primary"><i class="bi bi-geo-alt"></i> Filtrado por Centro: {{ $centroFiltrado }}</span>
            @else
                <span class="badge bg-dark"><i class="bi bi-globe"></i> Vista Global (Todos los Centros)</span>
            @endif
        </div>
        <div class="col-md-5 text-md-end ">
            <a href="?refresh=1" class="btn color_bacho_gray_light shadow-sm"><i class="bi bi-arrow-clockwise"></i> Actualizar Datos</a>
        </div>
    </div>

    <ul class="nav nav-pills mb-4 shadow-sm nav_bacho" id="reporteTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-resumen">Resumen</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-cruzado">Matriz Centro/Rol</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-cursos">Cursos</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-graficas">Análisis Visual</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-resumen">
            <div class="row g-3 mb-4 text-white">
                <div class="col-md-3">
                    <div class="card card-hover bg-gradient-primary btn-modal" data-tipo="acceso" data-valor="Total">
                        <div class="card-body text-center"><h6 class="opacity-75">Total</h6><h2>{{ $stats['total'] }}</h2></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-hover bg-gradient-success btn-modal" data-tipo="acceso" data-valor="Han Accedido">
                        <div class="card-body text-center"><h6 class="opacity-75">Accedieron</h6><h2>{{ $stats['acceso']['alguna_vez'] }}</h2></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-hover bg-gradient-info btn-modal" data-tipo="acceso" data-valor="Activos 7 días">
                        <div class="card-body text-center"><h6 class="opacity-75">Activos (7d)</h6><h2>{{ $stats['acceso']['reciente'] }}</h2></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-hover bg-gradient-danger btn-modal" data-tipo="acceso" data-valor="Nunca Ingresaron">
                        <div class="card-body text-center"><h6 class="opacity-75">Nunca</h6><h2>{{ $stats['acceso']['nunca'] }}</h2></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white fw-bold">Roles</div>
                        <div class="card-body">
                            <table id="tablaRoles" class="table table-hover table-sm table-clickable">
                                <thead><tr><th>Rol</th><th class="text-end">Cant.</th></tr></thead>
                                <tbody>
                                    @foreach($stats['roles'] as $rol => $c)
                                    <tr class="btn-modal" data-tipo="rol" data-valor="{{ $rol }}">
                                        <td>{{ $rol }}</td><td class="text-end"><strong>{{ $c }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white fw-bold">Centros</div>
                        <div class="card-body">
                            <table id="tablaCentros" class="table table-hover table-sm table-clickable">
                                <thead><tr><th>Centro</th><th class="text-end">Cant.</th></tr></thead>
                                <tbody>
                                    @foreach($stats['centros'] as $centro => $c)
                                    <tr class="btn-modal" data-tipo="centro" data-valor="{{ $centro }}">
                                        <td>{{ $centro }}</td><td class="text-end"><strong>{{ $c }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-cruzado">
             @include('reporte.partials.matriz_cruzada')
        </div>

        <div class="tab-pane fade" id="tab-cursos">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaCursosAjax" class="table table-bordered w-100">
                            <thead class="table-dark">
                                <tr><th>ID</th><th>Curso</th><th>Corto</th><th>Inscritos</th><th>Activos %</th></tr>
                            </thead>
                            <tbody id="bodyCursos">
                                <tr><td colspan="5" class="text-center p-5">
                                    <div class="spinner-border text-primary"></div><br>Cargando cursos...
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-graficas">
            @include('reporte.partials.graficas_avanzadas')
        </div>
    </div>
</div>

@include('reporte.partials.modal_usuarios')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const dtLang = { url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json" };

    $('#tablaRoles, #tablaCentros').DataTable({
        pageLength: 8, searching: false, lengthChange: false, language: dtLang
    });

    $('button[data-bs-target="#tab-cursos"]').one('shown.bs.tab', function () {
        $.get("{{ route('reporte.ajax.cursos') }}", function(data) {
            let html = '';
            data.forEach(c => {
                let nombreCurso = c.fullname || 'Sin nombre';
                html += `<tr>
                    <td>${c.id}</td>
                    <td><b>${nombreCurso}</b></td>
                    <td>${c.shortname}</td>
                    <td><span class="badge bg-secondary">N/A</span></td>
                    <td>-</td>
                </tr>`;
            });
            $('#bodyCursos').html(html);
            $('#tablaCursosAjax').DataTable({ dom: 'Bfrtip', language: dtLang });
        });
    });

    // LÓGICA DE GRÁFICAS RESTAURADA
    $('button[data-bs-target="#tab-graficas"]').one('shown.bs.tab', function () {
        const palette = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'];

        // Gráfica de Salud de Acceso
        new Chart(document.getElementById('chartAccesoViejo'), {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Nunca'],
                datasets: [{ 
                    data: [{{ $stats['acceso']['alguna_vez'] }}, {{ $stats['acceso']['nunca'] }}], 
                    backgroundColor: ['#1cc88a', '#e74a3b'] 
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // Gráfica Comparativa
        new Chart(document.getElementById('chartComparativoViejo'), {
            type: 'bar',
            data: {
                labels: ['Métricas'],
                datasets: [
                    { label: 'Total', data: [{{ $stats['total'] }}], backgroundColor: '#4e73df' },
                    { label: 'Accedieron', data: [{{ $stats['acceso']['alguna_vez'] }}], backgroundColor: '#1cc88a' },
                    { label: 'Nunca', data: [{{ $stats['acceso']['nunca'] }}], backgroundColor: '#e74a3b' }
                ]
            },
            options: { maintainAspectRatio: false }
        });

        // Gráfica de Roles
        new Chart(document.getElementById('chartRolesNuevo'), {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($stats['roles'])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($stats['roles'])) !!},
                    backgroundColor: palette
                }]
            },
            options: { maintainAspectRatio: false }
        });

        // Gráfica de Centros
        new Chart(document.getElementById('chartCentrosNuevo'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_slice(array_keys($stats['centros']), 0, 10)) !!},
                datasets: [{
                    label: 'Usuarios',
                    data: {!! json_encode(array_slice(array_values($stats['centros']), 0, 10)) !!},
                    backgroundColor: '#f6c23e'
                }]
            },
            options: { indexAxis: 'y', maintainAspectRatio: false }
        });

        // Matriz Apilada
        const matrizRaw = {!! json_encode($stats['matriz_centro_rol']) !!};
        const centrosLabels = Object.keys(matrizRaw);
        const rolesLabels = {!! json_encode(array_keys($stats['roles'])) !!};

        const datasetsApilados = rolesLabels.map((rol, i) => ({
            label: rol,
            data: centrosLabels.map(c => matrizRaw[c][rol] || 0),
            backgroundColor: palette[i % palette.length]
        }));

        new Chart(document.getElementById('chartMatrizApilada'), {
            type: 'bar',
            data: { labels: centrosLabels, datasets: datasetsApilados },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { stacked: true }, y: { stacked: true } },
                plugins: { tooltip: { mode: 'index' } }
            }
        });
    });

    // LÓGICA DEL MODAL
    let tablaModalInstancia = null;
    $(document).on('click', '.btn-modal', function(e) {
        e.preventDefault();
        const tipo = $(this).data('tipo');
        const valor = $(this).data('valor');
        const valorExtra = $(this).data('valor-extra'); 

        $('#modalTitulo').text('Usuarios: ' + valor + (valorExtra ? ' - ' + valorExtra : ''));
        if (tablaModalInstancia) { tablaModalInstancia.destroy(); $('#tablaUsuariosBody').empty(); }
        $('#tablaUsuariosBody').html('<tr><td colspan="4" class="text-center p-3">Cargando datos...</td></tr>');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUsuarios')).show();

        $.get("{{ route('reporte.ajax.modal') }}", { criterio: tipo, valor: valor, valor_extra: valorExtra }, function(data) {
            let tbody = $('#tablaUsuariosBody');
            tbody.empty();
            data.forEach(u => {
                tbody.append(`<tr><td>${u.nombre}</td><td>${u.email}</td><td>${u.rol}</td><td>${u.centro}</td></tr>`);
            });
            tablaModalInstancia = $('#tablaUsuariosDataTable').DataTable({ pageLength: 20, language: dtLang });
        });
    });
});
</script>
@endpush