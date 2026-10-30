<?php

use App\Http\Controllers\Admin\CarreraController;
use App\Http\Controllers\Admin\EmpresaController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\VacanteController;
use App\Http\Controllers\Empresa\PostulacionController;
use App\Http\Controllers\Estudiante\EmployabilityAnalysisController;
use App\Http\Controllers\Estudiante\PerfilController;
use App\Http\Controllers\Estudiante\PerfilProfesionalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return match (auth()->user()->rol) {
        'admin' => redirect()->route('admin.dashboard'),
        'empresa' => redirect()->route('empresa.dashboard'),
        default => redirect()->route('estudiante.dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->whereUuid('notification')
        ->name('notifications.read');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])
        ->whereUuid('notification')
        ->name('notifications.open');
});

Route::middleware(['auth', 'rol:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard', [
            'totalEstudiantes' => Estudiante::count(),
            'empresasAprobadas' => Empresa::where('estado', 'aprobada')->count(),
            'empresasPendientes' => Empresa::where('estado', 'pendiente')->count(),
            'vacantesActivas' => Vacante::where('estado', 'publicada')->count(),
        ]);
    })->name('dashboard');

    Route::get('empresas', [EmpresaController::class, 'index'])->name('empresas.index');
    Route::get('vacantes', [VacanteController::class, 'index'])->name('vacantes.index');
    Route::patch('empresas/{empresa}/aprobar', [EmpresaController::class, 'aprobar'])->name('empresas.aprobar');
    Route::patch('empresas/{empresa}/bloquear', [EmpresaController::class, 'bloquear'])->name('empresas.bloquear');
    Route::patch('empresas/{empresa}/rechazar', [EmpresaController::class, 'rechazar'])->name('empresas.rechazar');

    Route::get('carreras', [CarreraController::class, 'index'])->name('carreras.index');
    Route::post('carreras', [CarreraController::class, 'store'])->name('carreras.store');
    Route::patch('carreras/{carrera}', [CarreraController::class, 'update'])->name('carreras.update');
    Route::delete('carreras/{carrera}', [CarreraController::class, 'destroy'])->name('carreras.destroy');
    Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::patch('usuarios/{usuario}/bloquear', [UsuarioController::class, 'bloquear'])->name('usuarios.bloquear');
    Route::patch('usuarios/{usuario}/activar', [UsuarioController::class, 'activar'])->name('usuarios.activar');
});

Route::middleware(['auth', 'rol:empresa'])->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/dashboard', function () {
        $empresa = auth()->user()->empresa;

        return view('empresa.dashboard', [
            'vacantesActivas' => $empresa->vacantes()->where('estado', 'publicada')->count(),
            'postulacionesRecibidas' => Postulacion::whereHas('vacante', fn ($q) => $q->where('empresa_id', $empresa->id))->count(),
            'pendientesRevisar' => Postulacion::whereHas('vacante', fn ($q) => $q->where('empresa_id', $empresa->id))->where('estado', 'recibida')->count(),
        ]);
    })->name('dashboard');

    Route::resource('vacantes', App\Http\Controllers\Empresa\VacanteController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::patch('vacantes/{vacante}/publicar', [App\Http\Controllers\Empresa\VacanteController::class, 'publicar'])
        ->name('vacantes.publicar');

    Route::patch('vacantes/{vacante}/cerrar', [App\Http\Controllers\Empresa\VacanteController::class, 'cerrar'])
        ->name('vacantes.cerrar');

    Route::get('vacantes/{vacante}/postulaciones', [PostulacionController::class, 'index'])
        ->name('vacantes.postulaciones.index');

    Route::get('postulaciones/{postulacion}/cv', [PostulacionController::class, 'cv'])
        ->name('postulaciones.cv');

    Route::patch('postulaciones/{postulacion}/en-revision', [PostulacionController::class, 'enRevision'])
        ->name('postulaciones.en_revision');
    Route::patch('postulaciones/{postulacion}/aceptar', [PostulacionController::class, 'aceptar'])
        ->name('postulaciones.aceptar');
    Route::patch('postulaciones/{postulacion}/rechazar', [PostulacionController::class, 'rechazar'])
        ->name('postulaciones.rechazar');
});

Route::middleware(['auth', 'rol:estudiante'])->prefix('estudiante')->name('estudiante.')->group(function () {
    Route::get('/dashboard', function () {
        $estudiante = auth()->user()->estudiante;

        return view('estudiante.dashboard', [
            'vacantesDisponibles' => Vacante::where('estado', 'publicada')
                ->whereHas('carreras', fn ($q) => $q->where('carreras.id', $estudiante->carrera_id))
                ->count(),
            'misPostulaciones' => $estudiante->postulaciones()->count(),
            'postulacionesAceptadas' => $estudiante->postulaciones()->where('estado', 'aceptada')->count(),
        ]);
    })->name('dashboard');

    Route::get('vacantes', [App\Http\Controllers\Estudiante\VacanteController::class, 'index'])->name('vacantes.index');
    Route::get('vacantes/{vacante}', [App\Http\Controllers\Estudiante\VacanteController::class, 'show'])->name('vacantes.show');
    Route::post('vacantes/{vacante}/analisis-cv', [EmployabilityAnalysisController::class, 'analyze'])
        ->middleware('throttle:cv-analysis')
        ->name('vacantes.analizar-cv');
    Route::post('vacantes/{vacante}/cv-adaptado', [EmployabilityAnalysisController::class, 'downloadAdaptedCv'])
        ->middleware('throttle:cv-analysis')
        ->name('vacantes.cv-adaptado');
    Route::post('vacantes/{vacante}/postular', [App\Http\Controllers\Estudiante\PostulacionController::class, 'store'])->name('vacantes.postular');
    Route::get('postulaciones', [App\Http\Controllers\Estudiante\PostulacionController::class, 'index'])->name('postulaciones.index');

    Route::get('perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::post('perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::get('perfil/cv', [PerfilController::class, 'cv'])->name('perfil.cv');
    Route::delete('perfil/cv', [PerfilController::class, 'destroyCv'])->name('perfil.cv.destroy');

    Route::post('perfil/formaciones', [PerfilProfesionalController::class, 'storeFormacion'])->name('perfil.formaciones.store');
    Route::put('perfil/formaciones/{formacion}', [PerfilProfesionalController::class, 'updateFormacion'])->name('perfil.formaciones.update');
    Route::delete('perfil/formaciones/{formacion}', [PerfilProfesionalController::class, 'destroyFormacion'])->name('perfil.formaciones.destroy');
    Route::post('perfil/experiencias', [PerfilProfesionalController::class, 'storeExperiencia'])->name('perfil.experiencias.store');
    Route::put('perfil/experiencias/{experiencia}', [PerfilProfesionalController::class, 'updateExperiencia'])->name('perfil.experiencias.update');
    Route::delete('perfil/experiencias/{experiencia}', [PerfilProfesionalController::class, 'destroyExperiencia'])->name('perfil.experiencias.destroy');
    Route::post('perfil/habilidades', [PerfilProfesionalController::class, 'storeHabilidad'])->name('perfil.habilidades.store');
    Route::put('perfil/habilidades/{habilidad}', [PerfilProfesionalController::class, 'updateHabilidad'])->name('perfil.habilidades.update');
    Route::delete('perfil/habilidades/{habilidad}', [PerfilProfesionalController::class, 'destroyHabilidad'])->name('perfil.habilidades.destroy');
    Route::post('perfil/idiomas', [PerfilProfesionalController::class, 'storeIdioma'])->name('perfil.idiomas.store');
    Route::put('perfil/idiomas/{idioma}', [PerfilProfesionalController::class, 'updateIdioma'])->name('perfil.idiomas.update');
    Route::delete('perfil/idiomas/{idioma}', [PerfilProfesionalController::class, 'destroyIdioma'])->name('perfil.idiomas.destroy');
    Route::post('perfil/certificaciones', [PerfilProfesionalController::class, 'storeCertificacion'])->name('perfil.certificaciones.store');
    Route::put('perfil/certificaciones/{certificacion}', [PerfilProfesionalController::class, 'updateCertificacion'])->name('perfil.certificaciones.update');
    Route::delete('perfil/certificaciones/{certificacion}', [PerfilProfesionalController::class, 'destroyCertificacion'])->name('perfil.certificaciones.destroy');
});

require __DIR__.'/auth.php';
