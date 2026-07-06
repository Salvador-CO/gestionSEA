@extends('layouts.app')

@section('content')
<!-- Librerías de soporte visual -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        --info-gradient: linear-gradient(135deg, #0dcaf0 0%, #007bff 100%);
        --danger-soft: #fff5f5;
    }

    /* Toasts */
    .toast-container { position: fixed; top: 20px; right: 20px; z-index: 2000; }
    .custom-toast {
        min-width: 320px; background: rgba(255,255,255,0.98); backdrop-filter: blur(10px);
        border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        border-left: 6px solid #6c757d; display: flex; align-items: center; padding: 15px; margin-bottom: 10px;
        transform: translateX(120%); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .custom-toast.show { transform: translateX(0); }
    .toast-success { border-left-color: #198754; }
    .toast-danger  { border-left-color: #dc3545; }
    .toast-warning { border-left-color: #ffc107; }

    /* Cards */
    .card { border-radius: 15px; border: none; overflow: hidden; }
    .card-header { border-bottom: 1px solid rgba(0,0,0,0.05); }
    .form-control, .form-select { border-radius: 10px; border: 1px solid #dee2e6; padding: 0.6rem 0.75rem; }

    .tiny-text { font-size: 0.75rem; margin-top: 4px; font-weight: 600; display: block; }
    .btn-action { border-radius: 10px; font-weight: bold; transition: all 0.3s; }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .badge-contador { font-size: 0.9rem; padding: 0.5em 1em; }

    /* Lista de cursos asignados */
    .course-item {
        border-left: 4px solid #198754 !important;
        background-color: #f8fff9;
        margin-bottom: 5px;
        border-radius: 8px !important;
    }

    /* ── Stepper visual ──────────────────────────────────────────────────────── */
    .step-indicator { display: flex; align-items: center; margin-bottom: 1.5rem; }
    .step-item {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 20px; border-radius: 12px;
        font-size: 0.85rem; font-weight: 700;
        transition: all 0.35s ease;
        flex: 1; cursor: default; user-select: none;
    }
    .step-item.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white; box-shadow: 0 4px 15px rgba(102,126,234,0.35);
    }
    .step-item.done {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white; box-shadow: 0 4px 15px rgba(40,167,69,0.25);
    }
    .step-item.pending { background: #f0f2f5; color: #adb5bd; }
    .step-number {
        width: 30px; height: 30px; border-radius: 50%;
        background: rgba(255,255,255,0.3);
        display: flex; align-items: center; justify-content: center;
        font-weight: 900; font-size: 0.9rem; flex-shrink: 0;
    }
    .step-item.pending .step-number { background: rgba(0,0,0,0.08); }
    .step-arrow { font-size: 1.1rem; color: #dee2e6; margin: 0 10px; flex-shrink: 0; }
</style>

<div class="container-fluid py-4">
    <div id="toastContainer" class="toast-container"></div>

    <div class="row justify-content-center">
        <div class="col-xl-11 col-lg-12">

            <!-- ── Banner de Políticas (compacto con acceso al Buscador) ─────── -->
            <div class="card shadow-sm mb-4 border-start border-4 border-info animate__animated animate__fadeInDown">
                <div class="card-body d-flex align-items-center py-3">
                    <div class="icon-box me-3 bg-info bg-opacity-10 p-3 rounded-circle flex-shrink-0">
                        <i class="bi bi-shield-lock-fill fs-3 text-info"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold text-dark">
                            Gestión de Inscripciones — {{ $user->centro ?? 'Administración Global' }}
                        </h6>
                        <p class="small mb-0 text-muted">
                            <span class="badge bg-light text-dark border me-1">
                                <i class="bi bi-person me-1"></i>Usuario: minúsculas y números
                            </span>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-key me-1"></i>Pass: 8+ car, Mayús, Núm y Símbolo (*, ., -, #)
                            </span>
                        </p>
                    </div>
                    <a href="{{ route('registro.usuarios') }}"
                       class="btn btn-outline-primary btn-sm rounded-pill px-3 ms-3 flex-shrink-0">
                        <i class="bi bi-people-fill me-1"></i> Buscador
                    </a>
                </div>
            </div>

            <!-- ── Stepper Visual (Paso 1 → Paso 2) ─────────────────────────── -->
            <div class="step-indicator">
                <div class="step-item active" id="step1Indicator">
                    <div class="step-number">1</div>
                    <span>Registrar / Validar Alumno</span>
                </div>
                <div class="step-arrow"><i class="bi bi-chevron-right"></i></div>
                <div class="step-item pending" id="step2Indicator">
                    <div class="step-number">2</div>
                    <span>Asignar Cursos</span>
                </div>
            </div>

            <div class="row g-4">

                <!-- ══ COLUMNA IZQUIERDA: REGISTRO (col-lg-5) ══════════════════ -->
                <div class="col-lg-5">
                    <div class="card shadow-lg h-100 animate__animated animate__fadeInLeft">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-person-plus-fill text-primary me-2"></i>Paso 1 — Estudiante
                            </h5>
                        </div>
                        <div class="card-body p-4">

                            <!-- Validar Correo -->
                            <div id="step1" class="mb-4">
                                <label class="form-label small fw-bold text-uppercase text-muted">
                                    Correo Institucional
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-envelope text-primary"></i>
                                    </span>
                                    <input type="email" id="check_email"
                                        class="form-control border-start-0 shadow-none"
                                        placeholder="correo@ejemplo.com"
                                        value="{{ request('email') ?? old('email') }}">
                                    <button class="btn btn-primary px-4 fw-bold" id="btnCheckEmail">Validar</button>
                                </div>
                                <div id="emailFeedback" class="mt-2"></div>
                            </div>

                            <hr class="my-4 opacity-50">

                            <!-- Formulario Nuevo Usuario (oculto hasta correo libre) -->
                            <form action="{{ route('registro.store') }}" method="POST" id="formRegistro"
                                style="display:none;" onsubmit="return validarFormulario()"
                                class="animate__animated animate__fadeIn">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">CORREO DISPONIBLE</label>
                                        <input type="email" name="email" id="final_email"
                                            class="form-control bg-light fw-bold text-success border-success" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">USUARIO / MATRÍCULA</label>
                                        <input type="text" name="username" id="username"
                                            class="form-control" placeholder="ej: 26500289a"
                                            value="{{ request('matricula') ?? old('username') }}" required>
                                        <div id="userError" class="text-danger tiny-text" style="display:none;">
                                            <i class="bi bi-x-circle"></i> Mínimo 4 caracteres (letras minúsculas y números).
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-bold">NOMBRE(S)</label>
                                        <input type="text" name="firstname" class="form-control"
                                            value="{{ request('nombre')}}" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-bold">APELLIDOS</label>
                                        <input type="text" name="lastname" class="form-control"
                                            value="{{ request('apellidos')}}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">CONTRASEÑA</label>
                                        <div class="input-group">
                                            <input type="password" name="password" id="password"
                                                class="form-control" required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="bi bi-eye" id="eyeIcon"></i>
                                            </button>
                                        </div>
                                        <div id="passError" class="text-danger tiny-text" style="display:none;">
                                            <i class="bi bi-exclamation-triangle"></i> No cumple con las políticas de seguridad.
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-bold">ROL</label>
                                        <select name="rol" class="form-select">
                                            <option value="Estudiante">Estudiante</option>
                                            @if($isAdmin) <option value="DOCENTE">Docente</option> @endif
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-bold">COHORTE / CENTRO</label>
                                        <select name="cohort_id" class="form-select" required>
                                            @foreach($centrosDisponibles as $nombre => $id)
                                                <option value="{{ $id }}">{{ $nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit"
                                            class="btn btn-success w-100 py-3 fw-bold btn-action shadow"
                                            style="background: var(--success-gradient); border:none;">
                                            <i class="bi bi-person-check-fill me-2"></i>CREAR USUARIO Y CONTINUAR
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ══ COLUMNA DERECHA: ASIGNACIÓN DE CURSOS (col-lg-7) ════════ -->
                <div class="col-lg-7">

                    <!-- Panel de Cursos (aparece tras validar/crear) -->
                    <div id="sectionCursos" style="display:none;"
                        class="card shadow-lg border-0 h-100 animate__animated animate__fadeInRight">
                        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-journal-plus me-2"></i>Paso 2 — Asignar Cursos
                            </h6>
                            <span id="badgeContador" class="badge bg-primary badge-contador rounded-pill">0 / 8</span>
                        </div>
                        <div class="card-body p-4">

                            <!-- Alumno activo -->
                            <div class="user-preview mb-4 p-3 rounded-3 bg-light border">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                            style="width:45px;height:45px;">
                                            <i class="bi bi-person-fill fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 id="studentName" class="mb-0 fw-bold text-dark">-</h6>
                                        <span id="studentCentro" class="badge bg-info text-dark small-text">Centro</span>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" id="currentUserId">
                            <input type="hidden" id="currentUserCentro">

                            <!-- Selector de Cursos -->
                            <div id="cursoSelector">
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted mb-1">CURSO A INSCRIBIR</label>
                                    <select id="selectCurso" class="form-select shadow-sm"></select>
                                </div>

                                <div class="text-center my-3" id="spinnerGrupos" style="display:none;">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                    <span class="small ms-2 text-muted">Buscando grupos, espere...</span>
                                </div>

                                <div class="mb-4 animate__animated animate__fadeIn" id="groupWrapper" style="display:none;">
                                    <label class="small fw-bold text-muted mb-1">GRUPO CORRESPONDIENTE</label>
                                    <select id="selectGrupo" class="form-select border-primary shadow-sm"></select>
                                </div>

                                <button id="btnAddCourse" class="btn btn-primary w-100 py-2 fw-bold btn-action mb-4">
                                    <span id="btnText"><i class="bi bi-plus-circle me-1"></i> INSCRIBIR ESTE CURSO</span>
                                    <span id="btnLoading" style="display:none;">
                                        <span class="spinner-border spinner-border-sm"></span> Procesando...
                                    </span>
                                </button>
                            </div>

                            <!-- Lista de cursos asignados en esta sesión -->
                            <h6 class="small fw-bold text-muted mb-3">CURSOS ASIGNADOS EN ESTA SESIÓN:</h6>
                            <ul class="list-group list-group-flush border rounded-3 mb-4 bg-white shadow-sm" id="ulCursos">
                                <li id="liVacio" class="list-group-item text-center text-muted py-3 small">
                                    Ningún curso asignado aún
                                </li>
                            </ul>

                            <!-- Botón de finalizar (aparece tras primer inscripción) -->
                            <div id="finalizarWrapper" style="display:none;" class="animate__animated animate__fadeInUp">
                                <p class="tiny-text text-center text-success mb-2">
                                    <i class="bi bi-info-circle"></i> Puede finalizar ahora o agregar más cursos.
                                </p>
                                <button id="btnFinalizarTodo"
                                    class="btn btn-dark w-100 py-3 fw-bold btn-action shadow"
                                    onclick="confirmarSalida()">
                                    <i class="bi bi-check2-all me-2"></i>FINALIZAR Y CERRAR REGISTRO
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Estado Vacío (antes de validar) -->
                    <div id="emptyCursos"
                        class="card shadow-sm h-100 border-0 bg-light d-flex align-items-center justify-content-center p-5 text-center text-muted">
                        <i class="bi bi-person-check display-1 mb-3 opacity-25"></i>
                        <h6 class="fw-bold text-muted">Paso 2 disponible aquí</h6>
                        <p class="small">Valide el correo del alumno en el <strong>Paso 1</strong><br>para habilitar la asignación de cursos.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
let cursosInscritosContador = 0;
let cursosYaInscritosIds = [];

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

@if(session('success')) showToast("{{ session('success') }}", 'success'); @endif
@if(session('error')) showToast("{{ session('error') }}", 'danger'); @endif

// ── FIX: Guard null ───────────────────────────────────────────────────────────
// #togglePassword solo existe cuando el formulario nuevo usuario está visible.
// Si el alumno ya existe en Moodle, el form se oculta → null reference sin este guard.
const togglePwdBtn = document.getElementById('togglePassword');
if (togglePwdBtn) {
    togglePwdBtn.addEventListener('click', function() {
        const password = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
}

function validarFormulario() {
    const username = document.getElementById('username').value;
    const pass = document.getElementById('password').value;
    let valid = true;

    const userReg = /^[a-z0-9]{4,}$/;
    if (!userReg.test(username)) {
        document.getElementById('userError').style.display = 'block';
        valid = false;
    } else { document.getElementById('userError').style.display = 'none'; }

    const passReg = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._\-#]).{8,}$/;
    if (!passReg.test(pass)) {
        document.getElementById('passError').style.display = 'block';
        valid = false;
    } else { document.getElementById('passError').style.display = 'none'; }

    if (!valid) showToast("Revise los requisitos de seguridad", "warning");
    return valid;
}

// ── Actualizar Stepper visual ─────────────────────────────────────────────────
function activarStep(numero) {
    const s1 = document.getElementById('step1Indicator');
    const s2 = document.getElementById('step2Indicator');
    if (numero === 2) {
        s1.className = 'step-item done';
        s2.className = 'step-item active';
    } else {
        s1.className = 'step-item active';
        s2.className = 'step-item pending';
    }
}

// ── Validar correo ────────────────────────────────────────────────────────────
document.getElementById('btnCheckEmail').addEventListener('click', function() {
    const email = document.getElementById('check_email').value;
    const feedback = document.getElementById('emailFeedback');
    const btn = this;

    if (!email) return showToast("Ingrese un correo", "warning");

    btn.disabled = true;
    feedback.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div> <span class="small text-muted ms-2">Verificando...</span>';

    fetch(`{{ route('registro.validar') }}?email=${email}`)
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;

            if (data.exists) {
                if (data.acceso_denegado) {
                    feedback.innerHTML = `
                        <div class="alert alert-danger py-2 small fw-bold mb-0 animate__animated animate__shakeX">
                            <i class="bi bi-x-octagon-fill me-2"></i>${data.message}
                        </div>`;
                    document.getElementById('formRegistro').style.display = 'none';
                    document.getElementById('sectionCursos').style.display = 'none';
                    document.getElementById('emptyCursos').style.display = 'flex';
                    showToast("Acceso restringido: Centro no corresponde", "danger");
                } else {
                    feedback.innerHTML = `<div class="alert alert-info py-2 small fw-bold mb-0"><i class="bi bi-info-circle-fill me-2"></i>Usuario encontrado. Proceda a inscribir.</div>`;
                    document.getElementById('formRegistro').style.display = 'none';
                    // Doble candado: IDs ya inscritos para filtro JS (refuerzo del backend)
                    cursosYaInscritosIds = data.cursos_inscritos ? data.cursos_inscritos.map(id => parseInt(id)) : [];
                    iniciarFlujoCursos(data.moodle_id, data.nombre, data.centro);
                    showToast("Usuario localizado", "success");
                }
            } else {
                feedback.innerHTML = `<div class="alert alert-success py-2 small fw-bold mb-0"><i class="bi bi-check-circle-fill me-2"></i>Correo disponible, para inscribir.</div>`;
                document.getElementById('formRegistro').style.display = 'block';
                document.getElementById('final_email').value = email;
                document.getElementById('sectionCursos').style.display = 'none';
                document.getElementById('emptyCursos').style.display = 'flex';
                cursosYaInscritosIds = [];
            }
        })
        .catch(error => {
            btn.disabled = false;
            console.error(error);
            showToast("Error al conectar con el servidor", "danger");
        });
});

function iniciarFlujoCursos(userId, userName, centro) {
    document.getElementById('sectionCursos').style.display = 'block';
    document.getElementById('emptyCursos').style.display = 'none';
    document.getElementById('currentUserId').value = userId;
    document.getElementById('currentUserCentro').value = centro;
    document.getElementById('studentName').innerText = userName || 'Usuario sin nombre';
    document.getElementById('studentCentro').innerText = (centro && centro !== '') ? centro : 'GENERAL';
    activarStep(2);
    cargarCursos(userId);
}

function cargarCursos(userId) {
    fetch(`{{ route('registro.obtenerCursos') }}?userId=${userId}`)
        .then(res => res.json())
        .then(cursos => {
            let html = '<option value="">-- Seleccione curso --</option>';
            cursos.forEach(c => {
                const cursoIdInt = parseInt(c.id);
                // El backend filtra inscritos y EVALs. JS refuerza cursos de esta sesión.
                if (c.format !== 'site' && !cursosYaInscritosIds.includes(cursoIdInt)) {
                    html += `<option value="${c.id}">${c.fullname}</option>`;
                }
            });
            document.getElementById('selectCurso').innerHTML = html;
        });
}

document.getElementById('selectCurso').addEventListener('change', function() {
    const courseId = this.value;
    const centro   = document.getElementById('currentUserCentro').value;
    const wrapper  = document.getElementById('groupWrapper');
    const spinner  = document.getElementById('spinnerGrupos');

    if (!courseId) { wrapper.style.display = 'none'; return; }
    wrapper.style.display = 'none';
    spinner.style.display = 'block';

    fetch(`/registro/obtener-grupos/${courseId}?centro=${encodeURIComponent(centro)}`)
        .then(res => res.json())
        .then(grupos => {
            spinner.style.display = 'none';
            const selectGrupo = document.getElementById('selectGrupo');
            if (Array.isArray(grupos) && grupos.length > 0) {
                wrapper.style.display = 'block';
                let html = '<option value="">-- Seleccione grupo --</option>';
                grupos.forEach(g => {
                    const totalPart  = g.total_participantes !== undefined ? g.total_participantes : 0;
                    const asesorName = g.nombre_asesor ? g.nombre_asesor : 'Por asignar';
                    html += `<option value="${g.id}">${g.name} (${totalPart} | Asesor: ${asesorName})</option>`;
                });
                selectGrupo.innerHTML = html;
            } else {
                selectGrupo.innerHTML = '<option value="">Sin grupos en este centro</option>';
                wrapper.style.display = 'block';
            }
        });
});

document.getElementById('btnAddCourse').addEventListener('click', function() {
    if (cursosInscritosContador >= 8) return;

    const userId     = document.getElementById('currentUserId').value;
    const courseId   = document.getElementById('selectCurso').value;
    const groupId    = document.getElementById('selectGrupo').value;
    const select     = document.getElementById('selectCurso');
    const courseText = select.options[select.selectedIndex].text;

    if (!courseId) return showToast("Seleccione un curso", "warning");

    document.getElementById('btnText').style.display    = 'none';
    document.getElementById('btnLoading').style.display = 'inline-block';
    this.disabled = true;

    fetch(`{{ route('registro.inscribir') }}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ userId, courseId, groupId })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('btnText').style.display    = 'inline-block';
        document.getElementById('btnLoading').style.display = 'none';
        this.disabled = false;

        if (data.success) {
            if (cursosInscritosContador === 0) document.getElementById('liVacio').remove();

            cursosInscritosContador++;
            document.getElementById('badgeContador').innerText = `${cursosInscritosContador} / 8`;

            const li = document.createElement('li');
            li.className = "list-group-item d-flex justify-content-between align-items-center small course-item animate__animated animate__fadeInLeft";
            li.innerHTML = `<span><i class="bi bi-check-circle-fill text-success me-2"></i> ${courseText}</span>`;
            document.getElementById('ulCursos').appendChild(li);

            document.getElementById('finalizarWrapper').style.display = 'block';
            showToast("Inscrito correctamente", "success");

            // Remover del select para evitar re-inscripción en esta sesión
            select.remove(select.selectedIndex);
            select.value = "";
            document.getElementById('groupWrapper').style.display = 'none';

            if (cursosInscritosContador >= 8) {
                document.getElementById('cursoSelector').style.display = 'none';
                showToast("Se alcanzó el máximo de cursos", "info");
            }
        } else {
            showToast(data.message, "danger");
        }
    });
});

function confirmarSalida() {
    Swal.fire({
        title: '¿Finalizar proceso?',
        text: `Has inscrito ${cursosInscritosContador} curso(s). Se cerrará la sesión actual de registro.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#343a40',
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Agregar más'
    }).then((result) => {
        if (result.isConfirmed) { location.reload(); }
    });
}

@if(session('nuevo_usuario_id'))
    iniciarFlujoCursos(
        "{{ session('nuevo_usuario_id') }}",
        "{{ session('nuevo_usuario_nombre') }}",
        "{{ session('nuevo_usuario_centro') }}"
    );
@endif

document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('check_email');
    if (emailInput.value.length > 5) {
        document.getElementById('btnCheckEmail').click();
    }
});
</script>
@endsection