<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //GET este metodo lista todos los datos
    public function index()
    {
        //
        $clientes = Clientes::all();
        return response()->json($clientes, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    //Levanta vistas HTML
    public function create()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     */
    //POST este metodo crea un nuevo registro
    public function store(Request $request)
    {
        //
        $clientes = Clientes::create($request->all());
        return response()->json($clientes, 201);
    }

    /**
     * Display the specified resource.
     */
    //GET este metodo muestra un registro especifico por medio de id
    public function show(Clientes $clientes)
    {
        //
        return response()->json($clientes, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    //Levanta vistas HTML
    public function edit(Clientes $clientes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    //PUT este metodo actualiza un registro especifico por medio de id
    public function update(Request $request, $id)
    {
        //
        Clientes::where('id', $id)->update($request->all());
        return response()->json($request, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    //DELETE este metodo elimina un registro especifico por medio de id
    public function destroy(Request $request, $id)
    {
        //
        Clientes::where('id', $id )->delete();
        return response()->json(null, 204);
    }
}