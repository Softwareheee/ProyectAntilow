<?php

namespace App\Http\Controllers;

use App\Models\SolicitudServicio;
use Illuminate\Http\Request;

class SolicitudesServicioController extends Controller
{
    public function index(Request $request)
    {
        $solicitudes = $request->user()->role === 'admin'
            ? SolicitudServicio::with('user:id,name,email')->latest()->get()
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
        ]);

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