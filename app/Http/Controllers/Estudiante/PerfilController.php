<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function edit()
    {
        $estudiante = auth()->user()->estudiante;

        return view('estudiante.perfil.edit', compact('estudiante'));
    }

    public function update(Request $request)
    {
        $estudiante = auth()->user()->estudiante;

        $request->validate([
            'telefono' => ['nullable', 'string', 'max:20'],
            'resumen' => ['nullable', 'string', 'max:1000'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $estudiante->telefono = $request->telefono;
        $estudiante->resumen = $request->resumen;

        if ($request->hasFile('cv')) {
            if ($estudiante->cv_path) {
                Storage::disk('public')->delete($estudiante->cv_path);
            }

            $estudiante->cv_path = $request->file('cv')->store('cvs', 'public');
        }

        $estudiante->save();

        return back()->with('status', 'Perfil actualizado.');
    }
}