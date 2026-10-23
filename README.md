# Bolsa de Empleo del ITSC

Plataforma web que conecta a estudiantes y egresados del Instituto Técnico Superior Comunitario (ITSC) con empresas que publican ofertas de empleo y pasantías.

Proyecto final de la asignatura **SOF-113 - PROYECTO FINAL**.

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
- Asistente de empleabilidad con análisis puntual del CV y generación de una versión adaptada en PDF.

## Asistente Inteligente de Empleabilidad

El estudiante puede analizar su CV frente a una vacante dirigida a su carrera, revisar las evidencias y recomendaciones, editar la propuesta y descargar un PDF. El análisis y el PDF son efímeros: no se crea historial ni se guardan resultados adaptados. El CV original se reutiliza desde el almacenamiento privado del perfil y se puede eliminar desde allí.

Configura las variables en `.env` sin compartir ni subir la clave al repositorio:

```dotenv
GEMINI_API_KEY=tu_clave_de_api
GEMINI_MODEL=gemini-2.5-flash
```

Después de aplicar la migración, cada análisis requiere consentimiento explícito y se limita a cinco solicitudes por estudiante por hora. Se envía a Gemini el texto extraído del CV y los datos de la vacante; el sistema omite el nombre, correo y matrícula del perfil, además de correos y teléfonos detectables. Revisa las condiciones de privacidad del plan de Google antes de procesar currículums reales. La puntuación es orientativa y se calcula en el backend: cuenta los requisitos cuyas citas literales son verificables y contienen al menos la mitad de las palabras clave del requisito. No representa una probabilidad de contratación.

La extracción se realiza localmente y admite PDF con texto seleccionable de hasta 2 MB, 50 páginas y 60 000 caracteres. Los PDF escaneados que necesiten OCR no son compatibles. En cada análisis, el estudiante puede subir un PDF para usarlo solo en esa solicitud; si no lo hace, se utiliza el CV de su perfil. El PDF subido no reemplaza ni se guarda en el perfil y se conserva en caché temporal hasta 30 minutos para validar la descarga del CV adaptado. Se priorizan los CV del disco privado `local`; también se admiten CV ya existentes en `public` para evitar que fallen los perfiles antiguos. Para mantener privado un CV antiguo guardado en `public`, reemplázalo desde Mi Perfil. Las respuestas del análisis no se almacenan.

## Modelo de datos

El módulo reutiliza las tablas existentes y añade únicamente `cv_original_name` a `estudiantes` para mostrar el nombre del archivo. No guarda análisis ni crea una tabla de historial.

## Instalación local

### Requisitos previos

- PHP >= 8.2 con Composer
- MySQL (por ejemplo, vía XAMPP)
- Node.js y npm

### Pasos

```bash
# Clonar el repositorio
git clone https://github.com/ioribel/Bolsa-Empleo-ITSC.git
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

- **Desarrollo:** Gildebran Ventura
- **Documentación:** Gildebran Ventura
## Licencia

Proyecto académico desarrollado para el Instituto Técnico Superior Comunitario (ITSC).
