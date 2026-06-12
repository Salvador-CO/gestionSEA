@extends('layouts.app')
@section('content')
<div class="card shadow">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Gestión de Usuarios</h3>
        <a href="{{ route('usuarios.create') }}" class="btn btn-success">Nuevo Usuario</a>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Usuario</th>
                    <th>Centro</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $u)
                <tr>
                    <td>{{ $u->nombre }} {{ $u->apellido }}</td>
                    <td>{{ $u->username }}</td>
                    <td><small>{{ $u->centro ?? 'N/A' }}</small></td>
                    <td><span class="badge bg-info text-dark">{{ $u->rol->nombre }}</span></td>
                    <td>
                        {!! $u->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Suspendido</span>' !!}
                    </td>
                    <td>
                        <a href="{{ route('usuarios.edit', $u->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ url('/usuarios/toggle/'.$u->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $u->activo ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                                {{ $u->activo ? 'Suspender' : 'Activar' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection