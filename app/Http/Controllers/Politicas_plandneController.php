<?php

namespace App\Http\Controllers;

use App\Models\Politicas_plandne;
use App\Models\Obj_pol_plandne;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Politicas_plandneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Politicas_plandne::select(
                'politicas_plandne.*'
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

            $objetivopolplandne = Obj_pol_plandne::find($request->id_obj_pol_pladne);
    
            if (!$objetivopolplandne) {
                return response()->json(['error' => true, 'mensaje' => 'Política no válida'], 404);
            }
    
            $id_pladne = $objetivopolplandne->id_pladne;
    
            // 2. Verificar si el código ya existe en objetivos que pertenecen al MISMO PEI
            $existe = Politicas_plandne::where('cod_pol', $request->cod_pol)
                ->whereHas('objetivos_plandne', function ($query) use ($id_pladne) {
                    $query->where('id_pladne', $id_pladne);
                })->exists();
    
            if ($existe) {
                return response()->json([
                    'error' => true,
                    'mensaje' => "El código {$request->cod_pol} ya está registrado en este PLANDE."
                ], 409);
            }
    
            $res = Politicas_plandne::create($request->all());
            DB::commit();
                // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de un Politica para el departamento - INVESTIGACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de un Politica con ID: {$res->id_obj_pol_pladne} y cod_pol: {$res->cod_pol}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            return response()->json([
                'data' => $res,
                'mensaje' => 'Agregado con Éxito!!',
            ]);
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}
    public function listarPorPlandne($id_pladne)
    {
        // 1. Ejecutamos la consulta y obtenemos los datos (usamos get() al final)
        $politicas = Politicas_plandne::whereHas('objetivos_plandne', function ($query) use ($id_pladne) {
            $query->where('id_pladne', $id_pladne);
        })
            ->with('objetivos_plandne')
            ->get();

        // 2. Ordenamos la colección resultante usando PHP
        $politicasOrdenadas = $politicas->sort(function ($a, $b) {

            // Primero: Ordenamos por el Objetivo. 
            // Si quieres ordenar alfabéticamente por el nombre del objetivo, cambia 
            // 'id_obj_pol_pladne' por 'objetivos_plandne->nombre_objetivo' (o el campo que uses).
            if ($a->id_obj_pol_pladne !== $b->id_obj_pol_pladne) {
                return $a->id_obj_pol_pladne <=> $b->id_obj_pol_pladne;
            }

            // Segundo: Si pertenecen al mismo objetivo, aplicamos el "Natural Sort" al código.
            // strnatcmp es una función nativa de PHP que entiende que "1.10" es mayor que "1.2"
            return strnatcmp($a->cod_pol, $b->cod_pol);
        })->values(); // values() es crucial para resetear los índices numéricos del array resultante

        // 3. Retornamos la respuesta
        return response()->json($politicasOrdenadas);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try{

            $res = Politicas_plandne::find($id);
    
            if (isset($res)) {
                // 1. Obtener el PEI a través del subsistema (por si cambiaron de subsistema en el edit)
                $objetivopolplandne = Obj_pol_plandne::find($request->id_obj_pol_pladne);
                $id_pladne = $objetivopolplandne->id_pladne;
    
                // 2. Validar código duplicado en el mismo PEI, excluyendo el registro actual
                $existe = Politicas_plandne::where('cod_pol', $request->cod_pol)
                    ->where('id_pol_pladne', '!=', $id) // Excluir el actual
                    ->whereHas('objetivos_plandne', function ($query) use ($id_pladne) {
                        $query->where('id_pladne', $id_pladne);
                    })->exists();
    
                if ($existe) {
                    return response()->json([
                        'error' => true,
                        'mensaje' => "El código {$request->cod_pol} ya pertenece a otra política de este PLANDE."
                    ], 409);
                }
    
                $res->id_obj_pol_pladne = $request->id_obj_pol_pladne;
                $res->cod_pol = $request->cod_pol;
                $res->detalle_pol = $request->detalle_pol;
    
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Actualización de un Politica para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de un Politica con ID: {$res->id_obj_pol_pladne} y cod_pol: {$res->cod_pol}",
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
    
            return response()->json(['error' => true, 'mensaje' => "La politica con id: $id no Existe"], 404);

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

            $res = Politicas_plandne::find($id);
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
                            'bt_accion' => 'Eliminación de un Politica para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Eliminación de un Politica con ID: {$res->id_obj_pol_pladne} y cod_pol: {$res->cod_pol}",
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
                        'mensaje' => "La politica no existe (puede que ya la haya eliminado)",
                    ]);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'mensaje' => "La politica con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
}
