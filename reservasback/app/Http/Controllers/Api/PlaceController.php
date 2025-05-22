<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlaceController extends Controller
{
    public function index()
    {
        return response()->json(Place::orderBy('created_at', 'desc')->get());
    }

    public function show($id)
    {
        return response()->json(Place::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'excerpt'    => 'required|string',
            'activities' => 'nullable|array',
            'stats'      => 'nullable|array',
            'image_file' => 'nullable|image|max:2048',
            'image_url'  => 'nullable|url|max:2048',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
            'category'   => 'nullable|string|max:100',
        ]);

        // Si el usuario sube una imagen, se procesa y guarda
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('places', 'public');
            $data['image_url'] = asset("storage/$path");
        }

        // Crea un nuevo destino turístico con los datos validados
        $place = Place::create($data);
        return response()->json($place, 201);
    }

    public function update(Request $request, $id)
    {
        $place = Place::findOrFail($id);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'excerpt'    => 'required|string',
            'activities' => 'nullable|array',
            'stats'      => 'nullable|array',
            'image_file' => 'nullable|image|max:2048',
            'image_url'  => 'nullable|url|max:2048',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
            'category'   => 'nullable|string|max:100',
        ]);

        // Si el usuario sube una nueva imagen, se reemplaza la anterior
        if ($request->hasFile('image_file')) {
            // Borrar la imagen anterior si existe
            if ($place->image_url && str_contains($place->image_url, '/storage/')) {
                $old = str_replace(asset('storage/') . '', '', $place->image_url);
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('image_file')->store('places', 'public');
            $data['image_url'] = asset("storage/$path");
        }

        // Actualiza los datos del lugar con los nuevos valores
        $place->update($data);
        return response()->json($place);
    }

    public function destroy($id)
    {
        $place = Place::findOrFail($id);

        // Si tiene imagen, se elimina
        if ($place->image_url && str_contains($place->image_url, '/storage/')) {
            $old = str_replace(asset('storage/') . '', '', $place->image_url);
            Storage::disk('public')->delete($old);
        }

        // Elimina el destino turístico
        $place->delete();
        return response()->json(null, 204);
    }
    private function normalizeImageUrl($url)
    {
        if (!$url) return null;

        // Si es relativa (ej. /storage/...), se convierte a URL completa
        if (str_starts_with($url, '/')) {
            return url($url);
        }

        // Si ya es una URL completa, se deja igual
        return $url;
    }
}
