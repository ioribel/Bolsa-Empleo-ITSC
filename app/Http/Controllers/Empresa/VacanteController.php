<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreVacanteRequest;
use App\Models\Carrera;
use App\Http\Requests\UpdateVacanteRequest;

class VacanteController extends Controller
{
    

    public function index(Request $request)
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'in:borrador,publicada,cerrada'],
            'carrera' => ['nullable', 'integer', 'exists:carreras,id'],
            'modalidad' => ['nullable', 'in:presencial,remoto,hibrido'],
            'tipo_empleo' => ['nullable', 'in:tiempo_completo,medio_tiempo,pasantia'],
        ]);
        $filters = array_filter($filters, fn ($value) => filled($value));

        $vacantes = $request->user()->empresa->vacantes()
            ->withCount('postulaciones')
            ->when($filters['buscar'] ?? null, function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('titulo', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->when($filters['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->when($filters['carrera'] ?? null, fn ($query, $carrera) => $query->whereHas('carreras', fn ($relacion) => $relacion->where('carreras.id', $carrera)))
            ->when($filters['modalidad'] ?? null, fn ($query, $modalidad) => $query->where('modalidad', $modalidad))
            ->when($filters['tipo_empleo'] ?? null, fn ($query, $tipoEmpleo) => $query->where('tipo_empleo', $tipoEmpleo))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('empresa.vacantes.index', [
            'vacantes' => $vacantes,
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        if (auth()->user()->empresa->estado !== 'aprobada') {
            return redirect()->route('empresa.vacantes.index')
                ->with('error', 'No puedes crear vacantes hasta que tu empresa sea aprobada por un administrador.');
        }
        $carreras = Carrera::orderBy('nombre')->get();

        return view('empresa.vacantes.create', compact('carreras'));
    }

    public function store(StoreVacanteRequest $request)
    {
        $vacante = $request->user()->empresa->vacantes()->create($request->validated());

        $vacante->carreras()->sync($request->carreras);

        return redirect()->route('empresa.vacantes.index')
            ->with('status', 'Vacante creada como borrador.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $vacante = auth()->user()->empresa->vacantes()
            ->with('carreras')
            ->withCount('postulaciones')
            ->findOrFail($id);

        return view('empresa.vacantes.show', compact('vacante'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $vacante = auth()->user()->empresa->vacantes()->with('carreras')->findOrFail($id);
        $carreras = Carrera::orderBy('nombre')->get();

        return view('empresa.vacantes.edit', compact('vacante', 'carreras'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVacanteRequest $request, string $id)
    {
        //
        $vacante = auth()->user()->empresa->vacantes()->findOrFail($id);

        $vacante->update($request->validated());
        $vacante->carreras()->sync($request->carreras);

        return redirect()->route('empresa.vacantes.index')
            ->with('status', 'Vacante actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $vacante = auth()->user()->empresa->vacantes()->findOrFail($id);

        $vacante->delete();

        return redirect()->route('empresa.vacantes.index')
            ->with('status', 'Vacante eliminada.');
    }

    public function publicar(string $id)
{
    $vacante = auth()->user()->empresa->vacantes()->findOrFail($id);

    try {
        $vacante->publicar();
    } catch (\RuntimeException $e) {
        return back()->with('error', $e->getMessage());
    }

    return back()->with('status', 'Vacante publicada. Ya es visible para los estudiantes.');
}

    public function cerrar(string $id)
    {
        $vacante = auth()->user()->empresa->vacantes()->findOrFail($id);

        try {
            $vacante->cerrar();
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Vacante cerrada. Ya no acepta nuevas postulaciones.');
    }
}
