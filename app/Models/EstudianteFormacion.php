<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudianteFormacion extends Model
{
    use HasFactory;

    protected $table = 'estudiante_formaciones';

    protected $fillable = [
        'estudiante_id', 'institucion', 'titulo', 'nivel', 'campo_estudio',
        'fecha_inicio', 'fecha_fin', 'en_curso', 'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'en_curso' => 'boolean',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
}
