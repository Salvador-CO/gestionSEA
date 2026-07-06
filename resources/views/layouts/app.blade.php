<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema SEA</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .color_bacho_primary { background-color: #0F8A7A; }
        .color_bacho_primary_dark { background-color: #0B6E61; }
        .color_bacho_primary_light { background-color: #1FB3A1; }
        .color_bacho_gray_dark { background-color: #5F666B; }
        .color_bacho_gray { background-color: #8A9297; }
        .color_bacho_gray_light { background-color: #C5CBCD; }
        .color_bacho_white { background-color: #FFFFFF; }
        .color_bacho_black { background-color: #000000; }
        .text_bacho_primary { color: #0F8A7A; }
        .text_bacho_gray_dark { color: #5F666B; }
        .text_bacho_white { color: #FFFFFF; }
        
        .color_bacho1 { background: linear-gradient(45deg, #0F8A7A, #1FB3A1); }
        .color_bacho3 { background: linear-gradient(45deg, #0B6E61, #0F8A7A); }

        body { display: flex; min-height: 100vh; background: #f8f9fa; margin: 0; overflow-x: hidden; }
        
        .sidebar { 
            width: 250px; 
            background: #212529; 
            color: white; 
            transition: all 0.3s; 
            position: fixed; 
            top: 0; 
            bottom: 0;
            left: 0;
            z-index: 1050;
        }

        @media (max-width: 991.98px) {
            .sidebar { margin-left: -250px; }
            .sidebar.active { margin-left: 0; }
            .main-content { margin-left: 0 !important; }
        }

        .sidebar a, .nav-link-custom { 
            color: #adb5bd; 
            text-decoration: none; 
            padding: 12px 20px; 
            display: block; 
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            transition: 0.2s;
        }

        .sidebar a:hover, .sidebar a.active, .nav-link-custom:hover { 
            background: #343a40; 
            color: white; 
            border-left: 4px solid #008675; 
        }

        .submenu-container {
            background: #1a1d20;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .submenu-container a {
            padding-left: 45px !important;
            font-size: 0.85rem;
            border-left: 4px solid transparent !important;
        }

        .main-content { 
            flex: 1; 
            padding: 20px; 
            transition: all 0.3s;
            margin-left: 250px; 
        }

        .navbar { background: white; border-bottom: 1px solid #dee2e6; margin-bottom: 20px; }
        .text-uppercase-xs { font-size: 0.75rem; letter-spacing: 1px; font-weight: bold; }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        .sidebar-overlay.show { display: block; }

        .nav-link-custom[aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
            transition: 0.3s;
        }
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="sidebar d-flex flex-column" id="sidebar">
        <div class="p-4 text-center fs-4 fw-bold border-bottom border-secondary">
            SEA PANEL
            <button class="btn btn-link text-white d-lg-none float-end p-0" id="closeSidebar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <nav class="mt-3 flex-grow-1 overflow-auto">
            <a href="/dashboard" class="{{ Request::is('dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door me-2"></i> Inicio
            </a>

            <!-- Módulos Dinámicos -->
            @php 
                $permisosDinamicos = \App\Models\Permiso::where('nombre', 'like', 'ver_%')->get();
            @endphp

            @if($permisosDinamicos->count() > 0)
                <div class="small text-uppercase-xs px-4 mt-4 text-secondary">Módulos</div>
                @foreach($permisosDinamicos as $permiso)
                    @if(auth()->user()->tienePermiso($permiso->nombre))
                        @php $ruta = str_replace('ver_', '', $permiso->nombre); @endphp
                        <a href="/{{ $ruta }}" class="{{ Request::is($ruta.'*') && !Request::is($ruta.'/usuarios*') ? 'active' : '' }}">
                            <i class="bi bi-layers me-2"></i> {{ ucfirst(str_replace('_', ' ', $ruta)) }}
                        </a>
                        @if($ruta === 'registro')
                            {{-- Sub-ítem: Buscador de Estudiantes --}}
                            <a href="{{ route('registro.usuarios') }}"
                               class="{{ Request::is('registro/usuarios*') ? 'active' : '' }}"
                               style="padding-left: 40px; font-size: 0.82rem;">
                                <i class="bi bi-search me-2"></i> Buscador
                            </a>
                        @endif
                    @endif
                @endforeach
            @endif

            <!-- BLOQUE DE ADMINISTRACIÓN CON LÓGICA DE VISIBILIDAD TOTAL -->
            @if(
                auth()->user()->tienePermiso('gestionar_usuarios') || 
                auth()->user()->tienePermiso('gestionar_roles_permisos') || 
                auth()->user()->tienePermiso('gestionar_centros') || 
                auth()->user()->tienePermiso('gestionar_asignaturas') || 
                auth()->user()->tienePermiso('gestionar_grupos') || 
                auth()->user()->tienePermiso('gestionar_asesores') || 
                auth()->user()->tienePermiso('gestionar_cargos')
            )
                <div class="small text-uppercase-xs px-4 mt-4 text-secondary">Administración</div>
                
                @if(auth()->user()->tienePermiso('gestionar_usuarios'))
                <a href="/usuarios" class="{{ Request::is('usuarios*') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
                @endif

                @if(auth()->user()->tienePermiso('gestionar_usuarios'))
                <a href="{{ route('auditoria.index') }}" class="{{ Request::is('auditoria*') ? 'active' : '' }}">
                    <i class="bi bi-journal-check me-2"></i> Auditoría
                </a>
                @endif

                @if(auth()->user()->tienePermiso('gestionar_roles_permisos'))
                <a href="/roles" class="{{ Request::is('roles*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock me-2"></i> Roles y Permisos
                </a>
                @endif

                <!-- Menú Desplegable: Catálogos (Solo aparece si tiene al menos UN permiso de los de adentro) -->
                @if(
                    auth()->user()->tienePermiso('gestionar_centros') || 
                    auth()->user()->tienePermiso('gestionar_asignaturas') || 
                    auth()->user()->tienePermiso('gestionar_grupos') || 
                    auth()->user()->tienePermiso('gestionar_asesores') || 
                    auth()->user()->tienePermiso('gestionar_cargos')
                )
                    <div class="nav-item">
                        <a class="nav-link-custom d-flex justify-content-between align-items-center" 
                           data-bs-toggle="collapse" 
                           href="#menuCatalogos" 
                           role="button" 
                           aria-expanded="{{ Request::is('centros*') || Request::is('asignaturas*') || Request::is('grupos*') || Request::is('asesores*') || Request::is('cargos*') ? 'true' : 'false' }}">
                            <span><i class="bi bi-folder2-open me-2"></i> Catálogos</span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        
                        <div class="collapse {{ Request::is('centros*') || Request::is('asignaturas*') || Request::is('grupos*') || Request::is('asesores*') || Request::is('cargos*') ? 'show' : '' }}" id="menuCatalogos">
                            <div class="submenu-container">
                                @if(auth()->user()->tienePermiso('gestionar_centros'))
                                <a href="/centros" class="{{ Request::is('centros*') ? 'active' : '' }}">
                                    <i class="bi bi-building me-2"></i> Centros
                                </a>
                                @endif

                                @if(auth()->user()->tienePermiso('gestionar_asignaturas'))
                                <a href="/asignaturas" class="{{ Request::is('asignaturas*') ? 'active' : '' }}">
                                    <i class="bi bi-book me-2"></i> Asignaturas
                                </a>
                                @endif

                                @if(auth()->user()->tienePermiso('gestionar_grupos'))
                                <a href="/grupos" class="{{ Request::is('grupos*') ? 'active' : '' }}">
                                    <i class="bi bi-collection me-2"></i> Grupos
                                </a>
                                @endif

                                @if(auth()->user()->tienePermiso('gestionar_asesores'))
                                <a href="/asesores" class="{{ Request::is('asesores*') ? 'active' : '' }}">
                                    <i class="bi bi-person-badge me-2"></i> Asesores
                                </a>
                                @endif

                                @if(auth()->user()->tienePermiso('gestionar_cargos'))
                                <a href="/cargos" class="{{ Request::is('cargos*') ? 'active' : '' }}">
                                    <i class="bi bi-briefcase me-2"></i> Cargos
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </nav>

        <div class="p-3 border-top border-secondary">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </button>
            </form>
        </div>
    </div>

    <div class="main-content w-100">
        <nav class="navbar px-4 py-2 shadow-sm rounded d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="toggleSidebar">
                    <i class="bi bi-list fs-4"></i>
                </button>
                
                <span class="navbar-text d-none d-md-block">
                    Bienvenido, <strong>{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</strong> 
                    <span class="badge color_bacho_primary_dark ms-2">{{ auth()->user()->rol->nombre }}</span>
                </span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-muted small d-none d-sm-block">
                    <i class="bi bi-calendar3 me-1"></i> {{ date('d/m/Y') }}
                </div>
                
                <div class="dropdown">
                    <button class="btn btn-light btn-sm rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear-fill text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><h6 class="dropdown-header">Ajustes de Usuario</h6></li>
                        <li><a class="dropdown-item" href="/perfil"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="/configuracion"><i class="bi bi-sliders me-2"></i> Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#toggleSidebar, #sidebarOverlay, #closeSidebar').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#sidebarOverlay').toggleClass('show');
            });
        });
    </script>

    @stack('scripts')
</body>
</html>