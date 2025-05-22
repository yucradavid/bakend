<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // Obtener contacto (público)
    public function index()
    {
        $contact = Contact::first();

        // Si no hay contacto, se devuelve un contacto vacío con id null
        return response()->json($contact ?? [
            'id' => null,
            'address' => '',
            'phone' => '',
            'email' => '',
            'facebook' => '',
            'instagram' => '',
            'google_maps_embed' => ''
        ]);
    }

    // Actualizar contacto (admin)
    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $data = $request->validate([
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'google_maps_embed' => 'nullable|string',
        ]);

        $contact->update($data);

        return response()->json($contact);
    }
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:100',
                'facebook' => 'nullable|url|max:255',
                'instagram' => 'nullable|url|max:255',
                'google_maps_embed' => 'nullable|string',
            ]);

            // Si no hay datos, no creamos el contacto
            if (!$data['address'] && !$data['phone'] && !$data['email'] && !$data['facebook'] && !$data['instagram'] && !$data['google_maps_embed']) {
                return response()->json(['error' => 'Debe ingresar al menos un campo'], 400);
            }

            $contact = Contact::create($data);

            return response()->json($contact, 201);
        } catch (\Exception $e) {
            // Log del error
            \Log::error('Error al crear el contacto: '.$e->getMessage());
            return response()->json(['error' => 'Error al crear el contacto.'], 500);
        }
    }


}
