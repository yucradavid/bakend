<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Listar todas las categorías
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // Crear una nueva categoría
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'icon' => 'nullable|string|max:50'
        ]);

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    // Mostrar una categoría específica
    public function show(Category $category)
    {
        return response()->json($category->load('entrepreneurs'));
    }

    // Actualizar una categoría
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
            'icon' => 'nullable|string|max:50'
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    // Eliminar una categoría
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Categoría eliminada']);
    }
}