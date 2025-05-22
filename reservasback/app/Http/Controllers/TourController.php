<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;

class TourController extends Controller
{
    // Obtener todos los tours
    public function index()
    {
        $tours = Tour::all();
        return response()->json($tours);
    }

    // Obtener un tour específico
    public function show($id)
    {
        $tour = Tour::findOrFail($id);
        return response()->json($tour);
    }

    // Crear un nuevo tour
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|string', // URL o ruta de la imagen
        ]);

        $tour = Tour::create($request->all());

        return response()->json($tour, 201);
    }

    // Actualizar un tour
    public function update(Request $request, $id)
    {
        $tour = Tour::findOrFail($id);
        $tour->update($request->all());

        return response()->json($tour);
    }

    // Eliminar un tour
    public function destroy($id)
    {
        $tour = Tour::findOrFail($id);
        $tour->delete();

        return response()->json(null, 204);
    }
}
