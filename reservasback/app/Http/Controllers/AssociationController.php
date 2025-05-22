<?php

namespace App\Http\Controllers;

use App\Models\Association;
use Illuminate\Http\Request;

class AssociationController extends Controller
{
    // Listar todas las asociaciones
    public function index()
    {
        $associations = Association::all();
        return response()->json($associations);
    }

    // Crear una nueva asociación
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:associations',
            'description' => 'nullable|string',
            'region' => 'nullable|string|max:100'
        ]);

        $association = Association::create($validated);
        return response()->json($association, 201);
    }

    // Mostrar una asociación específica
    public function show(Association $association)
    {
        return response()->json($association->load('entrepreneurs'));
    }

    // Actualizar una asociación
    public function update(Request $request, Association $association)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:associations,name,' . $association->id,
            'description' => 'nullable|string',
            'region' => 'nullable|string|max:100'
        ]);

        $association->update($validated);
        return response()->json($association);
    }

    // Eliminar una asociación (solo si no tiene emprendedores asociados)
    public function destroy(Association $association)
{
    // Eliminar emprendedores primero (solo si es lo que quieres)
    $association->entrepreneurs()->delete();

    $association->delete();
    return response()->json(['message' => 'Asociación y emprendedores eliminados']);
}

public function count()
{
    $count = Association::count();  // Asumiendo que tienes un modelo Association
    return response()->json($count);
}

}
