<?php

namespace App\Http\Controllers;

use App\Models\Invi_deta_inte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InviDetalleIntegranteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         // Iniciamos la consulta cargando las relaciones necesarias
        $query = Invi_deta_inte::with([
            'informacionPersonalD', 
            'informacionpersonal', // Si tienes otra tabla/relación para estudiantes
            'funciones' // Asumiendo que también cargas el rol
        ]);

        // Si desde el frontend mandan el proyect_id, filtramos por ese proyecto
        if ($request->has('proyect_id') && $request->proyect_id != '') {
            $query->where('proyect_id', $request->proyect_id);
        }

        // Ejecutamos la consulta
        $integrantes = $query->get();

        return response()->json([
            'status' => true,
            'data' => $integrantes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    
}
