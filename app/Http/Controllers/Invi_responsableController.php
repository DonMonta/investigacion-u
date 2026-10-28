<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
// Importando las clases necesarias para el controlador
use App\Models\Invi_responsable; // Utilizado para recibir los datos enviados por el cliente
// Utilizado para trabajar con fechas y horas
// Utilizado para realizar consultas a la base de datos
use Illuminate\Http\JsonResponse; // Utilizado para realizar operaciones de archivos
use Illuminate\Http\Request; // Utilizado para registrar eventos en el sistema
// Utilizado para realizar operaciones de autenticación
use Illuminate\Support\Facades\File; // Importamos la clase Facultad
use Illuminate\Support\Facades\Log; // Importamos la clase JsonResponse
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Definición de la clase Invi_responsableController
class Invi_responsableController extends Controller
{
    /**
     * Función index que permite realizar una búsqueda en la tabla invi_responsable
     * Esta función recibe un parámetro de búsqueda que se utiliza para realizar la búsqueda en la tabla
     * La función devuelve un array de objetos que representan los resultados de la búsqueda
     *
     * @return array
     */
    public function index(Request $request)
    {
        try {
            $searchQuery = $request->input('search_query'); // Obtenemos el parámetro de búsqueda
            $query = Invi_responsable::select(
                'invi_responsable.*',
                'informacionpersonal_d.NombInfPer',
                'informacionpersonal_d.ApellInfPer',
                'informacionpersonal_d.ApellMatInfPer',
                'facultad.*'
            ) // Se seleccionan los campos necesarios para mostrar
                ->join('informacionpersonal_d', 'informacionpersonal_d.CIInfPer', '=', 'invi_responsable.ciinfper_doc') // Unión la tabla informacionpersonald con la tabla invi_responsable
                ->join('facultad', 'facultad.idfacultad', '=', 'invi_responsable.idfacultad') // Unión la tabla facultad con la tabla invi_responsable
                ->where('tipo_responsable', '=', 'INVESTIGACIÓN'); // Se filtran los registros que pertenecen al tipo de responsable INVESTIGACIÓN

            if ($searchQuery) { // Si se ha introducido un parámetro de búsqueda
                $query->where(function ($q) use ($searchQuery) { // Se aplica la condición de búsqueda a la consulta
                    $q->where('invi_responsable.ciinfper_doc', 'LIKE', '%' . $searchQuery . '%'); // Se aplica la condición de búsqueda a la consulta
                });
            }
            if ($request->has('all') && $request->all === 'true') { // Si se ha introducido el parámetro all y su valor es true
                $data = $query->get(); // Se obtienen los datos de la consulta

                // Asegurar codificación UTF-8
                $data->transform(function ($item) { // Se transforman los datos de la consulta
                    $attributes = $item->getAttributes(); // Se obtienen los atributos de la consulta
                    foreach ($attributes as $key => $value) { // Se recorren los atributos de la consulta
                        if (is_string($value)) { // Si el valor es una cadena de caracteres
                            $attributes[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8'); // Se codifican los caracteres UTF-8
                        }
                    }

                    return $attributes; // Se devuelven los atributos transformados
                });

                return response()->json(['data' => $data]); // Se devuelven los datos transformados
            }

            // Paginación por defecto
            $data = $query->paginate(20);

            if ($data->isEmpty()) { // Si no hay datos
                return response()->json(['error' => 'No se encontraron datos'], 404); // Se devuelven un error 404
            }

            // Convertir los datos a UTF-8 válido
            $data->getCollection()->transform(function ($item) { // Se transforman los datos de la consulta
                $attributes = $item->getAttributes(); // Se obtienen los atributos de la consulta
                foreach ($attributes as $key => $value) { // Se recorren los atributos de la consulta
                    if (is_string($value)) { // Si el valor es una cadena de caracteres
                        $attributes[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8'); // Se codifican los caracteres UTF-8
                    }
                }

                return $attributes; // Se devuelven los atributos transformados
            });

            // Se devuelven los datos transformados y la información de paginación
            return response()->json([
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                ],
            ]);
        }
        // Se captura el error y se devuelve un mensaje de error
        catch (\Exception $e) {
            return response()->json(['error' => 'Error al codificar los datos a JSON: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Función store que permite crear un nuevo registro en la tabla invi_responsable
     * Esta función recibe los datos del registro en formato JSON y los almacena en la tabla
     * La función devuelve un array de objetos que representan los resultados de la operación
     * Se valida que el registro no exista ya creado para el integrante
     *
     * @return array
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $existeActivo = Invi_responsable::where('ciinfper_doc', $request->ciinfper_doc) // Se verifican si el registro ya existe
                ->where('estado_responsable', 1) // Se filtran los registros que estén activos
                ->exists(); // Se verifican si el registro existe
            if ($existeActivo) { // Si el registro ya existe
                // Se devuelven un error 422 con un mensaje de error
                return response()->json([
                    'message' => 'Ya existe un responsable activo para este integrante.',
                ], 422);
            }
            $inputs = $request->all(); // Se obtienen los datos del registro
            $res = Invi_responsable::create($inputs); // Se crea el registro
            DB::commit();
                // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de un Responsable para el departamento - INVESTIGACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de un Responsable con ID: {$res->id_responsable} y ci: {$res->ciinfper_doc}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            // Se devuelven los datos del registro creado
            return response()->json([
                'message' => 'Registro creado exitosamente',
                'data' => $res,
            ], 201);
        } catch (\Exception $e) {
            // Se captura el error y se devuelve un mensaje de error
            return response()->json(['error' => 'Error al codificar los datos a JSON: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Función para actualizar un registro de Responsable
     * Esta función recibe un Request con los datos del registro a actualizar
     * y el ID del registro a actualizar
     * La función devuelve un array de objetos que representan los resultados de la operación
     * Se valida que el registro exista y que el estado del registro sea 1
     * Si el estado es 1, se valida que no exista otro registro activo con el mismo cédula
     *
     * @return array
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            // 1. Obtener el registro a actualizar
            $res = Invi_responsable::find($id);

            if (! isset($res)) {
                return response()->json([
                    'mensaje' => "El Responsable con id: $id no Existe",
                ], 404); // Retornamos un 404 real si no se encuentra
            }

            $ciNuevo = $request->input('ciinfper_doc', $res->ciinfper_doc);
            $estadoNuevo = $request->input('estado_responsable', $res->estado_responsable);

            // 2. Validación de regla de negocio
            if ($estadoNuevo == 1) {
                $existeOtroActivo = Invi_responsable::where('ciinfper_doc', $ciNuevo)
                    ->where('estado_responsable', 1)
                    ->where('id_responsable', '!=', $id) // Excluimos el registro actual
                    ->exists();

                if ($existeOtroActivo) {
                    return response()->json([
                        'mensaje' => 'No se puede actualizar: El usuario ya posee otro registro diferente que está activo.',
                    ], 422);
                }
            }

            // fill() toma los datos del request (incluyendo evidencia_arch) y los monta sobre el modelo
            $res->fill($request->all());

            if ($res->save()) {
                DB::commit();
                    // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                try {
                    $user = Auth::user(); // Obtenemos el usuario autenticado
                    Bitacora::create([
                        'bt_usuario' => $user->NombUsu,
                        'bt_fechahora' => Carbon::now(),
                        'bt_accion' => 'Actualización de un Responsable para el departamento - INVESTIGACIÓN',
                        'bt_ippc' => $request->ip(),
                        'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de un Responsable con ID: {$res->id_responsable} y ci: {$res->ciinfper_doc}",
                    ]);
                } catch (\Exception $ex) {
                    Log::error('Error bitácora en Actualizar: ' . $ex->getMessage());
                }
                return response()->json([
                    'mensaje' => 'Actualizado con Éxito!!',
                    'data' => $res,
                ], 200);
            } else {
                return response()->json([
                    'mensaje' => 'Error al Guardar los cambios en la base de datos',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error interno al actualizar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Función para ihabilitar un registro de Responsable
     * Esta función recibe un string con el ID del registro a ihabilitar
     * La función devuelve un array de objetos que representan los resultados de la operación
     * Se valida que el registro exista y que el estado del registro sea 1
     * Si el estado es 1, se valida que no exista otro registro activo con el mismo cédula
     *
     * @return array
     */
    public function destroy(Request $request, string $id)
    {
        DB::beginTransaction();
        try{

            $res = Invi_responsable::find($id); // Obtenemos el registro a inhabilitar
            if (isset($res)) { // Si el registro existe
                $res->estado_responsable = 0; // Se inhabilita el registro
                $res->save(); // Se guarda el registro
                $data = $res->toArray(); // Se obtiene los datos del registro
                if ($data) { // Si los datos del registro se obtienen correctamente
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Inhabilitación de un Responsable para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Inhabilitación de un Responsable con ID: {$res->id_responsable} y ci: {$res->ciinfper_doc}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Inhabilitar: ' . $ex->getMessage());
                    }
                    // Se devuelve un array con el registro inhabilitado
                    return response()->json([
                        'data' => $data,
                        'mensaje' => 'Inhabilitado con Éxito!!',
                    ]);
                } else {
                    // Se devuelve un array con el mensaje de error
                    return response()->json([
                        'data' => $data,
                        'mensaje' => 'El Responsable no existe (puede que ya la haya eliminado)',
                    ]);
                }
            } else {
                // Se devuelve un array con el mensaje de error
                return response()->json([
                    'error' => true,
                    'mensaje' => "El Responsable con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Función para habilitar un registro de Responsable
     * Esta función recibe un string con el ID del registro a habilitar
     * La función devuelve un array de objetos que representan los resultados de la operación
     * Se valida que el registro exista y que el estado del registro sea 0
     * Si el estado es 0, se valida que no exista otro registro activo con el mismo cédula
     *
     * @return array
     */
    public function habilitar(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $res = Invi_responsable::find($id); // Obtenemos el registro a habilitar

            // 1. Validar que el registro exista en la BD
            if (! isset($res)) {
                return response()->json([
                    'mensaje' => "El Responsable con id: $id no Existe",
                ], 404);
            }

            // 2. 🔥 NUEVA VALIDACIÓN: Verificar si la facultad ya tiene un responsable activo
            $existeFacultadActiva = Invi_responsable::where('idfacultad', $res->idfacultad)
                ->where('estado_responsable', 1)
                ->where('id_responsable', '!=', $id) // Excluimos el ID actual por seguridad
                ->exists();

            if ($existeFacultadActiva) {
                return response()->json([
                    'mensaje' => 'No se puede habilitar porque ya existe un responsable activo con la misma facultad. Primero debe inhabilitar al anterior.',
                ], 422); // Código 422 para que Vue lo capture directo en el catch
            }

            // 3. Proceder con la habilitación si superó la validación
            $res->estado_responsable = 1;

            if ($res->save()) {
                DB::commit();
                    // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                try {
                    $user = Auth::user(); // Obtenemos el usuario autenticado
                    Bitacora::create([
                        'bt_usuario' => $user->NombUsu,
                        'bt_fechahora' => Carbon::now(),
                        'bt_accion' => 'Habilitación de un Responsable para el departamento - INVESTIGACIÓN',
                        'bt_ippc' => $request->ip(),
                        'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Habilitación de un Responsable con ID: {$res->id_responsable} y ci: {$res->ciinfper_doc}",
                    ]);
                } catch (\Exception $ex) {
                    Log::error('Error bitácora en Habilitar: ' . $ex->getMessage());
                }
                return response()->json([
                    'mensaje' => 'Habilitado con Éxito!!',
                    'data' => $res->toArray(),
                ], 200);
            } else {
                return response()->json([
                    'mensaje' => 'Error al guardar los cambios en la base de datos',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error interno al habilitar el responsable: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Función para subir un archivo
     * Esta función recibe un Request con el archivo a subir
     * La función devuelve un array de objetos que representan los resultados de la operación
     * Se valida que el archivo exista y que el tamaño del archivo sea menor que 10MB
     * Si el archivo es válido, se guarda el archivo en la carpeta Documentos/INVESTIGACIÓN/AnexoResponsable
     * Se devuelve un array con el mensaje de éxito
     *
     * @return array
     */
    public function uploadArchivo(Request $request)
    {
        if ($request->hasFile('file')) { // Si el archivo existe
            Log::info('Archivo detectado: ' . $request->file('file')->getClientOriginalName()); // Se registra el nombre del archivo detectado
            Log::info('Error de subida PHP: ' . $request->file('file')->getError()); // Se registra el error de subida PHP
            Log::info('Tamaño recibido: ' . $request->file('file')->getSize()); // Se registra el tamaño del archivo recibido
        } else {
            // Se registra un error de subida si no se detectó ningún archivo en la petición
            Log::warning('No se detectó ningún archivo en la petición.');
        }
        $request->validate([
            'file' => 'required|max:10240', // 10MB
            'ci' => 'required|alpha_dash',
            'old_filename' => 'nullable|string',
        ]); // Se validan los datos de la petición

        try {
            $ci = basename($request->ci); // Se obtiene el cédula del archivo
            $file = $request->file('file'); // Se obtiene el archivo subido
            if (! $file->isValid()) { // Si el archivo no es válido
                throw new \Exception('Archivo inválido o corrupto.'); // Se lanza un error
            }
            if ($request->filled('old_filename')) { // Si se ha proporcionado un nombre de archivo antiguo
                $oldFilename = basename($request->old_filename); // Seguridad extra
                $oldPath = public_path("Documentos/INVESTIGACION/AnexoResponsable/{$ci}/{$oldFilename}"); // Se obtiene la ruta del archivo antiguo
                if (File::exists($oldPath)) { // Si el archivo antiguo existe
                    File::delete($oldPath); // Se elimina el archivo antiguo
                }
            }

            // Crear carpeta publica si no existe
            $directory = public_path("Documentos/INVESTIGACION/AnexoResponsable/{$ci}");

            if (! File::isDirectory($directory)) { // Si la carpeta no existe
                File::makeDirectory($directory, 0755, true, true); // Se crea la carpeta
            }

            // Generar nombre: CI + _ + aleatorio + _ + fecha (Ymd_His)
            $aleatorio = bin2hex(random_bytes(8)); // 16 caracteres hex
            $fechaHora = date('Ymd_His');          // Ej: 20251112_1741
            $extension = $file->getClientOriginalExtension(); // pdf

            $filename = "{$ci}_{$aleatorio}_{$fechaHora}.{$extension}"; // Se genera el nombre final del archivo

            // Guardar archivo
            $file->move($directory, $filename);

            // URL pública, para acceder al archivo desde fuera de la aplicación
            $url = url('Documentos/INVESTIGACION/AnexoResponsable/' . $ci . '/' . $filename);

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

    public function getFacultadesInvestigacion(Request $request)
    {
        try {
            // id_excluir representará el 'id_responsable' que se está editando actualmente
            $idExcluir = $request->input('id_excluir');
            $sedes_atraer = ['1', '2'];

            // 1. Obtener todas las facultades
            $facultades = Facultad::whereIn('idsede', $sedes_atraer)
                ->where('idfacultad', '!=', '6')
                ->whereNotNull('decano')
                ->get();

            // 2. Consultar quiénes ya tienen una facultad asignada de forma activa en INVESTIGACIÓN
            $queryAsignados = Invi_responsable::where('estado_responsable', 1)
                ->where('tipo_responsable', 'INVESTIGACIÓN');

            // Si estamos editando, excluimos este registro para que su propia facultad no aparezca como "Ocupada"
            if ($idExcluir) {
                $queryAsignados->where('id_responsable', '!=', $idExcluir);
            }

            // Creamos un mapa de formato [idfacultad => ciinfper_doc]
            $asignados = $queryAsignados->pluck('ciinfper_doc', 'idfacultad')->toArray();

            // 3. Transformar la colección adaptando los campos al HTML de Vue y asegurando UTF-8
            $resultado = $facultades->map(function ($facultad) use ($asignados) {
                $attributes = $facultad->getAttributes();

                // Asegurar codificación UTF-8 para las siglas y nombres de la facultad
                foreach ($attributes as $key => $value) {
                    if (is_string($value)) {
                        $attributes[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                }

                // Inyección de propiedades de asignación para el Frontend
                $attributes['is_assigned'] = isset($asignados[$facultad->idfacultad]);
                $attributes['asignado_a'] = $asignados[$facultad->idfacultad] ?? null;

                // TRUCO DE COMPATIBILIDAD: Mapeamos idfacultad al nombre que espera tu v-model/v-for
                $attributes['id_equipo_roles'] = $facultad->idfacultad;

                return $attributes;
            });

            return response()->json(['data' => $resultado]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener facultades: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Función para obtener información de los responsables de INVESTIGACIÓN
     * Esta función recibe no recibbe ningún parámetro
     * La función devuelve un array de objetos que representan los resultados de la búsqueda
     * Se traen a todos los responables activos que sean del tipo INVESTIGACIÓN y su facultad
     *
     * 
     */
    public function getResponsablesInfo(): JsonResponse
    {
        try {
            // 1. Buscamos a los responsables activos que sean del tipo INVESTIGACIÓN
            $responsables = Invi_responsable::with([
                'informacionpersonald.titulos.nivel',
                'facultad',
            ])
                ->where('tipo_responsable', 'INVESTIGACIÓN')
                ->where('estado_responsable', 1)
                ->get();

            $resultado = [];

            foreach ($responsables as $responsable) {
                $persona = $responsable->informacionpersonald;
                $facultad = $responsable->facultad;

                // Si falta la información personal básica, saltamos al siguiente registro
                if (! $persona) {
                    continue;
                }

                // 2. Lógica de Género orientada al cargo de Responsable
                $esMujer = strtoupper($persona->GeneroPer) === 'F';

                $labels = [
                    'pronombre' => $esMujer ? 'Ella' : 'Él',
                    'articulo' => $esMujer ? 'la' : 'el',
                    'titulo_rol' => $esMujer ? 'Responsable de INVESTIGACIÓN' : 'Responsable de INVESTIGACIÓN',
                ];

                // 3. Procesar títulos académicos filtrando por nv_numnivel
                $tituloGrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
                });

                $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
                });

                $prefijoNombre = '';
                $sufijoNombre = '';

                // A. Mapeo ampliado de PREFIJOS (3er Nivel)
                if ($tituloGrado) {
                    $textoGrado = mb_strtolower($tituloGrado->ad_titulo);

                    if (str_contains($textoGrado, 'licencia') || str_contains($textoGrado, 'lcda.')) {
                        $prefijoNombre = $esMujer ? 'Lcda.' : 'Lcdo.';
                    } elseif (str_contains($textoGrado, 'ingenier') || str_contains($textoGrado, 'ing.')) {
                        $prefijoNombre = $esMujer ? 'Ing.' : 'Ing.';
                    } elseif (str_contains($textoGrado, 'econom') || str_contains($textoGrado, 'econ.')) {
                        $prefijoNombre = $esMujer ? 'Econ.' : 'Econ.';
                    } elseif (str_contains($textoGrado, 'abogad') || str_contains($textoGrado, 'abg.')) {
                        $prefijoNombre = $esMujer ? 'Abg.' : 'Abg.';
                    } elseif (str_contains($textoGrado, 'arquitect') || str_contains($textoGrado, 'arq.')) {
                        $prefijoNombre = 'Arq.';
                    } else {
                        $prefijoNombre = 'Prof.';
                    }
                } else {
                    $prefijoNombre = $esMujer ? 'Sra.' : 'Sr.';
                }

                // B. Definir el SUFIJO (4to Nivel)
                if ($tituloPosgrado) {
                    $textoPosgrado = mb_strtolower($tituloPosgrado->ad_titulo);

                    if (str_contains($textoPosgrado, 'phd') || str_contains($textoPosgrado, 'doctorado') || str_contains($textoPosgrado, 'doctor')) {
                        $sufijoNombre = ', PhD';
                    } elseif (str_contains($textoPosgrado, 'msc') || str_contains($textoPosgrado, 'science') || str_contains($textoPosgrado, 'ciencias')) {
                        $sufijoNombre = ', MSc.';
                    } else {
                        $sufijoNombre = ', Mgtr.';
                    }
                }

                // Construcción del nombre formateado con sus títulos
                $nombreCompletoConTitulo = "{$prefijoNombre} {$persona->NombInfPer} {$persona->ApellInfPer}{$sufijoNombre}";

                // 4. Estructura del objeto final adaptado a la Facultad
                $resultado[] = [
                    'id_responsable' => $responsable->id_responsable,
                    'ci' => $persona->CIInfPer,
                    'nombre_completo' => $nombreCompletoConTitulo,
                    'email' => $persona->mailInst ?? 'vinculacion@utelvt.edu.ec',
                    'telefono' => $persona->Telf1InfPer ?? $persona->CelularInfPer ?? 'S/N',
                    'evidencia_arch' => $responsable->evidencia_arch,
                    'genero_labels' => $labels,
                    'titulo_grado' => $tituloGrado ? $tituloGrado->ad_titulo : null,
                    'titulo_posgrado' => $tituloPosgrado ? $tituloPosgrado->ad_titulo : null,

                    // Información detallada de la facultad asignada
                    'facultad' => $facultad ? [
                        'idfacultad' => $facultad->idfacultad,
                        'nombre' => mb_convert_encoding($facultad->facultad, 'UTF-8', 'UTF-8'),
                        'siglas' => mb_convert_encoding($facultad->siglas, 'UTF-8', 'UTF-8'),
                        'decano' => mb_convert_encoding($facultad->decano, 'UTF-8', 'UTF-8'),
                        'secretario' => mb_convert_encoding($facultad->secretario, 'UTF-8', 'UTF-8'),
                    ] : null,
                ];
            }

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar la solicitud de responsables',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function getResponsablesIndInfo(string $id): JsonResponse
    {
        try {
            // 1. Buscamos a los responsables activos que sean del tipo INVESTIGACIÓN
            $responsables = Invi_responsable::with([
                'informacionpersonald.titulos.nivel',
                'facultad',
            ])
                ->where('tipo_responsable', 'INVESTIGACIÓN')
                ->where('estado_responsable', 1)
                ->where('idfacultad', $id)
                ->get();

            $resultado = [];

            foreach ($responsables as $responsable) {
                $persona = $responsable->informacionpersonald;
                $facultad = $responsable->facultad;

                // Si falta la información personal básica, saltamos al siguiente registro
                if (! $persona) {
                    continue;
                }

                // 2. Lógica de Género orientada al cargo de Responsable
                $esMujer = strtoupper($persona->GeneroPer) === 'F';

                $labels = [
                    'pronombre' => $esMujer ? 'Ella' : 'Él',
                    'articulo' => $esMujer ? 'la' : 'el',
                    'titulo_rol' => $esMujer ? 'Responsable de INVESTIGACIÓN' : 'Responsable de INVESTIGACIÓN',
                ];

                // 3. Procesar títulos académicos filtrando por nv_numnivel
                $tituloGrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
                });

                $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
                });

                $prefijoNombre = '';
                $sufijoNombre = '';

                // A. Mapeo ampliado de PREFIJOS (3er Nivel)
                if ($tituloGrado) {
                    $textoGrado = mb_strtolower($tituloGrado->ad_titulo);

                    if (str_contains($textoGrado, 'licencia') || str_contains($textoGrado, 'lcda.')) {
                        $prefijoNombre = $esMujer ? 'Lcda.' : 'Lcdo.';
                    } elseif (str_contains($textoGrado, 'ingenier') || str_contains($textoGrado, 'ing.')) {
                        $prefijoNombre = $esMujer ? 'Ing.' : 'Ing.';
                    } elseif (str_contains($textoGrado, 'econom') || str_contains($textoGrado, 'econ.')) {
                        $prefijoNombre = $esMujer ? 'Econ.' : 'Econ.';
                    } elseif (str_contains($textoGrado, 'abogad') || str_contains($textoGrado, 'abg.')) {
                        $prefijoNombre = $esMujer ? 'Abg.' : 'Abg.';
                    } elseif (str_contains($textoGrado, 'arquitect') || str_contains($textoGrado, 'arq.')) {
                        $prefijoNombre = 'Arq.';
                    } else {
                        $prefijoNombre = 'Prof.';
                    }
                } else {
                    $prefijoNombre = $esMujer ? 'Sra.' : 'Sr.';
                }

                // B. Definir el SUFIJO (4to Nivel)
                if ($tituloPosgrado) {
                    $textoPosgrado = mb_strtolower($tituloPosgrado->ad_titulo);

                    if (str_contains($textoPosgrado, 'phd') || str_contains($textoPosgrado, 'doctorado') || str_contains($textoPosgrado, 'doctor')) {
                        $sufijoNombre = ', PhD';
                    } elseif (str_contains($textoPosgrado, 'msc') || str_contains($textoPosgrado, 'science') || str_contains($textoPosgrado, 'ciencias')) {
                        $sufijoNombre = ', MSc.';
                    } else {
                        $sufijoNombre = ', Mgtr.';
                    }
                }

                // Construcción del nombre formateado con sus títulos
                $nombreCompletoConTitulo = "{$prefijoNombre} {$persona->NombInfPer} {$persona->ApellInfPer}{$sufijoNombre}";

                // 4. Estructura del objeto final adaptado a la Facultad
                $resultado[] = [
                    'id_responsable' => $responsable->id_responsable,
                    'ci' => $persona->CIInfPer,
                    'nombre_completo' => $nombreCompletoConTitulo,
                    'email' => $persona->mailInst ?? 'vinculacion@utelvt.edu.ec',
                    'telefono' => $persona->Telf1InfPer ?? $persona->CelularInfPer ?? 'S/N',
                    'evidencia_arch' => $responsable->evidencia_arch,
                    'genero_labels' => $labels,
                    'titulo_grado' => $tituloGrado ? $tituloGrado->ad_titulo : null,
                    'titulo_posgrado' => $tituloPosgrado ? $tituloPosgrado->ad_titulo : null,

                    // Información detallada de la facultad asignada
                    'facultad' => $facultad ? [
                        'idfacultad' => $facultad->idfacultad,
                        'nombre' => mb_convert_encoding($facultad->facultad, 'UTF-8', 'UTF-8'),
                        'siglas' => mb_convert_encoding($facultad->siglas, 'UTF-8', 'UTF-8'),
                        'decano' => mb_convert_encoding($facultad->decano, 'UTF-8', 'UTF-8'),
                        'secretario' => mb_convert_encoding($facultad->secretario, 'UTF-8', 'UTF-8'),
                    ] : null,
                ];
            }

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar la solicitud de responsables',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
