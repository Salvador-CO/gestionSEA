@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .card {
        border-radius: 15px;
        border: none;
        overflow: hidden;
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    /* Toast */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 2000;
    }

    .custom-toast {
        min-width: 320px;
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
        border-left-color: #fff8e1;
    }

    /* Estilos para el reporte renderizado */
    .question-box {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        background: #fff;
        box-shadow: 0 4px 6px rgba(46, 42, 42, 0.02);
    }

    .status-correct {
        border-left: 5px solid #198754;
    }

    .status-incorrect {
        border-left: 5px solid #dc3545;
    }

    .status-partial {
        border-left: 5px solid #fff8e1;
    }

    /* Ocultar la bandera y otros íconos que abultan mucho */
    .moodle-feedback-container .questionflag {
        display: none !important;
    }

    /* Ocultar elementos técnicos y comentarios para TODOS siempre */
    .moodle-feedback-container .im-controls,
    .moodle-feedback-container .comment,
    .moodle-feedback-container .makecomment {
        display: none !important;
    }

    @if( !$isAdminOrJefe)

    /* SEGURIDAD: Ocultar texto de pregunta y opciones de respuesta para asesores */
    .moodle-feedback-container .qtext,
    .moodle-feedback-container .ablock,
    .moodle-feedback-container .answer {
        display: none !important;
    }

    /* Deshabilitar clics en cualquier enlace dentro del feedback para no administradores */
    .moodle-feedback-container a {
        pointer-events: none;
        cursor: default;
        text-decoration: none;
        color: inherit;
    }

    @endif

    /* Mostrar solo la retroalimentación */
    .moodle-feedback-container .outcome,
    .moodle-feedback-container .specificfeedback,
    .moodle-feedback-container .generalfeedback,
    .moodle-feedback-container .rightanswer {
        display: block !important;
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-top: 10px;
        font-size: 0.95rem;
        color: #333;
        border-left: 4px solid #0dcaf0;
    }

    /* Reglas de Seguridad por Rol (Si NO es admin o jefe) */
    @if( !$isAdminOrJefe) .moodle-feedback-container .rightanswer {
        display: none !important;
    }

    @endif

    /* Evitar que el texto del acordeón cambie a azul al abrirse */
    .accordion-button:not(.collapsed) {
        color: inherit !important;
    }

    /* Colores suaves para los encabezados de pregunta */
    .header-correct { background-color: #e8f5e9 !important; }
    .header-incorrect { background-color: #ffebee !important; }
    .header-partial { background-color: #fff8e1 !important; }

    /* Estilos de impresión (Exportar a PDF) */
    @media print {
        body * {
            visibility: hidden;
        }

        .print-area,
        .print-area * {
            visibility: visible;
        }

        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .no-print {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .accordion-item {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            margin-bottom: 20px;
            break-inside: avoid;
        }

        /* Forzar que los colapsables se abran al imprimir */
        .collapse:not(.show) {
            display: block !important;
            height: auto !important;
        }

        .accordion-button::after {
            display: none !important;
        }
    }
</style>

<div class="container-fluid py-4">
    <div id="toastContainer" class="toast-container"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-search text-primary me-2"></i>Revisión Detallada de Exámenes</h3>
            <p class="text-muted small">Busca a un alumno y revisa la retroalimentación de sus intentos sin comprometer las preguntas.</p>
        </div>
        <a href="{{ route('calificaciones.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver a Calificaciones
        </a>
    </div>

    <div class="row g-4">
        <!-- Panel Izquierdo: Buscador y Filtros -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100 border-top border-4 border-primary">
                <div class="card-body p-4">

                    <!-- PASO 1: Buscar Usuario -->
                    <h6 class="fw-bold text-uppercase text-muted mb-3 small"><i class="bi bi-1-circle-fill text-primary me-1"></i> Paso 1: Buscar Alumno</h6>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white"><i class="bi bi-person-badge"></i></span>
                        <input type="text" id="inputCriterio" class="form-control" placeholder="Matrícula o Correo..." required>
                        <button type="button" id="btnBuscar" class="btn btn-primary fw-bold">Buscar</button>
                    </div>

                    <div id="userInfo" class="p-3 bg-light rounded-3 border mb-4 d-none animate__animated animate__fadeIn">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:45px;height:45px;">
                                <i class="bi bi-person-fill fs-4"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 id="studentName" class="mb-0 fw-bold text-dark">-</h6>
                                <span id="studentMatricula" class="small text-muted d-block"></span>
                                <span id="studentCentro" class="badge bg-info text-dark mt-1">Centro</span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="currentUserId">

                    <!-- PASO 2: Seleccionar Evaluación -->
                    <div id="panelEvaluacion" class="d-none animate__animated animate__fadeIn">
                        <hr class="my-4 opacity-25">
                        <h6 class="fw-bold text-uppercase text-muted mb-3 small"><i class="bi bi-2-circle-fill text-primary me-1"></i> Paso 2: Evaluación e Intento</h6>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Exámenes del Alumno</label>
                            <select id="selectExamen" class="form-select border-primary shadow-sm">
                                <option value="">-- Primero busque un alumno --</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Intento Finalizado</label>
                            <select id="selectIntento" class="form-select shadow-sm" disabled>
                                <option value="">-- Seleccione un examen arriba --</option>
                            </select>
                        </div>

                        <button id="btnVerRevision" class="btn btn-success w-100 py-2 fw-bold shadow" disabled>
                            <i class="bi bi-eye me-1"></i> CARGAR REVISIÓN DETALLADA
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Panel Derecho: Resultados (Retroalimentación) -->
        <div class="col-lg-8 print-area">
            <div class="card shadow-sm h-100 bg-light border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Desglose de Resultados</h6>
                    <div class="d-flex align-items-center gap-2">
                        <button id="btnImprimir" class="btn btn-sm btn-outline-secondary d-none no-print" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Descargar PDF
                        </button>
                        <span id="badgeCalificacion" class="badge bg-dark d-none fs-6">Nota: -</span>
                    </div>
                </div>

                <div class="card-body p-4" style="max-height: 75vh; overflow-y: auto;">

                    <!-- Estado Vacío -->
                    <div id="emptyResultados" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted opacity-50 py-5">
                        <i class="bi bi-file-earmark-bar-graph display-1 mb-3"></i>
                        <h5>Resultados aparecerán aquí</h5>
                        <p class="small text-center">Complete el paso 1 y 2 para cargar la retroalimentación de la evaluación.</p>
                    </div>

                    <!-- Loader -->
                    <div id="loaderResultados" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
                        <div class="mt-3 fw-bold text-muted">Consultando plataforma, por favor espere...</div>
                    </div>

                    <!-- Contenedor Dinámico de Preguntas (Acordeón) -->
                    <div id="contenedorPreguntas" class="d-none accordion"></div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

    // 1. Buscar Usuario
    document.getElementById('btnBuscar').addEventListener('click', function() {
        const criterio = document.getElementById('inputCriterio').value.trim();
        if (!criterio) return showToast('Ingrese matrícula o correo', 'warning');

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch('{{ route("calificaciones.api.buscarUsuario") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    query: criterio
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Buscar';

                if (data.success) {
                    showToast('Usuario encontrado', 'success');

                    // Mostrar Info
                    const u = data.usuario;
                    document.getElementById('currentUserId').value = u.moodle_id;
                    document.getElementById('studentName').innerText = u.firstname + ' ' + u.lastname;
                    document.getElementById('studentMatricula').innerText = u.username + ' | ' + u.email;
                    document.getElementById('studentCentro').innerText = u.centro;
                    document.getElementById('userInfo').classList.remove('d-none');

                    // Habilitar Panel 2 y limpiar todo
                    document.getElementById('panelEvaluacion').classList.remove('d-none');
                    limpiarResultados();
                    cargarExamenes(u.moodle_id);
                } else {
                    showToast(data.message || 'Usuario no encontrado', 'danger');
                    document.getElementById('userInfo').classList.add('d-none');
                    document.getElementById('panelEvaluacion').classList.add('d-none');
                    limpiarResultados();
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Buscar';
                showToast('Error de conexión', 'danger');
            });
    });

    // 2. Cargar Exámenes
    function cargarExamenes(userId) {
        const sel = document.getElementById('selectExamen');
        sel.innerHTML = '<option value="">Cargando exámenes...</option>';
        sel.disabled = true;

        document.getElementById('selectIntento').innerHTML = '<option value="">-- Seleccione un examen arriba --</option>';
        document.getElementById('selectIntento').disabled = true;
        document.getElementById('btnVerRevision').disabled = true;

        fetch(`/calificaciones/api/examenes/${userId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let html = '<option value="">-- Seleccione un examen --</option>';
                    data.quizzes.forEach(q => {
                        html += `<option value="${q.id}">${q.name}</option>`;
                    });
                    sel.innerHTML = html;
                    sel.disabled = false;
                } else {
                    sel.innerHTML = `<option value="">${data.message}</option>`;
                }
            });
    }

    // 3. Cargar Intentos al seleccionar examen
    document.getElementById('selectExamen').addEventListener('change', function() {
        const quizId = this.value;
        const userId = document.getElementById('currentUserId').value;
        const selIntento = document.getElementById('selectIntento');
        const btnRevision = document.getElementById('btnVerRevision');

        limpiarResultados();

        if (!quizId) {
            selIntento.innerHTML = '<option value="">-- Seleccione un examen arriba --</option>';
            selIntento.disabled = true;
            btnRevision.disabled = true;
            return;
        }

        selIntento.innerHTML = '<option value="">Cargando intentos...</option>';
        selIntento.disabled = true;
        btnRevision.disabled = true;

        fetch(`/calificaciones/api/intentos/${quizId}/${userId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let html = '<option value="">-- Seleccione un intento --</option>';
                    data.attempts.forEach((att, index) => {
                        const date = new Date(att.timefinish * 1000).toLocaleString();
                        html += `<option value="${att.id}">Intento ${index + 1} - Finalizado: ${date}</option>`;
                    });
                    selIntento.innerHTML = html;
                    selIntento.disabled = false;
                } else {
                    selIntento.innerHTML = `<option value="">${data.message}</option>`;
                }
            });
    });

    document.getElementById('selectIntento').addEventListener('change', function() {
        document.getElementById('btnVerRevision').disabled = !this.value;
    });

    // 4. Cargar Revisión
    document.getElementById('btnVerRevision').addEventListener('click', function() {
        const attemptId = document.getElementById('selectIntento').value;
        if (!attemptId) return;

        document.getElementById('emptyResultados').classList.add('d-none');
        document.getElementById('contenedorPreguntas').classList.add('d-none');
        document.getElementById('loaderResultados').classList.remove('d-none');
        document.getElementById('badgeCalificacion').classList.add('d-none');

        fetch(`/calificaciones/api/revision/${attemptId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('loaderResultados').classList.add('d-none');

                if (data.success) {
                    renderizarRevision(data.review.questions);
                    if (data.review.grade) {
                        const b = document.getElementById('badgeCalificacion');
                        b.innerText = 'Calificación: ' + parseFloat(data.review.grade).toFixed(2);
                        b.classList.remove('d-none');
                    }
                } else {
                    document.getElementById('emptyResultados').classList.remove('d-none');
                    showToast(data.message, 'danger');
                }
            })
            .catch(err => {
                document.getElementById('loaderResultados').classList.add('d-none');
                document.getElementById('emptyResultados').classList.remove('d-none');
                showToast('Error cargando revisión', 'danger');
            });
    });

    function renderizarRevision(questions) {
        const contenedor = document.getElementById('contenedorPreguntas');
        contenedor.innerHTML = '';

        if (!questions || questions.length === 0) {
            contenedor.innerHTML = '<div class="alert alert-info">No se encontraron preguntas en este intento.</div>';
        } else {
            questions.forEach((q, index) => {
                let estadoClase = 'status-partial';
                let headerClase = 'header-partial';
                let icon = 'bi-dash-circle text-warning';
                let badgeStyle = 'text-dark';
                let esCorrecta = false;

                if (q.state.toLowerCase().includes('correct') && !q.state.toLowerCase().includes('incorrect')) {
                    estadoClase = 'status-correct';
                    headerClase = 'header-correct';
                    icon = 'bi-check-circle-fill text-success';
                    esCorrecta = true;
                } else if (q.state.toLowerCase().includes('incorrect')) {
                    estadoClase = 'status-incorrect';
                    headerClase = 'header-incorrect';
                    icon = 'bi-x-circle-fill text-danger';
                }

                // Evaluar los puntos para pintar el badge de forma dinámica
                let pt = parseFloat(q.mark);
                if (!isNaN(pt)) {
                    if (pt === 0) {
                        badgeStyle = 'text-danger fw-bold';
                    } else if (pt > 0) {
                        badgeStyle = 'text-success fw-bold';
                    }
                }

                // Si es correcta, solo mostrar un mensaje compacto sin todo el HTML (a menos que sea admin, si quieres, 
                // pero para hacerla fácil de visualizar como se solicitó, la comprimiremos)
                let contenidoCuerpo = '';

                if (esCorrecta) {
                    contenidoCuerpo = `
                    <div class="text-success fw-bold p-3 bg-white rounded">
                        <i class="bi bi-hand-thumbs-up-fill me-2"></i>¡Respuesta Correcta! No hay retroalimentación pendiente.
                    </div>
                `;
                } else {
                    contenidoCuerpo = `
                    <div class="moodle-feedback-container">
                        ${q.html}
                    </div>
                `;
                }

                // Inyectar HTML estilo Acordeón
                const cardHtml = `
                <div class="accordion-item ${estadoClase} animate__animated animate__fadeInUp border rounded shadow-sm mb-3 overflow-hidden" style="animation-delay: ${index * 0.05}s">
                    <h2 class="accordion-header" id="heading${index}">
                        <button class="accordion-button collapsed ${headerClase}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${index}" aria-expanded="false" aria-controls="collapse${index}">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <span class="fw-bold fs-6"><i class="bi ${icon} me-2 fs-5 align-middle"></i>Pregunta ${q.number}</span>
                                <span class="badge bg-white ${badgeStyle} border shadow-sm fs-6">Puntos: ${q.mark} / ${q.maxmark}</span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse${index}" class="accordion-collapse collapse" aria-labelledby="heading${index}">
                        <div class="accordion-body p-3 bg-white">
                            ${contenidoCuerpo}
                        </div>
                    </div>
                </div>
            `;
                contenedor.insertAdjacentHTML('beforeend', cardHtml);
            });

            // Asegurar que cualquier enlace de Moodle se abra en nueva pestaña
            const links = contenedor.querySelectorAll('a');
            links.forEach(a => a.setAttribute('target', '_blank'));
        }

        contenedor.classList.remove('d-none');
        document.getElementById('btnImprimir').classList.remove('d-none');
    }

    function limpiarResultados() {
        document.getElementById('emptyResultados').classList.remove('d-none');
        document.getElementById('contenedorPreguntas').classList.add('d-none');
        document.getElementById('contenedorPreguntas').innerHTML = '';
        document.getElementById('badgeCalificacion').classList.add('d-none');
        document.getElementById('btnImprimir').classList.add('d-none');
    }

    // Permitir presionar Enter en el input
    document.getElementById('inputCriterio').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') document.getElementById('btnBuscar').click();
    });
</script>
@endsection