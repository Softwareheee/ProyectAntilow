<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** CRUD del directorio de clientes disponible para el administrador. */
class ClientesController extends Controller
{
    /** Devuelve todos los clientes registrados. */
    public function index()
    {
        $clientes = Clientes::all();
        return response()->json($clientes, 200);
    }

    /** Método de vista no utilizado por la API resource. */
    public function create()
    {
    }

    /** Persiste un cliente enviado por el panel administrativo. */
    public function store(Request $request)
    {
        $clientes = Clientes::create($request->all());
        return response()->json($clientes, 201);
    }

    /** Devuelve el cliente resuelto por el model binding de Laravel. */
    public function show(Clientes $clientes)
    {
        return response()->json($clientes, 200);
    }

    /** Método de vista no utilizado por la API resource. */
    public function edit(Clientes $clientes)
    {
    }

    /** Actualiza los campos del cliente identificado por su ID. */
    public function update(Request $request, $id)
    {
        Clientes::where('id', $id)->update($request->all());
        return response()->json($request, 200);
    }

    /** Elimina un cliente y responde sin cuerpo. */
    public function destroy(Request $request, $id)
    {
        Clientes::where('id', $id )->delete();
        return response()->json(null, 204);
    }
}