<?php

namespace App\Http\Controllers;

use App\Models\Invi_linea_investigacion;
use App\Models\Facultad;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Invi_linea_investigaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $searchQuery = $request->input('search_query');
            // Usamos withCount para obtener los totales de las relaciones
            $query = Invi_linea_investigacion::withCount(['facultades', 'sub_linea_investigacion']);
            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('nombre_lin', 'LIKE', '%' . $searchQuery . '%');
                });
            }

            if ($request->has('all') && $request->all === 'true') {
                $data = $query->get();

                // Transformación para UTF-8 y manejo de atributos
                $data->transform(function ($item) {
                    $attributes = $item->toArray(); // Usamos toArray para incluir los campos _count
                    foreach ($attributes as $key => $value) {
                        if (is_string($value)) {
                            $attributes[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                        }
                    }
                    return $attributes;
                });

                return response()->json(['data' => $data]);
            }

            // Paginación por defecto
            $data = $query->paginate(20);

            if ($data->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'message' => 'No se encontraron datos'
                ], 200);
            }

            $data->getCollection()->transform(function ($item) {
                $attributes = $item->toArray();
                foreach ($attributes as $key => $value) {
                    if (is_string($value)) {
                        $attributes[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                }
                return $attributes;
            });

            return response()->json([
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function getFacultades()
    {
        $sedes_atraer = ['1', '2'];
        $facultades = Facultad::whereIn('idsede', $sedes_atraer)
            ->where('idfacultad', '!=', '6')
            ->whereNotNull('decano')
            ->get();
        if ($facultades->isEmpty()) {
            return response()->json([
                'data' => [],
                'message' => 'No se encontraron datos'
            ], 200);
        }
        return response()->json([
            'data' => $facultades,
            'message' => 'Se encontraron datos'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{

            $inputs = $request->all();
            $res = Invi_linea_investigacion::create($inputs);
            DB::commit();
                // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de una Línea de Investigación para el departamento - INVESTIGACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de una Línea de Investigación con ID: {$res->id_lin_investiga} y nombre: {$res->nombre_lin}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            return response()->json([
                'data' => $res,
                'mensaje' => $res->estado_lin_investiga == 0 && $request->estado_lin_investiga == 1
                    ? "Agregado, pero se guardó como Inactivo porque ya existe una Línea de Investigación activa."
                    : "Agregado con Éxito!!",
            ]);
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $res = Invi_linea_investigacion::find($id);
        if (isset($res)) {
            return response()->json([
                'data' => $res,
                'mensaje' => "Encontrado con Éxito!!",
            ]);
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "La Línea de Investigación con id: $id no Existe",
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try{

            $res = Invi_linea_investigacion::find($id);
    
            if (isset($res)) {
                $res->idfacultad = $request->idfacultad;
                $res->nombre_lin = $request->nombre_lin;
                $res->estado_lin_investiga = $request->estado_lin_investiga;
    
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Actualización de una Línea de Investigación para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de una Línea de Investigación con ID: {$res->id_lin_investiga} y nombre: {$res->nombre_lin}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Actualizar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $res,
                        'mensaje' => 'Actualizado con Éxito!',
                    ]);
                }
    
                return response()->json(['error' => true, 'mensaje' => 'Error al Actualizar'], 500);
            }
    
            return response()->json(['error' => true, 'mensaje' => "La Línea de Investigación con id: $id no Existe"], 404);
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        DB::beginTransaction();
        try{

            $res = Invi_linea_investigacion::find($id);
            if (isset($res)) {
                $res->estado_lin_investiga = 0;
                $res->save();
                $data = $res->toArray();
                if ($data) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Inhabilitación de una Línea de Investigación para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Inhabilitación de una Línea de Investigación con ID: {$res->id_lin_investiga} y nombre: {$res->nombre_lin}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Inhabilitar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "Inhabilitado con Éxito!!",
                    ]);
                } else {
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "La Línea de Investigación no existe (puede que ya la haya eliminado)",
                    ]);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'mensaje' => "La Línea de Investigación con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function habilitar(Request $request, string $id)
    {
        DB::beginTransaction();
        try{

            $res = Invi_linea_investigacion::find($id);
    
            if (isset($res)) {
                $res->estado_lin_investiga = 1;
    
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Habilitación de una Línea de Investigación para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Habilitación de una Línea de Investigación con ID: {$res->id_lin_investiga} y nombre: {$res->nombre_lin}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Habilitar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'status' => true,
                        'data' => $res,
                        'mensaje' => "¡Línea de Investigación Habilitada con Éxito!",
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'mensaje' => "Error interno al intentar guardar los cambios.",
                    ], 500);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'mensaje' => "La Línea de Investigación con id: $id no existe o fue eliminada.",
                ], 404);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
}
