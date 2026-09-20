<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query()->where('activo', true)->latest();
        if ($request->user()?->role === 'admin') {
            $query = Producto::query()->latest();
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $producto = Producto::create($this->validated($request));

        return response()->json($producto, 201);
    }

    public function update(Request $request, Producto $producto)
    {
        $producto->update($this->validated($request, false));

        return response()->json($producto->fresh());
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();

        return response()->noContent();
    }

    private function validated(Request $request, bool $required = true): array
    {
        $rule = $required ? 'required' : 'sometimes';

        return $request->validate([
            'nombre' => [$rule, 'string', 'max:150'],
            'categoria' => ['sometimes', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string'],
            'precio' => [$rule, 'numeric', 'min:0'],
            'stock' => [$rule, 'integer', 'min:0'],
            'imagen_url' => ['nullable', 'url', 'max:500'],
            'caracteristicas' => ['nullable', 'array'],
            'activo' => ['sometimes', 'boolean'],
        ]);
    }
}