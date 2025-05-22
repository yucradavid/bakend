<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Product;
use App\Models\Entrepreneur;
use App\Models\PackageImage;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    /**
     * Listar todos los paquetes con relaciones.
     */
    public function index()
{
    try {
        $packages = Package::with(['entrepreneur', 'products', 'images'])->latest()->get();
        return response()->json($packages);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Error interno',
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
}


    /**
     * Mostrar un solo paquete.
     */
    public function show($id)
    {
        $package = Package::with(['entrepreneur', 'products', 'images'])->find($id);

        if (!$package) {
            return response()->json(['error' => 'Paquete no encontrado'], 404);
        }

        return response()->json($package);
    }

    /**
     * Crear nuevo paquete turístico con múltiples imágenes.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $entrepreneur = Entrepreneur::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        // Calcular precio si no se especifica
        $price = $validated['price'] ?? Product::whereIn('id', $validated['product_ids'])->sum('price');

        $package = Package::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $price,
            'entrepreneur_id' => $entrepreneur->id,
        ]);

        $package->products()->attach($validated['product_ids']);

        // Procesar imágenes si existen
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('package_images', 'public');
                $package->images()->create(['image_path' => $path]);
            }
        }

        return response()->json(['message' => 'Paquete creado correctamente', 'data' => $package->load('images')], 201);
    }

    /**
     * Mostrar solo los paquetes del emprendedor autenticado.
     */
    public function myPackages()
    {
        $entrepreneur = auth()->user()->entrepreneur;

        if (!$entrepreneur) {
            return response()->json(['message' => 'No eres un emprendedor.'], 403);
        }

        $packages = Package::with(['products', 'images'])
            ->where('entrepreneur_id', $entrepreneur->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($packages);
    }

    /**
     * Actualizar un paquete.
     */
    public function update(Request $request, $id)
    {
        $package = Package::find($id);
        if (!$package) {
            return response()->json(['error' => 'Paquete no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package->update($request->only(['name', 'description', 'price']));

        if ($request->has('product_ids')) {
            $package->products()->sync($request->product_ids);
        }

        // Agregar nuevas imágenes si se suben
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('package_images', 'public');
                $package->images()->create(['image_path' => $path]);
            }
        }

        return response()->json(['message' => 'Paquete actualizado', 'data' => $package->load('images')]);
    }

    /**
     * Eliminar un paquete y sus imágenes.
     */
    public function destroy($id)
    {
        $package = Package::with('images')->find($id);
        if (!$package) {
            return response()->json(['error' => 'Paquete no encontrado'], 404);
        }

        // Eliminar imágenes del disco
        foreach ($package->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }

        $package->delete();

        return response()->json(['message' => 'Paquete eliminado']);
    }

    /**
     * Reservas de productos y paquetes personalizados del emprendedor.
     */
    public function entrepreneurReservations($entrepreneurId)
{
    $reservations = Reservation::with(['user', 'product', 'customPackage'])
        ->where(function ($query) use ($entrepreneurId) {
            $query->whereHas('product', function ($q) use ($entrepreneurId) {
                $q->where('entrepreneur_id', $entrepreneurId);
            })->orWhereHas('customPackage.products', function ($q) use ($entrepreneurId) {
                $q->where('entrepreneur_id', $entrepreneurId);
            });
        })
        ->get();

    return response()->json($reservations);
}
public function authenticatedEntrepreneur()
{
    $user = auth()->user();
    $entrepreneur = Entrepreneur::where('user_id', $user->id)->first();

    return response()->json(['entrepreneur' => $entrepreneur]);
}


}
