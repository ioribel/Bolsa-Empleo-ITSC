<?php

namespace Database\Seeders;

use App\Models\Habilidad;
use Illuminate\Database\Seeder;

class HabilidadSeeder extends Seeder
{
    public function run(): void
    {
        $habilidades = [
            'técnica' => [
                'php', 'laravel', 'javascript', 'typescript', 'html', 'css', 'tailwind css',
                'react', 'vue', 'alpine.js', 'python', 'java', 'c#', 'sql', 'mysql',
                'postgresql', 'git', 'github', 'linux', 'redes informáticas', 'ciberseguridad',
                'soporte técnico', 'excel', 'power bi', 'diseño gráfico', 'autocad',
                'contabilidad', 'marketing digital', 'gestión de proyectos',
            ],
            'blanda' => [
                'comunicación', 'trabajo en equipo', 'resolución de problemas', 'adaptabilidad',
                'pensamiento crítico', 'organización', 'liderazgo', 'atención al cliente',
                'creatividad', 'gestión del tiempo', 'responsabilidad', 'aprendizaje continuo',
            ],
        ];

        foreach ($habilidades as $tipo => $nombres) {
            foreach ($nombres as $nombre) {
                Habilidad::firstOrCreate(['nombre' => $nombre], ['tipo' => $tipo]);
            }
        }
    }
}
