<?php

namespace App\Http\Controllers;

use App\Models\Invi_convocatoria;// Importando las clases necesarias para el controlador
use Illuminate\Support\Facades\File; // Importamos la clase Facultad
use Illuminate\Support\Facades\Log; // Importamos la librería Log
use Illuminate\Http\Request;// Importamos la la librería Request para manejar peticiones HTTP
use Carbon\Carbon;// Importamos la librería Carbon para manejar fechas y horas
use App\Models\Bitacora;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
                       

/**
 * Clase Invi_convocatoriaController
 * Esta clase contiene los métodos para la gestión de la tabla invi_convocatoria
 * Contiene métodos para crear, leer, actualizar y eliminar registros de la tabla invi_convocatoria
 * Contiene métodos para subir archivos y habilitar y inhabilitar registros de la tabla invi_convocatoria
 */
class Invi_convocatoriaController extends Controller
{
    /**
     * Función index que permite realizar una búsqueda en la tabla invi_convocatoria
     * Esta función recibe un parámetro de búsqueda que se utiliza para realizar la búsqueda en la tabla
     * La función devuelve un array de objetos que representan los resultados de la búsqueda
     * @param  \Illuminate\Http\Request  $request Objeto con los parámetros de la petición (search_query, all).
     * @return \Illuminate\Http\JsonResponse Respuesta en formato JSON con los datos y/o estado del proceso.
     */
    public function index(Request $request)
    {
        try {
            // Deshabilita las convocatorias expiradas
            $this->deshabilitarExpiradas();
            // Extrae el término de búsqueda de la petición
            $searchQuery = $request->input('search_query');
            // Inicializa la consulta base sobre la tabla de convocatorias
            $query = Invi_convocatoria::select(
                'invi_convocatoria.*'
            )->where('tipo_convocatoria', 'INVESTIGACIÓN');
            // Aplica filtro de búsqueda por coincidencia parcial si el parámetro existe
            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('num_convocatoria', 'LIKE', '%' . $searchQuery . '%');
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
    /***
     * Listar convocatorias: Devuelve un listado de todas las convocatorias
     */
    public function listconvocatoria()
    {
        try {
            $convocatorias = Invi_convocatoria::select('id_convocatoria', 'num_convocatoria', 'titulo_convocatoria')
                ->where('tipo_convocatoria', 'INVESTIGACIÓN')
                ->orderBy('id_convocatoria', 'desc')
                ->get();

            return response()->json(['data' => $convocatorias], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener convocatorias: ' . $e->getMessage()], 500);
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
    
            // Evalúa si el usuario intenta guardar la nueva convocatoria con estado ACTIVO (1)
            if ($request->estado == 1) {
                // Verifica la existencia previa de al menos una convocatoria activa en la base de datos
                $existeActivo = Invi_convocatoria::where('estado', 1)->exists();
    
                if ($existeActivo) {
                    // Si existe conflicto de unicidad activa, se fuerza el estado del nuevo registro a INACTIVO (0)
                    $inputs['estado'] = 0;
                }
            }
            // Crea y persiste el registro en la base de datos con los inputs procesados
            $res = Invi_convocatoria::create($inputs);
            DB::commit();
                // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de una convocatoria para el departamento - INVESTIGACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de una convocatoria con ID: {$res->id_convocatoria} y num_convocatoria: {$res->num_convocatoria}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            // Retorna la respuesta HTTP indicando el éxito y notificando si el estado sufrió variaciones por regla de negocio
            return response()->json([
                'data' => $res,
                'mensaje' => $res->estado == 0 && $request->estado == 1
                    ? "Agregado, pero se guardó como Inactivo porque ya existe una convocatoria activa."
                    : "Agregado con Éxito!!",
            ]);
        }catch(\Exception $e){
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show: Muestra los detalles de una convocatoria específica por su identificador.
     * * Busca el registro en la base de datos a través del ID provisto; si existe, 
     * retorna la información del modelo, de lo contrario, devuelve un mensaje de error.
     *
     * @param  string  $id Identificador único de la convocatoria.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con los datos del registro o mensaje de error en caso de no existir.
     */
    public function show(string $id)
    {
        // Realiza la búsqueda del registro de la convocatoria por su clave primaria
        $res = Invi_convocatoria::find($id);
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
                'mensaje' => "La convocatoria con id: $id no Existe",
            ]);
        }
    }

    /**
     * Update: Actualiza los datos de una convocatoria específica en la base de datos.
     * * Valida la existencia del recurso por su ID, mapea los campos del formulario, 
     * aplica la regla de exclusividad para estados activos (evitando múltiples registros activos) 
     * y maneja la actualización opcional de archivos adjuntos.
     *
     * @param  \Illuminate\Http\Request  $request Objeto con los nuevos datos para la actualización.
     * @param  string  $id Identificador único de la convocatoria a modificar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el resultado de la operación o mensaje de error.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try{
            
            // Intenta localizar el registro de la convocatoria por su ID
            $res = Invi_convocatoria::find($id);
            // Verifica si la convocatoria solicitada existe en el sistema
            if (isset($res)) {
                // Asignación manual de los atributos del modelo con base en los datos de la petición
                $res->num_convocatoria = $request->num_convocatoria;
                $res->titulo_convocatoria = $request->titulo_convocatoria;
                $res->elaboracion = $request->elaboracion;
                $res->revision = $request->revision;
                $res->aprobacion = $request->aprobacion;
                $res->fecha_inicio = $request->fecha_inicio;
                $res->fecha_fin = $request->fecha_fin;
                $res->num_resolucion = $request->num_resolucion;
                $res->tipo_convocatoria = 'INVESTIGACIÓN';
                $res->habilitar_edicion = $request->habilitar_edicion;
    
                // --- Lógica de validación de estado por regla de negocio ---
                if ($request->estado == 1) {
                    // Verifica si existe OTRA convocatoria activa que difiera de la que se está editando actualmente
                    $otroActivo = Invi_convocatoria::where('estado', 1)
                        ->where('id_convocatoria', '!=', $id)
                        ->where('tipo_convocatoria', 'INVESTIGACIÓN')
                        ->exists();
    
                    if ($otroActivo) {
                        // Si hay conflicto con otro registro activo, fuerza este a ser inactivo (0)
                        $res->estado = 0;
                        $mensajeFinal = "Actualizado, pero se cambió a Inactivo porque ya existe otra convocatoria activa.";
                    } else {
                        // Si no hay conflictos, aprueba la activación del registro (1)
                        $res->estado = 1;
                        $mensajeFinal = "Actualizado con Éxito!!";
                    }
                } else {
                    // Si la petición define el estado como inactivo, se asigna directamente
                    $res->estado = 0;
                    $mensajeFinal = "Actualizado con Éxito!!";
                }
                // Asigna el nuevo archivo adjunto únicamente si viene incluido en la petición
                if ($request->has('archivo')) {
                    $res->archivo = $request->archivo;
                }
                // Guarda y persiste los cambios en la base de datos
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Actualización de un Convocatoria para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de un Convocatoria con ID: {$res->id_convocatoria} y num_convocatoria: {$res->num_convocatoria}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Actualizar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $res,
                        'mensaje' => $mensajeFinal,
                    ]);
                }
                // Retorna una respuesta de error del servidor en caso de fallar el proceso de guardado
                return response()->json(['error' => true, 'mensaje' => 'Error al Actualizar'], 500);
            }
            // Retorna un error HTTP 404 si el recurso no fue localizado en la base de datos
            return response()->json(['error' => true, 'mensaje' => "El pei con id: $id no Existe"], 404);
        }catch(\Exception $e){
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Destroy: Realiza la baja lógica de una convocatoria específica en la base de datos.
     * * En lugar de destruir físicamente el registro, este método aplica un borrado lógico
     * modificando el estado de la convocatoria a inactivo (0) para preservar la integridad referencial 
     * e histórico de los datos.
     *
     * @param  string  $id Identificador único de la convocatoria que se desea inhabilitar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con los datos del registro afectado o mensaje de error.
     */
    public function destroy(Request $request,string $id)
    {
        DB::beginTransaction();
        try{

            // Busca el registro de la convocatoria mediante su identificador único
            $res = Invi_convocatoria::find($id);
            // Verifica si la convocatoria existe antes de proceder con la modificación
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
                            'bt_accion' => 'Inhabilitación de una convocatoria para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Inhabilitación de una convocatoria con ID: {$res->id_convocatoria} y num_convocatoria: {$res->num_convocatoria}",
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
                        'mensaje' => "La convocatoria no existe (puede que ya la haya eliminado)",
                    ]);
                }
            } else {
                // Retorna un mensaje indicando que el registro no fue localizado en el sistema
                return response()->json([
                    'error' => true,
                    'mensaje' => "La convocatoria con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Habilitar: Habilita una convocatoria específica cambiando su estado a activo.
     * * Realiza una validación previa de reglas de negocio para asegurar que no coexistan 
     * múltiples convocatorias activas en el sistema. Si pasa la validación, localiza el 
     * registro por su ID y realiza la actualización correspondiente.
     *
     * @param  string  $id Identificador único de la convocatoria que se desea habilitar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el estado de la operación, datos del modelo o códigos de error HTTP (422, 404, 500).
     */
    public function habilitar(Request $request, string $id)
    {
        DB::beginTransaction();
        try{

            // Validación de Regla de Negocio: Verificar si ya existe alguna convocatoria activa en el sistema
            $existeActivo = Invi_convocatoria::where('estado', 1)->exists();
    
            if ($existeActivo) {
                // Retorna un código 422 (Unprocessable Entity) debido a un conflicto con las reglas del negocio
                return response()->json([
                    'status' => false,
                    'mensaje' => "No se puede habilitar: Ya existe una convocatoria activa actualmente. Por favor, desactive el anterior primero."
                ], 422); // Código 422: Entidad no procesable (error de validación de negocio)
            }
    
            // Proceso de Actualización: Si el sistema está libre de activos, localiza el recurso solicitado
            $res = Invi_convocatoria::find($id);
            // Valida la existencia física del registro en la base de datos
            if (isset($res)) {
                // Modifica el estado para activar la convocatoria
                $res->estado = 1;
                // Intenta persistir los cambios efectuados en el modelo
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---

                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Habilitación de una convocatoria para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Habilitación de una convocatoria con ID: {$res->id_convocatoria} y num_convocatoria: {$res->num_convocatoria}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Habilitar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'status' => true,
                        'data' => $res,
                        'mensaje' => "¡Convocatoria Habilitada con Éxito!",
                    ]);
                } else {
                    // Retorna un error de servidor (500) en caso de fallas imprevistas en la persistencia
                    return response()->json([
                        'status' => false,
                        'mensaje' => "Error interno al intentar guardar los cambios.",
                    ], 500);
                }
            } else {
                // Retorna una respuesta 404 si el identificador no coincide con ningún registro en la base de datos
                return response()->json([
                    'status' => false,
                    'mensaje' => "La Convocatoria con id: $id no existe o fue eliminado.",
                ], 404);
            }
        }catch(\Exception $e){
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    /**
     * uploadArchivo: Gestiona la carga, validación y almacenamiento de archivos adjuntos para las convocatorias.
     * * El método realiza un seguimiento mediante logs del estado del archivo recibido, 
     * valida los requisitos de seguridad y tamaño (máx. 10MB), elimina versiones anteriores 
     * para optimizar el almacenamiento, crea la estructura de directorios necesaria en el path público 
     * y genera un nombre único y seguro para mitigar colisiones o ataques de inyección de archivos.
     *
     * @param  \Illuminate\Http\Request  $request Objeto con el archivo y la metadata necesaria (num_convocatoria, old_filename).
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el estado de la carga, el nuevo nombre asignado y la URL pública de acceso.
     */
    public function uploadArchivo(Request $request)
    {
        // Bloque de depuración y auditoría: Registra el estado inicial del archivo en los logs del sistema
        if ($request->hasFile('file')) { // Si el archivo existe
            Log::info('Archivo detectado: '.$request->file('file')->getClientOriginalName()); // Se registra el nombre del archivo detectado
            Log::info('Error de subida PHP: '.$request->file('file')->getError()); // Se registra el error de subida PHP
            Log::info('Tamaño recibido: '.$request->file('file')->getSize()); // Se registra el tamaño del archivo recibido
        } else {
            // Se registra un error de subida si no se detectó ningún archivo en la petición
            Log::warning('No se detectó ningún archivo en la petición.');
        }
        // Aplica reglas de validación estrictas para garantizar la seguridad del servidor
        $request->validate([
            'file' => 'required|max:10240', // 10MB
            'num_convocatoria' => 'required|alpha_dash',// Solo permite caracteres alfanuméricos, guiones y guiones bajos
            'old_filename' => 'nullable|string',
        ]); // Se validan los datos de la petición

        try {
            // Sanitiza el parámetro utilizando 'basename' para prevenir ataques de Directory Traversal
            $num_convocatoria = basename($request->num_convocatoria); // Se obtiene el cédula del archivo
            $file = $request->file('file'); // Se obtiene el archivo subido
            // Valida que el archivo se haya subido completamente sin corrupciones en el flujo temporal
            if (! $file->isValid()) { // Si el archivo no es válido
                throw new \Exception('Archivo inválido o corrupto.'); // Se lanza un error
            }
            // Remueve de forma permanente el archivo anterior si es reemplazado en una edición
            if ($request->filled('old_filename')) { // Si se ha proporcionado un nombre de archivo antiguo
                $oldFilename = basename($request->old_filename); // Seguridad extra
                $oldPath = public_path("Documentos/INVESTIGACION/Convocatorias/{$num_convocatoria}/{$oldFilename}"); // Se obtiene la ruta del archivo antiguo
                if (File::exists($oldPath)) { // Si el archivo antiguo existe
                    File::delete($oldPath); // Se elimina el archivo antiguo
                }
            }

            // Crear carpeta publica si no existe
            $directory = public_path("Documentos/INVESTIGACION/Convocatorias/{$num_convocatoria}");

            if (! File::isDirectory($directory)) { // Si la carpeta no existe
                File::makeDirectory($directory, 0755, true, true); // Se crea la carpeta
            }

            // Generar nombre: CI + _ + aleatorio + _ + fecha (Ymd_His)
            $aleatorio = bin2hex(random_bytes(8)); // 16 caracteres hex
            $fechaHora = date('Ymd_His');          // Ej: 20251112_1741
            $extension = $file->getClientOriginalExtension(); // pdf

            $filename = "{$num_convocatoria}_{$aleatorio}_{$fechaHora}.{$extension}"; // Se genera el nombre final del archivo

            // Guardar archivo
            $file->move($directory, $filename);

            // URL pública, para acceder al archivo desde fuera de la aplicación
            $url = url('Documentos/INVESTIGACION/Convocatorias/'.$num_convocatoria.'/'.$filename);

            // Se devuelve un array con el mensaje de éxito y el nombre del archivo y la URL pública
            return response()->json([
                'status' => true,
                'filename' => $filename,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            // Se devuelve un array con el mensaje de error y el error generado
            return response()->json([
                'status' => false,
                'message' => 'Seguridad: El archivo no pudo ser procesado.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * 
     */
    private function deshabilitarExpiradas()
    {
        $hoy = Carbon::now()->toDateString();
        
        Invi_convocatoria::where('estado', 1)
            ->where('fecha_fin', '<', $hoy)
            ->update(['estado' => 0,
                'habilitar_edicion' => 0]);
    }
}
