<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonio;
use Illuminate\Http\Request;

class TestimonioController extends Controller
{
    // Listar todos los testimonios
    public function index()
    {
        $testimonios = Testimonio::orderBy('created_at', 'desc')
            // quitamos with('lugar','user') porque 'lugar' no está definido
            ->get()
            ->map(fn($t) => [
                'id'         => $t->id,
                'nombre'     => $t->user?->name ?? $t->nombre,
                'estrellas'  => $t->estrellas,
                'comentario' => $t->comentario,
                'fecha'      => $t->created_at->format('d/m/Y'),
            ]);

        return response()->json($testimonios);
    }

    // Guardar un nuevo testimonio
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'   => 'nullable|exists:users,id',
            'nombre'    => 'required_without:user_id|string|max:100',
            //'lugar_id'  => 'required|exists:places,id',
            'estrellas' => 'required|integer|min:1|max:5',
            'comentario'=> 'required|string',
        ]);

        $t = Testimonio::create($data);

        return response()->json([
            'id'       => $t->id,
            'nombre'   => $t->user?->name ?? $t->nombre,
            //'lugar'    => $t->lugar->name,
            'estrellas'=> $t->estrellas,
            'comentario'=> $t->comentario,
            'fecha'    => $t->created_at->format('d/m/Y'),
        ], 201);
    }
}
