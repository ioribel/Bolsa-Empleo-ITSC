<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Empresa;
use App\Models\Vacante;
use Illuminate\Http\Request;

class VacanteController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:255'],
            'empresa' => ['nullable', 'integer', 'exists:empresas,id'],
            'carrera' => ['nullable', 'integer', 'exists:carreras,id'],
            'modalidad' => ['nullable', 'in:presencial,remoto,hibrido'],
            'tipo_empleo' => ['nullable', 'in:tiempo_completo,medio_tiempo,pasantia'],
        ]);

        $vacantes = Vacante::query()
            ->where('estado', 'publicada')
            ->with(['empresa', 'carreras'])
            ->withCount('postulaciones')
            ->when($filters['buscar'] ?? null, function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('titulo', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%")
                        ->orWhereHas('empresa', fn ($empresa) => $empresa->where('nombre_empresa', 'like', "%{$buscar}%"));
                });
            })
            ->when($filters['empresa'] ?? null, fn ($query, $empresa) => $query->where('empresa_id', $empresa))
            ->when($filters['carrera'] ?? null, fn ($query, $carrera) => $query->whereHas('carreras', fn ($relacion) => $relacion->where('carreras.id', $carrera)))
            ->when($filters['modalidad'] ?? null, fn ($query, $modalidad) => $query->where('modalidad', $modalidad))
            ->when($filters['tipo_empleo'] ?? null, fn ($query, $tipoEmpleo) => $query->where('tipo_empleo', $tipoEmpleo))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.vacantes.index', [
            'vacantes' => $vacantes,
            'empresas' => Empresa::orderBy('nombre_empresa')->get(['id', 'nombre_empresa']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => $filters,
        ]);
    }
}