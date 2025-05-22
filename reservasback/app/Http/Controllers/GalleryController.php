<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
      // Listar todas las entradas (público)
      public function index()
      {
          $items = Gallery::all()->map(fn($g) => [
              'id'         => $g->id,
              'caption'    => $g->caption,
              'image_url'  => asset('storage/' . $g->image_path),
          ]);
          return response()->json($items);
      }

      // Agregar nueva imagen (solo admin)
      public function store(Request $request)
      {
          try {
              $request->validate([
                  'images.*'   => 'required|image|max:2048',
                  'captions'   => 'nullable|array',
                  'captions.*' => 'nullable|string'
              ]);

              $items = [];

              foreach ($request->file('images') as $index => $image) {
                  $path = $image->store('gallery', 'public');

                  $item = Gallery::create([
                      'image_path' => $path,
                      'caption'    => $request->captions[$index] ?? null,
                  ]);

                  $items[] = [
                      'id'        => $item->id,
                      'caption'   => $item->caption,
                      'image_url' => asset('storage/' . $item->image_path),
                  ];
              }

              return response()->json($items, 201);
          } catch (\Throwable $e) {
              \Log::error('Error al subir imágenes: ' . $e->getMessage());
              return response()->json(['error' => 'Error interno del servidor'], 500);
          }
      }



      // Actualizar caption o imagen (solo admin)
      public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $data = $request->validate([
            'caption' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Eliminar la imagen anterior
            Storage::disk('public')->delete($gallery->image_path);

            // Almacenar la nueva imagen
            $data['image_path'] = $request->file('image')->store('gallery', 'public');
        }

        $gallery->update($data);

        return response()->json([
            'id' => $gallery->id,
            'caption' => $gallery->caption,
            'image_url' => asset('storage/' . $gallery->image_path),
        ]);
    }


      // Eliminar (solo admin)
      public function destroy($id)
      {
          $g = Gallery::findOrFail($id);
          Storage::disk('public')->delete($g->image_path);
          $g->delete();

          return response()->json(['message' => 'Eliminado'], 204);
      }
}
