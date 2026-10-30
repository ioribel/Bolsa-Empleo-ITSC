<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\EstudianteFormacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstudianteFormacion>
 */
class EstudianteFormacionFactory extends Factory
{
    protected $model = EstudianteFormacion::class;

    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::factory(),
            'institucion' => fake()->company(),
            'titulo' => fake()->jobTitle(),
            'nivel' => 'grado',
            'campo_estudio' => fake()->word(),
            'fecha_inicio' => fake()->dateTimeBetween('-5 years', '-2 years')->format('Y-m-d'),
            'fecha_fin' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'en_curso' => false,
            'descripcion' => fake()->sentence(),
        ];
    }
}
