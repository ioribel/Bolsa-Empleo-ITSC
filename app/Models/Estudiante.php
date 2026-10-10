<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    //
    protected $fillable = ['user_id', 'carrera_id', 'matricula', 'tipo', 'telefono', 'cv_path', 'resumen'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class);
    }
}
