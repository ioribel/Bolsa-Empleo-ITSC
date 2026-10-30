<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Habilidad extends Model
{
    use HasFactory;

    protected $table = 'habilidades';

    protected $fillable = ['nombre', 'tipo'];

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_habilidad')
            ->withPivot('nivel')
            ->withTimestamps();
    }
}
