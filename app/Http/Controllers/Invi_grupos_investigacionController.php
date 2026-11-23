<?php

namespace App\Http\Controllers;

use App\Models\Invi_grupos_investigacion;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Invi_grupos_investigacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Extrae el término de búsqueda de la petición
            $searchQuery = $request->input('search_query');
            // Inicializa la consulta base sobre la tabla de convocatorias
            $query = Invi_grupos_investigacion::with([
                'invi_proyectos',
                'integrantes.informacionPersonalD',
                'integrantes.informacionpersonal',
                'integrantes.funciones',
                'objetivos',
                'actividades'
            ]);
            // Aplica filtro de búsqueda por coincidencia parcial si el parámetro existe
            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('acronimo', 'LIKE', '%' . $searchQuery . '%');
                });
            }
            // 2. Creamos un closure para limpiar UTF-8 que incluya las relaciones (toArray en lugar de getAttributes)
            $sanitizeUtf8 = function ($item) {
                $array = $item->toArray(); // toArray() sí incluye las relaciones cargadas en el "with"

                // array_walk_recursive iterará sobre los atributos principales y los de las relaciones
                array_walk_recursive($array, function (&$value) {
                    if (is_string($value)) {
                        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                });

                return $array;
            };

            if ($request->has('all') && $request->all === 'true') {
                $data = $query->get();
                // Usar map() en lugar de transform() es más seguro porque retorna una nueva colección
                $dataSanitizada = $data->map($sanitizeUtf8);

                return response()->json(['data' => $dataSanitizada]);
            }

            // Paginación por defecto
            $data = $query->paginate(20);

            if ($data->isEmpty()) {
                return response()->json(['error' => 'No se encontraron datos'], 404);
            }

            // Extraemos los items y los procesamos sin alterar el objeto paginador original
            $itemsSanitizados = collect($data->items())->map($sanitizeUtf8)->all();

            return response()->json([
                'data' => $itemsSanitizados, // Usamos el array ya procesado
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            // Captura cualquier excepción y retorna una respuesta de error del lado del servidor (500)
            return response()->json(['error' => 'Error al codificar los datos a JSON: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $inputs = $request->all();
            $res = Invi_grupos_investigacion::create($inputs);
            DB::commit();
            // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->LoginUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de un Grupo de Investigación - INVESTIGACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->LoginUsu} REALIZÓ: Creación de un Grupo de Investigación con ID: {$res->id_grupo} y acronimo: {$res->acronimo}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            return response()->json([
                'message' => 'Registro creado exitosamente',
                'data' => $res
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear el registro: ' . $e->getMessage());
            DB::rollBack();
            return response()->json(['error' => 'Error al crear el registro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {

            $res = Invi_grupos_investigacion::find($id);
            if (isset($res)) {
                $res->proyect_id = $request->proyect_id;
                $res->acronimo = $request->acronimo;
                $res->nombre_grupo = $request->nombre_grupo;
                $res->estado = $request->estado;
                if ($res->save()) {
                    DB::commit();
                    // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->LoginUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Actualización de un Grupo de Investigación - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->LoginUsu} REALIZÓ: Actualización de un Grupo de Investigación con ID: {$res->id_grupo} y acronimo: {$res->acronimo}",
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
                    'mensaje' => "El Grupo de Investigación con id: $id no Existe",
                ]);
            }
        } catch (\Exception $e) {
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
        try {

            // Busca el registro de El Programa mediante su identificador único
            $res = Invi_grupos_investigacion::find($id);
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
                            'bt_usuario' => $user->LoginUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Inhabilitación de un Grupo de Investigación',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->LoginUsu} REALIZÓ: Inhabilitación de un Grupo de Investigación con ID: {$res->id_grupo} y acronimo: {$res->acronimo}",
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
                        'mensaje' => "El Grupo de Investigación no existe (puede que ya la haya eliminado)",
                    ]);
                }
            } else {
                // Retorna un mensaje indicando que el registro no fue localizado en el sistema
                return response()->json([
                    'error' => true,
                    'mensaje' => "El Grupo de Investigación con id: $id no Existe",
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function habilitar(Request $request, string $id)
    {
        DB::beginTransaction();
        try {

            // Busca el registro de El Grupo de Investigación mediante su identificador único
            $res = Invi_grupos_investigacion::find($id);
            // Verifica si El Grupo de Investigación existe antes de proceder con la modificación
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
                            'bt_usuario' => $user->LoginUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Habilitación de un Grupo de Investigación - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->LoginUsu} REALIZÓ: Habilitación de un Grupo de Investigación con ID: {$res->id_grupo} y acronimo: {$res->acronimo}",
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
                        'mensaje' => "El Grupo de Investigación no existe (puede que ya la haya eliminado)",
                    ]);
                }
            } else {
                // Retorna un mensaje indicando que el registro no fue localizado en el sistema
                return response()->json([
                    'error' => true,
                    'mensaje' => "El Grupo de Investigación con id: $id no Existe",
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
}
