@extends('layouts.app')

@section('content')
<!-- Librerías de soporte -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<!-- Select2 CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    /* Toasts Estilo Glassmorphism */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 2000;
    }

    .custom-toast {
        min-width: 300px;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border-left: 6px solid #6c757d;
        display: flex;
        align-items: center;
        padding: 15px;
        margin-bottom: 10px;
        transform: translateX(120%);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .custom-toast.show {
        transform: translateX(0);
    }

    .toast-success {
        border-left-color: #198754;
    }

    .toast-danger {
        border-left-color: #dc3545;
    }

    .toast-warning {
        border-left-color: #ffc107;
    }

    /* Optimización de Espacio */
    .card {
        border-radius: 15px;
        border: none;
        margin-bottom: 1rem;
    }

    .table-container-scroll {
        max-height: 300px;
        overflow-y: auto;
    }

    /* Botones y Inputs compactos */
    .btn-action {
        border-radius: 8px;
        transition: all 0.2s;
    }

    .form-control-sm,
    .form-select-sm {
        border-radius: 8px;
    }

    /* Estilo de la tabla de evaluaciones (La importante) */
    .table-evaluaciones {
        background-color: #fffdf7;
    }

    .table-evaluaciones thead {
        background-color: #fff3cd;
        color: #856404;
    }

    /* Animaciones */
    .fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }

    /* Toggle de tipo de búsqueda */
    .search-toggle {
        display: flex;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #dee2e6;
        margin-bottom: 8px;
    }

    .search-toggle-btn {
        flex: 1;
        padding: 5px 10px;
        font-size: 0.78rem;
        font-weight: 600;
        border: none;
        background: #f8f9fa;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .search-toggle-btn.active {
        background: #0d6efd;
        color: white;
    }

    .search-toggle-btn:first-child {
        border-right: 1px solid #dee2e6;
    }

    /* Select2 personalizado para que se vea igual al select nativo */
    .select2-container--default .select2-selection--single {
        height: 31px;
        border-radius: 8px;
        border-color: #dee2e6;
        font-size: 0.875rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 29px;
        padding-left: 10px;
        color: #212529;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px;
    }

    .select2-dropdown {
        border-radius: 8px;
        font-size: 0.85rem;
    }

    .select2-container {
        width: 100% !important;
    }

    /* Badge de pendiente */
    .badge-pendiente {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
        }

        50% {
            box-shadow: 0 0 0 5px rgba(40, 167, 69, 0);
        }
    }

    .badge-sin-pendiente {
        color: #adb5bd;
        font-size: 0.8rem;
    }

    /* Botón quitar intento */
    .btn-quitar {
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 6px;
        padding: 3px 8px;
        line-height: 1.4;
    }

    /* Botón Ver Calificaciones (trigger lazy) */
    .btn-ver-calificaciones {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 700;
        font-size: 0.88rem;
        letter-spacing: 0.3px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 15px rgba(102,126,234,0.35);
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        justify-content: center;
    }
    .btn-ver-calificaciones:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(102,126,234,0.45);
        color: white;
    }
    .btn-ver-calificaciones.collapsed {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #495057;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .btn-ver-calificaciones.collapsed:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        color: #212529;
    }
</style>

<div class="container-fluid py-3">
    <div id="toastContainer" class="toast-container"></div>

    <div class="row g-3">
        <!-- COLUMNA IZQUIERDA: BUSCADOR Y PERFIL (30%) -->
        <div class="col-xl-3 col-lg-4">
            <!-- Buscador -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <h6 class="mb-0 small fw-bold"><i class="bi bi-search me-2"></i>Localizar Estudiante</h6>
                </div>
                <div class="card-body p-3">
                    <!-- Toggle Correo / Matrícula -->
                    <div class="search-toggle">
                        <button type="button" class="search-toggle-btn active" id="toggleEmail" onclick="setSearchMode('email')">
                            <i class="bi bi-envelope-fill"></i> Correo
                        </button>
                        <button type="button" class="search-toggle-btn" id="toggleMatricula" onclick="setSearchMode('username')">
                            <i class="bi bi-person-badge-fill"></i> Matrícula
                        </button>
                    </div>
                    <div class="input-group input-group-sm">
                        <input type="email" id="searchInput" class="form-control" placeholder="Correo institucional...">
                        <button class="btn btn-primary" id="btnBuscar">
                            <i class="bi bi-search" id="btnIcon"></i>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                    <small id="searchHint" class="text-muted mt-1 d-block" style="font-size:0.72rem;">
                        <i class="bi bi-info-circle me-1"></i>Ingresa el correo registrado en Moodle
                    </small>
                </div>
            </div>

            <!-- Perfil compacto -->
            <div id="perfilUsuario" class="card shadow-sm d-none animate__animated animate__fadeIn">
                <div class="card-body p-3 text-center">
                    <img id="userFoto" src="" class="rounded-circle border mb-2 shadow-sm" width="80" height="80" style="object-fit: cover;">
                    <h6 id="userNombre" class="fw-bold mb-0 text-dark"></h6>
                    <p id="userEmail" class="text-muted mb-1" style="font-size: 0.75rem;"></p>
                    <p id="userUsername" class="text-muted mb-2 font-monospace" style="font-size: 0.72rem;"></p>

                    <div class="row g-1 border-top pt-2">
                        <div class="col-6">
                            <small class="text-muted d-block small-text">Plantel</small>
                            <span id="userCentro" class="fw-bold small-text text-truncate d-block px-1"></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block small-text">Tipo</small>
                            <span id="userRol" class="fw-bold small-text"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Asignación -->
            <div id="panelAsignacion" class="card shadow-sm d-none mt-3 animate__animated animate__fadeIn">
                <div class="card-header text-white py-2" style="background: var(--success-gradient);">
                    <h6 class="mb-0 small fw-bold"><i class="bi bi-plus-circle me-2"></i>Nueva Inscripción</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">EVALUACIÓN</label>
                        <!-- Select2 se inicializa aquí via JS -->
                        <select id="selectEval" class="form-select form-select-sm"></select>
                    </div>
                    <div id="divGrupo" class="mb-3 d-none">
                        <label class="small fw-bold text-muted mb-1">
                            GRUPO DESTINO <span id="loadGrupos" class="spinner-border spinner-border-sm text-primary d-none"></span>
                        </label>
                        <select id="selectGrupo" class="form-select form-select-sm border-success"></select>
                    </div>
                    <button id="btnAsignar" class="btn btn-success btn-sm w-100 fw-bold btn-action py-2">
                        <span id="txtBtn">INSCRIBIR AHORA</span>
                        <span id="spinAsignar" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: TABLAS (70%) -->
        <div class="col-xl-9 col-lg-8">

            <!-- ── FASE 1: Carga inmediata ───────────────────────────────────── -->
            <div id="panelTablas" class="d-none">

                <!-- Tabla Otros Cursos (siempre visible, sin llamadas extra) -->
                <div class="card shadow-sm animate__animated animate__fadeInUp mb-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 small fw-bold"><i class="bi bi-journal-text me-2"></i>Otros cursos del estudiante (Vista rápida)</h6>
                    </div>
                    <div class="table-container-scroll">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="bg-white sticky-top small">
                                <tr>
                                    <th class="ps-3">Nombre</th>
                                    <th>ID</th>
                                    <th class="text-end pe-3">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyGeneral" style="font-size: 0.78rem;"></tbody>
                        </table>
                    </div>
                </div>

                <!-- ── BOTÓN TRIGGER — Fase 2 ────────────────────────────────── -->
                <div class="mb-3">
                    <button class="btn-ver-calificaciones collapsed"
                        id="btnVerCalificaciones"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#panelCalificaciones"
                        aria-expanded="false"
                        onclick="onToggleCalificaciones(this)">
                        <i class="bi bi-bar-chart-fill" id="icoCalif"></i>
                        <span id="txtCalif">Ver Calificaciones e Intentos de Evaluación</span>
                        <span id="spinCalif" class="spinner-border spinner-border-sm d-none"></span>
                        <i class="bi bi-chevron-down" id="chevronCalif" style="margin-left:auto;"></i>
                    </button>
                </div>

                <!-- ── FASE 2: Carga lazy (Bootstrap Collapse) ───────────────── -->
                <div class="collapse" id="panelCalificaciones">
                    <div class="card shadow-sm border-top border-4 border-warning animate__animated animate__fadeInDown">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-star-fill text-warning me-2"></i>Calificaciones e Intentos de Evaluación</h6>
                            <span class="badge bg-warning text-dark small-text">Atención Prioritaria</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 table-evaluaciones">
                                <thead class="small">
                                    <tr>
                                        <th class="ps-3">CURSO EVALUACIÓN</th>
                                        <th class="text-center">CALIFICACIÓN</th>
                                        <th class="text-center">INTENTOS</th>
                                        <th class="text-center">PENDIENTE</th>
                                        <th class="text-end pe-3">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyEval" class="small-text"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Estado Inicial -->
            <div id="emptyState" class="text-center py-5">
                <i class="bi bi-person-video2 display-1 text-light"></i>
                <h5 class="mt-3 text-muted">Gestión de Evaluaciones</h5>
                <p class="text-secondary small">Busque un estudiante por correo o matrícula para habilitar el panel de control.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historial -->
<div class="modal fade" id="modalHistorial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered shadow-lg">
        <div class="modal-content border-0" style="border-radius: 15px;">
            <div class="modal-header bg-info text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="modal-title fw-bold"><i class="bi bi-clock-history me-2"></i>Historial de Intentos</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div id="contentHistorial" class="modal-body p-0"></div>
        </div>
    </div>
</div>

<script>
    let currentUserId = null;
    let currentCentro = null;
    let searchMode = 'email';       // 'email' o 'username'
    let inscritosCache = null;      // guarda inscritos del último alumno buscado
    let evaluacionesCargadas = false; // evita recargar si ya se consultó Moodle

    // ─── Inicializar Select2 ──────────────────────────────────────────────────────
    $(document).ready(function() {
        $('#selectEval').select2({
            placeholder: '-- Busca o selecciona una evaluación --',
            allowClear: true,
            language: {
                noResults: function() {
                    return "Sin resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });

        // Disparar evento change de jQuery cuando Select2 cambia
        $('#selectEval').on('change', function() {
            const cid = $(this).val();
            if (!cid) {
                document.getElementById('divGrupo').classList.add('d-none');
                return;
            }
            document.getElementById('divGrupo').classList.remove('d-none');
            const spin = document.getElementById('loadGrupos');
            spin.classList.remove('d-none');

            fetch(`/registro/obtener-grupos/${cid}?centro=${encodeURIComponent(currentCentro)}`)
                .then(res => res.json())
                .then(data => {
                    spin.classList.add('d-none');
                    let h = '<option value="">-- Grupo --</option>';
                    if (data.length === 0) {
                        h = '<option value="">Sin grupos</option>';
                        document.getElementById('btnAsignar').disabled = true;
                    } else {
                        data.forEach(g => h += `<option value="${g.id}">${g.name}</option>`);
                        document.getElementById('btnAsignar').disabled = false;
                    }
                    document.getElementById('selectGrupo').innerHTML = h;
                });
        });
    });

    // ─── Toggle de modo de búsqueda ───────────────────────────────────────────────
    function setSearchMode(mode) {
        searchMode = mode;
        const input = document.getElementById('searchInput');
        const hint = document.getElementById('searchHint');
        const btnEmail = document.getElementById('toggleEmail');
        const btnMatricula = document.getElementById('toggleMatricula');

        if (mode === 'email') {
            input.type = 'email';
            input.placeholder = 'Correo institucional...';
            hint.innerHTML = '<i class="bi bi-info-circle me-1"></i>Ingresa el correo registrado en Moodle';
            btnEmail.classList.add('active');
            btnMatricula.classList.remove('active');
        } else {
            input.type = 'text';
            input.placeholder = 'Matrícula (ej: 12345678)...';
            hint.innerHTML = '<i class="bi bi-info-circle me-1"></i>Ingresa el username / matrícula de Moodle';
            btnEmail.classList.remove('active');
            btnMatricula.classList.add('active');
        }
        input.value = '';
        input.focus();
    }

    // ─── Función Toast ────────────────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        const icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'danger' ? 'bi-x-circle-fill' : 'bi-exclamation-triangle-fill');
        toast.className = `custom-toast toast-${type}`;
        toast.innerHTML = `<i class="bi ${icon} fs-5 me-3 text-${type}"></i><div class="flex-grow-1 text-dark small fw-bold">${message}</div>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 50);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    // ─── Buscar estudiante ────────────────────────────────────────────────────────
    document.getElementById('btnBuscar').addEventListener('click', function() {
        const valor = document.getElementById('searchInput').value.trim();
        if (!valor) return showToast(`Escriba un ${searchMode === 'email' ? 'correo' : 'matrícula'}`, 'warning');

        toggleLoading('btnBuscar', true);

        // Construir URL según el modo de búsqueda
        const param = searchMode === 'email' ? `email=${encodeURIComponent(valor)}` : `username=${encodeURIComponent(valor)}`;
        fetch(`{{ route('asignacion.validar') }}?${param}`)
            .then(res => res.json())
            .then(data => {
                toggleLoading('btnBuscar', false);
                if (!data.success) {
                    Swal.fire('No encontrado', data.message, 'error');
                    resetUI();
                    return;
                }

                currentUserId = data.user.id;
                currentCentro = data.user.centro;

                document.getElementById('userNombre').innerText = data.user.nombre;
                document.getElementById('userEmail').innerText = data.user.email;
                document.getElementById('userUsername').innerText = data.user.username ? `@${data.user.username}` : '';
                document.getElementById('userCentro').innerText = data.user.centro;
                document.getElementById('userRol').innerText = data.user.rol;
                document.getElementById('userFoto').src = data.user.foto || 'https://www.gravatar.com/avatar/000?d=mp';

                document.getElementById('perfilUsuario').classList.remove('d-none');
                document.getElementById('panelAsignacion').classList.remove('d-none');
                document.getElementById('panelTablas').classList.remove('d-none');
                document.getElementById('emptyState').classList.add('d-none');

                // Llenamos el Select2 con los cursos disponibles
                const inscritosIds = data.inscritos.map(c => parseInt(c.id));
                let options = '<option value=""></option>'; // opción vacía para el placeholder de Select2

                data.cursosEval.forEach(c => {
                    if (!inscritosIds.includes(parseInt(c.id))) {
                        options += `<option value="${c.id}">[${c.shortname}] ${c.fullname}</option>`;
                    }
                });

                // Destruir y re-inicializar Select2 para refrescar opciones
                if ($('#selectEval').data('select2')) {
                    $('#selectEval').select2('destroy');
                }
                document.getElementById('selectEval').innerHTML = options;
                $('#selectEval').select2({
                    placeholder: '-- Busca o selecciona una evaluación --',
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return "Sin resultados";
                        },
                        searching: function() {
                            return "Buscando...";
                        }
                    }
                });
                $('#selectEval').on('change', function() {
                    const cid = $(this).val();
                    if (!cid) {
                        document.getElementById('divGrupo').classList.add('d-none');
                        return;
                    }
                    document.getElementById('divGrupo').classList.remove('d-none');
                    const spin = document.getElementById('loadGrupos');
                    spin.classList.remove('d-none');
                    fetch(`/registro/obtener-grupos/${cid}?centro=${encodeURIComponent(currentCentro)}`)
                        .then(res => res.json())
                        .then(data => {
                            spin.classList.add('d-none');
                            let h = '<option value="">-- Grupo --</option>';
                            if (data.length === 0) {
                                h = '<option value="">Sin grupos</option>';
                                document.getElementById('btnAsignar').disabled = true;
                            } else {
                                data.forEach(g => h += `<option value="${g.id}">${g.name}</option>`);
                                document.getElementById('btnAsignar').disabled = false;
                            }
                            document.getElementById('selectGrupo').innerHTML = h;
                        });
                });

                if (data.cursosEval.length > 0 && options === '<option value=""></option>') {
                    document.getElementById('selectEval').innerHTML = '<option value="">Ya está inscrito en todas las evaluaciones</option>';
                }

                // Resetear el grupo
                document.getElementById('divGrupo').classList.add('d-none');

                // ── Guardar en caché y resetear estado lazy ────────────────────
                inscritosCache = data.inscritos;
                evaluacionesCargadas = false;

                // Colapsar el panel de calificaciones si estaba abierto
                const collapseEl = document.getElementById('panelCalificaciones');
                const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
                if (bsCollapse) bsCollapse.hide();

                // Resetear botón trigger
                const btnCalif = document.getElementById('btnVerCalificaciones');
                btnCalif.classList.add('collapsed');
                document.getElementById('txtCalif').textContent = 'Ver Calificaciones e Intentos de Evaluación';
                document.getElementById('chevronCalif').style.transform = '';
                document.getElementById('icoCalif').className = 'bi bi-bar-chart-fill';

                // ── FASE 1: Renderizar solo otros cursos (inmediato) ───────────
                renderOtrosCursos(data.inscritos);
                // Limpiar tabla eval (se cargará en Fase 2)
                document.getElementById('tbodyEval').innerHTML =
                    '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-2"></i>Presiona "Ver Calificaciones" para cargar</td></tr>';

                showToast("Datos actualizados");
            })
            .catch(err => {
                toggleLoading('btnBuscar', false);
                showToast("Error de conexión", "danger");
            });
    });

    // ─── FASE 1: Renderizar solo "Otros Cursos" (inmediato, sin llamadas Moodle) ──
    function renderOtrosCursos(inscritos) {
        let gen = '';
        inscritos.forEach(c => {
            const displayName = `<b>${c.shortname}</b> | ${c.fullname}`;
            gen += `<tr><td class="ps-3">${displayName}</td><td>${c.id}</td><td class="text-end pe-3"><span class="text-success fw-bold">Activo</span></td></tr>`;
        });
        document.getElementById('tbodyGeneral').innerHTML =
            gen || '<tr><td colspan="3" class="text-center py-2">Sin otros cursos</td></tr>';
    }

    // ─── FASE 2: Renderizar Calificaciones e Intentos (lazy, bajo demanda) ────────
    function renderEvaluaciones(inscritos) {
        let ev = '';
        let hayEval = false;

        inscritos.forEach(c => {
            if (!c.fullname.toUpperCase().includes('EVAL')) return;
            hayEval = true;

            const nota       = c.nota || 'N/A';
            const esAprobado = (nota !== 'N/A' && parseFloat(nota) >= 6);
            const colorClass = esAprobado ? 'badge bg-success' : 'badge bg-danger';

            ev += `<tr>
                <td class="ps-3">
                    <div class="fw-bold">${c.fullname}</div>
                    <small class="text-muted font-monospace" style="font-size:0.7rem">ID: ${c.id}</small>
                </td>
                <td class="text-center"><span class="${colorClass} px-3 py-2">${nota}</span></td>
                <td class="text-center" id="attempt-count-${c.id}"><div class="spinner-border spinner-border-sm text-secondary"></div></td>
                <td class="text-center" id="attempt-pending-${c.id}"><div class="spinner-border spinner-border-sm text-secondary"></div></td>
                <td class="text-end pe-3">
                    <div class="btn-group" id="attempt-actions-${c.id}">
                        <button class="btn btn-sm btn-outline-info" onclick="verHistorial(${currentUserId}, ${c.id})" title="Ver historial">
                            <i class="bi bi-clock-history"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" disabled title="Cargando...">
                            <span class="spinner-border spinner-border-sm"></span>
                        </button>
                    </div>
                </td>
            </tr>`;

            // Ahora sí disparamos las llamadas a Moodle (overrides + intentos)
            cargarEstadoIntentos(currentUserId, c.id, nota, esAprobado);
        });

        document.getElementById('tbodyEval').innerHTML =
            hayEval ? ev : '<tr><td colspan="5" class="text-center py-4">No tiene evaluaciones inscritas</td></tr>';
    }

    // ─── Toggle del panel de Calificaciones (botón disparador) ───────────────────
    function onToggleCalificaciones(btn) {
        const abierto = !btn.classList.contains('collapsed');
        const chevron = document.getElementById('chevronCalif');
        const ico     = document.getElementById('icoCalif');
        const txt     = document.getElementById('txtCalif');
        const spin    = document.getElementById('spinCalif');

        if (abierto) {
            // Panel se está ABRIENDO → cargar si no se ha cargado aún
            chevron.style.transform = 'rotate(180deg)';
            if (!evaluacionesCargadas && inscritosCache) {
                spin.classList.remove('d-none');
                ico.className = 'bi bi-hourglass-split';
                txt.textContent = 'Cargando calificaciones...';
                // Pequeño delay para que se vea la animación de apertura
                setTimeout(() => {
                    renderEvaluaciones(inscritosCache);
                    evaluacionesCargadas = true;
                    spin.classList.add('d-none');
                    ico.className = 'bi bi-star-fill text-warning';
                    txt.textContent = 'Ocultar Calificaciones e Intentos';
                }, 300);
            } else {
                ico.className = 'bi bi-star-fill text-warning';
                txt.textContent = 'Ocultar Calificaciones e Intentos';
            }
        } else {
            // Panel se está CERRANDO
            chevron.style.transform = '';
            ico.className = 'bi bi-bar-chart-fill';
            txt.textContent = 'Ver Calificaciones e Intentos de Evaluación';
        }
    }

    // ─── Cargar estado de intentos (conteo + pendientes + botones) ───────────────
    function cargarEstadoIntentos(uId, cId, nota, esAprobado) {
        fetch(`{{ route('asignacion.historial') }}?userId=${uId}&courseId=${cId}`)
            .then(res => res.json())
            .then(data => {
                const elCount = document.getElementById(`attempt-count-${cId}`);
                const elPending = document.getElementById(`attempt-pending-${cId}`);
                const elActions = document.getElementById(`attempt-actions-${cId}`);

                if (data.success) {
                    // ── Columna INTENTOS ─────────────────────────────────────────
                    elCount.innerHTML = `<span class="fw-bold">${data.conteo}</span>`;

                    // ── Columna PENDIENTE ────────────────────────────────────────
                    const pendientes = data.pendientes ?? 0;
                    if (pendientes > 0) {
                        elPending.innerHTML = `
                        <span class="badge-pendiente" title="Tiene ${pendientes} intento(s) habilitado(s) sin presentar">
                            <i class="bi bi-check-circle-fill"></i>
                            ${pendientes === 1 ? 'Puede presentar' : `${pendientes} pend.`}
                        </span>`;
                    } else {
                        elPending.innerHTML = `<span class="badge-sin-pendiente" title="Sin intentos pendientes">—</span>`;
                    }

                    // ── Columna ACCIONES (reactiva a estado) ─────────────────────
                    let btnExtra = '';
                    if (esAprobado) {
                        // Ya aprobó → APROBADO deshabilitado (sin botón quitar)
                        btnExtra = `<button class="btn btn-sm btn-light text-muted fw-bold" disabled title="Ya aprobó">
                                    <i class="bi bi-check2-all"></i> APROBADO
                                </button>`;
                    } else if (pendientes > 0) {
                        // Tiene intento pendiente → bloquear +EXTRA, mostrar -QUITAR
                        btnExtra = `
                        <button class="btn btn-sm btn-warning fw-bold" disabled
                            title="Ya tiene un intento habilitado sin presentar. Primero debe presentarlo.">
                            <i class="bi bi-lock-fill"></i> EXTRA
                        </button>
                        <button class="btn btn-sm btn-danger btn-quitar" 
                            onclick="confirmarQuitar(this, ${uId}, ${cId})"
                            title="Quitar el intento extra habilitado">
                            <span class="t-btn"><i class="bi bi-dash-circle-fill"></i> QUITAR</span>
                            <span class="spinner-border spinner-border-sm d-none"></span>
                        </button>`;
                    } else {
                        // Sin pendientes y no aprobado → +EXTRA habilitado
                        btnExtra = `<button class="btn btn-sm btn-warning fw-bold" 
                                    onclick="confirmarExtra(this, ${uId}, ${cId})">
                                    <span class="t-btn">+ EXTRA</span>
                                    <span class="spinner-border spinner-border-sm d-none"></span>
                                </button>`;
                    }

                    elActions.innerHTML = `
                    <button class="btn btn-sm btn-outline-info" onclick="verHistorial(${uId}, ${cId})" title="Ver historial">
                        <i class="bi bi-clock-history"></i>
                    </button>
                    ${btnExtra}`;

                } else {
                    // Error al cargar
                    elCount.innerHTML = `<i class="bi bi-question-circle text-muted"></i>`;
                    elPending.innerHTML = `<span class="badge-sin-pendiente">—</span>`;
                    elActions.innerHTML = `
                    <button class="btn btn-sm btn-outline-info" onclick="verHistorial(${uId}, ${cId})">
                        <i class="bi bi-clock-history"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary" disabled title="Error al cargar estado">
                        <i class="bi bi-exclamation-triangle"></i>
                    </button>`;
                }
            })
            .catch(() => {
                const elCount = document.getElementById(`attempt-count-${cId}`);
                const elPending = document.getElementById(`attempt-pending-${cId}`);
                const elActions = document.getElementById(`attempt-actions-${cId}`);
                if (elCount) elCount.innerHTML = `<i class="bi bi-exclamation-triangle text-danger"></i>`;
                if (elPending) elPending.innerHTML = `<span class="badge-sin-pendiente">—</span>`;
                if (elActions) elActions.innerHTML = `
                <button class="btn btn-sm btn-outline-info" onclick="verHistorial(${uId}, ${cId})">
                    <i class="bi bi-clock-history"></i>
                </button>
                <button class="btn btn-sm btn-secondary" disabled><i class="bi bi-wifi-off"></i></button>`;
            });
    }

    // ─── Ver historial en modal ───────────────────────────────────────────────────
    function verHistorial(uId, cId) {
        const content = document.getElementById('contentHistorial');
        content.innerHTML = '<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>';
        const myModal = new bootstrap.Modal(document.getElementById('modalHistorial'));
        myModal.show();

        fetch(`{{ route('asignacion.historial') }}?userId=${uId}&courseId=${cId}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success || data.conteo === 0) {
                    content.innerHTML = '<div class="p-4 text-center text-muted">Sin intentos previos</div>';
                    return;
                }

                // Resumen de estado
                const pendientes = data.pendientes ?? 0;
                const pendienteHTML = pendientes > 0 ?
                    `<div class="alert alert-success py-2 px-3 mb-0 small fw-bold">
                       <i class="bi bi-check-circle-fill me-1"></i>
                       El alumno tiene <strong>${pendientes}</strong> intento(s) habilitado(s) sin presentar.
                   </div>` :
                    `<div class="alert alert-secondary py-2 px-3 mb-0 small">
                       <i class="bi bi-dash-circle me-1"></i> Sin intentos pendientes por presentar.
                   </div>`;

                let table = `${pendienteHTML}
                         <table class="table table-sm table-striped mb-0 small">
                            <thead class="bg-light"><tr><th class="ps-3">No.</th><th>Fecha</th><th>Estado</th><th class="pe-3">Aciertos</th></tr></thead>
                            <tbody>`;
                data.intentos.forEach((att, i) => {
                    const fecha = new Date(att.timemodified * 1000).toLocaleString();
                    const grade = att.sumgrades ? parseFloat(att.sumgrades).toFixed(2) : '0.00';
                    const estado = att.state === 'finished' ? '<span class="badge bg-success">Finalizado</span>' : '<span class="badge bg-warning text-dark">En curso</span>';
                    table += `<tr><td class="ps-3">${i+1}</td><td>${fecha}</td><td>${estado}</td><td class="pe-3 fw-bold">${grade}</td></tr>`;
                });
                table += '</tbody></table>';
                content.innerHTML = table;
            });
    }

    // ─── Botón Inscribir ──────────────────────────────────────────────────────────
    document.getElementById('btnAsignar').addEventListener('click', function() {
        const cid = $('#selectEval').val();
        const gid = document.getElementById('selectGrupo').value;
        if (!cid || !gid) return showToast("Seleccione evaluación y grupo", "warning");

        toggleLoading('btnAsignar', true);
        fetch(`{{ route('asignacion.procesar') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            },
            body: JSON.stringify({
                userId: currentUserId,
                courseId: cid,
                groupId: gid
            })
        }).then(res => res.json()).then(data => {
            toggleLoading('btnAsignar', false);
            if (data.success) {
                Swal.fire('¡Éxito!', 'Inscripción procesada correctamente.', 'success');
                document.getElementById('btnBuscar').click();
            } else {
                showToast(data.message, "danger");
            }
        });
    });

    // ─── Confirmar intento extra ──────────────────────────────────────────────────
    function confirmarExtra(btn, uId, cId) {
        Swal.fire({
            title: '¿Autorizar Intento Extra?',
            text: "Se habilitará una oportunidad más para el estudiante.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'Sí, habilitar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const t = btn.querySelector('.t-btn');
                const s = btn.querySelector('.spinner-border');
                btn.disabled = true;
                t.classList.add('d-none');
                s.classList.remove('d-none');

                fetch(`{{ route('asignacion.reintentar') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{csrf_token()}}'
                    },
                    body: JSON.stringify({
                        userId: uId,
                        courseId: cId
                    })
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        Swal.fire('Habilitado', 'Intento extra concedido con éxito.', 'success');
                        document.getElementById('btnBuscar').click();
                    } else {
                        showToast(data.message, "danger");
                        btn.disabled = false;
                        t.classList.remove('d-none');
                        s.classList.add('d-none');
                    }
                });
            }
        });
    }

    // ─── Quitar intento extra ─────────────────────────────────────────────────────
    function confirmarQuitar(btn, uId, cId) {
        Swal.fire({
            title: '¿Quitar Intento Extra?',
            html: `Se <strong>eliminará</strong> el intento habilitado que aún no ha presentado el estudiante.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, quitar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const t = btn.querySelector('.t-btn');
                const s = btn.querySelector('.spinner-border');
                btn.disabled = true;
                t.classList.add('d-none');
                s.classList.remove('d-none');

                fetch(`{{ route('asignacion.quitarIntento') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{csrf_token()}}'
                    },
                    body: JSON.stringify({
                        userId: uId,
                        courseId: cId
                    })
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        Swal.fire('Retirado', data.mensaje || 'Intento extra eliminado correctamente.', 'success');
                        document.getElementById('btnBuscar').click();
                    } else {
                        showToast(data.message, 'danger');
                        btn.disabled = false;
                        t.classList.remove('d-none');
                        s.classList.add('d-none');
                    }
                });
            }
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────────
    function toggleLoading(id, load) {
        const btn = document.getElementById(id);
        const spin = btn.querySelector('.spinner-border');
        const icon = btn.querySelector('i') || btn.querySelector('span:first-child');
        btn.disabled = load;
        if (load) {
            spin.classList.remove('d-none');
            if (icon) icon.classList.add('d-none');
        } else {
            spin.classList.add('d-none');
            if (icon) icon.classList.remove('d-none');
        }
    }

    function resetUI() {
        document.getElementById('perfilUsuario').classList.add('d-none');
        document.getElementById('panelAsignacion').classList.add('d-none');
        document.getElementById('panelTablas').classList.add('d-none');
        document.getElementById('emptyState').classList.remove('d-none');
        // Resetear estado lazy
        inscritosCache = null;
        evaluacionesCargadas = false;
    }

    // Permitir buscar con Enter
    document.getElementById('searchInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') document.getElementById('btnBuscar').click();
    });
</script>
@endsection