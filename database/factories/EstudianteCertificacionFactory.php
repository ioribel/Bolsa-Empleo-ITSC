<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\EstudianteCertificacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstudianteCertificacion>
 */
class EstudianteCertificacionFactory extends Factory
{
    protected $model = EstudianteCertificacion::class;

    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::factory(),
            'nombre' => fake()->catchPhrase(),
            'entidad_emisora' => fake()->company(),
            'fecha_emision' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'fecha_vencimiento' => null,
            'url_credencial' => null,
        ];
    }
}
