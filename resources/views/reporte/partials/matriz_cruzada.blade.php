<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold text_bacho_primary"><i class="bi bi-grid-3x3"></i> Matriz de Distribución: Centro vs Rol</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light text-center">
                    <tr>
                        <th rowspan="2" class="align-middle">Centro Poblado</th>
                        <th colspan="{{ count($stats['roles']) }}" class="py-2">Roles Disponibles</th>
                    </tr>
                    <tr>
                        @foreach(array_keys($stats['roles']) as $rol)
                            <th style="font-size: 0.75rem;">{{ $rol }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['matriz_centro_rol'] as $centro => $roles)
                    <tr>
                        <td class="fw-bold small">{{ $centro }}</td>
                        @foreach(array_keys($stats['roles']) as $rolName)
                            @php $val = $roles[$rolName] ?? 0; @endphp
                            <td class="text-center">
                                @if($val > 0)
                                    <span class="badge rounded-pill color_bacho_primary_dark btn-modal" 
                                          style="cursor:pointer"
                                          data-tipo="centro_rol" 
                                          data-valor="{{ $centro }}" 
                                          data-valor-extra="{{ $rolName }}">
                                        {{ $val }}
                                    </span>
                                @else
                                    <span class="text-muted opacity-25">0</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>