<?php

use App\Http\Controllers\ProfileController;
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
});

Route::middleware(['auth', 'rol:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard', [
            'totalEstudiantes' => \App\Models\Estudiante::count(),
            'empresasAprobadas' => \App\Models\Empresa::where('estado', 'aprobada')->count(),
            'empresasPendientes' => \App\Models\Empresa::where('estado', 'pendiente')->count(),
            'vacantesActivas' => \App\Models\Vacante::where('estado', 'publicada')->count(),
        ]);
    })->name('dashboard');

    Route::get('empresas', [\App\Http\Controllers\Admin\EmpresaController::class, 'index'])->name('empresas.index');
    Route::patch('empresas/{empresa}/aprobar', [\App\Http\Controllers\Admin\EmpresaController::class, 'aprobar'])->name('empresas.aprobar');
    Route::patch('empresas/{empresa}/bloquear', [\App\Http\Controllers\Admin\EmpresaController::class, 'bloquear'])->name('empresas.bloquear');
    Route::patch('empresas/{empresa}/rechazar', [\App\Http\Controllers\Admin\EmpresaController::class, 'rechazar'])->name('empresas.rechazar');

    Route::get('carreras', [\App\Http\Controllers\Admin\CarreraController::class, 'index'])->name('carreras.index');
    Route::post('carreras', [\App\Http\Controllers\Admin\CarreraController::class, 'store'])->name('carreras.store');
    Route::patch('carreras/{carrera}', [\App\Http\Controllers\Admin\CarreraController::class, 'update'])->name('carreras.update');
    Route::delete('carreras/{carrera}', [\App\Http\Controllers\Admin\CarreraController::class, 'destroy'])->name('carreras.destroy');
    Route::get('usuarios', [\App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('usuarios.index');
    Route::patch('usuarios/{usuario}/bloquear', [\App\Http\Controllers\Admin\UsuarioController::class, 'bloquear'])->name('usuarios.bloquear');
    Route::patch('usuarios/{usuario}/activar', [\App\Http\Controllers\Admin\UsuarioController::class, 'activar'])->name('usuarios.activar');
});

Route::middleware(['auth', 'rol:empresa'])->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/dashboard', function () {
        $empresa = auth()->user()->empresa;

        return view('empresa.dashboard', [
            'vacantesActivas' => $empresa->vacantes()->where('estado', 'publicada')->count(),
            'postulacionesRecibidas' => \App\Models\Postulacion::whereHas('vacante', fn ($q) => $q->where('empresa_id', $empresa->id))->count(),
            'pendientesRevisar' => \App\Models\Postulacion::whereHas('vacante', fn ($q) => $q->where('empresa_id', $empresa->id))->where('estado', 'recibida')->count(),
        ]);
    })->name('dashboard');

    Route::resource('vacantes', \App\Http\Controllers\Empresa\VacanteController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::patch('vacantes/{vacante}/publicar', [\App\Http\Controllers\Empresa\VacanteController::class, 'publicar'])
    ->name('vacantes.publicar');

    Route::patch('vacantes/{vacante}/cerrar', [\App\Http\Controllers\Empresa\VacanteController::class, 'cerrar'])
    ->name('vacantes.cerrar');

    Route::get('vacantes/{vacante}/postulaciones', [\App\Http\Controllers\Empresa\PostulacionController::class, 'index'])
    ->name('vacantes.postulaciones.index');

    Route::patch('postulaciones/{postulacion}/en-revision', [\App\Http\Controllers\Empresa\PostulacionController::class, 'enRevision'])
        ->name('postulaciones.en_revision');
    Route::patch('postulaciones/{postulacion}/aceptar', [\App\Http\Controllers\Empresa\PostulacionController::class, 'aceptar'])
        ->name('postulaciones.aceptar');
    Route::patch('postulaciones/{postulacion}/rechazar', [\App\Http\Controllers\Empresa\PostulacionController::class, 'rechazar'])
        ->name('postulaciones.rechazar');
});

Route::middleware(['auth', 'rol:estudiante'])->prefix('estudiante')->name('estudiante.')->group(function () {
    Route::get('/dashboard', function () {
        $estudiante = auth()->user()->estudiante;

        return view('estudiante.dashboard', [
            'vacantesDisponibles' => \App\Models\Vacante::where('estado', 'publicada')
                ->whereHas('carreras', fn ($q) => $q->where('carreras.id', $estudiante->carrera_id))
                ->count(),
            'misPostulaciones' => $estudiante->postulaciones()->count(),
            'postulacionesAceptadas' => $estudiante->postulaciones()->where('estado', 'aceptada')->count(),
        ]);
    })->name('dashboard');

    Route::get('vacantes', [\App\Http\Controllers\Estudiante\VacanteController::class, 'index'])->name('vacantes.index');
    Route::get('vacantes/{vacante}', [\App\Http\Controllers\Estudiante\VacanteController::class, 'show'])->name('vacantes.show');
    Route::post('vacantes/{vacante}/postular', [\App\Http\Controllers\Estudiante\PostulacionController::class, 'store'])->name('vacantes.postular');
    Route::get('postulaciones', [\App\Http\Controllers\Estudiante\PostulacionController::class, 'index'])->name('postulaciones.index');

    Route::get('perfil', [\App\Http\Controllers\Estudiante\PerfilController::class, 'edit'])->name('perfil.edit');
    Route::post('perfil', [\App\Http\Controllers\Estudiante\PerfilController::class, 'update'])->name('perfil.update');
});

require __DIR__.'/auth.php';