<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registro de nuevo usuario.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed',
            'role' => 'required|string|in:cliente,emprendedor,super-admin',

            // Nuevos campos
            'phone' => 'nullable|string|max:20',
            'document_id' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'profile_image' => 'nullable|url',
            'location' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'document_id' => $request->document_id,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'profile_image' => $request->profile_image,
            'location' => $request->location,
        ]);

        if (Role::where('name', $request->role)->exists()) {
            $user->assignRole($request->role);
        } else {
            return response()->json(['error' => 'Rol no válido.'], 400);
        }

        $token = $user->createToken('Token')->plainTextToken;

        return response()->json([
            'message' => 'Registro exitoso',
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'token' => $token,
        ]);
    }

    /**
     * Login de usuario.
     */
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        throw ValidationException::withMessages([
            'email' => ['Credenciales incorrectas'],
        ]);
    }

    $user = Auth::user();

    if ($user->getRoleNames()->isEmpty()) {
        return response()->json(['message' => 'Este usuario no tiene un rol asignado.'], 403);
    }

    $token = $user->createToken('Token')->plainTextToken;

    $entrepreneur_id = null;
    if ($user->hasRole('emprendedor') && $user->entrepreneur) {
        $entrepreneur_id = $user->entrepreneur->id;
    }

    return response()->json([
        'message' => 'Login exitoso',
        'user' => $user,
        'roles' => $user->getRoleNames(),
        'token' => $token,
        'entrepreneur_id' => $entrepreneur_id, // ✅ ahora sí lo devuelves
    ]);
}


    /**
     * Logout de usuario.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
            return response()->json(['message' => 'Sesión cerrada exitosamente']);
        }

        return response()->json(['message' => 'No autenticado'], 401);
    }

    /**
     * Obtener rol de un usuario específico por su ID.
     */
    public function getUserRole($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json([
            'user' => $user->name,
            'roles' => $user->getRoleNames(),
        ]);
    }
    public function update(Request $request)
{
    $user = $request->user();

    $request->validate([
        'name' => 'required|string',
        'phone' => 'nullable|string|max:20',
        'document_id' => 'nullable|string|max:20',
        'birth_date' => 'nullable|date',
        'address' => 'nullable|string|max:255',
        'profile_image' => 'nullable|url',
        'location' => 'nullable|string|max:100',
    ]);

    $user->update($request->only([
        'name', 'phone', 'document_id', 'birth_date',
        'address', 'profile_image', 'location'
    ]));

    return response()->json(['message' => 'Perfil actualizado correctamente', 'user' => $user]);
}
public function search(Request $request)
{
    $email = $request->query('email'); // <-- usa query() en vez de $request->email

    if (!$email) {
        return response()->json(['message' => 'Email es requerido'], 400);
    }

    $user = User::where('email', $email)->first();

    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    return response()->json($user);
}


}
