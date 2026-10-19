<?php

namespace App\Http\Controllers;

use App\Models\Zona_planificacion;
use App\Models\Provincia;
use App\Models\Detalle_zona_planificacion;
use App\Models\Bitacora;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;


class Zona_planificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $searchQuery = $request->input('search_query');
            $query = Zona_planificacion::withCount('detalle_zona_planificacion');
            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('nombre_zona', 'LIKE', '%' . $searchQuery . '%');
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

            $data = $query->paginate(20);

            if ($data->isEmpty()) {
                return response()->json(['error' => 'No se encontraron datos'], 404);
            }

            return response()->json([
                'data' => $data->items(),
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
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
            $inputs = $request->input();
           
            $res = Zona_planificacion::create($inputs);
            DB::commit();
                // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario'     => $user->NombUsu,
                    'bt_fechahora'   => Carbon::now(),
                    'bt_accion'      => "Crear Zona de Planificación" . " - VINCULACIÓN",
                    'bt_ippc'        => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Crear Zona de Planificación con ID: {$res->id_zona_plan} y nombre: {$res->nombre_zona}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            return response()->json([
                'data'=>$res,
                'mensaje'=>"Agregado con Éxito!!",
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
        $res = Zona_planificacion::find($id);
        if (isset($res)) {
            return response()->json([
                'data' => $res,
                'mensaje' => "Encontrado con Éxito!!",
            ]);
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "La Zona de Planificación con id: $id no Existe",
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

            $res = Zona_planificacion::find($id);
    
            if (isset($res)) {
                $res->nombre_zona = $request->nombre_zona;
                $res->save();
                DB::commit();
                    // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                try {
                    $user = Auth::user(); // Obtenemos el usuario autenticado
                    Bitacora::create([
                        'bt_usuario'     => $user->NombUsu,
                        'bt_fechahora'   => Carbon::now(),
                        'bt_accion'      => "Actualizar Zona de Planificación" . " - VINCULACIÓN",
                        'bt_ippc'        => $request->ip(),
                        'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualizar Zona de Planificación con ID: {$res->id_zona_plan} y nombre: {$res->nombre_zona}",
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
                    'mensaje' => "La Zona de Planificación con id: $id no Existe",
                ]);
            }
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

            $res = Zona_planificacion::find($id);
    
            if (isset($res)) {
                $res->delete();
                DB::commit();
                    // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                try {
                    $user = Auth::user(); // Obtenemos el usuario autenticado
                    Bitacora::create([
                        'bt_usuario'     => $user->NombUsu,
                        'bt_fechahora'   => Carbon::now(),
                        'bt_accion'      => "Eliminar Zona de Planificación" . " - VINCULACIÓN",
                        'bt_ippc'        => $request->ip(),
                        'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Eliminar Zona de Planificación con ID: {$res->id_zona_plan} y nombre: {$res->nombre_zona}",
                    ]);
                } catch (\Exception $ex) {
                    Log::error('Error bitácora en Eliminar: ' . $ex->getMessage());
                }
                return response()->json([
                    'mensaje' => "Eliminado con Éxito!!",
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'mensaje' => "La Zona de Planificación con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function getProvincias()
    {
        try {
            // Hacemos un Left Join para traer todas las provincias y cruzar si ya tienen zona asignada
            $provincias = Provincia::leftJoin('detalle_zona_planificacion', 'provincia.id_provincia', '=', 'detalle_zona_planificacion.id_provincia')
                ->select(
                    'provincia.id_provincia',
                    'provincia.codigo',
                    'provincia.detalle',
                    'provincia.region',
                    'detalle_zona_planificacion.id_zona_plan' // Inyecta el ID de zona actual si existe
                )
                ->get();

            return response()->json([
                'data' => $provincias
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener el catálogo de provincias: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getProvinciasPorZona(string $id_zona_plan)
    {
        try {
            $asignadas = Detalle_zona_planificacion::where('id_zona_plan', $id_zona_plan)->get();

            return response()->json([
                'data' => $asignadas
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener las provincias asignadas: ' . $e->getMessage()
            ], 500);
        }
    }
    public function guardarProvincias(Request $request)
    {
        DB::beginTransaction();
        try{
            
            $request->validate([
                'id_zona_plan' => 'required',
                'provincias'   => 'present|array' // 'present' permite mandar un arreglo vacío si desmarcan todas
            ]);
    
            try {
                DB::beginTransaction();
    
                $id_zona_plan = $request->input('id_zona_plan');
                $provinciasSeleccionadas = $request->input('provincias', []);
    
                // Ojo: Como en tu modelo pusiste `$incrementing = false`, significa que la base de datos
                // no genera el ID automáticamente, o manejas un ID manual.
                // Calculamos el último ID actual para simular el incremental de forma segura:
                $ultimoId = Detalle_zona_planificacion::max('id_detalle_zona_plan') ?? 0;
    
                // Paso A: Eliminamos todas las relaciones anteriores de ESTA zona (Simula un "sync")
                Detalle_zona_planificacion::where('id_zona_plan', $id_zona_plan)->delete();
    
                // Paso B: Insertamos las nuevas provincias seleccionadas
                foreach ($provinciasSeleccionadas as $id_provincia) {
                    $ultimoId++;
                    
                    Detalle_zona_planificacion::create([
                        'id_detalle_zona_plan' => $ultimoId, // Obligatorio por tu $incrementing = false
                        'id_zona_plan'         => $id_zona_plan,
                        'id_provincia'        => $id_provincia
                    ]);
                }
    
                DB::commit();
                // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                try {
                    $user = Auth::user(); // Obtenemos el usuario autenticado
                    Bitacora::create([
                        'bt_usuario'     => $user->NombUsu,
                        'bt_fechahora'   => Carbon::now(),
                        'bt_accion'      => "Actualizar Zona de Planificación" . " - VINCULACIÓN",
                        'bt_ippc'        => $request->ip(),
                        'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualizar Zona de Planificación con ID: {$request->id_zona_plan} y nombre: {$request->nombre_zona}",
                    ]);
                } catch (\Exception $ex) {
                    Log::error('Error bitácora en Actualizar: ' . $ex->getMessage());
                }
                        
                return response()->json([
                    'success' => true,
                    'message' => 'Provincias sincronizadas con éxito'
                ], 200);
    
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Error al procesar la asignación: ' . $e->getMessage()
                ], 500);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

}
