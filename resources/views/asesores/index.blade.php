@extends('layouts.app')

@section('content')
<div class="card shadow border-0 overflow-hidden">
    <div class="card-header color_bacho1 text-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2"></i> Gestión de Asesores</h5>
        <a href="{{ route('asesores.create') }}" class="btn btn-light btn-sm fw-bold text_bacho_primary shadow-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Nuevo Asesor
        </a>
    </div>
    <div class="card-body bg-light-subtle">
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white" id="tablaAsesores">
                <thead class="table-light text-uppercase small fw-bold">
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre Completo</th>
                        <th>Centro</th>
                        <th>Cargo</th>
                        <th>Contacto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asesores as $as)
                    <tr>
                        <td><span class="badge bg-dark px-2 py-2">{{ $as->matricula }}</span></td>
                        <td>
                            <div class="fw-bold text-dark">{{ $as->nombre }} {{ $as->apellidos }}</div>
                        </td>
                        <td>
                            <span class="badge rounded-pill border text-primary border-primary px-3">
                                <i class="bi bi-building-fill me-1"></i> {{ $as->centro->clave ?? 'S/C' }}
                            </span>
                        </td>
                        <td><span class="badge color_bacho4 text-dark shadow-sm">{{ $as->cargo->nombre ?? 'N/A' }}</span></td>
                        <td>
                            <small class="d-block text-muted"><i class="bi bi-envelope-at me-1"></i>{{ $as->correo }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('asesores.edit', $as->id) }}" class="btn btn-sm btn-outline-info" title="Editar"><i class="bi bi-pencil-square"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $as->id }}" title="Eliminar">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                            <form id="delete-form-{{ $as->id }}" action="{{ route('asesores.destroy', $as->id) }}" method="POST" class="d-none">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#tablaAsesores').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }
        });

        // Configuración de Toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // Mensaje de Éxito
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: '{{ session("success") }}'
            });
        @endif

        // Mensaje de Error
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: '¡Oops!',
                text: '{{ session("error") }}'
            });
        @endif

        // Confirmar eliminación
        $('.btn-delete').on('click', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Confirmas la eliminación?',
                text: "Se borrarán los datos del asesor del sistema.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, borrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#delete-form-' + id).submit();
                }
            });
        });
    });
</script>
@endpush
@endsection