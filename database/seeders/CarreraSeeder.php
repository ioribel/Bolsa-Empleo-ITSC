<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarreraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carreras = [
            // Informática
            'Desarrollo de Software',
            'Administración de Redes',
            'Soporte Informático',
            'Desarrollo de Aplicaciones de Animación y Videojuegos',

            // Artes
            'Diseño Gráfico',
            'Diseño de Interiores',
            'Diseño de Modas',
            'Fotografía',
            'Producción de Eventos',

            // Salud
            'Enfermería',
            'Imágenes Médicas',
            'Higiene Dental',
            'Mecánica Dental',

            // Turismo
            'Gestión de Cocina (Gastronomía)',
            'Panadería y Repostería',
            'Gestión de Servicios de Alimentos y Bebidas',
            'Gestión de Servicios de Eventos y Viajes',

            // Industrial
            'Logística',
            'Diseño Industrial',
            'Dirección de Proyectos',
            'Tecnologías de la Manufactura',

            // Electromecánica
            'Tecnología de Semiconductores',
            'Electricidad',
            'Refrigeración',
            'Electrónica',
            'Mecánica Automotriz',
            'Mantenimiento de Sistemas de Electromedicina',
        ];

        foreach ($carreras as $nombre) {
            \App\Models\Carrera::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
