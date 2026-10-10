<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacante extends Model
{
    //
    protected $fillable = ['empresa_id', 'titulo', 'descripcion', 'requisitos', 'tipo_empleo', 'modalidad', 'ubicacion', 'salario_min', 'salario_max', 'fecha_cierre', 'estado'];
    protected $casts = [
    'fecha_cierre' => 'date',
    'salario_min' => 'decimal:2',
    'salario_max' => 'decimal:2',
    ];
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function carreras()
    {
        return $this->belongsToMany(Carrera::class, 'vacante_carrera');
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class);
    }

    public function publicar(): void
    {
        if ($this->estado !== 'borrador') {
            throw new \RuntimeException('Solo una vacante en borrador puede publicarse.');
        }

        $this->update(['estado' => 'publicada']);
    }

    public function cerrar(): void
    {
        if ($this->estado !== 'publicada') {
            throw new \RuntimeException('Solo una vacante publicada puede cerrarse.');
        }

        $this->update(['estado' => 'cerrada']);
    }
}