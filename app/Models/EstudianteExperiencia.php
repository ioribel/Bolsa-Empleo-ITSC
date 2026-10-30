<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudianteExperiencia extends Model
{
    use HasFactory;

    protected $table = 'estudiante_experiencias';

    protected $fillable = [
        'estudiante_id', 'empresa', 'cargo', 'tipo', 'ubicacion',
        'fecha_inicio', 'fecha_fin', 'actual', 'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'actual' => 'boolean',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
}
