<?php

namespace App\Models;

use App\Events\ApplicationStatusChanged;
use Illuminate\Database\Eloquent\Model;

class Postulacion extends Model
{
    //
    protected $table = 'postulaciones';

    protected $fillable = ['vacante_id', 'estudiante_id', 'carta_presentacion', 'cv_path', 'estado'];

    public function vacante()
    {
        return $this->belongsTo(Vacante::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function marcarEnRevision(): void
    {
        if ($this->estado !== 'recibida') {
            throw new \RuntimeException('Solo una postulación recibida puede pasar a revisión.');
        }

        $previousStatus = $this->estado;
        $this->update(['estado' => 'en_revision']);
        ApplicationStatusChanged::dispatch((int) $this->getKey(), $previousStatus, 'en_revision');
    }

    public function aceptar(): void
    {
        if ($this->estado !== 'en_revision') {
            throw new \RuntimeException('Solo una postulación en revisión puede aceptarse.');
        }

        $previousStatus = $this->estado;
        $this->update(['estado' => 'aceptada']);
        ApplicationStatusChanged::dispatch((int) $this->getKey(), $previousStatus, 'aceptada');
    }

    public function rechazar(): void
    {
        if (! in_array($this->estado, ['recibida', 'en_revision'])) {
            throw new \RuntimeException('Esta postulación ya no puede rechazarse.');
        }

        $previousStatus = $this->estado;
        $this->update(['estado' => 'rechazada']);
        ApplicationStatusChanged::dispatch((int) $this->getKey(), $previousStatus, 'rechazada');
    }
}
