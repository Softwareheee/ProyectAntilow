<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query()->where('activo', true)->latest();
        if ($request->user()?->role === 'admin') {
            $query = Producto::query()->latest();
        }

        $products = $query->get()->each(function (Producto $producto): void {
            $producto->imagen_url = $this->absoluteImageUrl($producto->imagen_url);
        });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        if ($request->hasFile('imagen')) {
            $validated['imagen_url'] = Storage::disk('public')->url($request->file('imagen')->store('productos', 'public'));
        }
        unset($validated['imagen']);
        $producto = Producto::create($validated);

        $producto->imagen_url = $this->absoluteImageUrl($producto->imagen_url);

        return response()->json($producto, 201);
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $this->validated($request, false);
        if ($request->hasFile('imagen')) {
            if ($producto->imagen_url && str_contains($producto->imagen_url, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', parse_url($producto->imagen_url, PHP_URL_PATH)));
            }
            $validated['imagen_url'] = Storage::disk('public')->url($request->file('imagen')->store('productos', 'public'));
        }
        unset($validated['imagen']);
        $producto->update($validated);

        $producto = $producto->fresh();
        $producto->imagen_url = $this->absoluteImageUrl($producto->imagen_url);

        return response()->json($producto);
    }

    public function destroy(Producto $producto)
    {
        if ($producto->imagen_url && str_contains($producto->imagen_url, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', parse_url($producto->imagen_url, PHP_URL_PATH)));
        }
        $producto->delete();

        return response()->noContent();
    }

    private function validated(Request $request, bool $required = true): array
    {
        $rule = $required ? 'required' : 'sometimes';

        if (is_string($request->input('caracteristicas'))) {
            $request->merge([
                'caracteristicas' => json_decode($request->input('caracteristicas'), true) ?: [],
            ]);
        }

        return $request->validate([
            'nombre' => [$rule, 'string', 'max:150'],
            'categoria' => ['sometimes', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string'],
            'precio' => [$rule, 'numeric', 'min:0'],
            'stock' => [$rule, 'integer', 'min:0'],
            'imagen_url' => ['nullable', 'url', 'max:500'],
            'imagen' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'caracteristicas' => ['nullable', 'array'],
            'activo' => ['sometimes', 'boolean'],
        ]);
    }

    private function absoluteImageUrl(?string $imageUrl): ?string
    {
        if (!$imageUrl || str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }

        return url($imageUrl);
    }
}