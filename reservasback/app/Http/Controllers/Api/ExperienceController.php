<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExperienceController extends Controller
{
    // Obtener todas las experiencias ordenadas
    public function index()
    {
        return Experience::orderBy('order')->get();
    }

    // Crear una nueva experiencia
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'slug'       => 'required|string|unique:experiences,slug',
            'category'   => 'required|string|max:50',
            'icon'       => 'nullable|string|max:255',
            'content'    => 'required|string',
            'order'      => 'nullable|integer',
            'image'      => 'nullable|image|max:2048', // para archivos
        ]);

        // Si se subió imagen, almacenarla
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('experiences', 'public');
            $data['image_url'] = asset('storage/' . $path);
        }


        $experience = Experience::create($data);

        // Devolver URL completa de imagen
        $experience->image_url = $experience->image_url
            ? asset('storage/' . $experience->image_url)
            : null;

        return response()->json($experience, 201);
    }

    // Actualizar experiencia existente
    public function update(Request $request, $id)
    {
        $experience = Experience::findOrFail($id);

        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'slug'       => "required|string|unique:experiences,slug,{$id}",
            'category'   => 'required|string|max:50',
            'icon'       => 'nullable|string|max:255',
            'content'    => 'required|string',
            'order'      => 'nullable|integer',
            'image'      => 'nullable|image|max:2048',
        ]);

        // Subir nueva imagen si fue enviada
        if ($request->hasFile('image')) {
            // Opcional: eliminar la anterior
            if ($experience->image_url && Storage::disk('public')->exists($experience->image_url)) {
                Storage::disk('public')->delete($experience->image_url);
            }

            $data['image_url'] = $request->file('image')->store('experiences', 'public');
        }

        $experience->update($data);

        $experience->image_url = $experience->image_url
            ? asset('storage/' . $experience->image_url)
            : null;

        return response()->json($experience);
    }

    // Eliminar experiencia
    public function destroy($id)
    {
        $experience = Experience::findOrFail($id);

        if ($experience->image_url && Storage::disk('public')->exists($experience->image_url)) {
            Storage::disk('public')->delete($experience->image_url);
        }

        $experience->delete();

        return response()->json(['message' => 'Eliminado correctamente'], 204);
    }
}
