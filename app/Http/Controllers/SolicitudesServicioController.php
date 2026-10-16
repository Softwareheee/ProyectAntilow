<?php

namespace App\Http\Controllers;

use App\Models\SolicitudServicio;
use Illuminate\Http\Request;

/** Administra solicitudes de soporte y compras asociadas a cuentas de usuario. */
class SolicitudesServicioController extends Controller
{
    /** Admin recibe todas las solicitudes; clientes solo consultan las propias. */
    public function index(Request $request)
    {
        $solicitudes = $request->user()->role === 'admin'
            ? SolicitudServicio::with('user:id,name,email', 'producto:id,nombre,precio')->latest()->get()
            : $request->user()->solicitudes()->with('producto:id,nombre,precio')->latest()->get();

        return response()->json($solicitudes);
    }

    /** Registra una solicitud y valida stock cuando se trata de una compra. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => ['required', 'string', 'max:100'],
            'asunto' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string'],
            'nombre_cliente' => ['required', 'string', 'max:255'],
            'telefono_cliente' => ['required', 'string', 'max:30'],
            'prioridad' => ['sometimes', 'in:baja,normal,alta,urgente'],
            'product_id' => ['nullable', 'exists:productos,id'],
            'cantidad' => ['required_with:product_id', 'nullable', 'integer', 'min:1', 'max:65535'],
        ]);

        if (!empty($validated['product_id'])) {
            $producto = \App\Models\Producto::findOrFail($validated['product_id']);
            if ($producto->stock < 1 || !$producto->activo) {
                return response()->json(['message' => 'El producto no está disponible.'], 422);
            }
            if ($validated['cantidad'] > $producto->stock) {
                return response()->json(['message' => 'La cantidad solicitada supera las unidades disponibles.'], 422);
            }
            $validated['tipo'] = 'compra';
            $validated['asunto'] = 'Solicitud de compra: ' . $producto->nombre;
            $validated['descripcion'] = 'El cliente solicita ' . $validated['cantidad'] . ' unidad(es) de ' . $producto->nombre . '.';
            $validated['precio_unitario'] = $producto->precio;
        }

        $solicitud = $request->user()->solicitudes()->create($validated);

        return response()->json($solicitud, 201);
    }

    /** Actualiza estado, prioridad, costo o notas del seguimiento administrativo. */
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

    /** Elimina la solicitud seleccionada por model binding. */
    public function destroy(SolicitudServicio $solicitudServicio)
    {
        $solicitudServicio->delete();

        return response()->noContent();
    }
}