@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3><i class="fas fa-graduated-cap"></i> Reporte de Calificaciones</h3>
            @if(isset($centroFiltrado) && $centroFiltrado)
                <span class="badge bg-primary">Filtrado por: {{ $centroFiltrado }}</span>
            @endif
        </div>
        <div>
            <a href="{{ route('calificaciones.create') }}" class="btn btn-outline-primary fw-bold shadow-sm">
                <i class="bi bi-search me-1"></i> Revisión Detallada
            </a>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0">Filtros de Búsqueda Avanzada</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('calificaciones.index') }}" method="GET" id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">1. Categoría</label>
                    <select name="category_id" class="form-select" onchange="document.getElementById('course_select').value=''; this.form.submit();">
                        <option value="">-- Seleccione Categoría --</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat['id'] }}" {{ $selectedCategory == $cat['id'] ? 'selected' : '' }}>
                                {{ $cat['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">2. Curso</label>
                    <select name="course_id" id="course_select" class="form-select" {{ empty($cursos) ? 'disabled' : '' }}>
                        <option value="">-- Seleccione un curso --</option>
                        @if(!empty($cursos))
                            <option value="all" {{ $selectedCourse == 'all' ? 'selected' : '' }}>[ TRAER TODOS LOS CURSOS ]</option>
                            @foreach($cursos as $curso)
                                <option value="{{ $curso['id'] }}" {{ $selectedCourse == $curso['id'] ? 'selected' : '' }}>
                                    {{ $curso['shortname'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">3. Rol</label>
                    <select name="role_name" class="form-select">
                        <option value="">-- Todos los Roles --</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol }}" {{ $selectedRole == $rol ? 'selected' : '' }}>
                                {{ $rol }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                    <a href="{{ route('calificaciones.index') }}" class="btn btn-secondary ms-2">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    @if($selectedCategory && $selectedCourse)
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <strong>Resultados del Reporte</strong>
            <div>
                <span class="badge bg-info text-dark">Registros: {{ count($calificaciones) }}</span>
                <span class="text-muted small ms-3">Actualizado: {{ $fuente }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaCalificaciones">
                    <thead class="table-dark">
                        <tr>
                            <th>Estudiante</th>
                            <th>Usuario</th>
                            <th>Centro</th>
                            <th>Fecha de Entrega</th>
                            <th>Calificación</th>
                            <th>Tipo Rol</th>
                            <th>ID Curso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($calificaciones as $row)
                        <tr>
                            <td>{{ $row['estudiante'] }}</td>
                            <td><code class="text-primary">{{ $row['Usuario'] }}</code></td>
                            <td>{{ $row['Centro'] }}</td>
                            <td>{{ $row['Fecha'] }}</td>
                            <td>
                                <span class="badge {{ (is_numeric($row['Calificacion']) && $row['Calificacion'] >= 6) ? 'bg-success' : 'bg-danger' }}" style="font-size: 1rem;">
                                    {{ $row['Calificacion'] }}
                                </span>
                            </td>
                            <td>{{ $row['Tipo_Rol'] }}</td>
                            <td><small class="text-muted">{{ $row['Clave'] }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No se encontraron registros para los filtros seleccionados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning border-0 shadow-sm">
        <i class="fas fa-info-circle"></i> Por favor, seleccione una <strong>Categoría</strong> y luego un <strong>Curso</strong> para generar el reporte.
    </div>
    @endif
</div>
@endsection