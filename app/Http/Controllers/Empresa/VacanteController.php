<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreVacanteRequest;
use App\Models\Carrera;
use App\Http\Requests\UpdateVacanteRequest;

class VacanteController extends Controller
{
    

    public function index()
    {
        $vacantes = auth()->user()->empresa->vacantes()
            ->withCount('postulaciones')
            ->latest()
            ->get();

        return view('empresa.vacantes.index', compact('vacantes'));
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
