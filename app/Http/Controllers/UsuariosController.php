<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuariosController extends Controller
{
    public function index()
    {
        return response()->json(User::query()->select('id', 'name', 'email', 'role', 'created_at')->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['sometimes', 'in:admin,client'],
        ]);
        $validated['password'] = Hash::make($validated['password']);

        return response()->json(User::create($validated), 201);
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $usuario->id],
            'role' => ['sometimes', 'in:admin,client'],
            'password' => ['sometimes', 'string', 'min:8'],
        ]);
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        $usuario->update($validated);

        return response()->json($usuario->fresh());
    }

    public function destroy(Request $request, User $usuario)
    {
        abort_if($request->user()->is($usuario), 422, 'No puedes eliminar tu propia cuenta.');
        $usuario->delete();

        return response()->noContent();
    }
}