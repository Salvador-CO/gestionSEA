<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Fuente formal -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <title>Login - Sistema SEA</title>

    <style>
        :root {
            --cobach-green: #1a4d2e;
            --cobach-green-dark: #0b1f14;
            --cobach-gold: #b38e2e;
            --bg-light: #f4f6f8;
        }

        * {
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: var(--bg-light);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 15px;
        }

        .login-card {
            border-radius: 12px;
            background: #fff;
            border-top: 6px solid var(--cobach-green);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .login-header {
            text-align: center;
            padding: 30px 25px 20px;
            border-bottom: 1px solid #eee;
        }

        .login-header i {
            font-size: 3rem;
            color: var(--cobach-green);
        }

        .login-header h3 {
            margin-top: 10px;
            font-weight: 700;
            color: var(--cobach-green);
            letter-spacing: 1px;
        }

        .login-header p {
            font-size: 0.85rem;
            color: #666;
        }

        .card-body {
            padding: 30px;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #444;
        }

        .input-group-text {
            background: #e9ecef;
            border: 1px solid #ced4da;
            border-right: none;
        }

        .form-control {
            border: 1px solid #ced4da;
            font-size: 0.9rem;
        }

        .form-control:focus {
            border-color: var(--cobach-green);
            box-shadow: 0 0 0 0.2rem rgba(26, 77, 46, 0.15);
        }

        .btn-login {
            background: var(--bg-light);
            border: none;
            font-weight: 600;
            padding: 12px;
            transition: 0.3s;
            border-radius: 30px;
        }

        .btn-login:hover {
            background: var(--cobach-green);
            color: var(--bg-light);
        }

        /* BOTÓN LOADING */
        .btn-loading {
            pointer-events: none;
            opacity: 0.8;
        }

        /* ALERTAS */
        .alert-custom {
            border-left: 4px solid;
            font-size: 0.85rem;
            padding: 10px;
            display: flex;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .alert-danger {
            border-left-color: #dc3545;
            background: #fff5f5;
        }

        .alert-warning {
            border-left-color: #ffc107;
            background: #fff9e6;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .footer {
            text-align: center;
            font-size: 0.75rem;
            padding: 10px;
            background: #f1f1f1;
            color: #666;
        }

        /* OVERLAY LOADING */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--cobach-green);
        }
    </style>
</head>

<body>

    <!-- OVERLAY -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="spinner-border"></div>
            <div class="mt-2 text-muted">Validando credenciales...</div>
        </div>
    </div>

    <div class="login-container">
        <div class="login-card">

            <div class="login-header">
                <i class="bi bi-mortarboard-fill"></i>
                <h3>SIGAEEAA</h3>
                <p>Sistema de Gestión Académica Escolar de Estudiantes y Asesores (SEA)</p>
            </div>

            <div class="card-body">

                @if($errors->has('error_login'))
                <div class="alert alert-danger alert-custom mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span>{{ $errors->first('error_login') }}</span>
                </div>
                @endif

                @if($errors->has('suspendido'))
                <div class="alert alert-warning alert-custom mb-3">
                    <i class="bi bi-person-x-fill me-2"></i>
                    <span>{{ $errors->first('suspendido') }}</span>
                </div>
                @endif

                <form action="/login" method="POST" id="loginForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Usuario Institucional</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" name="username" class="form-control"
                                value="{{ old('username') }}"
                                placeholder="Ej. matricula_123" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="••••••••" required>
                            <button type="button" class="input-group-text" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login w-100" id="loginBtn">
                        Iniciar Sesión
                    </button>

                </form>
            </div>

            <div class="footer">
                Sistema oficial · Colegio de Bachilleres &copy; 2026
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // MOSTRAR / OCULTAR CONTRASEÑA
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            togglePassword.innerHTML = type === 'password' ?
                '<i class="bi bi-eye"></i>' :
                '<i class="bi bi-eye-slash"></i>';
        });

        // LOADING AL ENVIAR
        const form = document.getElementById('loginForm');
        const btn = document.getElementById('loginBtn');
        const overlay = document.getElementById('loadingOverlay');

        form.addEventListener('submit', () => {
            btn.classList.add('btn-loading');
            btn.innerHTML = 'Procesando...';
            overlay.style.display = 'flex';
        });
    </script>

</body>

</html>