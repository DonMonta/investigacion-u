<?php

namespace App\Http\Controllers;

use App\Models\Invi_tip_proyect;// Importando las clases necesarias para el controlador
use Illuminate\Http\Request;// Importamos la la librería Request para manejar peticiones HTTP
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Clase Invi_tip_proyectController
 * Esta clase contiene los métodos para la gestión de la tabla invi_tip_proyect
 * Contiene métodos para crear, leer, actualizar registros de la tabla invi_tip_proyect
 */
class Invi_tip_proyectController extends Controller
{
    /**
     * Función index que permite realizar una búsqueda en la tabla invi_tip_proyect
     * Esta función recibe un parámetro de búsqueda que se utiliza para realizar la búsqueda en la tabla
     * La función devuelve un array de objetos que representan los resultados de la búsqueda
     * @param  \Illuminate\Http\Request  $request Objeto con los parámetros de la petición (search_query, all).
     * @return \Illuminate\Http\JsonResponse Respuesta en formato JSON con los datos y/o estado del proceso.
     */
    public function index(Request $request)
    {
        try {
            // Extrae el término de búsqueda de la petición
            $searchQuery = $request->input('search_query');
            // Inicializa la consulta base sobre la tabla de convocatorias
            $query = Invi_tip_proyect::select(
                'invi_tip_proyect.*'
            )->where('tipo', 'INVESTIGACIÓN');
            // Aplica filtro de búsqueda por coincidencia parcial si el parámetro existe
            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('detalle_invi_proyect', 'LIKE', '%' . $searchQuery . '%');
                });
            }
            // Evalúa si se solicita retornar la totalidad de los registros sin paginar
            if ($request->has('all') && $request->all === 'true') {
                $data = $query->get();

                // Transforma la colección asegurando que todos los strings estén en formato UTF-8 válido
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

            // Aplica paginación por defecto si no se solicitan todos los registros (20 por página)
            $data = $query->paginate(20);
            // Retorna un error 404 si la consulta paginada no arrojó resultados
            if ($data->isEmpty()) {
                return response()->json(['error' => 'No se encontraron datos'], 404);
            }

            // Transforma la colección paginada para asegurar codificación UTF-8 válida en cadenas de texto
            $data->getCollection()->transform(function ($item) {
                $attributes = $item->getAttributes();
                foreach ($attributes as $key => $value) {
                    if (is_string($value)) {
                        $attributes[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                }
                return $attributes;
            });
            // Retorna los registros de la página actual acompañados de la metadata de paginación
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
            // Captura cualquier excepción y retorna una respuesta de error del lado del servidor (500)
            return response()->json(['error' => 'Error al codificar los datos a JSON: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store: Almacena una nueva convocatoria en la base de datos.
     * * Implementa la regla de negocio que restringe la existencia de múltiples convocatorias
     * activas en simultáneo. Si ya existe un registro activo, el nuevo registro se fuerza
     * automáticamente a un estado inactivo.
     *
     * @param  \Illuminate\Http\Request  $request Objeto con los datos del formulario de creación.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el modelo creado y un mensaje descriptivo del estado final.
     */
    public function store(Request $request)
    {   
        DB::beginTransaction();
        try{
            // Obtiene todos los datos enviados en la petición
            $inputs = $request->all();
            // Crea y persiste el registro en la base de datos con los inputs procesados
            $res = Invi_tip_proyect::create($inputs);
            // Retorna la respuesta HTTP indicando el éxito y notificando si el estado sufrió variaciones por regla de negocio
            DB::commit();
                // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de un Tipo de Proyecto para el departamento - INVESTIGACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de un Tipo de Proyecto con ID: {$res->id_tip_invi_proy} y detalle_tip_invi_proy: {$res->detalle_invi_proyect}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            return response()->json([
                'data' => $res,
                'mensaje' => "Agregado con Éxito!!"
            ]);

        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show: Muestra los detalles de un tipo de proyecto específica por su identificador.
     *
     * @param  string  $id Identificador único de el tipo de proyecto.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con los datos del registro o mensaje de error en caso de no existir.
     */
    public function show(string $id)
    {
        // Realiza la búsqueda del registro de el tipo de proyecto por su clave primaria
        $res = Invi_tip_proyect::find($id);
        // Verifica si el registro fue encontrado de manera exitosa
        if (isset($res)) {
            return response()->json([
                'data' => $res,
                'mensaje' => "Encontrado con Éxito!!",
            ]);
        } else {
            // Retorna una respuesta indicando la inexistencia del recurso solicitado
            return response()->json([
                'error' => true,
                'mensaje' => "El tipo de proyecto con id: $id no Existe",
            ]);
        }
    }

    /**
     * Update: Actualiza los datos de un tipo de proyecto específica en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request Objeto con los nuevos datos para la actualización.
     * @param  string  $id Identificador único de el tipo de proyecto a modificar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el resultado de la operación o mensaje de error.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try{
            
            // Intenta localizar el registro de el tipo de proyecto por su ID
            $res = Invi_tip_proyect::find($id);
            // Verifica si el tipo de proyecto solicitada existe en el sistema
            if (isset($res)) {
                // Asignación manual de los atributos del modelo con base en los datos de la petición
                $res->detalle_invi_proyect = $request->detalle_invi_proyect;
                $res->tipo = $request->tipo;
                // Guarda y persiste los cambios en la base de datos
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Actualización de un Tipo de Proyecto para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de un Tipo de Proyecto con ID: {$res->id_tip_invi_proy} y detalle_tip_invi_proy: {$res->detalle_invi_proyect}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Actualizar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $res,
                        'mensaje' => 'Actualizado con Éxito!!',
                    ]);
                }
                // Retorna una respuesta de error del servidor en caso de fallar el proceso de guardado
                return response()->json(['error' => true, 'mensaje' => 'Error al Actualizar'], 500);
            }
            // Retorna un error HTTP 404 si el recurso no fue localizado en la base de datos
            return response()->json(['error' => true, 'mensaje' => "El pei con id: $id no Existe"], 404);
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

   
}
