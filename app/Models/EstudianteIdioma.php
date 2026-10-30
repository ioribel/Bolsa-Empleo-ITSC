<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudianteIdioma extends Model
{
    use HasFactory;

    protected $table = 'estudiante_idiomas';

    protected $fillable = ['estudiante_id', 'idioma', 'nivel'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
}
