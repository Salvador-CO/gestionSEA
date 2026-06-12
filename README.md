<div align="center">

# 🎓 gestionSEA

### Sistema de Gestión del Sistema de Enseñanza Abierta (SEA)
**Colegio de Bachilleres del Estado de Guanajuato**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)

---

> **gestionSEA** es un sistema web interno desarrollado con **Laravel 12** que permite gestionar el registro de estudiantes, asignación de grupos, seguimiento académico y administración de usuarios, todo integrado directamente con la plataforma **Moodle** del SEA a través de su API REST.

</div>

---

## 📋 Tabla de Contenidos

- [¿Qué es este sistema?](#-qué-es-este-sistema)
- [Características principales](#-características-principales)
- [Stack tecnológico](#-stack-tecnológico)
- [Requisitos del sistema](#-requisitos-del-sistema)
- [Instalación paso a paso](#-instalación-paso-a-paso)
- [Configuración de la base de datos](#-configuración-de-la-base-de-datos)
- [Configuración de Moodle](#-configuración-de-moodle)
- [Estructura del proyecto](#-estructura-del-proyecto)
- [Esquema de la base de datos](#-esquema-de-la-base-de-datos)
- [Módulos del sistema](#-módulos-del-sistema)
- [Sistema de permisos](#-sistema-de-permisos)
- [Preguntas frecuentes](#-preguntas-frecuentes)

---

## 🎯 ¿Qué es este sistema?

**gestionSEA** es la herramienta de administración interna del Sistema de Enseñanza Abierta (SEA). Permite al personal administrativo:

- 📝 **Registrar nuevos estudiantes** directamente en Moodle desde un formulario centralizado
- 👥 **Crear y sincronizar grupos** académicos entre la base de datos local y Moodle
- 📊 **Consultar calificaciones** y estadísticas de actividad de los estudiantes
- 📧 **Gestionar correos institucionales** importados desde Excel
- 🔐 **Administrar usuarios** del sistema con roles y permisos granulares
- 🎯 **Asignar evaluaciones** (módulo EVAL) y controlar reintentos de exámenes

---

## ✨ Características principales

| Característica | Descripción |
|---|---|
| 🔐 **Autenticación local** | Sistema de login independiente con sesiones en BD |
| 🛡️ **RBAC completo** | Roles y permisos configurables por módulo |
| 🔗 **Integración Moodle** | 18+ funciones de la API REST de Moodle |
| 👨‍🏫 **Gestión de asesores** | CRUD de personal académico con cargos |
| 🏫 **Multi-centro** | Soporte para múltiples planteles/centros |
| 📈 **Reportes en tiempo real** | Estadísticas de acceso y actividad académica |
| 📥 **Importación Excel** | Carga masiva de correos institucionales |
| 🔄 **Caché inteligente** | Respuestas de Moodle cacheadas en BD |

---

## 🛠️ Stack tecnológico

### Backend
- **[Laravel 12](https://laravel.com/docs/12.x)** — Framework PHP principal (MVC)
- **PHP 8.2+** — Lenguaje de servidor
- **MySQL 8.0+** — Base de datos relacional
- **Laravel Eloquent ORM** — Mapeo objeto-relacional
- **Laravel Queue** — Procesamiento de tareas en cola (driver: database)

### Frontend
- **[Blade](https://laravel.com/docs/12.x/blade)** — Motor de plantillas de Laravel
- **[TailwindCSS 4.0](https://tailwindcss.com)** — Framework CSS utilitario
- **[Vite 7](https://vitejs.dev)** — Bundler y servidor de desarrollo
- **[Axios](https://axios-http.com)** — Cliente HTTP para peticiones AJAX

### Integración externa
- **[Moodle REST API](https://docs.moodle.org/dev/Web_services)** — Plataforma educativa del SEA

---

## 💻 Requisitos del sistema

Antes de instalar, asegúrate de tener lo siguiente:

| Herramienta | Versión mínima | Cómo verificar |
|---|---|---|
| **PHP** | 8.2 o superior | `php -v` |
| **Composer** | 2.x | `composer -V` |
| **Node.js** | 18.x o superior | `node -v` |
| **npm** | 9.x o superior | `npm -v` |
| **MySQL** | 8.0 o superior | `mysql --version` |
| **Git** | Cualquier versión | `git --version` |

> 💡 **Recomendación para Windows**: Usa [XAMPP](https://www.apachefriends.org/es/index.html) (incluye PHP + MySQL + Apache) junto con [Composer](https://getcomposer.org) y [Node.js](https://nodejs.org) instalados por separado.

---

## 📦 Instalación paso a paso

### 1. Clonar el repositorio

```bash
git clone https://github.com/Salvador-CO/gestionSEA.git
cd gestionSEA
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Instalar dependencias de JavaScript

```bash
npm install
```

### 4. Copiar el archivo de configuración

```bash
cp .env.example .env
```

> ⚠️ **En Windows** usa el Explorador de archivos para duplicar `.env.example` y renombrarlo a `.env`, o ejecuta en PowerShell:
> ```powershell
> Copy-Item .env.example .env
> ```

### 5. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 6. Configurar la base de datos (ver sección siguiente)

### 7. Ejecutar las migraciones

```bash
php artisan migrate
```

### 8. (Opcional) Poblar datos de prueba

```bash
php artisan db:seed
```

### 9. Compilar los assets del frontend

Para desarrollo (con recarga automática):
```bash
npm run dev
```

Para producción:
```bash
npm run build
```

### 10. Iniciar el servidor de desarrollo

```bash
php artisan serve
```

El sistema estará disponible en: **http://localhost:8000**

---

### 🚀 Instalación rápida (todo en uno)

Si usas el script automático de Composer:

```bash
git clone https://github.com/Salvador-CO/gestionSEA.git
cd gestionSEA
composer run setup
```

Este comando ejecuta automáticamente: `composer install` → copiar `.env` → `key:generate` → `migrate` → `npm install` → `npm run build`.

---

## 🗄️ Configuración de la base de datos

### Crear la base de datos en MySQL

**Opción A — phpMyAdmin (XAMPP)**
1. Abre `http://localhost/phpmyadmin`
2. Clic en **"Nueva"**
3. Nombre: `sistema_sea`
4. Cotejamiento: `utf8mb4_unicode_ci`
5. Clic en **Crear**

**Opción B — Línea de comandos**
```sql
mysql -u root -p
CREATE DATABASE sistema_sea CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Editar el archivo `.env`

Abre el archivo `.env` y configura la sección de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sistema_sea
DB_USERNAME=root
DB_PASSWORD=           # Tu contraseña de MySQL (vacía en XAMPP por defecto)
```

### Ejecutar las migraciones

```bash
php artisan migrate
```

Esto creará automáticamente las siguientes tablas:

| Tabla | Descripción |
|---|---|
| `usuarios` | Usuarios del sistema SEA |
| `roles` | Roles de acceso (Admin, Jefe de Centro, etc.) |
| `permisos` | Permisos individuales por módulo |
| `rol_permisos` | Relación muchos-a-muchos Rol↔Permiso |
| `centros` | Planteles educativos (C01, C02, etc.) |
| `cargos` | Tipos de cargo de asesores |
| `asignaturas` | Materias/asignaturas disponibles |
| `asesores` | Personal académico asesor |
| `grupos` | Grupos académicos (locales + Moodle) |
| `estudiante_correos` | Correos institucionales importados |
| `sessions` | Sesiones activas |
| `cache` | Caché de la aplicación |
| `jobs` | Cola de trabajos |

### Datos iniciales necesarios

Después de las migraciones, debes insertar manualmente los datos base usando phpMyAdmin o la CLI de MySQL. A continuación los datos mínimos para que el sistema funcione:

```sql
-- 1. Insertar rol de Administrador
INSERT INTO roles (nombre, descripcion, activo) VALUES ('Administrador', 'Acceso total al sistema', 1);

-- 2. Insertar todos los permisos del sistema
INSERT INTO permisos (nombre, descripcion) VALUES
('gestionar_roles_permisos', 'Gestionar roles y permisos del sistema'),
('gestionar_usuarios', 'Gestionar usuarios del sistema'),
('ver_calificaciones', 'Ver calificaciones de estudiantes'),
('ver_registro', 'Registrar y gestionar alumnos en Moodle'),
('ver_reporte', 'Ver reportes y estadísticas'),
('gestionar_asesores', 'CRUD de asesores académicos'),
('gestionar_centros', 'CRUD de centros/planteles'),
('gestionar_asignaturas', 'CRUD de asignaturas'),
('gestionar_cargos', 'CRUD de cargos de asesores'),
('gestionar_grupos', 'Gestionar grupos y tablero Moodle'),
('ver_correo', 'Gestionar correos institucionales');

-- 3. Asignar TODOS los permisos al rol Administrador (asumiendo que el rol tiene id=1)
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT 1, id FROM permisos;

-- 4. Crear usuario administrador inicial
-- IMPORTANTE: Reemplaza 'tu_password_aqui' con una contraseña segura
INSERT INTO usuarios (username, email, password, nombre, apellido, centro, rol_id, activo)
VALUES ('admin', 'admin@sea.edu.mx', '$2y$12$REEMPLAZAR_CON_HASH', 'Administrador', 'SEA', NULL, 1, 1);
```

> ⚠️ **Para generar el hash de la contraseña**, usa Tinker:
> ```bash
> php artisan tinker
> >>> bcrypt('tu_contraseña_aqui')
> ```
> Copia el resultado y reemplázalo en el INSERT de arriba.

### Centros disponibles (datos de ejemplo)

```sql
INSERT INTO centros (clave, nombre, descripcion) VALUES
('C01', 'Centro 1', 'Plantel Centro 1 SEA'),
('C02', 'Centro 2', 'Plantel Centro 2 SEA'),
('C03', 'Centro 3', 'Plantel Centro 3 SEA'),
('C04', 'Centro 4', 'Plantel Centro 4 SEA'),
('C05', 'Centro 5', 'Plantel Centro 5 SEA'),
('CAE01', 'CAE León', 'Centro de Atención Escolarizada León'),
('CAE02', 'CAE Irapuato', 'Centro de Atención Escolarizada Irapuato');
```

### Cargos de asesores (datos de ejemplo)

```sql
INSERT INTO cargos (nombre) VALUES
('Asesor de Contenido'),
('Psicopedagogo'),
('Responsable de Plantel');
```

---

## 🔗 Configuración de Moodle

El sistema se integra con la plataforma Moodle del SEA mediante su API REST. Para configurarlo:

### 1. Obtener las credenciales de la API

Necesitas:
- **URL del servidor Moodle** (ej: `https://plataformadigitalsea.cbachilleres.edu.mx`)
- **Token de servicio web** (generado en Moodle por el administrador)

### 2. Agregar al archivo `.env`

```env
MOODLE_URL=https://tu-moodle.edu.mx/webservice/rest/server.php
MOODLE_TOKEN=tu_token_de_moodle_aqui
```

> 💡 **Nota**: Actualmente el token está configurado directamente en los archivos de servicio (`app/Services/`). Para mayor seguridad, se recomienda moverlo al `.env` como se indica arriba y actualizar los servicios.

### 3. Verificar conexión

Una vez configurado, puedes verificar que la API responde correctamente accediendo al módulo de **Registro** y buscando un estudiante.

---

## 📁 Estructura del proyecto

```
gestionSEA/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # 15 controladores (uno por módulo)
│   │   │   ├── AuthController.php        # Login y logout
│   │   │   ├── DashboardController.php   # Panel principal
│   │   │   ├── ModuloController.php      # Roles y permisos
│   │   │   ├── UsuarioController.php     # CRUD de usuarios
│   │   │   ├── RegistroController.php    # Registro de alumnos en Moodle
│   │   │   ├── AsignacionController.php  # Módulo EVAL
│   │   │   ├── CalificacionesController.php
│   │   │   ├── ReporteController.php     # Estadísticas
│   │   │   ├── GrupoController.php       # Grupos + Tablero Moodle
│   │   │   ├── AsesorController.php
│   │   │   ├── CentroController.php
│   │   │   ├── AsignaturaController.php
│   │   │   ├── CargoController.php
│   │   │   └── CorreoController.php      # Importación de Excel
│   │   └── Middleware/
│   │       └── CheckPermiso.php          # Middleware RBAC
│   ├── Models/                   # 15 modelos Eloquent
│   │   ├── User.php              # Usuarios del sistema (tabla: usuarios)
│   │   ├── Rol.php / Permiso.php / RolPermiso.php
│   │   ├── Centro.php / Cargo.php / Asignatura.php
│   │   ├── Asesor.php / Grupo.php / Correo.php
│   │   └── Semestre.php / Asignacion.php / Calificaciones.php
│   └── Services/                 # Lógica de integración con Moodle
│       ├── MoodleService.php     # Estadísticas, calificaciones
│       ├── MoodleGrupoService.php # Grupos en Moodle
│       ├── RegistroService.php   # Crear/inscribir usuarios
│       ├── AsignacionService.php # Módulo EVAL, quiz, reintentos
│       └── BusquedaService.php   # Búsqueda de estudiantes
├── database/
│   ├── migrations/               # 10 migraciones
│   ├── seeders/                  # DatabaseSeeder.php
│   └── factories/
├── resources/
│   ├── css/app.css               # Estilos globales
│   ├── js/app.js                 # JavaScript principal
│   └── views/                   # Plantillas Blade
│       ├── layouts/              # Layout principal
│       ├── login.blade.php
│       ├── dashboard.blade.php
│       ├── registro/             # Vistas del módulo de registro
│       ├── asignacion/           # Vistas del módulo EVAL
│       ├── grupos/               # Gestión de grupos
│       ├── correo/               # Gestión de correos
│       ├── reporte/              # Reportes y estadísticas
│       ├── roles/ / usuarios/ / asesores/
│       ├── centros/ / asignaturas/ / cargos/
│       └── calificaciones/
├── routes/
│   └── web.php                   # ~40 rutas del sistema
├── .env.example                  # Plantilla de configuración
├── composer.json                 # Dependencias PHP
├── package.json                  # Dependencias JavaScript
└── vite.config.js                # Configuración de Vite
```

---

## 🗂️ Esquema de la base de datos

```
usuarios ──── roles ──────── rol_permisos ──── permisos
              (RBAC)

centros ──────── grupos ──── asignaturas
                    │
                asesores ──── cargos

estudiante_correos   (tabla independiente - importación Excel)
sessions / cache / jobs   (tablas de sistema Laravel)
```

### Código de grupos (formato Moodle)

Los grupos se identifican con un código generado automáticamente:

```
C01  S1   811   P01
 │    │    │     │
 │    │    │     └── Número consecutivo del grupo (01, 02...)
 │    │    └──────── Clave de la asignatura
 │    └───────────── Semestre
 └────────────────── Clave del centro
```

**Ejemplo**: `C01S1811P01` = Centro 1, Semestre 1, Asignatura 811, Grupo 01

---

## 📚 Módulos del sistema

### 🔐 Autenticación
- Login con `username` y `password` (almacenados en tabla `usuarios`)
- Verificación de cuenta activa/suspendida
- Sesiones almacenadas en base de datos MySQL

### 👥 Gestión de Usuarios
- CRUD completo de usuarios del sistema SEA
- Vinculación opcional con cuenta Moodle
- Asignación de roles y permisos

### 🛡️ Roles y Permisos
- Crear roles personalizados (ej: Administrador, Jefe de Centro, Operador)
- Asignar permisos individuales a cada rol
- Middleware automático en todas las rutas protegidas

### 📝 Registro de Alumnos
- Validar si el estudiante ya existe en Moodle por email
- Crear nuevo usuario en Moodle (con campos personalizados: centro, tipoROL)
- Inscribir al estudiante en cursos y grupos específicos
- Buscar estudiante por nombre, matrícula o email

### 🎯 Asignación EVAL
- Módulo exclusivo para cursos de tipo "EVAL" (evaluación diagnóstica)
- Validar inscripción y calificación actual del estudiante
- Habilitar intentos adicionales en exámenes Moodle (quiz overrides)
- Ver historial completo de intentos

### 👨‍🏫 Gestión de Grupos
- Crear grupos locales con código Moodle auto-generado
- Sincronizar grupo local → crear grupo real en Moodle
- Tablero Moodle: vista de todos los grupos remotos con sus asesores y alumnos
- Asignar/desvincular asesores en grupos de Moodle

### 📊 Reportes y Estadísticas
- Total de usuarios registrados en Moodle
- Usuarios sin acceso / con acceso reciente / con acceso previo
- Distribución por roles y centros
- Calificaciones filtradas por categoría, curso y centro

### 📧 Gestión de Correos
- Importar lista de correos institucionales desde Excel/CSV
- Gestionar estatus de entrega (Pendiente / Entregado)
- Exportar pendientes por plantel
- Descargar plantilla de Excel para importación

---

## 🔑 Sistema de permisos

Cada ruta del sistema está protegida por el middleware `CheckPermiso`. Los permisos disponibles son:

| Permiso | Módulo protegido |
|---|---|
| `gestionar_roles_permisos` | Roles y permisos |
| `gestionar_usuarios` | CRUD de usuarios |
| `ver_calificaciones` | Módulo de calificaciones |
| `ver_registro` | Registro e inscripción de alumnos + módulo EVAL |
| `ver_reporte` | Reportes y estadísticas |
| `gestionar_asesores` | CRUD de asesores |
| `gestionar_centros` | CRUD de centros/planteles |
| `gestionar_asignaturas` | CRUD de asignaturas |
| `gestionar_cargos` | CRUD de cargos |
| `gestionar_grupos` | Grupos y tablero Moodle |
| `ver_correo` | Gestión de correos institucionales |

---

## ❓ Preguntas frecuentes

**¿El sistema funciona sin conexión a Moodle?**
> Parcialmente. Los módulos de Catálogos (Centros, Asesores, Cargos, Asignaturas) funcionan localmente. Los módulos de Registro, Calificaciones, Reportes y Grupos requieren conexión a la API de Moodle.

**¿Qué pasa si ejecuto `php artisan migrate` en una BD que ya tiene tablas?**
> Laravel es inteligente: solo ejecuta las migraciones que aún no se han aplicado. Es seguro correrlo múltiples veces.

**¿Cómo reinicio la base de datos completamente?**
> ```bash
> php artisan migrate:fresh
> # Esto BORRA todas las tablas y las crea de nuevo
> ```

**¿Puedo usar SQLite en lugar de MySQL?**
> Sí, para desarrollo local. Cambia en `.env`: `DB_CONNECTION=sqlite` y asegúrate que existe `database/database.sqlite`. Sin embargo, para producción se recomienda MySQL.

**¿Cómo ejecuto el sistema completo en modo desarrollo?**
> ```bash
> composer run dev
> ```
> Esto lanza simultáneamente: servidor PHP, queue worker, visor de logs y Vite.

---

## 👨‍💻 Desarrollado por

**Salvador** — Sistema de Enseñanza Abierta, COBAG  
Proyecto interno de gestión académica y administrativa.

---

<div align="center">

**gestionSEA** · Laravel 12 · PHP 8.2 · MySQL · TailwindCSS · Moodle API

</div>
