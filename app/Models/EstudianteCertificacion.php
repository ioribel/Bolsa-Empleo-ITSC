<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudianteCertificacion extends Model
{
    use HasFactory;

    protected $table = 'estudiante_certificaciones';

    protected $fillable = [
        'estudiante_id', 'nombre', 'entidad_emisora', 'fecha_emision',
        'fecha_vencimiento', 'url_credencial',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
}
