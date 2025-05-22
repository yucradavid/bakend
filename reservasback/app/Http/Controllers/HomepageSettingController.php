<?php

namespace App\Http\Controllers;

use App\Models\HomepageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomepageSettingController extends Controller
{
    // Listar todas las configuraciones
    public function index()
    {
        return HomepageSetting::orderByDesc('created_at')->get()->map(function ($item) {
            return [
                'id'               => $item->id,
                'title_text'       => $item->title_text,
                'title_color'      => $item->title_color,
                'title_size'       => $item->title_size,
                'description'      => $item->description,
                'background_color' => $item->background_color,
                'active'           => $item->active,
                'created_at'       => $item->created_at->toDateTimeString(),
                'image_urls'       => collect($item->image_path ?? [])->map(fn($img) => asset('storage/' . $img)),
            ];
        });
    }

    // Obtener la configuración activa
    public function active()
    {
        $setting = HomepageSetting::where('active', true)->first();

        if (!$setting) {
            return response()->json(['error' => 'No hay configuración activa.'], 404);
        }

        return response()->json([
            'id'               => $setting->id,
            'title_text'       => $setting->title_text,
            'title_color'      => $setting->title_color,
            'title_size'       => $setting->title_size,
            'description'      => $setting->description,
            'background_color' => $setting->background_color,
            'image_urls'       => collect($setting->image_path ?? [])->map(fn($img) => asset('storage/' . $img)),
        ]);
    }

    // Crear nueva configuración
    public function store(Request $request)
    {
        $data = $request->validate([
            'title_text'       => 'nullable|string|max:255',
            'title_color'      => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'title_size'       => 'nullable|string',
            'description'      => 'nullable|string',
            'background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'image.*'          => 'nullable|image|max:2048',
        ]);

        $paths = [];
        if ($request->hasFile('image')) {
            $images = $request->file('image');
            if (!is_array($images)) $images = [$images];
            foreach ($images as $img) {
                $paths[] = $img->store('homepage', 'public');
            }
            $data['image_path'] = $paths;
        }

        $data['active'] = false;

        $new = HomepageSetting::create($data);

        return response()->json(['message' => 'Configuración creada.', 'id' => $new->id], 201);
    }

    // Actualizar una configuración específica por ID
    public function update(Request $request, $id)
    {
        $request->validate([
            'title_text'       => 'nullable|string|max:255',
            'title_color'      => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'title_size'       => 'nullable|string',
            'description'      => 'nullable|string',
            'background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'image.*'          => 'nullable|image|max:2048',
        ]);

        $settings = HomepageSetting::findOrFail($id);

        $data = $request->only(['title_text', 'title_color', 'title_size', 'description', 'background_color']);

        if ($request->hasFile('image')) {
            if (is_array($settings->image_path)) {
                foreach ($settings->image_path as $img) {
                    Storage::disk('public')->delete($img);
                }
            }

            $uploaded = $request->file('image');
            if (!is_array($uploaded)) $uploaded = [$uploaded];

            $paths = [];
            foreach ($uploaded as $file) {
                $paths[] = $file->store('homepage', 'public');
            }

            $data['image_path'] = $paths;
        }

        $settings->update($data);

        return response()->json(['message' => 'Configuración actualizada.']);
    }

    // Eliminar todas las imágenes de la configuración activa
    public function removeImage()
    {
        $home = HomepageSetting::where('active', true)->first();

        if ($home && is_array($home->image_path)) {
            foreach ($home->image_path as $img) {
                Storage::disk('public')->delete($img);
            }

            $home->image_path = [];
            $home->save();
        }

        return response()->json(['message' => 'Todas las imágenes fueron eliminadas.']);
    }

    // Activar una configuración específica
    public function activate($id)
    {
        HomepageSetting::query()->update(['active' => false]);

        $setting = HomepageSetting::findOrFail($id);
        $setting->active = true;
        $setting->save();

        return response()->json(['message' => 'Configuración activada correctamente.']);
    }

    // Método de prueba pública
    public function public()
    {
        return response()->json(['message' => 'Este es un método público para home'], 200);
    }
    public function destroy($id)
{
    $setting = HomepageSetting::findOrFail($id);

    // Eliminar imágenes asociadas si existen
    if (is_array($setting->image_path)) {
        foreach ($setting->image_path as $img) {
            Storage::disk('public')->delete($img);
        }
    }

    $setting->delete();

    return response()->json(['message' => 'Configuración eliminada correctamente.']);
}

}
