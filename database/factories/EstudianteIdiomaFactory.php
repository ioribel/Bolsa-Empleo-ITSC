<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\EstudianteIdioma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstudianteIdioma>
 */
class EstudianteIdiomaFactory extends Factory
{
    protected $model = EstudianteIdioma::class;

    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::factory(),
            'idioma' => fake()->unique()->word(),
            'nivel' => 'intermedio',
        ];
    }
}
