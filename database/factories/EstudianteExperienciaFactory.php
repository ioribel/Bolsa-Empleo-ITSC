<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\EstudianteExperiencia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstudianteExperiencia>
 */
class EstudianteExperienciaFactory extends Factory
{
    protected $model = EstudianteExperiencia::class;

    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::factory(),
            'empresa' => fake()->company(),
            'cargo' => fake()->jobTitle(),
            'tipo' => 'empleo',
            'ubicacion' => fake()->city(),
            'fecha_inicio' => fake()->dateTimeBetween('-5 years', '-2 years')->format('Y-m-d'),
            'fecha_fin' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'actual' => false,
            'descripcion' => fake()->sentence(),
        ];
    }
}
