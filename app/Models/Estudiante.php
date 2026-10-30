<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estudiante extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'carrera_id', 'matricula', 'tipo', 'telefono', 'cv_path',
        'cv_original_name', 'resumen', 'titular', 'linkedin_url', 'github_url',
        'portafolio_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }

    public function formaciones(): HasMany
    {
        return $this->hasMany(EstudianteFormacion::class);
    }

    public function experiencias(): HasMany
    {
        return $this->hasMany(EstudianteExperiencia::class);
    }

    public function habilidades(): BelongsToMany
    {
        return $this->belongsToMany(Habilidad::class, 'estudiante_habilidad')
            ->withPivot('nivel')
            ->withTimestamps();
    }

    public function idiomas(): HasMany
    {
        return $this->hasMany(EstudianteIdioma::class);
    }

    public function certificaciones(): HasMany
    {
        return $this->hasMany(EstudianteCertificacion::class);
    }

    public function porcentajeCompletitud(): int
    {
        // Eight equally weighted checks: headline, summary, CV, education, experience,
        // three skills, one language, and at least one professional link.
        $criterios = [
            filled($this->titular),
            filled($this->resumen),
            filled($this->cv_path),
            $this->formaciones()->exists(),
            $this->experiencias()->exists(),
            $this->habilidades()->count() >= 3,
            $this->idiomas()->exists(),
            filled($this->linkedin_url) || filled($this->github_url) || filled($this->portafolio_url),
        ];

        return (int) round(count(array_filter($criterios)) / count($criterios) * 100);
    }

    /**
     * Returns the profile sections still needed to meet the completion criteria.
     *
     * @return list<string>
     */
    public function perfilPendienteCompletar(): array
    {
        $pending = [];

        if (! filled($this->titular)) {
            $pending[] = 'Agregar un titular profesional';
        }
        if (! filled($this->resumen)) {
            $pending[] = 'Escribir un resumen profesional';
        }
        if (! filled($this->cv_path)) {
            $pending[] = 'Cargar el currículum';
        }
        if (! $this->formaciones()->exists()) {
            $pending[] = 'Agregar formación académica';
        }
        if (! $this->experiencias()->exists()) {
            $pending[] = 'Agregar experiencia o pasantía';
        }
        if ($this->habilidades()->count() < 3) {
            $pending[] = 'Agregar al menos 3 habilidades';
        }
        if (! $this->idiomas()->exists()) {
            $pending[] = 'Agregar un idioma';
        }
        if (! filled($this->linkedin_url) && ! filled($this->github_url) && ! filled($this->portafolio_url)) {
            $pending[] = 'Agregar al menos un enlace profesional';
        }

        return $pending;
    }

    public function perfilComoTexto(): string
    {
        $lines = [];
        $append = function (string $label, mixed $value) use (&$lines): void {
            if (! filled($value)) {
                return;
            }

            $safe = $this->textoProfesionalSeguro((string) $value);
            if ($safe !== '') {
                $lines[] = "{$label}: {$safe}";
            }
        };

        $append('Titular', $this->titular);
        $append('Resumen', $this->resumen);

        foreach ($this->formaciones as $formacion) {
            $append('Formación', implode(' — ', array_filter([
                $formacion->titulo,
                $formacion->institucion,
                $formacion->campo_estudio,
                $formacion->nivel,
                $formacion->descripcion,
            ])));
        }

        foreach ($this->experiencias as $experiencia) {
            $append('Experiencia', implode(' — ', array_filter([
                $experiencia->cargo,
                $experiencia->empresa,
                $experiencia->tipo,
                $experiencia->descripcion,
            ])));
        }

        foreach ($this->habilidades as $habilidad) {
            $append('Habilidad', $habilidad->nombre.' ('.$habilidad->pivot->nivel.')');
        }

        foreach ($this->idiomas as $idioma) {
            $append('Idioma', $idioma->idioma.' ('.$idioma->nivel.')');
        }

        foreach ($this->certificaciones as $certificacion) {
            $append('Certificación', implode(' — ', array_filter([
                $certificacion->nombre,
                $certificacion->entidad_emisora,
            ])));
        }

        return implode("\n", $lines);
    }

    private function textoProfesionalSeguro(string $text): string
    {
        $text = strip_tags($text);
        $privateValues = array_filter([
            $this->matricula,
            $this->telefono,
            $this->user?->email,
            $this->user?->name,
        ]);

        foreach ($privateValues as $privateValue) {
            $text = str_ireplace($privateValue, '', $text);
        }

        $text = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '', $text) ?? $text;
        $text = preg_replace('~(?:https?|ftp)://\S+|www\.\S+|mailto:\S+~i', '', $text) ?? $text;
        $text = preg_replace('/\b(?:[a-z0-9-]+\.)+[a-z]{2,}(?:\/\S*)?/i', '', $text) ?? $text;
        $text = preg_replace('/(?<!\d)\+?\d[\d\s().-]{5,}\d(?!\d)/', '', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }
}
