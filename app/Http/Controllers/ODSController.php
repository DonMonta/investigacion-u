<?php

namespace App\Http\Controllers;

use App\Models\ODS;
use App\Models\Agenda_ODS;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ODSController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = ODS::select(
                'ods.*'
            );

            if ($request->has('all') && $request->all === 'true') {
                $data = $query->get();

                // Convertir los datos a UTF-8 válido
                $data->transform(function ($item) {
                    $attributes = $item->getAttributes();
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
                $attributes = $item->getAttributes();
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
            return response()->json(['error' => 'Error al codificar los datos a JSON: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{

            // Validar si el ODS existe
            $plan = Agenda_ODS::find($request->id_ag_ods);
            if (!$plan) {
                return response()->json(['error' => true, 'mensaje' => 'Agenda ODS no válida'], 404);
            }
    
            // Verificar duplicado en el mismo plan
            $existe = ODS::where('cod_ods', $request->cod_ods)
                ->where('id_ag_ods', $request->id_ag_ods)
                ->exists();
    
            if ($existe) {
                return response()->json([
                    'error' => true,
                    'mensaje' => "El código {$request->cod_ods} ya está registrado en esta Agenda ODS."
                ], 409);
            }
    
            $res = ODS::create($request->all());
            DB::commit();
                // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de un ODS para el departamento - VINCULACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de un ODS con ID: {$res->id_ods} y cod_ods: {$res->cod_ods}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            return response()->json(['data' => $res, 'mensaje' => 'Agregado con Éxito!!']);
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
        $res = ODS::select(
            'ods.*'
        )->where('id_ag_ods', $id)
            ->get();
        if ($res->isEmpty()) {
            return response()->json([
                'data' => [],
                'mensaje' => "El ods con id: $id no Existe",
            ], 404);
        } else {
            return response()->json([
                'data' => $res,
                'mensaje' => "ODS encontrado con id: $id",
            ], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try{

            $res = ODS::find($id);
    
            if (isset($res)) {
                // 1. Obtener el id_ag_ods del request (o del registro actual si no viene en el request)
                $id_ag_ods = $request->id_ag_ods ?? $res->id_ag_ods;
    
                // 2. Validar código duplicado dentro del mismo Agenda ODS, excluyendo el ID actual
                $existe = ODS::where('cod_ods', $request->cod_ods)
                    ->where('id_ods', '!=', $id) // Excluir el registro actual
                    ->where('id_ag_ods', $id_ag_ods)        // Filtrar por el mismo Agenda ODS
                    ->exists();
    
                if ($existe) {
                    return response()->json([
                        'error' => true,
                        'mensaje' => "El código {$request->cod_ods} ya está registrado en esta Agenda ODS."
                    ], 409);
                }
    
                // 3. Asignar valores
                $res->id_ag_ods = $id_ag_ods;
                $res->cod_ods = $request->cod_ods;
                $res->detalle_ods = $request->detalle_ods;
    
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Actualización de un ODS para el departamento - VINCULACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de un ODS con ID: {$res->id_ods} y cod_ods: {$res->cod_ods}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Actualizar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $res,
                        'mensaje' => "Actualizado con Éxito!!",
                    ]);
                }
    
                return response()->json(['error' => true, 'mensaje' => 'Error al Actualizar'], 500);
            }
    
            return response()->json(['error' => true, 'mensaje' => "El ods con id: $id no Existe"], 404);
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

            $res = ODS::find($id);
            if (isset($res)) {
                $res->delete();
                $data = $res->toArray();
                if ($data) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Eliminación de un ODS para el departamento - VINCULACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Eliminación de un ODS con ID: {$res->id_ods} y cod_ods: {$res->cod_ods}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Eliminar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "Eliminado con Éxito!!",
                    ]);
                } else {
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "El ods no existe (puede que ya lo haya eliminado)",
                    ]);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'mensaje' => "El ods con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
}
