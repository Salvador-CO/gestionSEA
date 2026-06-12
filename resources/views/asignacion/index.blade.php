@extends('layouts.app')

@section('content')
<!-- Librerías de soporte -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    /* Toasts Estilo Glassmorphism */
    .toast-container { position: fixed; top: 20px; right: 20px; z-index: 2000; }
    .custom-toast {
        min-width: 300px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px);
        border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); 
        border-left: 6px solid #6c757d; display: flex; align-items: center; padding: 15px; margin-bottom: 10px;
        transform: translateX(120%); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .custom-toast.show { transform: translateX(0); }
    .toast-success { border-left-color: #198754; }
    .toast-danger { border-left-color: #dc3545; }
    .toast-warning { border-left-color: #ffc107; }
    
    /* Optimización de Espacio */
    .card { border-radius: 15px; border: none; margin-bottom: 1rem; }
    .table-container-scroll { max-height: 300px; overflow-y: auto; }
     
    
    /* Botones y Inputs compactos */
    .btn-action { border-radius: 8px; transition: all 0.2s; }
    .form-control-sm, .form-select-sm { border-radius: 8px; }

    /* Estilo de la tabla de evaluaciones (La importante) */
    .table-evaluaciones { background-color: #fffdf7; }
    .table-evaluaciones thead { background-color: #fff3cd; color: #856404; }

    /* Animaciones */
    .fade-in-up { animation: fadeInUp 0.5s ease-out; }
    
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
                    <div class="input-group input-group-sm">
                        <input type="email" id="emailSearch" class="form-control" placeholder="Correo institucional...">
                        <button class="btn btn-primary" id="btnBuscar">
                            <i class="bi bi-search" id="btnIcon"></i>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Perfil compacto -->
            <div id="perfilUsuario" class="card shadow-sm d-none animate__animated animate__fadeIn">
                <div class="card-body p-3 text-center">
                    <img id="userFoto" src="" class="rounded-circle border mb-2 shadow-sm" width="80" height="80" style="object-fit: cover;">
                    <h6 id="userNombre" class="fw-bold mb-0 text-dark"></h6>
                    <p id="userEmail" class="text-muted mb-2" style="font-size: 0.75rem;"></p>
                    
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
            <div id="panelTablas" class="d-none">
                <div class="row g-3">
                    <!-- Tabla de Calificaciones (LA MÁS IMPORTANTE - ARRIBA) -->
                    <div class="col-12">
                        <div class="card shadow-sm border-top border-4 border-warning animate__animated animate__fadeInRight">
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
                                            <th class="text-end pe-3">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyEval" class="small-text"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Cursos Generales (MÁS PEQUEÑA) -->
                    <div class="col-12">
                        <div class="card shadow-sm animate__animated animate__fadeInUp">
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
                    </div>
                </div>
            </div>
            
            <!-- Estado Inicial -->
            <div id="emptyState" class="text-center py-5">
                <i class="bi bi-person-video2 display-1 text-light"></i>
                <h5 class="mt-3 text-muted">Gestión de Evaluaciones</h5>
                <p class="text-secondary small">Busque un estudiante para habilitar el panel de control.</p>
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

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'danger' ? 'bi-x-circle-fill' : 'bi-exclamation-triangle-fill');
    toast.className = `custom-toast toast-${type}`;
    toast.innerHTML = `<i class="bi ${icon} fs-5 me-3 text-${type}"></i><div class="flex-grow-1 text-dark small fw-bold">${message}</div>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 50);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 500); }, 4000);
}

document.getElementById('btnBuscar').addEventListener('click', function() {
    const email = document.getElementById('emailSearch').value;
    if(!email) return showToast("Escriba un correo", "warning");
    
    toggleLoading('btnBuscar', true);
    fetch(`{{ route('asignacion.validar') }}?email=${email}`)
        .then(res => res.json())
        .then(data => {
            toggleLoading('btnBuscar', false);
            if(!data.success) { 
                Swal.fire('No encontrado', data.message, 'error');
                resetUI(); 
                return; 
            }

            currentUserId = data.user.id;
            currentCentro = data.user.centro;
            
            document.getElementById('userNombre').innerText = data.user.nombre;
            document.getElementById('userEmail').innerText = data.user.email;
            document.getElementById('userCentro').innerText = data.user.centro;
            document.getElementById('userRol').innerText = data.user.rol;
            document.getElementById('userFoto').src = data.user.foto || 'https://www.gravatar.com/avatar/000?d=mp';
            
            document.getElementById('perfilUsuario').classList.remove('d-none');
            document.getElementById('panelAsignacion').classList.remove('d-none');
            document.getElementById('panelTablas').classList.remove('d-none');
            document.getElementById('emptyState').classList.add('d-none');

            // 1. Mapeamos los IDs de lo que el alumno ya tiene
            const inscritosIds = data.inscritos.map(c => parseInt(c.id));
            const selectEval = document.getElementById('selectEval'); // Definimos la variable
            let options = '<option value="">-- Seleccionar --</option>';

            // 2. Filtramos los cursos de evaluación
            data.cursosEval.forEach(c => {
                if (!inscritosIds.includes(parseInt(c.id))) {
                    options += `<option value="${c.id}">[${c.shortname}] ${c.fullname}</option>`;
                }
            });

            // 3. Insertamos las opciones
            selectEval.innerHTML = options;

            // 4. VALIDACIÓN EXTRA: Si el alumno ya está inscrito en todos los cursos que encontró el Service
            if (data.cursosEval.length > 0 && options === '<option value="">-- Seleccionar --</option>') {
                selectEval.innerHTML = '<option value="">Ya está inscrito en todas las evaluaciones</option>';
            }

            // 5. Actualizamos el resto de la vista
            renderTablas(data.inscritos);
            showToast("Datos actualizados");
        })
        .catch(err => { toggleLoading('btnBuscar', false); showToast("Error de conexión", "danger"); });
});

function renderTablas(inscritos) {
    let gen = ''; let ev = '';
    inscritos.forEach(c => {
        const displayName = `<b>${c.shortname}</b> | ${c.fullname}`;
        gen += `<tr><td class="ps-3">${displayName}</td><td>${c.id}</td><td class="text-end pe-3"><span class="text-success fw-bold">Activo</span></td></tr>`;
        
        if(c.fullname.toUpperCase().includes('EVAL')) {
            const nota = c.nota || 'N/A';
            const esAprobado = (nota !== 'N/A' && parseFloat(nota) >= 6);
            const colorClass = esAprobado ? 'badge bg-success' : 'badge bg-danger';
            
            // Lógica para el botón extra: Si ya aprobó, deshabilitamos el botón
            const btnExtra = esAprobado 
                ? `<button class="btn btn-sm btn-light text-muted fw-bold" disabled title="Ya aprobado">
                    <i class="bi bi-check2-all"></i> APROBADO
                   </button>`
                : `<button class="btn btn-sm btn-warning fw-bold" onclick="confirmarExtra(this, ${currentUserId}, ${c.id})">
                    <span class="t-btn">+ EXTRA</span>
                    <span class="spinner-border spinner-border-sm d-none"></span>
                   </button>`;

            ev += `<tr>
                <td class="ps-3">
                    <div class="fw-bold">${c.fullname}</div>
                    <small class="text-muted font-monospace" style="font-size:0.7rem">ID: ${c.id}</small>
                </td>
                <td class="text-center"><span class="${colorClass} px-3 py-2">${nota}</span></td>
                <td class="text-center" id="attempt-info-${c.id}"><div class="spinner-border spinner-border-sm text-light"></div></td>
                <td class="text-end pe-3">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-info" onclick="verHistorial(${currentUserId}, ${c.id})"><i class="bi bi-clock-history"></i></button>
                        ${btnExtra}
                    </div>
                </td>
            </tr>`;
            cargarConteoIndividual(currentUserId, c.id);
        }
    });
    document.getElementById('tbodyGeneral').innerHTML = gen || '<tr><td colspan="3" class="text-center py-2">Sin otros cursos</td></tr>';
    document.getElementById('tbodyEval').innerHTML = ev || '<tr><td colspan="4" class="text-center py-4">No tiene evaluaciones inscritas</td></tr>';
}

function cargarConteoIndividual(uId, cId) {
    fetch(`{{ route('asignacion.historial') }}?userId=${uId}&courseId=${cId}`)
        .then(res => res.json())
        .then(data => {
            const el = document.getElementById(`attempt-info-${cId}`);
            if(data.success) el.innerHTML = `<span class="fw-bold">${data.conteo}</span>`;
            else el.innerHTML = `<i class="bi bi-question-circle"></i>`;
        });
}

function verHistorial(uId, cId) {
    const content = document.getElementById('contentHistorial');
    content.innerHTML = '<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>';
    const myModal = new bootstrap.Modal(document.getElementById('modalHistorial'));
    myModal.show();

    fetch(`{{ route('asignacion.historial') }}?userId=${uId}&courseId=${cId}`)
        .then(res => res.json())
        .then(data => {
            if(!data.success || data.conteo === 0) {
                content.innerHTML = '<div class="p-4 text-center text-muted">Sin intentos previos</div>';
                return;
            }
            let table = `<table class="table table-sm table-striped mb-0 small">
                            <thead class="bg-light"><tr><th class="ps-3">No.</th><th>Fecha</th><th class="pe-3">Nota</th></tr></thead>
                            <tbody>`;
            data.intentos.forEach((att, i) => {
                const fecha = new Date(att.timemodified * 1000).toLocaleString();
                const grade = att.sumgrades ? parseFloat(att.sumgrades).toFixed(2) : '0.00';
                table += `<tr><td class="ps-3">${i+1}</td><td>${fecha}</td><td class="pe-3 fw-bold">${grade}</td></tr>`;
            });
            table += '</tbody></table>';
            content.innerHTML = table;
        });
}

document.getElementById('selectEval').addEventListener('change', function() {
    const cid = this.value;
    if(!cid) { document.getElementById('divGrupo').classList.add('d-none'); return; }
    document.getElementById('divGrupo').classList.remove('d-none');
    const spin = document.getElementById('loadGrupos');
    spin.classList.remove('d-none');
    
    fetch(`/registro/obtener-grupos/${cid}?centro=${encodeURIComponent(currentCentro)}`)
        .then(res => res.json())
        .then(data => {
            spin.classList.add('d-none');
            let h = '<option value="">-- Grupo --</option>';
            if(data.length === 0) {
                h = '<option value="">Sin grupos</option>';
                document.getElementById('btnAsignar').disabled = true;
            } else {
                data.forEach(g => h += `<option value="${g.id}">${g.name}</option>`);
                document.getElementById('btnAsignar').disabled = false;
            }
            document.getElementById('selectGrupo').innerHTML = h;
        });
});

document.getElementById('btnAsignar').addEventListener('click', function() {
    const cid = document.getElementById('selectEval').value;
    const gid = document.getElementById('selectGrupo').value;
    if(!cid || !gid) return showToast("Seleccione evaluación y grupo", "warning");
    
    toggleLoading('btnAsignar', true);
    fetch(`{{ route('asignacion.procesar') }}`, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{csrf_token()}}'},
        body: JSON.stringify({userId: currentUserId, courseId: cid, groupId: gid})
    }).then(res => res.json()).then(data => {
        toggleLoading('btnAsignar', false);
        if(data.success) { 
            Swal.fire('¡Éxito!', 'Inscripción procesada correctamente.', 'success');
            document.getElementById('btnBuscar').click();
        } else { showToast(data.message, "danger"); }
    });
});

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
            const t = btn.querySelector('.t-btn'); const s = btn.querySelector('.spinner-border');
            btn.disabled = true; t.classList.add('d-none'); s.classList.remove('d-none');
            
            fetch(`{{ route('asignacion.reintentar') }}`, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{csrf_token()}}'},
                body: JSON.stringify({userId: uId, courseId: cId})
            }).then(res => res.json()).then(data => {
                if(data.success) {
                    Swal.fire('Habilitado', 'Intento extra concedido con éxito.', 'success');
                    document.getElementById('btnBuscar').click();
                } else { 
                    showToast(data.message, "danger"); 
                    btn.disabled = false; t.classList.remove('d-none'); s.classList.add('d-none');
                }
            });
        }
    });
}

function toggleLoading(id, load) {
    const btn = document.getElementById(id);
    const spin = btn.querySelector('.spinner-border');
    const icon = btn.querySelector('i') || btn.querySelector('span:first-child');
    btn.disabled = load;
    if(load) { spin.classList.remove('d-none'); if(icon) icon.classList.add('d-none'); }
    else { spin.classList.add('d-none'); if(icon) icon.classList.remove('d-none'); }
}

function resetUI() {
    document.getElementById('perfilUsuario').classList.add('d-none');
    document.getElementById('panelAsignacion').classList.add('d-none');
    document.getElementById('panelTablas').classList.add('d-none');
    document.getElementById('emptyState').classList.remove('d-none');
}
</script>
@endsection