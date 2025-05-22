<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\EntrepreneurCategory;
use Illuminate\Http\Request;

class EntrepreneurCategoryController extends Controller
{
    // 1. Listar todas las asignaciones emprendedor–categoría
    public function index()
    {
        return response()->json(EntrepreneurCategory::all());
    }

    // 2. Crear una asignación (asignar categoría a emprendedor)
    public function store(Request $request)
    {
        $data = $request->validate([
            'entrepreneur_id' => 'required|exists:entrepreneurs,id',
            'category_id'     => 'required|exists:categories,id',
        ]);

        $ec = EntrepreneurCategory::create($data);
        return response()->json($ec, 201);
    }

    // 3. Mostrar una asignación específica
    public function show(EntrepreneurCategory $entrepreneurCategory)
    {
        return response()->json($entrepreneurCategory);
    }
    public function update(Request $request, EntrepreneurCategory $entrepreneurCategory)
    {
        $data = $request->validate([
            'entrepreneur_id' => 'required|exists:entrepreneurs,id',
            'category_id'     => 'required|exists:categories,id',
        ]);

        $entrepreneurCategory->update($data);
        return response()->json($entrepreneurCategory);
    }

    // 4. Eliminar una asignación (desasignar)
    public function destroy(EntrepreneurCategory $entrepreneurCategory)
    {
        $entrepreneurCategory->delete();
        return response()->json(['message' => 'Categoría desasignada del emprendedor']);
    }
    public function count()
{
    return response()->json([
        'count' => Category::count()
    ]);
}
}
