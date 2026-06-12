<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 text-center bg-light">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Salud de Acceso</h6>
                <div style="height: 200px;">
                    <canvas id="chartAccesoViejo"></canvas>
                </div>
                <div class="mt-2">
                    <span class="badge bg-success">Activos: {{ $stats['acceso']['alguna_vez'] }}</span>
                    <span class="badge bg-danger">Nunca: {{ $stats['acceso']['nunca'] }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small text-center">Comparativa General</h6>
                <div style="height: 230px;">
                    <canvas id="chartComparativoViejo"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 text-center">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Tasa de Inactividad</h6>
                @php
                    $tasa = $stats['total'] > 0 ? round(($stats['acceso']['nunca'] / $stats['total']) * 100, 1) : 0;
                @endphp
                <h1 class="display-4 fw-bold text-danger mt-3">{{ $tasa }}%</h1>
                <p class="text-muted small">Porcentaje de usuarios que nunca han ingresado a la plataforma.</p>
                <div style="height: 100px;">
                    <canvas id="chartRetencionViejo"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Distribución por Roles (Dona)</h6>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="chartRolesNuevo"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Top 10 Centros Poblados</h6>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="chartCentrosNuevo"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Análisis Detallado: Roles por Centro (Apilado)</h6>
            </div>
            <div class="card-body">
                <div style="height: 450px;">
                    <canvas id="chartMatrizApilada"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>