<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function edit()
    {
        $empresa = auth()->user()->empresa;

        abort_unless($empresa, 404);

        return view('empresa.perfil.edit', compact('empresa'));
    }

    public function update(Request $request)
    {
        $empresa = $request->user()->empresa;

        abort_unless($empresa, 404);

        $validated = $request->validate([
            'nombre_empresa' => ['required', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'sitio_web' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($empresa->logo_path) {
                Storage::disk('public')->delete($empresa->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $empresa->fill([
            'nombre_empresa' => $validated['nombre_empresa'],
            'sector' => $validated['sector'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'sitio_web' => $validated['sitio_web'] ?? null,
        ]);

        if (array_key_exists('logo_path', $validated)) {
            $empresa->logo_path = $validated['logo_path'];
        }

        $empresa->save();

        return redirect()->route('empresa.perfil.edit')->with('status', 'Perfil de la empresa actualizado correctamente.');
    }
}
