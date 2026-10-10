<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use Illuminate\Http\Request;

class CarreraController extends Controller
{
    public function index()
    {
        $carreras = Carrera::withCount(['estudiantes', 'vacantes'])->orderBy('nombre')->get();

        return view('admin.carreras.index', compact('carreras'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:carreras,nombre'],
        ]);

        Carrera::create(['nombre' => $request->nombre]);

        return back()->with('status', 'Carrera creada.');
    }

    public function update(Request $request, string $id)
    {
        $carrera = Carrera::findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:carreras,nombre,'.$carrera->id],
        ]);

        $carrera->update(['nombre' => $request->nombre]);

        return back()->with('status', 'Carrera actualizada.');
    }

    public function destroy(string $id)
    {
        $carrera = Carrera::withCount(['estudiantes', 'vacantes'])->findOrFail($id);

        if ($carrera->estudiantes_count > 0 || $carrera->vacantes_count > 0) {
            return back()->with('error', 'No se puede eliminar esta carrera porque tiene estudiantes o vacantes asociadas.');
        }

        $carrera->delete();

        return back()->with('status', 'Carrera eliminada.');
    }
}