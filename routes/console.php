<?php

use App\Models\Vacante;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('vacantes:cerrar-vencidas', function () {
    $cerradas = Vacante::query()
        ->where('estado', 'publicada')
        ->whereDate('fecha_cierre', '<', today())
        ->update(['estado' => 'cerrada']);

    $this->info("Se cerraron {$cerradas} vacantes vencidas.");
})->purpose('Cerrar vacantes publicadas cuya fecha de cierre ya venció');

Schedule::command('vacantes:cerrar-vencidas')->dailyAt('00:05');
