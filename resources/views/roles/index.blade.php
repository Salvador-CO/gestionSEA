@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="bi bi-plus-circle me-2"></i> Generador de Módulos Automático
                </div>
                <div class="card-body">
                    <form action="/crear-modulo" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre de la Categoría/Módulo</label>
                            <label>escribe: Nombre en singular y con la primera mayúscula.</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Producto, Cliente, Reporte" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-magic me-1"></i> Crear Módulo Completo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-shield-check me-2"></i> Gestión de Roles y Permisos</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoRol">
                <i class="bi bi-plus-lg me-1"></i> Agregar Nuevo Rol
            </button>
        </div>
        <div class="card-body">
            <form action="/roles/update" method="POST" id="formSeguridad">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-center align-middle">
                        <thead>
                            <tr class="table-secondary">
                                <th class="text-start">Permiso / Módulo</th>
                                @foreach($roles as $rol)
                                    <th>{{ $rol->nombre }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permisos as $permiso)
                            <tr>
                                <td class="text-start">
                                    <span class="badge bg-light text-dark border">{{ $permiso->nombre }}</span>
                                </td>
                                @foreach($roles as $rol)
                                    <td>
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="roles[{{ $rol->id }}][]" 
                                                   value="{{ $permiso->id }}"
                                                   {{ $rol->permisos->contains($permiso->id) ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Guardar Cambios de Seguridad
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PARA NUEVO ROL -->
    <div class="modal fade" id="modalNuevoRol" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold text-white">Crear Nuevo Rol</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAddRol">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nombre del Rol</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Supervisor" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" placeholder="Describe brevemente las funciones"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveRol">Guardar Rol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $('#formAddRol').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnSaveRol');
            btn.prop('disabled', true).text('Guardando...');

            $.ajax({
                url: "{{ route('roles.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Hecho!',
                        text: response.success,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => { location.reload(); }, 1500);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Guardar Rol');
                    Swal.fire('Error', 'El nombre del rol ya existe o es inválido', 'error');
                }
            });
        });
    </script>

    {{-- SCRIPT PARA AUTO-REFRESCO DESPUÉS DE GUARDAR --}}
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false
        });
        setTimeout(function(){
            window.location.reload();
        }, 2000);
    </script>
    @endif
@endsection