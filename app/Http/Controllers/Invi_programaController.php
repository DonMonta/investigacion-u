<?php

namespace App\Http\Controllers;

use App\Models\Invi_programa;
use Illuminate\Http\Request;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Invi_programaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $searchQuery = $request->input('search_query');
            $query = Invi_programa::select(
                'invi_programa.*'
            );
            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('nombre_programa', 'LIKE', '%' . $searchQuery . '%');
                });
            }
            if ($request->has('all') && $request->all === 'true') {
                $data = $query->get();

                // Asegurar codificación UTF-8
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
                return response()->json(['error' => 'No se encontraron datos'], 404);
            }

            // Convertir los datos a UTF-8 válido
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
            $inputs = $request->all();
            $res = Invi_programa::create($inputs);
            DB::commit();
                // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de un Programa - INVESTIGACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de un Programa con ID: {$res->id_programa} y nombre_programa: {$res->nombre_programa}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            return response()->json([
                'message' => 'Registro creado exitosamente',
                'data' => $res
            ], 201);

        }catch(\Exception $e){
            Log::error('Error al crear el registro: ' . $e->getMessage());
            DB::rollBack();
            return response()->json(['error' => 'Error al crear el registro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $res = Invi_programa::find($id);
        if (isset($res)) {
            return response()->json([
                'data' => $res,
                'mensaje' => "Encontrado con Éxito!!",
            ]);
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "El Programa con id: $id no Existe",
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

            $res = Invi_programa::find($id);
            if (isset($res)) {
                $res->nombre_programa = $request->nombre_programa;
                $res->estado_programa = $request->estado_programa;
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Actualización de un Programa - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de un Programa con ID: {$res->id_programa} y nombre_programa: {$res->nombre_programa}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Actualizar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $res,
                        'mensaje' => "Actualizado con Éxito!!",
                    ]);
                } else {
                    return response()->json([
                        'error' => true,
                        'mensaje' => "Error al Actualizar",
                    ]);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'mensaje' => "El Programa con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            DB::rollBack();
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

            // Busca el registro de El Programa mediante su identificador único
            $res = Invi_programa::find($id);
            // Verifica si El Programa existe antes de proceder con la modificación
            if (isset($res)) {
                // Aplica el borrado lógico cambiando el estado a inactivo (0)
                $res->estado = 0;
                $res->save();
                // Convierte la instancia del modelo afectado a una estructura de array para devolverla
                $data = $res->toArray();
                // Evalúa que la conversión de datos se haya realizado correctamente para estructurar la respuesta
                if ($data) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Inhabilitación de un Programa - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Inhabilitación de un Programa con ID: {$res->id_programa} y nombre_programa: {$res->nombre_programa}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Inhabilitar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "Inhabilitado con Éxito!!",
                    ]);
                } else {
                    // Control alternativo en caso de inconsistencia con la existencia del recurso
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "El Programa no existe (puede que ya la haya eliminado)",
                    ]);
                }
            } else {
                // Retorna un mensaje indicando que el registro no fue localizado en el sistema
                return response()->json([
                    'error' => true,
                    'mensaje' => "El Programa con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function habilitar(Request $request, string $id)
    {
         DB::beginTransaction();
        try{

            // Busca el registro de El Programa mediante su identificador único
            $res = Invi_programa::find($id);
            // Verifica si El Programa existe antes de proceder con la modificación
            if (isset($res)) {
                // Aplica el borrado lógico cambiando el estado a inactivo (0)
                $res->estado = 1;
                $res->save();
                // Convierte la instancia del modelo afectado a una estructura de array para devolverla
                $data = $res->toArray();
                // Evalúa que la conversión de datos se haya realizado correctamente para estructurar la respuesta
                if ($data) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Habilitación de un Programa - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Habilitación de un Programa con ID: {$res->id_convocatoria} y nombre_programa: {$res->nombre_programa}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Habilitación: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "Habilitación con Éxito!!",
                    ]);
                } else {
                    // Control alternativo en caso de inconsistencia con la existencia del recurso
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "El Programa no existe (puede que ya la haya eliminado)",
                    ]);
                }
            } else {
                // Retorna un mensaje indicando que el registro no fue localizado en el sistema
                return response()->json([
                    'error' => true,
                    'mensaje' => "El Programa con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
}
