@extends('layouts.app')

@section('content')
<div class="card shadow col-md-10 mx-auto">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Registrar Nuevo Usuario</h4>
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-light btn-sm">Volver</a>
    </div>
    <div class="card-body">
        <div class="alert alert-secondary">
            <i class="bi bi-info-circle-fill"></i> Ingrese el correo institucional para buscar datos en Moodle automáticamente.
        </div>

        <form action="{{ route('usuarios.store') }}" method="POST" id="formUsuario">
            @csrf
            {{-- Campo oculto para el ID de Moodle --}}
            <input type="hidden" name="moodle_user_id" id="moodle_user_id">
            <input type="hidden" name="activo" value="1">

            <div class="row">
                <div class="col-md-12 mb-4">
                    <label class="form-label fw-bold">1. Validar Correo Electrónico</label>
                    <div class="input-group">
                        <input type="email" name="email" id="email" class="form-control" placeholder="usuario@cbachilleres.edu.mx" required>
                        <button type="button" class="btn btn-primary" id="btnVerificar">
                            <span id="spinner" class="spinner-border spinner-border-sm d-none"></span> 
                            <i class="bi bi-search"></i> Verificar en Moodle
                        </button>
                    </div>
                    <small id="feedback" class="form-text"></small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre(s)</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Apellido(s)</label>
                    <input type="text" name="apellido" id="apellido" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre de Usuario (Login)</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Centro / Plantel</label>
                    <input type="text" name="centro" id="centro" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contraseña para este sistema</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rol Asignado</label>
                    <select name="rol_id" class="form-select" required>
                        <option value="">Seleccione un rol...</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr>
            <div class="text-end">
                <button type="reset" class="btn btn-secondary">Limpiar Formulario</button>
                <button type="submit" class="btn btn-success px-5">
                    <i class="bi bi-save"></i> Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnVerificar = document.getElementById('btnVerificar');
    const feedback = document.getElementById('feedback');
    const spinner = document.getElementById('spinner');

    btnVerificar.addEventListener('click', function() {
        const email = document.getElementById('email').value;

        if (!email || !email.includes('@')) {
            alert('Por favor, ingrese un correo electrónico válido.');
            return;
        }

        // Bloquear botón y mostrar carga
        btnVerificar.disabled = true;
        spinner.classList.remove('d-none');
        feedback.className = 'form-text text-primary';
        feedback.innerText = 'Consultando plataforma Moodle...';

        // Petición AJAX al controlador
        fetch(`{{ route('usuarios.verificarMoodle') }}?email=${encodeURIComponent(email)}`)
            .then(response => response.json())
            .then(data => {
                btnVerificar.disabled = false;
                spinner.classList.add('d-none');

                if (data.success) {
                    feedback.className = 'form-text text-success';
                    feedback.innerHTML = '<strong>✔ Usuario encontrado.</strong> Datos cargados.';
                    
                    // Rellenar campos automáticamente
                    document.getElementById('nombre').value = data.user.firstname;
                    document.getElementById('apellido').value = data.user.lastname;
                    document.getElementById('username').value = data.user.username;
                    document.getElementById('centro').value = data.user.centro;
                    document.getElementById('moodle_user_id').value = data.user.moodle_id;
                } else {
                    feedback.className = 'form-text text-danger';
                    feedback.innerText = 'No se encontró el usuario en Moodle. Puede completar los datos manualmente.';
                }
            })
            .catch(error => {
                btnVerificar.disabled = false;
                spinner.classList.add('d-none');
                feedback.className = 'form-text text-danger';
                feedback.innerText = 'Error de conexión con el servidor.';
                console.error('Error:', error);
            });
    });
});
</script>
@endsection