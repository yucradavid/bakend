<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with('reservation.product.entrepreneur', 'reservation.user', 'confirmer');

        // Filtros opcionales (estado y fecha)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $payments = $query->latest()->get();
        return response()->json($payments);
    }

    public function store(Request $request): JsonResponse
{
    $request->validate([
        'reservation_id'  => 'required|exists:reservations,id',
        'image_file'      => 'nullable|image|max:2048',
        'note'            => 'nullable|string',
        'operation_code'  => 'nullable|string|max:255',
        'confirmed_at'    => 'nullable|date',
        'payment_method'  => 'required|string|max:50',
    ]);

    $user = Auth::user();
    $isAdmin = $user && $user->role === 'super-admin';
    $isEntrepreneur = $user && $user->role === 'emprendedor';

    $reservation = Reservation::with('product')->findOrFail($request->reservation_id);

    $belongsToEntrepreneur = $isEntrepreneur &&
        $reservation->product &&
        $reservation->product->entrepreneur_id === optional($user->entrepreneur)->id;

    $isPresencial = $request->payment_method === 'efectivo';

    // Validación de permiso
    if ($isPresencial && !($isAdmin || $belongsToEntrepreneur)) {
        return response()->json([
            'message' => 'No tienes permiso para registrar pagos presenciales confirmados.'
        ], 403);
    }

    $status = 'enviado';
    $isConfirmed = false;
    $confirmationTime = null;
    $confirmationBy = null;
    $confirmedAt = null;

    // Confirmación automática solo si es efectivo y hecho por superadmin o emprendedor dueño del producto
    if ($isPresencial && ($isAdmin || $belongsToEntrepreneur)) {
        $status = 'confirmado';
        $isConfirmed = true;
        $confirmationTime = now();
        $confirmationBy = $user->id;
        $confirmedAt = $request->confirmed_at ?? now();
    }

    $imageUrl = null;
    if ($request->hasFile('image_file')) {
        $path = $request->file('image_file')->store('payments', 'public');
        $imageUrl = asset("storage/{$path}");
    }

    $payment = Payment::create([
        'reservation_id'     => $request->reservation_id,
        'image_url'          => $imageUrl,
        'note'               => $request->note,
        'status'             => $status,
        'operation_code'     => $request->operation_code,
        'payment_method'     => $request->payment_method,
        'is_confirmed'       => $isConfirmed,
        'confirmation_time'  => $confirmationTime,
        'confirmation_by'    => $confirmationBy,
        'confirmed_at'       => $confirmedAt,
    ]);

    // Actualizar reserva si se confirmó
    if ($status === 'confirmado') {
        $reservation->update(['status' => 'confirmada']);
        if ($reservation->product && $reservation->quantity) {
            $reservation->product->decrement('stock', $reservation->quantity);
        }
    }

    return response()->json($payment->load('reservation.product'), 201);
}


    public function show($id): JsonResponse
    {
        $payment = Payment::with('reservation.product', 'confirmer')->findOrFail($id);
        return response()->json($payment);
    }

   public function confirm($id): JsonResponse
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'No autenticado.'], 403);
    }

    $payment = Payment::with('reservation.product')->findOrFail($id);
    $reservation = $payment->reservation;

    // Verifica si el usuario es superadmin
    $isAdmin = $user->hasRole('super-admin');

    // Verifica si es emprendedor dueño del producto
    $belongsToEntrepreneur = $user->hasRole('emprendedor') &&
        $reservation &&
        $reservation->product &&
        $reservation->product->entrepreneur &&
        $reservation->product->entrepreneur->user_id === $user->id;

    if (!$isAdmin && !$belongsToEntrepreneur) {
        return response()->json(['message' => 'No autorizado para confirmar este pago.'], 403);
    }

    // Evitar confirmar dos veces
    if ($payment->is_confirmed) {
        return response()->json(['message' => 'El pago ya fue confirmado.'], 400);
    }

    // Confirmar pago
    $payment->update([
        'status'            => 'confirmado',
        'is_confirmed'      => true,
        'confirmation_time' => now(),
        'confirmation_by'   => $user->id,
    ]);

    // Actualizar reserva
    $reservation->update(['status' => 'confirmada']);

    if ($reservation->product && $reservation->quantity) {
        $reservation->product->decrement('stock', $reservation->quantity);
    }

    return response()->json(['message' => 'Pago confirmado correctamente.']);
}



public function reject($id): JsonResponse
{
    $user = Auth::user();
    if (!$user || $user->role !== 'super-admin') {
        return response()->json(['message' => 'No autorizado para rechazar pagos.'], 403);
    }

    $payment = Payment::findOrFail($id);

    // Evitar rechazar dos veces
    if ($payment->status === 'rechazado') {
        return response()->json(['message' => 'El pago ya fue rechazado.'], 400);
    }

    $payment->update([
        'status'      => 'rechazado',
        'rejected_at' => now(),
    ]);

    $payment->reservation->update(['status' => 'cancelada']);

    return response()->json(['message' => 'Pago rechazado. Reserva cancelada.']);
}


public function indexForEntrepreneur(Request $request): JsonResponse
{
    $user = Auth::user();

    // Verificar que el usuario es un emprendedor y está autenticado
    if (!$user || !$user->entrepreneur) {
        return response()->json([
            'message' => 'No eres un emprendedor autenticado.'
        ], 403);
    }

    $entrepreneurId = $user->entrepreneur->id;

    // Query de pagos para el emprendedor
    $query = Payment::whereHas('reservation.product', function ($query) use ($entrepreneurId) {
        $query->where('entrepreneur_id', $entrepreneurId);
    })
    ->with([
        'reservation.product',
        'reservation.user',
        'confirmer'
    ]);

    // Filtros opcionales de estado y fechas
    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    if ($request->has('from')) {
        $query->whereDate('created_at', '>=', $request->from);
    }

    if ($request->has('to')) {
        $query->whereDate('created_at', '<=', $request->to);
    }

    // Obtener los pagos más recientes
    $payments = $query->latest()->take(5)->get();

    return response()->json($payments, 200);
}

    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'super-admin') {
            return response()->json(['message' => 'No autorizado para eliminar pagos.'], 403);
        }

        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json(['message' => 'Pago eliminado.']);
    }

}
