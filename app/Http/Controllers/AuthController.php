<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/** Gestiona registro, autenticación y revocación de tokens Sanctum. */
class AuthController extends Controller
{
    /** Registra siempre un cliente y devuelve su token de acceso. */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'client',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json ([
            'user' => $user,
            'token' => $token
            
        ], 201);
    }
    
    /** Valida credenciales y crea un token personal para la sesión. */
    public function login(Request $request){
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }
    /** Revoca únicamente el token con el que se autenticó la petición. */
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Sesión cerrada'
        ], 200);

        
    }
}
