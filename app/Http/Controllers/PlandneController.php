<?php

namespace App\Http\Controllers;

use App\Models\Plandne;
use App\Models\PlandneEstadoDepartamento;
use App\Models\Departamento;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlandneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $searchQuery = $request->input('search_query');
            $id_departamento = $request->input('id_departamento');
            $nombre_departamento = 'Departamento seleccionado';
            if ($id_departamento) {
                $depto = Departamento::select('nombre_departamento')->where('id_departamento', $id_departamento)->first();
                if ($depto) {
                    // Ajusta 'nombre_departamento' al nombre real de tu columna en la base de datos
                    $nombre_departamento = $depto->nombre_departamento ?? $depto->nombre;
                }
            }
            // Usamos withCount para obtener los totales de las relaciones
            $query = Plandne::withCount(['objetivos_plandne', 'politicas_plandne']);
             if ($id_departamento) {
                $query->whereHas('estadosDepartamentosPlanDNE', function ($q) use ($id_departamento) {
                    $q->where('id_departamento', $id_departamento);
                });

                $query->with(['estadosDepartamentosPlanDNE' => function ($q) use ($id_departamento) {
                    $q->where('id_departamento', $id_departamento);
                }]);
            }
            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('nombre_plandne', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('anio_plandne', 'LIKE', '%' . $searchQuery . '%');
                });
            }

            // --- FUNCION AUXILIAR PARA TRANSFORMAR LA DATA ---
            $transformData = function ($item) use ($id_departamento, $nombre_departamento) {
                $attributes = $item->toArray();

                // Si consultamos por departamento, agregamos variables exclusivas para él
                if ($id_departamento && $item->estadosDepartamentosPlanDNE->isNotEmpty()) {
                    // YA NO sobreescribimos 'estado_pei' para conservar el estado general.
                    // En su lugar, creamos estas dos nuevas llaves:
                    $attributes['estado_departamento'] = $item->estadosDepartamentosPlanDNE->first()->estado;
                    $attributes['nombre_departamento'] = $nombre_departamento;
                }

                foreach ($attributes as $key => $value) {
                    if (is_string($value)) {
                        $attributes[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                }
                return $attributes;
            };

           if ($request->has('all') && $request->all === 'true') {
                $data = $query->get();
                $data->transform($transformData);
                return response()->json(['data' => $data]);
            }

            $data = $query->paginate(20);

            if ($data->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'message' => 'No se encontraron datos'
                ], 200);
            }

            $data->getCollection()->transform($transformData);

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id_departamento = $request->input('id_departamento');
        if (!$id_departamento) {
            return response()->json(['error' => true, 'mensaje' => 'El id_departamento es requerido.'], 400);
        }
        $existeDuplicado = Plandne::where('nombre_plandne', $request->nombre_plandne)
            ->where('anio_plandne', $request->anio_plandne)
            ->whereHas('estadosDepartamentosPlanDNE', function ($q) use ($id_departamento) {
                $q->where('id_departamento', $id_departamento);
            })
            ->exists();

        if ($existeDuplicado) {
            return response()->json([
                'status' => false,
                'mensaje' => 'Ya existe un PlANDE habilitado con el mismo nombre y período de años para su departamento.'
            ], 422);
        }
        DB::beginTransaction();
        try{

            $inputs = $request->all();
            $estado_final = $request->estado_plandne ?? 1;
            if ($estado_final == 1) {
                // Verificamos si este DEPARTAMENTO ya tiene un Plandne activo
                $existeActivo = PlandneEstadoDepartamento::where('id_departamento', $id_departamento)
                    ->where('estado', 1)->exists();
    
                if ($existeActivo) {
                    $estado_final = 0;
                }
            }
            $inputs['estado_plandne'] = $estado_final;
            $res = Plandne::create($inputs);
             // Creamos la relación en la tabla pivote
            PlandneEstadoDepartamento::create([
                'id_pladne' => $res->id_pladne,
                'id_departamento' => $id_departamento,
                'estado' => $estado_final
            ]);
            DB::commit();
                // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de un Plandne para el departamento - INVESTIGACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de un Plandne con ID: {$res->id_pladne} y nombre: {$res->nombre_plandne}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            return response()->json([
                'data' => $res,
                'mensaje' => ($estado_final == 0 && $request->estado_plandne == 1)
                    ? "Agregado, pero se guardó como Inactivo porque su departamento ya tiene un Plandne activo."
                    : "Agregado con Éxito!!",
            ]);
        }catch(\Exception $e){
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $res = Plandne::find($id);
        if (isset($res)) {
            return response()->json([
                'data' => $res,
                'mensaje' => "Encontrado con Éxito!!",
            ]);
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "El PlANDE con id: $id no Existe",
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $id_departamento = $request->input('id_departamento');

        if (!$id_departamento) {
            return response()->json(['error' => true, 'mensaje' => 'El id_departamento es requerido.'], 400);
        }
        $existeDuplicado = Plandne::where('id_pladne', $id)
            ->where('nombre_plandne', $request->nombre_plandne)
            ->where('anio_plandne', $request->anio_plandne)
            ->whereHas('estadosDepartamentosPlanDNE', function ($q) use ($id_departamento) {
                $q->where('id_departamento', $id_departamento);
            })
            ->exists();

        if ($existeDuplicado) {
            return response()->json([
                'status' => false,
                'mensaje' => 'Ya existe otro PlANDE habilitado con el mismo nombre y período de años en su departamento.'
            ], 422);
        }
        DB::beginTransaction();
        try{

            $res = Plandne::find($id);
            if (isset($res)) {
                $res->nombre_plandne = $request->nombre_plandne;
                $res->anio_plandne = $request->anio_plandne;
                $res->link_plandne = $request->link_plandne;
                $pivot = PlandneEstadoDepartamento::firstOrNew([
                    'id_pladne' => $res->id_pladne,
                    'id_departamento' => $id_departamento
                ]);
                
                // Lógica de validación de estado
                if ($request->estado_plandne == 1) {
                    // Buscamos si hay otro Plandne activo que NO SEA el que estamos editando
                    $otroActivo = PlandneEstadoDepartamento::where('id_departamento', $id_departamento)
                        ->where('id_pladne', '!=', $id)
                        ->where('estado', 1)
                        ->exists();
    
                    if ($otroActivo) {
                        $res->estado_plandne = 0;
                        $mensajeFinal = "Actualizado, pero se cambió a Inactivo porque su departamento ya tiene otro Plandne activo.";
                    } else {
                        $res->estado_plandne = 1;
                        $mensajeFinal = "Actualizado con Éxito!!";
                    }
                } else {
                    $res->estado_plandne = 0;
                    $mensajeFinal = "Actualizado con Éxito!!";
                }
                $pivot->save();
    
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Actualización de un Plandne para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de un Plandne con ID: {$res->id_pladne} y nombre: {$res->nombre_plandne}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Actualizar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $res,
                        'mensaje' => $mensajeFinal,
                    ]);
                }
                DB::rollBack();
                return response()->json(['error' => true, 'mensaje' => 'Error al Actualizar'], 500);
            }
    
            return response()->json(['error' => true, 'mensaje' => "El plandne con id: $id no Existe"], 404);
        }catch(\Exception $e){
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

            $id_departamento = $request->input('id_departamento');
    
            if (!$id_departamento) {
                return response()->json(['error' => true, 'mensaje' => 'El id_departamento es requerido.'], 400);
            }
            $res = Plandne::find($id);
            if (isset($res)) {
                $pivot = PlandneEstadoDepartamento::where('id_pladne', $id)
                    ->where('id_departamento', $id_departamento)
                    ->first();
    
                if ($pivot) {
                    $pivot->estado = 0;
                    $pivot->save();
                }
                $res->save();
                $data = $res->toArray();
                DB::commit();
                    // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                try {
                    $user = Auth::user(); // Obtenemos el usuario autenticado
                    Bitacora::create([
                        'bt_usuario' => $user->NombUsu,
                        'bt_fechahora' => Carbon::now(),
                        'bt_accion' => 'Inhabilitación de un Plandne para el departamento - INVESTIGACIÓN',
                        'bt_ippc' => $request->ip(),
                        'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Inhabilitación de un Plandne con ID: {$res->id_pladne} y nombre: {$res->nombre_plandne}",
                    ]);
                } catch (\Exception $ex) {
                    Log::error('Error bitácora en Inhabilitar: ' . $ex->getMessage());
                }
                return response()->json([
                    'data' => $data,
                    'mensaje' => "Inhabilitado con Éxito para su departamento!!",
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'mensaje' => "El plandne con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function habilitar(Request $request, string $id)
    {
        DB::beginTransaction();
        try{
            $id_departamento = $request->input('id_departamento');
            if (!$id_departamento) {
                return response()->json(['error' => true, 'mensaje' => 'El id_departamento es requerido.'], 400);
            }
            // 1. Verificar si YA existe algún PlANDE activo
            $existeActivo = PlandneEstadoDepartamento::where('id_departamento', $id_departamento)
                ->where('estado', 1)->exists();
    
            if ($existeActivo) {
                return response()->json([
                    'status' => false,
                    'mensaje' => "No se puede habilitar: Su departamento ya tiene un PlANDE activo actualmente. Por favor, desactive el anterior primero."
                ], 422); // Código 422: Entidad no procesable (error de validación de negocio)
            }
            
            // 2. Si no hay activos, procedemos a buscar y habilitar
            $res = Plandne::find($id);
    
            if (isset($res)) {
                $pivot = PlandneEstadoDepartamento::firstOrCreate(
                    ['id_pladne' => $res->id_pladne, 'id_departamento' => $id_departamento],
                    ['estado' => 1] // Lo crea en 1 si no existía el registro
                );
    
                $pivot->estado = 1;
                $pivot->save();
                $res->estado_plandne = 1;
    
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Habilitación de un Plandne para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Habilitación de un Plandne con ID: {$res->id_pladne} y nombre: {$res->nombre_plandne}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Habilitar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'status' => true,
                        'data' => $res,
                        'mensaje' => "¡PlANDE Habilitado con Éxito!",
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
                    'mensaje' => "El PlANDE con id: $id no existe o fue eliminado.",
                ], 404);
            }
        }catch(\Exception $e){
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
}
