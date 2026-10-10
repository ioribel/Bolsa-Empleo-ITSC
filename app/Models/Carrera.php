<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    //
    protected $fillable = ['nombre'];

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class);
    }

    public function vacantes()
    {
        return $this->belongsToMany(Vacante::class, 'vacante_carrera');
    }
}
