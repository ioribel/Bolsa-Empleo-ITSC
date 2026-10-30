<?php

namespace Database\Factories;

use App\Models\Habilidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Habilidad>
 */
class HabilidadFactory extends Factory
{
    protected $model = Habilidad::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->word(),
            'tipo' => fake()->randomElement(['técnica', 'blanda']),
        ];
    }
}
