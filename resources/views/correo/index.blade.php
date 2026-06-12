@extends('layouts.app')

@section('content')
<!-- Dependencias: SweetAlert2, FontAwesome y DataTables -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- DataTables CSS con integración Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<div class="container-fluid mt-4">
    {{-- ENCABEZADO --}}
    <div class="row align-items-center mb-4">
        <div class="col-lg-6">
            <h3 class="fw-bold text-dark"><i class="fas fa-envelope-open-text text-primary me-2"></i>Control de Correos</h3>
            <p class="text-muted">Gestión de credenciales y seguimiento de entregas por plantel.</p>
        </div>
        
        <div class="col-lg-6 text-lg-end">
            @if(Auth::user()->rol_id == 1)
                <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="collapse" data-bs-target="#collapseImport">
                    <i class="fas fa-file-upload me-1"></i> Importar CSV
                </button>
                <a href="{{ route('correos.plantilla') }}" class="btn btn-outline-secondary shadow-sm rounded-pill px-4">
                    <i class="fas fa-download me-1"></i> Descargar Plantilla
                </a>
                <button type="button" class="btn btn-info text-white shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalInstrucciones">
                    <i class="fas fa-question-circle"></i> Ayuda
                </button>
            @endif
        </div>
    </div>

    {{-- SECCIÓN DE IMPORTACIÓN --}}
    @if(Auth::user()->rol_id == 1)
    <div class="collapse mb-4" id="collapseImport">
        <div class="card card-body border-primary shadow-sm">
            <h5 class="card-title fw-bold">Subir Listado de Estudiantes</h5>
            <form action="{{ route('correo.importar') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-9">
                    <input type="file" name="archivo_csv" class="form-control" accept=".csv" required>
                    <small class="text-muted">Asegúrese de que el formato sea CSV delimitado por comas.</small>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100 fw-bold">PROCESAR ARCHIVO</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- TARJETAS DE ESTADÍSTICAS --}}
    @if($stats)
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100 stat-card">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small opacity-75">Total Alumnos</h6>
                            <h2 class="fw-bold mb-0">{{ number_format($stats['total']) }}</h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100 stat-card">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small opacity-75">Entregados</h6>
                            <h2 class="fw-bold mb-0">{{ number_format($stats['entregados']) }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100 stat-card">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small opacity-75">Pendientes</h6>
                            <h2 class="fw-bold mb-0">{{ number_format($stats['pendientes']) }}</h2>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100 stat-card">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small opacity-75">Eficiencia</h6>
                            <h2 class="fw-bold mb-0">{{ $stats['porcentaje'] }}%</h2>
                        </div>
                        <i class="fas fa-percentage fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TABLA DE RESUMEN POR PLANTEL --}}
    @if(isset($statsPlanteles))
    <div class="card shadow-sm mb-4 border-0 overflow-hidden">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2"></i>Resumen de Avance por Centro / Plantel</h6>
            
            <a href="{{ route('correos.exportar') }}" class="btn btn-sm btn-outline-light">
                <i class="fas fa-download me-1"></i> Descargar Todos los Pendientes
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 300px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-4">Centro / Plantel</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Entregados</th>
                            <th class="text-center">Pendientes</th>
                            <th style="width: 30%;">Progreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statsPlanteles as $sp)
                        @php $prog = $sp->total > 0 ? round(($sp->entregados / $sp->total) * 100) : 0; @endphp
                        <tr>
                            <td class="ps-4 fw-bold text-secondary">{{ $sp->plantel }}</td>
                            <td class="text-center">{{ $sp->total }}</td>
                            <td class="text-center text-success fw-bold">{{ $sp->entregados }}</td>
                            
                            <td class="text-center">
                                <span class="text-danger fw-bold me-2">{{ $sp->pendientes }}</span>
                                @if($sp->pendientes > 0)
                                <a href="{{ route('correos.exportar', ['plantel' => $sp->plantel]) }}" 
                                   class="btn btn-sm btn-light border-0 text-danger p-0 px-1" 
                                   title="Descargar pendientes de {{ $sp->plantel }}">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                                @endif
                            </td>

                            <td class="pe-4">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $prog }}%"></div>
                                    </div>
                                    <span class="ms-2 small fw-bold">{{ $prog }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- LISTADO DE CORREOS CON DATATABLES --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white pt-3 border-bottom-0">
            <ul class="nav nav-pills" id="mailTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold px-4" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pane-pendientes" type="button">
                        <i class="fas fa-clock me-1"></i> PENDIENTES
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold px-4" id="entregados-tab" data-bs-toggle="tab" data-bs-target="#pane-entregados" type="button">
                        <i class="fas fa-check-double me-1"></i> ENTREGADOS
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="mailTabsContent">
                {{-- TAB PENDIENTES --}}
                <div class="tab-pane fade show active" id="pane-pendientes" role="tabpanel">
                    <table id="tablaPendientes" class="table table-hover align-middle" style="width:100%">
                        <thead class="table-light text-uppercase small">
                            <tr>
                                <th>Plantel / Estudiante</th>
                                <th>Credenciales (Copia rápida)</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($correos->whereIn('estatus', ['Pendiente', 'Enviado']) as $item)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border mb-1">{{ $item->plantel }}</span>
                                    <div class="fw-bold text-dark">{{ $item->nombre }}</div>
                                    <div class="small text-muted">{{ $item->matricula }}</div>
                                    <div class="small text-muted">{{ $item->correo_personal }}</div>
                                </td>
                                <td>
                                    <div class="credential-box">
                                        <div class="input-group input-group-sm mb-1 shadow-sm">
                                            <span class="input-group-text bg-white"><i class="fas fa-user-circle text-primary"></i></span>
                                            <input type="text" class="form-control bg-white border-end-0" value="{{ $item->correo_institucional }}" readonly>
                                            <button class="btn btn-outline-primary border-start-0" onclick="copyToClipboard('{{ $item->correo_institucional }}', 'Correo')"><i class="fas fa-copy"></i></button>
                                        </div>
                                        <div class="input-group input-group-sm shadow-sm">
                                            <span class="input-group-text bg-white"><i class="fas fa-key text-warning"></i></span>
                                            <input type="text" class="form-control bg-white border-end-0" value="{{ $item->clave_correo }}" readonly>
                                            <button class="btn btn-outline-warning border-start-0" onclick="copyToClipboard('{{ $item->clave_correo }}', 'Clave')"><i class="fas fa-copy"></i></button>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if(Auth::user()->rol_id == 5)
                                        <span class="text-muted small italic">Solo lectura</span>
                                    @else
                                        <button onclick="confirmarEntrega({{ $item->id }})" class="btn btn-success rounded-pill px-4 shadow-sm btn-sm fw-bold">
                                            <i class="fas fa-paper-plane me-1"></i> ENTREGAR
                                        </button>
                                        <button type="button" 
                                            id="btn-correo-{{ $item->id }}"
                                            class="btn {{ $item->estatus == 'Enviado' ? 'btn-secondary' : 'btn-primary' }} rounded-pill px-3 shadow-sm btn-sm fw-bold"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEnviarCorreo"
                                            data-id="{{ $item->id }}"
                                            data-nombre="{{ $item->nombre }}"
                                            data-personal="{{ $item->correo_personal }}"
                                            data-correo="{{ $item->correo_institucional }}"
                                            data-clave="{{ $item->clave_correo }}">
                                            <i class="fas fa-envelope me-1"></i> {{ $item->estatus == 'Enviado' ? 'Enviado' : 'Correo' }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- TAB ENTREGADOS --}}
                <div class="tab-pane fade" id="pane-entregados" role="tabpanel">
                    <table id="tablaEntregados" class="table table-hover align-middle" style="width:100%">
                        <thead class="table-light text-uppercase small">
                            <tr>
                                <th>Estudiante</th>
                                <th>Correo Institucional</th>
                                <th>Fecha de Entrega</th>
                                <th class="text-center">Estatus / Registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($correos->where('estatus', 'Entregado') as $item)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $item->nombre }}</div>
                                    <div class="small text-muted">{{ $item->matricula }}</div>
                                </td>
                                <td><code>{{ $item->correo_institucional }}</code></td>
                                <td>
                                    <div class="small fw-bold text-dark"><i class="far fa-calendar-alt me-1"></i>{{ $item->fecha_entrega ? $item->fecha_entrega->format('d/m/Y') : 'N/A' }}</div>
                                    <div class="small text-muted"><i class="far fa-clock me-1"></i>{{ $item->fecha_entrega ? $item->fecha_entrega->format('H:i') : '--' }} hrs</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill small">
                                        <i class="fas fa-check-circle me-1"></i> ENTREGADO
                                    </span>

                                    {{-- NUEVO BOTÓN DE REGISTRO --}}
                                    @php
                                        $partes = explode('*', $item->nombre);
                                        
                                        $apellidoPaterno = $partes[0] ?? '';
                                        $apellidoMaterno = $partes[1] ?? '';
                                        $nombres = $partes[2] ?? ''; 
                                        $apellidosUnidos = trim("$apellidoPaterno $apellidoMaterno");
                                    @endphp

                                    <a href="{{ route('registro.index') }}?email={{ $item->correo_institucional }}&nombre={{ urlencode($nombres) }}&apellidos={{ urlencode($apellidosUnidos) }}&matricula={{ $item->matricula }}" 
                                       class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-bold">
                                        <i class="fas fa-user-plus me-1"></i> Registro en PDSEA
                                    </a>

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

{{-- MODAL DE INSTRUCCIONES --}}
<div class="modal fade" id="modalInstrucciones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2"></i>Guía de Importación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold">1. Formato requerido</h6>
                <p>El archivo debe ser un CSV separado por comas.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center small bg-light">
                        <thead>
                            <tr class="bg-secondary text-white">
                                <th>PLANTEL</th><th>MATRÍCULA</th><th>NOMBRE</th><th>FECHA INGRESO</th><th>CORREO PERSONAL</th><th>CORREO INSTITUCIONAL</th><th>CLAVE_CORREO</th><th>MATRICULA_ASESOR</th><th>NOMBRE_ASESOR</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="alert alert-warning mt-3 border-0">
                    <h6 class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Importante:</h6>
                    <ul class="mb-0 small">
                        <li>La <strong>Matrícula</strong> es el identificador único. Si ya existe, se actualizarán los datos.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal para Redactar Correo -->
<div class="modal fade" id="modalEnviarCorreo" tabindex="-1" aria-labelledby="modalEnviarCorreoLabel" aria-hidden="true">
    <!-- Guardamos el ID del registro actual en un input oculto -->
    <input type="hidden" id="current_correo_id"> 
    
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalEnviarCorreoLabel">
                    <i class="fas fa-paper-plane me-2"></i> Plantilla de Correo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Copia el siguiente texto para enviarlo al estudiante:</p>
                <div class="form-group">
                    <textarea id="textoCorreo" class="form-control bg-light" rows="15" readonly></textarea>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                
                <div>
                    <!-- BOTÓN NUEVO: Marcar como Enviado -->
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="marcarEnviadoDesdeModal()" id="btnMarcarEnviado">
                        <i class="fas fa-check-double me-1"></i> Ya lo envié
                    </button>

                    <button type="button" class="btn btn-success" onclick="copyModalText()">
                        <i class="fas fa-copy me-1"></i> Copiar Mensaje
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS: jQuery primero, luego DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Configuración común para las tablas
    const tableOptions = {
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        dom: '<"row align-items-center"<"col-md-6"l><"col-md-6"f>>rt<"row align-items-center"<"col-md-6"i><"col-md-6"p>>',
        pageLength: 10
    };

    // Inicializar tablas
    const tableP = $('#tablaPendientes').DataTable(tableOptions);
    const tableE = $('#tablaEntregados').DataTable(tableOptions);

    // Ajustar columnas al cambiar de pestaña (importante)
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
    });
});

    // Escuchar cuando la modal se abre
    // Configuración del enlace (Cámbialo por el tuyo)
        const LINK_ACCESO = "https://plataformadigitalsea.cbachilleres.edu.mx/"; 

        const modalCorreo = document.getElementById('modalEnviarCorreo');
        modalCorreo.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Botón que activó la modal
            // 1. Limpiar el textarea primero por seguridad
             document.getElementById('textoCorreo').value = "";
            
            // 1. Extraer todos los datos del botón
            const id = button.getAttribute('data-id');
            const nombreCompleto = button.getAttribute('data-nombre');
            const personal = button.getAttribute('data-personal'); // <--- El correo personal
            const correo = button.getAttribute('data-correo');
            const clave = button.getAttribute('data-clave');

            // 2. Guardar el ID en el input oculto (para el botón "Ya lo envié")
            document.getElementById('current_correo_id').value = id;
            
            // 3. Limpiar el nombre (lógica del asterisco)
            const partes = nombreCompleto.split('*');
            const soloNombre = partes[2] ? partes[2].trim() : nombreCompleto;

            // 4. Construir el mensaje dinámico
            // IMPORTANTE: Aquí usamos la variable 'personal' que extrajimos arriba
            const mensaje = `Para: ${personal}\n\n` + 
                `Asunto: Envío de correo y clave institucional.\n\n` +
                `Hola ${soloNombre},\n\n` +
                `Te enviamos tus credenciales para tu nuevo correo institucional:\n\n` +
                `📧 Correo: ${correo}\n` +
                `🔑 Contraseña: ${clave}\n\n` +
                `Puedes acceder desde el siguiente enlace:\n` +
                `${LINK_ACCESO}\n\n` +
                `Nota: Se recomienda cambiar tu contraseña al ingresar por primera vez.\n` +
                `Saludos cordiales.`;

            // 5. Inyectar el texto en el textarea
            document.getElementById('textoCorreo').value = mensaje;
        });

// Función para copiar el contenido de la modal
function copyModalText() {
        const textToCopy = document.getElementById('textoCorreo');
        
        // Seleccionar el texto
        textToCopy.select();
        textToCopy.setSelectionRange(0, 99999); 
        
        navigator.clipboard.writeText(textToCopy.value).then(() => {
            // Notificación corregida
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Mensaje copiado al portapapeles', // Aquí quitamos el error de "type"
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        }).catch(err => {
            console.error('Error al copiar: ', err);
        });
    }

function copyToClipboard(text, type) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: type + ' copiado',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    });
}

function confirmarEntrega(id) {
    Swal.fire({
        title: '¿Confirmar entrega?',
        text: "Se marcará como entregado y se moverá a la lista de entregados.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, entregado',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            actualizarEstatus(id);
        }
    })
}

function actualizarEstatus(id) {
    fetch(`/correo/status/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Entregado!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => location.reload());
        }
    })
    .catch(error => {
        console.error(error);
        Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
    });
}

function marcarEnviadoDesdeModal() {
    const id = document.getElementById('current_correo_id').value;
    const btn = document.getElementById('btnMarcarEnviado');
    
    if(!id) {
        console.error("No se encontró el ID en el modal");
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>...';

    fetch(`/correo/enviado/${id}`, {
        method: 'POST',
        headers: {
            // Asegúrate de que el token esté disponible. 
            // Si no usas la meta tag, usa '{{ csrf_token() }}' directamente:
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // 1. Cambiar color del botón en la tabla sin recargar
            const btnTabla = document.getElementById(`btn-correo-${id}`);
            if(btnTabla) {
                btnTabla.classList.replace('btn-primary', 'btn-secondary');
                btnTabla.innerHTML = '<i class="fas fa-envelope me-1"></i> Enviado';
            }
            
            // 2. Cerrar el modal
            const myModalEl = document.getElementById('modalEnviarCorreo');
            const modal = bootstrap.Modal.getInstance(myModalEl);
            modal.hide();
            
            // 3. Notificación bonita
            Swal.fire({
                icon: 'success',
                title: 'Estatus actualizado',
                text: 'Se marcó como enviado.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Atención', data.message, 'warning');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo actualizar', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-double me-1"></i> Ya lo envié';
    });
}

</script>

<style>
    /* Estilos personalizados y diseño */
    .stat-card { border-radius: 12px; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    
    .nav-pills .nav-link { 
        color: #6c757d; border-radius: 50px; transition: all 0.3s;
        border: 1px solid transparent; margin-right: 8px;
    }
    .nav-pills .nav-link.active { 
        background-color: #0d6efd; color: white; 
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25); 
    }

    /* Ajustes para DataTables Bootstrap 5 */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 50px;
        padding: 5px 15px;
        border: 1px solid #dee2e6;
        min-width: 250px;
    }
    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; }
    
    .credential-box { width: 100%; max-width: 350px; }
    code { color: #d63384; font-weight: bold; background: #f8f9fa; padding: 2px 5px; border-radius: 4px; }
    
    .table-responsive { border-radius: 0 0 12px 12px; }
    .sticky-top { z-index: 10; top: 0; }
</style>
@endsection