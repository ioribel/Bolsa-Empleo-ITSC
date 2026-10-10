# Bolsa de Empleo del ITSC

Plataforma web que conecta a estudiantes y egresados del Instituto Técnico Superior Comunitario (ITSC) con empresas que publican ofertas de empleo y pasantías.

Proyecto final de la asignatura **SOF-111 — Construcción de Software**, profesor Carlos Adames. El proyecto continúa en la asignatura SOF-113 con el mismo código base.

## Stack tecnológico

- **Backend:** PHP 8.2 + Laravel 11
- **Base de datos:** MySQL
- **Autenticación:** Laravel Breeze
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Control de versiones:** Git / GitHub

## Actores del sistema

| Actor | Descripción |
|---|---|
| **Administrador** | Aprueba o bloquea empresas, gestiona el catálogo de carreras y los usuarios del sistema. |
| **Empresa** | Se registra, publica y gestiona vacantes, y revisa/gestiona las postulaciones recibidas. |
| **Estudiante / Egresado** | Se registra con su matrícula y carrera, explora vacantes dirigidas a su carrera, se postula y gestiona su perfil con CV. |

## Funcionalidades principales

- Registro diferenciado para estudiantes/egresados y empresas, con validaciones de unicidad (email, matrícula, RNC).
- Aprobación de empresas por el administrador antes de poder publicar vacantes.
- CRUD completo de vacantes con ciclo de vida controlado: `borrador → publicada → cerrada`.
- Postulación de estudiantes a vacantes dirigidas a su carrera, con prevención de postulaciones duplicadas.
- Gestión de postulaciones por la empresa con flujo de estados: `recibida → en revisión → aceptada / rechazada`.
- Perfil de estudiante con carga de currículum (PDF).
- Gestión de usuarios (bloqueo/activación) y catálogo de carreras por el administrador.
- Roles y permisos aplicados por middleware en cada grupo de rutas.

## Modelo de datos

7 tablas: `users`, `estudiantes`, `empresas`, `carreras`, `vacantes`, `vacante_carrera` (pivote N:M), `postulaciones`.

## Instalación local

### Requisitos previos

- PHP >= 8.2 con Composer
- MySQL (por ejemplo, vía XAMPP)
- Node.js y npm

### Pasos

```bash
# Clonar el repositorio
git clone https://github.com/alexisgcn/bolsa-empleo-itsc.git
cd bolsa-empleo-itsc

# Instalar dependencias de PHP
composer install

# Configurar el entorno
cp .env.example .env
php artisan key:generate
```

Edita el archivo `.env` con los datos de tu base de datos local:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bolsa_empleo_itsc
DB_USERNAME=root
DB_PASSWORD=
```

Crea la base de datos vacía (por ejemplo, en MySQL Workbench o phpMyAdmin):

```sql
CREATE DATABASE bolsa_empleo_itsc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Continúa con las migraciones, seeders y dependencias de frontend:

```bash
php artisan migrate --seed
npm install
npm run build

php artisan storage:link
php artisan serve
```

La aplicación queda disponible en `http://127.0.0.1:8000`.

### Cuenta de administrador por defecto

El seeder crea una cuenta de administrador inicial:

- **Correo:** admin@itsc.edu.do
- **Contraseña:** admin1234

> Se recomienda cambiar esta contraseña en cualquier entorno que no sea de desarrollo local.

## Desarrollo

Durante el desarrollo activo (edición de archivos Blade), se recomienda correr Vite en modo watch en una terminal aparte para recompilar los estilos automáticamente:

```bash
npm run dev
```

## Equipo

- **Desarrollo:** Alexis Carmona
- **Documentación:** Gildebran Ventura
- **Apoyo en documentación:** Madelin Decena
## Licencia

Proyecto académico desarrollado para el Instituto Técnico Superior Comunitario (ITSC).