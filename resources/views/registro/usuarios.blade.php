@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Card de Buscador Principal -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
            <div>
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-search text-primary me-2"></i>Buscador de Estudiantes Digital</h5>
                <p class="mb-0 text-muted small">
                    <i class="bi bi-building"></i> Tu Centro actual: 
                    <span class="badge bg-info text-white">{{ $user->centro ?? 'Administración Global' }}</span>
                </p>
            </div>
            <a href="{{ route('registro.index') }}" class="btn btn-primary shadow-sm fw-bold">
                <i class="bi bi-person-plus-fill"></i> Ir a Panel de Registro
            </a>
        </div>
        <div class="card-body bg-light">
            <!-- Forzamos la desactivación del submit tradicional con onsubmit -->
            <form id="formBusqueda" onsubmit="ejecutarBusquedaAsincrona(event);" class="row g-3 justify-content-center py-3">
                @csrf
                <div class="col-md-7">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person-badge"></i></span>
                        <input type="text" id="inputCriterio" class="form-control border-start-0" placeholder="Buscar por Nombre, Apellidos, Matrícula o Correo..." required>
                        <button type="submit" id="btnBuscarSubmit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-search me-1"></i> Buscar
                        </button>
                    </div>
                    <div class="form-text text-center mt-2">Introduce los datos completos o parciales del estudiante a consultar.</div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor del Estado Visual de Espera -->
    <div id="statusLoader" class="text-center my-5 d-none">
        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <h5 class="fw-bold text-dark" id="loaderMessage">Buscando coincidencia de datos...</h5>
        <p class="text-muted small">Por favor espere un momento.</p>
    </div>

    <!-- Alerta de Respuesta del Servidor -->
    <div id="alertMessage" class="alert d-none shadow-sm" role="alert"></div>

    <!-- Contenedor Principal de Resultados Estructurados -->
    <div id="contenedorResultados" class="d-none">
        <div class="row">
            <!-- SECCIÓN 1: DATOS PERSONALES -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-dark text-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-card-heading me-2"></i>Sección: Datos Personales</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div id="avatarEstudiante" class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px; font-size: 2rem; font-weight: bold;"></div>
                            <h4 id="txtNombreCompleto" class="fw-bold mt-3 mb-1 text-dark"></h4>
                            <span id="badgeCentro" class="badge rounded-pill bg-light text-dark border px-3"></span>
                        </div>
                        <hr class="text-muted">
                        <table class="table table-borderless align-middle mb-0">
                            <tr>
                                <td class="text-muted py-2" style="width: 35%;"><i class="bi bi-hash me-2"></i>Matrícula:</td>
                                <td id="txtMatricula" class="fw-bold text-primary"></td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2"><i class="bi bi-envelope me-2"></i>Correo:</td>
                                <td id="txtCorreo" class="text-break"></td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2"><i class="bi bi-shield-check me-2"></i>Rol Moodle:</td>
                                <td id="txtRol" class="text-capitalize"></td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2"><i class="bi bi-clock-history me-2"></i>Último Acceso:</td>
                                <td id="txtAcceso"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer bg-white border-top-0 py-3 text-center">
                        <button id="btnInscribirDirecto" class="btn btn-outline-primary btn-sm fw-bold w-100">
                            <i class="bi bi-journal-plus me-1"></i> Inscribir a un Nuevo Curso
                        </button>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: DATOS ACADÉMICOS -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-secondary text-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-mortarboard-fill me-2"></i>Sección: Datos Académicos</h6>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3">Cursos inscritos actualmente y grupos asignados:</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border" id="tableAcademic">
                                <thead class="table-light">
                                    <tr>
                                        <th class="py-2">Nombre del Curso</th>
                                        <th>Código / Corto</th>
                                        <th>Grupo(s) Asignado(s)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyCursos">
                                    <!-- Dinámico por JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mantenemos JQuery abajo por si tus layouts lo usan, pero la búsqueda correrá con JS Nativo -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function ejecutarBusquedaAsincrona(event) {
    // 1. Evitamos por completo que la página intente recargarse
    event.preventDefault();
    
    const criterioInput = document.getElementById('inputCriterio');
    const criterio = criterioInput ? criterioInput.value.trim() : '';
    
    if (!criterio) return false;

    // 2. Elementos del DOM con JS Nativo para evitar fallos de librerías
    const btnSubmit = document.getElementById('btnBuscarSubmit');
    const contenedorResultados = document.getElementById('contenedorResultados');
    const alertMessage = document.getElementById('alertMessage');
    const statusLoader = document.getElementById('statusLoader');
    const loaderMessage = document.getElementById('loaderMessage');

    // 3. Estado visual de inicio de carga
    if (btnSubmit) btnSubmit.disabled = true;
    if (contenedorResultados) contenedorResultados.classList.add('d-none');
    if (alertMessage) {
        alertMessage.classList.add('d-none');
        alertMessage.classList.remove('alert-warning', 'alert-danger', 'alert-success');
    }
    if (statusLoader) statusLoader.classList.remove('d-none');
    if (loaderMessage) loaderMessage.innerText = 'Buscando coincidencia de datos...';

    // 4. Petición HTTP usando Fetch API (Nativo del Navegador)
    fetch('/registro/buscar-estudiante', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ query: criterio })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor (Código: ' + response.status + ')');
        }
        return response.json();
    })
    .then(data => {
        if (btnSubmit) btnSubmit.disabled = false;

        if (data.success && data.usuarios && data.usuarios.length > 0) {
            let estudiante = data.usuarios[0];
            
            if (loaderMessage) {
                loaderMessage.innerHTML = 'Espere, estamos consultando la información completa de <br><span class="text-primary">' + estudiante.fullname + '</span>';
            }

            setTimeout(function() {
                if (statusLoader) statusLoader.classList.add('d-none');
                
                // Mapeo de datos personales
                let inicial = estudiante.firstname ? estudiante.firstname.substring(0, 1).toUpperCase() : 'U';
                document.getElementById('avatarEstudiante').innerText = inicial;
                document.getElementById('txtNombreCompleto').innerText = estudiante.fullname;
                document.getElementById('badgeCentro').innerHTML = '<i class="bi bi-geo-alt-fill text-danger me-1"></i>' + estudiante.centro;
                document.getElementById('txtMatricula').innerText = estudiante.username;
                document.getElementById('txtCorreo').innerText = estudiante.email;
                document.getElementById('txtRol').innerText = estudiante.rol;
                
                // Formateo de fecha de acceso
                if (estudiante.lastaccess > 0) {
                    let date = new Date(estudiante.lastaccess * 1000);
                    let fechaFormateada = date.getDate().toString().padStart(2, '0') + '/' + 
                                         (date.getMonth()+1).toString().padStart(2, '0') + '/' + 
                                         date.getFullYear() + ' ' + 
                                         date.getHours().toString().padStart(2, '0') + ':' + 
                                         date.getMinutes().toString().padStart(2, '0');
                    document.getElementById('txtAcceso').innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> ' + fechaFormateada + '</span>';
                } else {
                    document.getElementById('txtAcceso').innerHTML = '<span class="badge bg-warning text-dark">Sin actividad</span>';
                }

                // Mapeo de Cursos y Grupos Académicos
                let htmlCursos = '';
                if (estudiante.cursos && estudiante.cursos.length > 0) {
                    estudiante.cursos.forEach(function(curso) {
                        let badgesGrupos = '';
                        curso.grupos.forEach(function(gName) {
                            let badgeClass = gName.includes('Sin grupo') ? 'bg-light text-muted border' : 'bg-primary text-white';
                            badgesGrupos += '<span class="badge ' + badgeClass + ' me-1 mb-1 d-inline-block">' + gName + '</span>';
                        });

                        htmlCursos += '<tr>' +
                            '<td><div class="fw-bold text-dark">' + curso.fullname + '</div></td>' +
                            '<td><code class="text-secondary">' + curso.shortname + '</code></td>' +
                            '<td>' + badgesGrupos + '</td>' +
                        '</tr>';
                    });
                } else {
                    htmlCursos = '<tr><td colspan="3" class="text-center text-muted py-3">Este estudiante no se encuentra inscrito en ningún curso actualmente.</td></tr>';
                }
                
                document.getElementById('tbodyCursos').innerHTML = htmlCursos;
                
                // Acción del botón Inscribir Directo
                const btnInscribir = document.getElementById('btnInscribirDirecto');
                if (btnInscribir) {
                    btnInscribir.onclick = function(e) {
                        e.preventDefault();
                        window.location.href = "{{ route('registro.index') }}";
                    };
                }

                if (contenedorResultados) contenedorResultados.classList.remove('d-none');
            }, 1200);

        } else {
            if (statusLoader) statusLoader.classList.add('d-none');
            if (alertMessage) {
                alertMessage.innerText = data.message || 'No se obtuvieron resultados válidos.';
                alertMessage.classList.add('alert-warning');
                alertMessage.classList.remove('d-none');
            }
        }
    })
    .catch(error => {
        if (btnSubmit) btnSubmit.disabled = false;
        if (statusLoader) statusLoader.classList.add('d-none');
        if (alertMessage) {
            alertMessage.innerText = 'Error de conexión: ' + error.message;
            alertMessage.classList.add('alert-danger');
            alertMessage.classList.remove('d-none');
        }
    });
}
</script>

<style>
    .table th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge { font-weight: 600; }
</style>
@endsection