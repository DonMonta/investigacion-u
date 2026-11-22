<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bitacora;
use App\Models\Grupo;
use App\Models\Invi_equipo_depart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BitacoraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         
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

    public function obtenerBitacoraInvestigacion(string $identificador)
    {
        $user = User::where('LoginUsu', $identificador)->orWhere('NombUsu', $identificador)->first();

        if (!$user) {
            return response()->json(['error' => true, 'mensaje' => 'Usuario no encontrado'], 404);
        }

        $grupo = Grupo::find($user->id_grupo);

        $equipoDepart = Invi_equipo_depart::with('equipo_roles', 'informacionpersonald')
            ->where('ciinfper_doc', $user->ciinfper)
            ->first();

        $equipoCargo = [];
        // Si pertenece al departamento y es DIRECTOR o LÍDER (Ajusta la palabra clave según tu BD)
        if ($equipoDepart && $equipoDepart->equipo_roles && stripos($equipoDepart->equipo_roles->nombre_rol, 'DIRECTOR') !== false) {
            // Traer a todos los que sean de tipo_rol INVESTIGACIÓN (excepto él mismo si lo deseas)
            $equipoCargo = Invi_equipo_depart::with('equipo_roles', 'informacionpersonald')
                ->whereHas('equipo_roles', function($q) {
                    $q->where('tipo_rol', 'INVESTIGACIÓN');
                })
                ->get()
                ->map(function($miembro) {
                    return [
                        'ciinfper' => $miembro->ciinfper_doc,
                        'nombres'  => $miembro->informacionpersonald->NombInfPer . ' ' . $miembro->informacionpersonald->ApellInfPer,
                        'rol'      => $miembro->equipo_roles->nombre_rol
                    ];
                });
        }

        $bitacora = Bitacora::where(function ($query) use ($user) {
                $query->where('bt_usuario', $user->NombUsu)
                      ->orWhere('bt_usuario', $user->LoginUsu)
                      ->orWhere('bt_usuario', $user->ciinfper);
            })
            ->where('bt_accion', 'LIKE', '%INVESTIGACIÓN%')
            ->orderBy('bt_fechahora', 'desc')
            ->get();

        return response()->json([
            'usuario' => [
                'LoginUsu' => $user->LoginUsu,
                'NombUsu'  => $user->NombUsu,
                'ciinfper' => $user->ciinfper,
                'email'    => $user->email,
            ],
            'grupo' => $grupo ? ['id' => $grupo->id, 'detalle' => $grupo->detalle] : null,
            'departamento_investigacion' => $equipoDepart,
            'equipo_cargo' => $equipoCargo,
            'bitacora' => $bitacora
        ], 200);
    }
}
