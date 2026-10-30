<?php

namespace Database\Factories;

use App\Models\Carrera;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Estudiante>
 */
class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['rol' => 'estudiante']),
            'carrera_id' => Carrera::query()->create(['nombre' => fake()->unique()->jobTitle()])->id,
            'matricula' => Str::upper(Str::random(10)),
            'tipo' => fake()->randomElement(['estudiante', 'egresado']),
        ];
    }
}
