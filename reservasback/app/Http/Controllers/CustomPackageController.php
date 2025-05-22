<?php

namespace App\Http\Controllers;

use App\Models\CustomPackage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomPackageController extends Controller
{
    /**
     * Mostrar todos los paquetes personalizados del usuario autenticado.
     */
    public function index()
    {
        $user = Auth::user();
        $packages = CustomPackage::with('products')->where('user_id', $user->id)->latest()->get();

        return response()->json($packages);
    }

    /**
     * Mostrar un solo paquete.
     */
    public function show($id)
    {
        $package = CustomPackage::with(['products'])->find($id);

        if (!$package || $package->user_id !== Auth::id()) {
            return response()->json(['error' => 'Paquete no encontrado'], 404);
        }

        return response()->json($package);
    }

    /**
     * Crear un nuevo paquete personalizado.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'status' => 'in:borrador,confirmado',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Crear paquete
        $package = CustomPackage::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'status' => $request->status ?? 'borrador',
        ]);

        $total = 0;

        foreach ($request->products as $productData) {
            $product = Product::find($productData['id']);
            $quantity = $productData['quantity'];

            $package->products()->attach($product->id, ['quantity' => $quantity]);

            $total += $product->price * $quantity;
        }

        $package->total_amount = $total;
        $package->save();

        return response()->json(['message' => 'Paquete personalizado creado', 'data' => $package], 201);
    }

    /**
     * Actualizar un paquete personalizado.
     */
    public function update(Request $request, $id)
    {
        $package = CustomPackage::where('user_id', Auth::id())->find($id);

        if (!$package) {
            return response()->json(['error' => 'Paquete no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'status' => 'in:borrador,confirmado',
            'products' => 'nullable|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package->update([
            'name' => $request->name ?? $package->name,
            'status' => $request->status ?? $package->status,
        ]);

        if ($request->has('products')) {
            $package->products()->detach();

            $total = 0;

            foreach ($request->products as $productData) {
                $product = Product::find($productData['id']);
                $quantity = $productData['quantity'];
                $package->products()->attach($product->id, ['quantity' => $quantity]);

                $total += $product->price * $quantity;
            }

            $package->total_amount = $total;
            $package->save();
        }

        return response()->json(['message' => 'Paquete actualizado', 'data' => $package]);
    }

    /**
     * Eliminar un paquete personalizado.
     */
    public function destroy($id)
    {
        $package = CustomPackage::where('user_id', Auth::id())->find($id);

        if (!$package) {
            return response()->json(['error' => 'Paquete no encontrado'], 404);
        }

        $package->delete();

        return response()->json(['message' => 'Paquete eliminado']);
    }

}
