<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    //
    protected $fillable = ['user_id', 'nombre_empresa', 'rnc', 'sector', 'descripcion', 'telefono', 'sitio_web', 'logo_path', 'estado'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vacantes()
    {
        return $this->hasMany(Vacante::class);
    }
}
