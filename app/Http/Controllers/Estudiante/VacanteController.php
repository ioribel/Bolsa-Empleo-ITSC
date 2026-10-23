<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Vacante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VacanteController extends Controller
{
    public function index(Request $request)
    {
        $estudiante = auth()->user()->estudiante;
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:255'],
            'empresa' => ['nullable', 'integer', 'exists:empresas,id'],
            'modalidad' => ['nullable', 'in:presencial,remoto,hibrido'],
            'tipo_empleo' => ['nullable', 'in:tiempo_completo,medio_tiempo,pasantia'],
        ]);
        $filters = array_filter($filters, fn ($value) => filled($value));

        $vacantes = Vacante::where('estado', 'publicada')
            ->whereHas('carreras', fn ($q) => $q->where('carreras.id', $estudiante->carrera_id))
            ->with('empresa')
            ->when($filters['buscar'] ?? null, function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('titulo', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%")
                        ->orWhereHas('empresa', fn ($empresa) => $empresa->where('nombre_empresa', 'like', "%{$buscar}%"));
                });
            })
            ->when($filters['empresa'] ?? null, fn ($query, $empresa) => $query->where('empresa_id', $empresa))
            ->when($filters['modalidad'] ?? null, fn ($query, $modalidad) => $query->where('modalidad', $modalidad))
            ->when($filters['tipo_empleo'] ?? null, fn ($query, $tipoEmpleo) => $query->where('tipo_empleo', $tipoEmpleo))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $empresas = Empresa::whereHas('vacantes', fn ($query) => $query
            ->where('estado', 'publicada')
            ->whereHas('carreras', fn ($carrera) => $carrera->where('carreras.id', $estudiante->carrera_id)))
            ->orderBy('nombre_empresa')
            ->get(['id', 'nombre_empresa']);

        return view('estudiante.vacantes.index', compact('vacantes', 'empresas', 'filters'));
    }

    public function show(string $id)
    {
        $estudiante = auth()->user()->estudiante;

        $vacante = Vacante::where('estado', 'publicada')
            ->with(['empresa', 'carreras'])
            ->findOrFail($id);

        $yaPostulado = $vacante->postulaciones()->where('estudiante_id', $estudiante->id)->exists();
        $tieneCvPerfil = false;

        if ($estudiante->cv_path) {
            foreach (['local', 'public'] as $disk) {
                if (Storage::disk($disk)->exists($estudiante->cv_path)) {
                    $tieneCvPerfil = true;
                    break;
                }
            }
        }

        $dirigidaASuCarrera = $vacante->carreras->contains('id', $estudiante->carrera_id);

        return view('estudiante.vacantes.show', compact('vacante', 'yaPostulado', 'tieneCvPerfil', 'dirigidaASuCarrera'));
    }
}