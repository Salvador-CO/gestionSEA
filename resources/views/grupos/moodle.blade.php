@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold text-dark"><i class="bi bi-layers-half me-2"></i> Monitor de Cursos y Grupos en Moodle</h4>
            <a href="{{ route('grupos.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Control Local
            </a>
        </div>
    </div>

    <!-- CONTENEDOR DE NOTIFICACIONES SOFISTICADAS (TOASTS) -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
        <div id="toastNotificacion" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-bold" id="toastMensaje">
                    <!-- Mensaje dinámico -->
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- SELECCIÓN DE ASIGNATURA -->
    <div class="card shadow border-0 mb-4">
        <div class="card-body bg-light">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase text-muted">Selecciona la Asignatura a Consultar</label>
                    <select id="selectAsignaturaMoodle" class="form-select form-select-lg shadow-sm">
                        <option value="">-- Seleccionar Asignatura --</option>
                        @foreach($asignaturas as $asig)
                            <option value="{{ $asig->clave }}">(Clave: {{ $asig->clave }}) - {{ $asig->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <span class="badge bg-info text-white p-2 shadow-sm">Consulta Directa vía WebService</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE RESULTADOS DE MOODLE -->
    <div class="card shadow border-0 d-none" id="cardResultadosMoodle">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
            <span>Grupos Activos detectados en la Plataforma Moodle</span>
            <span class="spinner-border spinner-border-sm text-primary d-none" id="cargandoMoodle" role="status"></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="tablaMoodleReal">
                    <thead class="table-dark">
                        <tr>
                            <th>Código Local (Laravel)</th>
                            <th>Centro / Asesor Local</th>
                            <th class="text-center">Estado Moodle</th>
                            <th class="text-center">Alumnos Moodle</th>
                            <th>Asesor en Moodle</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="bodyTablaMoodle">
                        <!-- Se llena dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MENSAJE INICIAL VACÍO -->
    <div class="text-center py-5" id="estadoVacio">
        <i class="bi bi-cloud-check text-muted" style="font-size: 4rem;"></i>
        <p class="text-muted mt-2">Por favor, selecciona una asignatura del menú superior para sincronizar la información con Moodle.</p>
    </div>
</div>

<!-- MODAL PARA ASIGNAR ASESORES INDEPENDIENTES CON ROLES -->
<div class="modal fade" id="modalAsesoresMoodle" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalAsesoresMoodleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="modalAsesoresMoodleLabel"><i class="bi bi-person-plus-fill me-2 text-warning"></i> Asignar Asesor Remoto Moodle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAsignarAsesorMoodle">
                <div class="modal-body p-4">
                    <div class="alert alert-secondary border-0 mb-3 small py-2 shadow-sm">
                        <span class="fw-bold text-uppercase d-block text-muted">Asesor Registrado en Local:</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span id="txtAsesorLocalModal" class="fw-bold text-dark">-</span>
                            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fw-bold" id="btnCopiarCorreoLocal" style="font-size:0.75rem;">
                                <i class="bi bi-clipboard-plus"></i> Usar Correo
                            </button>
                        </div>
                        <input type="hidden" id="hiddenCorreoLocal">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Rol del Asesor en Moodle</label>
                        <select name="role_type" id="modalRoleType" class="form-select shadow-sm" required>
                            <option value="contenido">Asesor de Contenido (Rol: contenido)</option>
                            <option value="psicopeda">Asesor Psicopedagógico (Rol: psicopeda)</option>
                            <option value="responsable">Responsable de Centro (Rol: respplantel)</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted text-uppercase">Correo Electrónico Registrado en Moodle</label>
                        <input type="email" name="email" id="modalEmailAsesor" class="form-select form-control shadow-sm" placeholder="ejemplo@moodle.com" required>
                        <div class="form-text text-muted small">El usuario debe existir previamente registrado dentro de la plataforma Moodle.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold px-3" id="btnGuardarAsesorMoodle">
                        <i class="bi bi-check-circle-fill"></i> Vincular Asesor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let courseIdMoodleActual = null;
    let grupoSeleccionadoIdMoodle = null;
    let inputSelectClaveActual = null;
    let filaObjetivoAsesor = null; 

    const toastElement = document.getElementById('toastNotificacion');
    const bootstrapToast = new bootstrap.Toast(toastElement, { delay: 6000 });

    function mostrarNotificacion(mensaje, tipo = 'success') {
        $('#toastNotificacion').removeClass('bg-success bg-danger bg-warning');
        if(tipo === 'success') $('#toastNotificacion').addClass('bg-success');
        if(tipo === 'danger') $('#toastNotificacion').addClass('bg-danger');
        if(tipo === 'warning') $('#toastNotificacion').addClass('bg-warning');
        
        $('#toastMensaje').html(mensaje);
        bootstrapToast.show();
    }

    function cargarTablaGrupos(clave) {
        $('#cargandoMoodle').removeClass('d-none');
        $('#cardResultadosMoodle').removeClass('d-none');
        $('#estadoVacio').addClass('d-none');
        $('#bodyTablaMoodle').html('');

        $.ajax({
            url: `/tablero-moodle/grupos/${clave}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#cargandoMoodle').addClass('d-none');
                courseIdMoodleActual = response.course_id_moodle;
                let data = response.grupos;
                
                if (data.length === 0) {
                    $('#bodyTablaMoodle').html(`
                        <tr>
                            <td colspan="6" class="text-center py-4 text-warning fw-bold">
                                <i class="bi bi-exclamation-triangle"></i> No tienes ningún grupo creado localmente para esta asignatura.
                            </td>
                        </tr>
                    `);
                    return;
                }

                let htmlRows = '';
                data.forEach(function(g) {
                    let badgeEstado = '';
                    let btnAccion = '';

                    if (g.existe_en_moodle) {
                        badgeEstado = `<span class="badge bg-success shadow-sm"><i class="bi bi-cloud-check-fill"></i> Activo</span>`;
                        btnAccion = `
                            <button class="btn btn-sm btn-primary fw-bold shadow-sm btn-abrir-modal-asesores" 
                                    data-idmoodle="${g.id_moodle}" 
                                    data-nombrelocal="${g.asesor_local}" 
                                    data-correolocal="${g.correo_asesor ? g.correo_asesor : ''}">
                                <i class="bi bi-person-gear"></i> Asignar Asesores
                            </button>`;
                    } else {
                        badgeEstado = `<span class="badge bg-danger shadow-sm"><i class="bi bi-cloud-slash-fill"></i> No existe</span>`;
                        btnAccion = `
                            <button class="btn btn-sm btn-warning fw-bold text-dark btn-crear-moodle" 
                                    data-codigo="${g.codigo_moodle}"
                                    data-nombrelocal="${g.asesor_local}" 
                                    data-correolocal="${g.correo_asesor ? g.correo_asesor : ''}">
                                <i class="bi bi-cloud-plus-fill"></i> Crear en Moodle
                            </button>`;
                    }

                    let correoAsesor = g.correo_asesor ? g.correo_asesor : 'Sin correo registrado';

                    htmlRows += `
                        <tr>
                            <td><strong class="text-primary">${g.codigo_moodle}</strong></td>
                            <td>
                                <small class="d-block fw-bold text-dark">${g.centro_nombre}</small>
                                <small class="d-block text-muted"><i class="bi bi-person"></i> ${g.asesor_local}</small>
                                <small class="d-block text-info fw-bold" style="font-size: 0.78rem;"><i class="bi bi-envelope"></i> <span class="text-dark bg-light px-1 rounded">${correoAsesor}</span></small>
                            </td>
                            <td class="text-center status-cell">${badgeEstado}</td>
                            <td class="text-center text-muted fw-bold students-count-cell">${g.existe_en_moodle ? g.total_alumnos + ' alumnos' : '-'}</td>
                            <td class="text-muted fw-bold small text-uppercase advisors-cell">${g.asesor_moodle}</td>
                            <td class="text-end action-cell">${btnAccion}</td>
                        </tr>
                    `;
                });
                
                $('#bodyTablaMoodle').html(htmlRows);
            },
            error: function() {
                $('#cargandoMoodle').addClass('d-none');
                $('#bodyTablaMoodle').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger fw-bold">Error al procesar la sincronización local.</td>
                    </tr>
                `);
            }
        });
    }

    $('#selectAsignaturaMoodle').on('change', function() {
        inputSelectClaveActual = $(this).val();
        if (!inputSelectClaveActual) {
            $('#cardResultadosMoodle').addClass('d-none');
            $('#estadoVacio').removeClass('d-none');
            return;
        }
        cargarTablaGrupos(inputSelectClaveActual);
    });

    $(document).on('click', '.btn-crear-moodle', function() {
        let boton = $(this);
        let codigoMoodle = boton.data('codigo');
        let nombreLocal = boton.data('nombrelocal');
        let correoLocal = boton.data('correolocal');
        let filaActual = boton.closest('tr');

        boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Subiendo...');

        $.ajax({
            url: "{{ route('grupos.crearRemoto') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                course_id: courseIdMoodleActual,
                codigo_moodle: codigoMoodle
            },
            success: function(res) {
                if(res.success) {
                    mostrarNotificacion('<i class="bi bi-check-circle-fill"></i> ¡Grupo remoto estructurado correctamente en Moodle!');
                    
                    filaActual.find('.status-cell').html(`<span class="badge bg-success shadow-sm"><i class="bi bi-cloud-check-fill"></i> Activo</span>`);
                    filaActual.find('.students-count-cell').html('0 alumnos');
                    filaActual.find('.advisors-cell').html('No asignado en Moodle');
                    
                    filaActual.find('.action-cell').html(`
                        <button class="btn btn-sm btn-primary fw-bold shadow-sm btn-abrir-modal-asesores" 
                                data-idmoodle="${res.id_moodle}" 
                                data-nombrelocal="${nombreLocal}" 
                                data-correolocal="${correoLocal ? correoLocal : ''}">
                            <i class="bi bi-person-gear"></i> Asignar Asesores
                        </button>
                    `);
                }
            },
            error: function(xhr) {
                let errorCompleto = 'Error desconocido en el servidor.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorCompleto = xhr.responseJSON.message;
                }
                mostrarNotificacion('<i class="bi bi-x-circle-fill"></i> Error en parámetros de Moodle:<br>' + errorCompleto, 'danger');
                boton.prop('disabled', false).html('<i class="bi bi-cloud-plus-fill"></i> Crear en Moodle');
            }
        });
    });

    $(document).on('click', '.btn-abrir-modal-asesores', function() {
        grupoSeleccionadoIdMoodle = $(this).data('idmoodle');
        let nombreLocal = $(this).data('nombrelocal');
        let correoLocal = $(this).data('correolocal');
        filaObjetivoAsesor = $(this).closest('tr'); 

        $('#formAsignarAsesorMoodle')[0].reset();
        
        if(correoLocal) {
            $('#txtAsesorLocalModal').html(`${nombreLocal} <code>&lt;${correoLocal}&gt;</code>`);
            $('#hiddenCorreoLocal').val(correoLocal);
            $('#btnCopiarCorreoLocal').removeClass('d-none');
        } else {
            $('#txtAsesorLocalModal').html(`<em>No hay ningún asesor local preasignado</em>`);
            $('#hiddenCorreoLocal').val('');
            $('#btnCopiarCorreoLocal').addClass('d-none');
        }

        $('#modalAsesoresMoodle').modal('show');
    });

    $('#btnCopiarCorreoLocal').on('click', function() {
        let correo = $('#hiddenCorreoLocal').val();
        if(correo) {
            $('#modalEmailAsesor').val(correo);
        }
    });

    $('#formAsignarAsesorMoodle').on('submit', function(e) {
        e.preventDefault();
        let btnGuardar = $('#btnGuardarAsesorMoodle');
        btnGuardar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Vinculando...');

        $.ajax({
            url: "{{ route('grupos.asignarAsesorMoodle') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                course_id: courseIdMoodleActual,
                moodle_group_id: grupoSeleccionadoIdMoodle,
                email: $('#modalEmailAsesor').val(),
                role_type: $('#modalRoleType').val()
            },
            success: function(res) {
                btnGuardar.prop('disabled', false).html('<i class="bi bi-check-circle-fill"></i> Vincular Asesor');
                $('#modalAsesoresMoodle').modal('hide');
                mostrarNotificacion('<i class="bi bi-check-circle-fill"></i> ¡Asesor guardado correctamente en Moodle!');
                
                if(filaObjetivoAsesor) {
                    let celdaAsesores = filaObjetivoAsesor.find('.advisors-cell');
                    let contenidoActual = celdaAsesores.html().trim();

                    if (contenidoActual === 'No asignado en Moodle' || contenidoActual === 'N/A') {
                        celdaAsesores.html(res.nombre_completo);
                    } else {
                        celdaAsesores.html(contenidoActual + '<br>' + res.nombre_completo);
                    }
                }
            },
            error: function(xhr) {
                btnGuardar.prop('disabled', false).html('<i class="bi bi-check-circle-fill"></i> Vincular Asesor');
                let err = 'No se pudo vincular al asesor.';
                if(xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                mostrarNotificacion('<i class="bi bi-x-circle-fill"></i> ' + err, 'danger');
            }
        });
    });
});
</script>
@endpush
@endsection