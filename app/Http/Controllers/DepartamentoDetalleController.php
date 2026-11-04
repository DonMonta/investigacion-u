<?php

namespace App\Http\Controllers;

use App\Models\DepartamentoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; // Importamos la clase Facultad
use Illuminate\Support\Facades\Log; // Importamos la clase JsonResponse
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class DepartamentoDetalleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $searchQuery = $request->input('search_query');
            $id_departamento = $request->input('id_departamento');
            // Inicializa la consulta base sobre la tabla de convocatorias
            $query = DepartamentoDetalle::with(['departamento'])
                ->where('id_departamento', $id_departamento);

            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('cod_norma', 'LIKE', '%' . $searchQuery . '%');
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
            // Validacion explícita personalizada: Verificar si el departamento ya tiene un registro
            $existeDetalle = DepartamentoDetalle::where('id_departamento', $request->id_departamento)->first();
            if ($existeDetalle) {
                return response()->json([
                    'error' => 'Este departamento ya cuenta con detalles registrados. Si desea modificarlos, debe usar la opción de editar.'
                ], 400); // 400 Bad Request
            }

            // 1. Validar los datos de entrada
            $validator = Validator::make($request->all(), [
                // El unique aquí actúa como doble barrera de seguridad
                'id_departamento' => 'required|integer|exists:departamentos,id_departamento|unique:departamento_detalles,id_departamento',
                'sobre_mi'        => 'nullable|string|max:2000',
                'mision'          => 'nullable|string|max:2000',
                'vision'          => 'nullable|string|max:2000',
            ], [
                'id_departamento.required' => 'El departamento es obligatorio.',
                'id_departamento.exists'   => 'El departamento seleccionado no existe.',
                'id_departamento.unique'   => 'Este departamento ya tiene información detallada registrada.',
                'string'                   => 'El campo debe ser un texto válido.',
                'max'                      => 'El texto no puede superar los 2000 caracteres.'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // 2. Crear el registro
            $detalle = DepartamentoDetalle::create([
                'id_departamento' => $request->id_departamento,
                'sobre_mi'        => $request->sobre_mi,
                'mision'          => $request->mision,
                'vision'          => $request->vision,
            ]);
            
            DB::commit();

            // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de detalle de departamento - VINCULACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de de detalle de departamento: {$request->id_departamento}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }

            return response()->json([
                'message' => 'Detalles del departamento registrados con éxito.',
                'data'    => $detalle
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Si hay error grave, revertimos la base de datos
            return response()->json(['error' => 'Error al guardar los datos: ' . $e->getMessage()], 500);
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
            // 1. Buscar el registro por su id_detalle
            $detalle = DepartamentoDetalle::find($id);

            if (!$detalle) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }

            // 2. Validar los datos de entrada
            // Notar la regla unique: ignoramos el id actual para que permita actualizar el mismo registro
            $validator = Validator::make($request->all(), [
                'id_departamento' => 'required|integer|exists:departamentos,id_departamento|unique:departamento_detalles,id_departamento,' . $id . ',id_detalle',
                'sobre_mi'        => 'nullable|string|max:2000',
                'mision'          => 'nullable|string|max:2000',
                'vision'          => 'nullable|string|max:2000',
            ], [
                'id_departamento.required' => 'El departamento es obligatorio.',
                'id_departamento.exists'   => 'El departamento seleccionado no existe.',
                'id_departamento.unique'   => 'Este departamento ya tiene otra información asignada.',
                'max'                      => 'El texto no puede superar los 2000 caracteres.'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // 3. Actualizar el registro
            $detalle->update([
                'id_departamento' => $request->id_departamento,
                'sobre_mi'        => $request->sobre_mi,
                'mision'          => $request->mision,
                'vision'          => $request->vision,
            ]);
            DB::commit();
            // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Actualización de detalle de departamento - VINCULACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de detalle de departamento: {$request->id_departamento}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }

            return response()->json([
                'message' => 'Detalles del departamento actualizados con éxito.',
                'data'    => $detalle
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $detalle = DepartamentoDetalle::find($id);

            if (!$detalle) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }

            $detalle->delete();
            DB::commit();
            // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Eliminación de detalle de departamento - VINCULACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Eliminación de detalle de departamento: {$id}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            return response()->json([
                'message' => 'Registro eliminado con éxito.'
            ], 200);
        } catch (QueryException $qe) {
            // Manejo específico si hay problemas de integridad referencial al intentar borrar
            return response()->json(['error' => 'No se puede eliminar porque está en uso o hay un error de base de datos.'], 409);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el registro: ' . $e->getMessage()], 500);
        }
    }
}
