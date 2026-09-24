<?php

namespace App\Http\Controllers;

use App\Models\SolicitudServicio;
use Illuminate\Http\Request;

class SolicitudesServicioController extends Controller
{
    public function index(Request $request)
    {
        $solicitudes = $request->user()->role === 'admin'
            ? SolicitudServicio::with('user:id,name,email', 'producto:id,nombre,precio')->latest()->get()
            : $request->user()->solicitudes()->latest()->get();

        return response()->json($solicitudes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => ['required', 'string', 'max:100'],
            'asunto' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string'],
            'prioridad' => ['sometimes', 'in:baja,normal,alta,urgente'],
            'product_id' => ['nullable', 'exists:productos,id'],
        ]);

        if (!empty($validated['product_id'])) {
            $producto = \App\Models\Producto::findOrFail($validated['product_id']);
            if ($producto->stock < 1 || !$producto->activo) {
                return response()->json(['message' => 'El producto no está disponible.'], 422);
            }
            $validated['tipo'] = 'compra';
            $validated['asunto'] = 'Solicitud de compra: ' . $producto->nombre;
            $validated['descripcion'] = $validated['descripcion'] ?: 'Solicitud de compra desde el catálogo.';
        }

        $solicitud = $request->user()->solicitudes()->create($validated);

        return response()->json($solicitud, 201);
    }

    public function update(Request $request, SolicitudServicio $solicitudServicio)
    {
        $validated = $request->validate([
            'tipo' => ['sometimes', 'string', 'max:100'],
            'asunto' => ['sometimes', 'string', 'max:150'],
            'descripcion' => ['sometimes', 'string'],
            'estado' => ['sometimes', 'in:pendiente,en_proceso,resuelta,cancelada'],
            'prioridad' => ['sometimes', 'in:baja,normal,alta,urgente'],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'notas_admin' => ['nullable', 'string'],
        ]);

        $solicitudServicio->update($validated);

        return response()->json($solicitudServicio->fresh('user:id,name,email'));
    }

    public function destroy(SolicitudServicio $solicitudServicio)
    {
        $solicitudServicio->delete();

        return response()->noContent();
    }
}