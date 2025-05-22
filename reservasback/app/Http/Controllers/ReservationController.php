<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Product;  // Cambié Tour a Product
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Crear una nueva reserva.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    // Mostrar todas las reservas del usuario
    public function userReservations()
        {
            $reservations = auth()->user()
            ->reservations()
            ->with('product.entrepreneur', 'customPackage.products.entrepreneur')
            ->latest()
            ->take(10)
            ->get();

        return response()->json($reservations);
        }



    // Mostrar todas las reservas de un emprendedor
    public function entrepreneurReservations($entrepreneurId)
{
    $reservations = Reservation::with(['product.entrepreneur', 'user', 'customPackage.products'])
        ->where(function ($query) use ($entrepreneurId) {
            // Reservas directas de productos del emprendedor
            $query->whereHas('product', function ($q) use ($entrepreneurId) {
                $q->where('entrepreneur_id', $entrepreneurId);
            })
            // O reservas de paquetes personalizados que contienen productos del emprendedor
            ->orWhereHas('customPackage.products', function ($q) use ($entrepreneurId) {
                $q->where('entrepreneur_id', $entrepreneurId);
            });
        })
        ->get();

    return response()->json($reservations);
}

    // Crear una nueva reserva
    public function store(Request $request)
{
    $request->validate([
        'product_id'       => 'nullable|exists:products,id',
        'custom_package_id'=> 'nullable|exists:custom_packages,id',
        'quantity'         => 'required|integer|min:1',
        'reservation_date' => 'required|date',
    ]);

    // Validar que al menos uno esté presente
    if (!$request->product_id && !$request->custom_package_id) {
        return response()->json(['message' => 'Debes enviar product_id o custom_package_id'], 422);
    }

    // Validar stock si es producto
    $totalPrice = 0;
    if ($request->product_id) {
        $product = Product::findOrFail($request->product_id);
        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Stock insuficiente'], 400);
        }
        $totalPrice = $product->price * $request->quantity;
        $product->decrement('stock', $request->quantity);
    }

    // Si es paquete personalizado
    if ($request->custom_package_id) {
        $customPackage = \App\Models\CustomPackage::with('products')->findOrFail($request->custom_package_id);
        $totalPrice = $customPackage->total_amount;
    }

    $reservation = Reservation::create([
    'user_id'           => auth()->id(),
    'product_id'        => $request->product_id,
    'custom_package_id' => $request->custom_package_id,
    'reservation_code'  => uniqid('RES-', true),
    'quantity'          => $request->quantity,
    'reservation_date'  => $request->reservation_date,
    'total_amount'      => $totalPrice,
    'status'            => 'pendiente' // <--- necesario
]);

    $reservation->load('product.entrepreneur', 'customPackage');

    return response()->json($reservation, 201);
}

public function update(Request $request, $id)
{
    $reservation = Reservation::findOrFail($id);

    $request->validate([
        'product_id' => 'nullable|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'reservation_date' => 'required|date',
        'status' => 'required|string'
    ]);

    $reservation->update([
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
        'reservation_date' => $request->reservation_date,
        'status' => $request->status
    ]);

    return response()->json($reservation);
}


    /**
     * Obtener una reserva específica.
     */
    public function show($id)
{
    $reservation = Reservation::with([
        'product.place', // 🔄 agrega también la relación place
        'product.entrepreneur.user',
        'customPackage.products.entrepreneur.user',
        'user',
        'payment'
    ])->findOrFail($id);

    return response()->json($reservation);
}

    /**
     * Listar todas las reservas.
     */
    public function index()
    {
        $reservations = Reservation::with(['product', 'user'])->get(); // Cambié tour a product
        return response()->json($reservations);
    }

    /**
     * Eliminar una reserva.
     */
    public function destroy($id)
{
    $reservation = Reservation::find($id);

    if (!$reservation) {
        return response()->json(['message' => 'Reserva no encontrada'], 404);
    }

    $user = auth()->user(); // <-- Agrega este log temporal
    \Log::info('Intento de eliminar reserva', [
        'auth_id' => $user?->id,
        'res_user_id' => $reservation->user_id
    ]);

    if ($reservation->user_id !== auth()->id()) {
        return response()->json(['message' => 'No autorizado'], 403);
    }

    $reservation->delete();
    return response()->json(['message' => 'Reserva eliminada correctamente']);
}

public function directSale(Request $request): JsonResponse
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'client_name' => 'required|string|max:255',
        'client_phone' => 'required|string|max:15',
        'client_email' => 'nullable|email',
        'quantity' => 'required|integer|min:1',
        'payment_method' => 'required|string',
        'operation_code' => 'nullable|string',
        'note' => 'nullable|string',
        'image_file' => 'nullable|image|max:2048',
    ]);

    // Validar stock
    $product = Product::findOrFail($request->product_id);
    if ($product->stock < $request->quantity) {
        return response()->json(['message' => 'Stock insuficiente.'], 400);
    }

    // Buscar usuario por teléfono o email
    $user = User::where('phone', $request->client_phone)
        ->orWhere('email', $request->client_email)
        ->first();

    $clientCreated = false;

    // Si no existe, crearlo
    if (!$user) {
        $user = User::create([
            'name' => $request->client_name,
            'phone' => $request->client_phone,
            'email' => $request->client_email,
            'password' => bcrypt('temporal123'),
        ]);
        $clientCreated = true;
    }

    // Crear reserva
    $reservation = Reservation::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'reservation_code' => uniqid('RES-'),
        'quantity' => $request->quantity,
        'total_amount' => $product->price * $request->quantity,
        'reservation_date' => now(),
        'status' => 'confirmada'
    ]);

    // Subir imagen si hay
    $imageUrl = null;
    if ($request->hasFile('image_file')) {
        $path = $request->file('image_file')->store('payments', 'public');
        $imageUrl = asset("storage/{$path}");
    }

    // Crear pago
    $payment = Payment::create([
        'reservation_id' => $reservation->id,
        'payment_method' => $request->payment_method,
        'payment_type' => 'presencial',
        'operation_code' => $request->operation_code,
        'note' => $request->note,
        'image_url' => $imageUrl,
        'status' => 'confirmado',
        'is_confirmed' => true,
        'confirmation_time' => now(),
        'confirmation_by' => Auth::id()
    ]);

    // Descontar stock
    $product->decrement('stock', $request->quantity);

    return response()->json([
        'message' => 'Reserva y pago registrados correctamente.',
        'reservation' => $reservation,
        'payment' => $payment,
        'client_created' => $clientCreated,
    ], 201);
}

}

