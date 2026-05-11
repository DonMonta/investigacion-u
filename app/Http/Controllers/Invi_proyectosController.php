<?php

namespace App\Http\Controllers;

use App\Models\Invi_proyectos;
use App\Models\Invi_deta_inte;
use App\Models\InformacionPersonalD;
use App\Models\informacionpersonal;
use App\Models\Carreras;
use App\Models\Invi_funcion;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Invi_proyectosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try { // 1. Obtener parámetros de búsqueda y paginación
            $searchQuery = $request->input('search_query');
            $query = Invi_proyectos::select(
                'invi_proyectos.*'
            )->where('proyect_tipo', '=', 'INVESTIGACIÓN');
            if (! empty($searchQuery)) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('invi_proyectos.proyect_cod', 'LIKE', "%{$searchQuery}%");
                });
            }

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

            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function getStats()
    {
        try {
            // 1. Total de proyectos de INVESTIGACIÓN
            $totalProyectos = Invi_proyectos::where('proyect_tipo', 'INVESTIGACIÓN')->count();

            // Query base para integrantes activos en proyectos de INVESTIGACIÓN
            $baseIntegrantes = Invi_deta_inte::where('reemplazado', 0)
                ->whereHas('invi_proyectos', function ($query) {
                    $query->where('proyect_tipo', 'INVESTIGACIÓN');
                });

            // 2. Contamos por nombre de función (usando tu lógica semántica)
            $statsIntegrantes = $baseIntegrantes->with('funciones')
                ->get()
                ->groupBy(function ($item) {
                    $nombre = strtoupper($item->funciones->nombre_funcion ?? '');
                    if (str_contains($nombre, 'DIRECTOR') && !str_contains($nombre, 'SUB')) return 'director';
                    if (str_contains($nombre, 'SUBDIRECTOR')) return 'subdirector';
                    if (str_contains($nombre, 'INVESTIGADOR')||str_contains($nombre, 'AYUDANTE')||str_contains($nombre, 'TÉCNICO')) return 'docente';
                    return 'otros';
                });

            return response()->json([
                'status' => true,
                'stats' => [
                    'total_proyectos' => $totalProyectos,
                    'total_directores' => $statsIntegrantes->get('director', collect())->count(),
                    'total_subdirectores' => $statsIntegrantes->get('subdirector', collect())->count(),
                    'total_docentes' => $statsIntegrantes->get('docente', collect())->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $inputs = $request->input();

        $res = Invi_proyectos::create($inputs);

        return response()->json([
            'data' => $res,
            'mensaje' => 'Agregado con Éxito!!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $proyecto = Invi_proyectos::with([
            'invi_detalle_fac_proy.facultades',
            'invi_detalle_fac_proy.facultades_priori',
            'invi_detalle_integrante.funciones',
            'invi_detalle_integrante.carreras',
            'invi_detalle_integrante.informacionPersonalD',
            'invi_detalle_integrante.informacionpersonal'
        ])->findOrFail($id);

        // 1. Extraer la facultad prioritaria
        $facultadPrincipal = $proyecto->invi_detalle_fac_proy
            ->whereNotNull('id_facultad_priori')
            ->first()?->facultades_priori;

        // 2. Extraer todas las facultades participantes
        $facultadesParticipantes = $proyecto->invi_detalle_fac_proy
            ->map(fn($detalle) => $detalle->facultades)
            ->filter()
            ->unique('idfacultad')
            ->values();

        // 3. Ordenar integrantes por nombre de la función
        $integrantesOrdenados = $proyecto->invi_detalle_integrante->sortBy(function ($integrante) {
            // Obtenemos el nombre de la función en mayúsculas para evitar problemas de case-sensitivity
            $nombreFuncion = strtoupper($integrante->funciones?->nombre_funcion ?? '');

            // Retornamos un peso numérico basado en el texto
            return match (true) {
                str_contains($nombreFuncion, 'DIRECTOR') && !str_contains($nombreFuncion, 'SUB') => 10,
                str_contains($nombreFuncion, 'SUBDIRECTOR') => 20,
                str_contains($nombreFuncion, 'INVESTIGADOR') => 30,
                str_contains($nombreFuncion, 'AYUDANTE') => 40,
                str_contains($nombreFuncion, 'TÉCNICO') => 50,
                str_contains($nombreFuncion, 'ESTUDIANTE') => 100,
                empty($nombreFuncion) => 999, // Integrantes sin función (reemplazados)
                default => 50, // Cualquier otra función intermedia
            };
        })->values();

        // 4. Formatear la respuesta JSON
        return response()->json([
            'proyect_id'      => $proyecto->proyect_id,
            'proyect_nombre'  => $proyecto->proyect_nombre,
            'proyect_titulo'  => $proyecto->proyect_titulo,
            'fechainicio'     => $proyecto->fechainicio,
            'fechafin'        => $proyecto->fechafin,
            'facultades_priori' => $facultadPrincipal,
            'facultades'      => $facultadesParticipantes,
            'invi_detalle_integrante' => $integrantesOrdenados
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $res = Invi_proyectos::find($id);
        if (isset($res)) {
            $res->proyect_cod = $request->proyect_cod;
            $res->proyect_nombre = $request->proyect_nombre;
            $res->proyect_titulo = $request->proyect_titulo;
            $res->fechainicio = $request->fechainicio;
            $res->fechafin = $request->fechafin;
            $res->proyect_tipo = $request->proyect_tipo;
            if ($res->save()) {
                return response()->json([
                    'data' => $res,
                    'mensaje' => 'Actualizado con Éxito!!',
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Error al Actualizar',
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "El proyecto con id: $id no Existe",
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function buscarIntegrante(Request $request)
    {
        $cedula = $request->cedula;
        $proyect_id_actual = $request->proyect_id;
        $es_reemplazo = $request->es_reemplazo;

        // 1. Buscar Datos Personales
        $docente = InformacionPersonalD::where('CIInfPer', $cedula)->first();
        $estudiante = informacionpersonal::where('CIInfPer', $cedula)->first();

        if (!$docente && !$estudiante) {
            return response()->json(['message' => 'Integrante no encontrado en la base de datos institucional.'], 404);
        }

        $persona = $docente ?: $estudiante;
        $tipo = $docente ? 'doc' : 'est';

        // 2. Obtener TODOS los proyectos de INVESTIGACIÓN donde está activo
        $proyectosActivos = Invi_deta_inte::where(function ($q) use ($cedula) {
            $q->where('ciinfper_doc', $cedula)->orWhere('ciinfper_est', $cedula);
        })
            ->where('reemplazado', 0)
            ->where('estado', 1) // Aseguramos que el registro esté vigente
            ->whereHas('invi_proyectos', function ($query) {
                $query->where('proyect_tipo', 'INVESTIGACIÓN');
            })
            ->with('invi_proyectos')
            ->get();

        $conteoProyectos = $proyectosActivos->count();
        $estaEnProyectoActual = $proyectosActivos->contains('proyect_id', $proyect_id_actual);
        $listaNombres = $proyectosActivos->pluck('invi_proyectos.proyect_nombre')->toArray();

        // --- LÓGICA DE VALIDACIÓN BASADA EN EL PUNTO 5 ---

        // CASO ESPECIAL: Ya está en el MISMO proyecto
        if ($estaEnProyectoActual && !$es_reemplazo) {
            return response()->json([
                'message' => "Este integrante ya forma parte de los miembros activos de este proyecto."
            ], 422);
        }

        // REGLA: Máximo 3 proyectos
        if ($conteoProyectos >= 3 && !$estaEnProyectoActual) {
            return response()->json([
                'message' => "El integrante ya alcanzó el límite máximo permitido. Actualmente participa en 3 proyectos: " . implode(', ', $listaNombres),
                'proyectos' => $listaNombres
            ], 422);
        }

        // ADVERTENCIA: Si está en 1 o 2 proyectos (y no es el actual)
        $advertencia = null;
        if ($conteoProyectos > 0 && !$estaEnProyectoActual) {
            $num = $conteoProyectos == 1 ? "un proyecto" : "dos proyectos";
            $advertencia = "Nota: El integrante ya pertenece a {$num}. Según el Punto 5 de la covocatoria de proyectos de Investigación: 'Se podrá participar en un máximo de tres proyectos de investigación', el usuario aún puede ser añadido a este nuevo proyecto.";
        }

        return response()->json([
            'cedula' => $persona->CIInfPer,
            'nombre_completo' => "{$persona->NombInfPer} {$persona->ApellInfPer} {$persona->ApellMatInfPer}",
            'tipo' => $tipo,
            'advertencia' => $advertencia,
            'proyectos_actuales' => $listaNombres,
            'conteo' => $conteoProyectos
        ]);
    }
    public function guardarCambios(Request $request)
    {
        try {
            DB::beginTransaction();

            $modo = $request->modo;
            $form = $request->form;
            $reemplazoConfig = $request->reemplazo_config;
            $proyect_id = $request->proyect_id;
            // --- OBTENER CÓDIGO DEL PROYECTO ---
            $proyecto = Invi_proyectos::find($proyect_id);
            $codigoProyect = $proyecto?->proyect_cod ?? 'S/N';
            // Buscamos la función que se intenta asignar
            $funcionSolicitada = Invi_funcion::find($form['id_funcion']);
            $nombreUpper = strtoupper($funcionSolicitada?->nombre_funcion ?? '');

            // Verificamos si es Director o Subdirector por texto
            $esDirectivo = str_contains($nombreUpper, 'DIRECTOR');
            if ($modo === 'nuevo') {
                $cedula = $form['cedula_nueva'];
                $existe = Invi_deta_inte::where('proyect_id', $proyect_id)
                    ->where(function ($q) use ($cedula) {
                        $q->where('ciinfper_doc', $cedula)->orWhere('ciinfper_est', $cedula);
                    })
                    ->where('estado', 1)
                    ->exists();

                if ($existe) {
                    DB::rollBack();
                    return response()->json(['message' => "Esta persona ya figura como integrante activo en este proyecto."], 422);
                }
            }
            if ($modo === 'nuevo') {
                // Validar que no se agregue Director/Subdirector si ya existen
                if ($esDirectivo) {
                    $existe = Invi_deta_inte::where('proyect_id', $request->proyect_id)
                        ->where('id_funcion', $form['id_funcion'])
                        ->where('reemplazado', 0)
                        ->where('estado', 1)
                        ->exists();
                    if ($existe) return response()->json(['message' => "Ya existe un {$funcionSolicitada->nombre_funcion} activo."], 422);
                }

                Invi_deta_inte::create([
                    'proyect_id'    => $proyect_id,
                    'ciinfper_doc'  => $form['tipo_nuevo'] == 'doc' ? $form['cedula_nueva'] : null,
                    'ciinfper_est'  => $form['tipo_nuevo'] == 'est' ? $form['cedula_nueva'] : null,
                    'horas'         => $form['horas'],
                    'reemplazado'   => 0,
                    'id_funcion'    => $form['id_funcion'],
                    'idCarr'        => $form['idCarr'],
                    'anexo_integrante2' => $form['anexo_integrante2'],
                    'estado' => 1
                ]);
                $accionBitacora = "REGISTRO DE NUEVO INTEGRANTE INVESTIGACIÓN";
                $obsBitacora = "Se agregó a la cédula {$form['cedula_nueva']} al proyecto: {$codigoProyect} con función {$nombreUpper}";
            } else {
                // MODO EDICIÓN
                $registroOriginal = Invi_deta_inte::findOrFail($request->id_deta_invi_proyect);

                if ($form['reemplazado'] == 1) {
                    // 1. Procesar al que SALE (Registro Original)
                    if ($reemplazoConfig['mantener_docente']) {
                        $registroOriginal->update([
                            'reemplazado' => 1,
                            'id_funcion'  => $reemplazoConfig['nueva_funcion_reemplazado'],
                            'horas'       => $reemplazoConfig['nuevas_horas_reemplazado'] ?? 0,
                            'estado' => 1
                        ]);
                    } else {
                        $registroOriginal->update([
                            'reemplazado' => 1,
                            'id_funcion'  => null,
                            'horas'       => 0,
                            'estado' => 0
                        ]);
                    }

                    // 2. Procesar al que ENTRA (El reemplazo)
                    $cedulaNueva = $form['cedula_nueva'];

                    // BUSCAMOS si esta persona ya estaba en el proyecto (aunque sea con otro rol)
                    $integranteExistente = Invi_deta_inte::where('proyect_id', $proyect_id)
                        ->where(function ($q) use ($cedulaNueva) {
                            $q->where('ciinfper_doc', $cedulaNueva)
                                ->orWhere('ciinfper_est', $cedulaNueva);
                        })
                        ->first();

                    $datosNuevoRol = [
                        'proyect_id'    => $proyect_id,
                        'ciinfper_doc'  => $form['tipo_nuevo'] == 'doc' ? $cedulaNueva : null,
                        'ciinfper_est'  => $form['tipo_nuevo'] == 'est' ? $cedulaNueva : null,
                        'horas'         => $form['horas'],
                        'reemplazado'   => 0, // El nuevo rol siempre entra como activo
                        'id_funcion'    => $form['id_funcion'],
                        'idCarr'        => $form['idCarr'],
                        'anexo_integrante' => $form['anexo_integrante'],
                        'estado' => 1
                    ];

                    if ($integranteExistente) {
                        // SI YA EXISTÍA: Lo actualizamos en lugar de crear uno nuevo
                        $integranteExistente->update($datosNuevoRol);
                        $accionBitacora = "REEMPLAZO DE INTEGRANTE EXISTENTE INVESTIGACIÓN";
                        $obsBitacora = "Reemplazo en proyecto: {$codigoProyect}. Reemplazo del integrante ID: {$request->id_deta_invi_proyect}, por un docente del mismo proyecto con cédula: {$form['cedula_nueva']}";
                    } else {
                        // SI NO EXISTÍA: Lo creamos
                        Invi_deta_inte::create($datosNuevoRol);
                        $accionBitacora = "REEMPLAZO DE INTEGRANTE POR UN DOCENTE NUEVO INVESTIGACIÓN";
                        $obsBitacora = "Reemplazo en proyecto: {$codigoProyect}. Reemplazo del integrante ID: {$request->id_deta_invi_proyect}, por un docente nuevo con cédula: {$form['cedula_nueva']}";
                    }
                } else {
                    // Edición simple sin reemplazo
                    $registroOriginal->update([
                        'id_funcion' => $form['id_funcion'],
                        'idCarr'     => $form['idCarr'],
                        'horas'      => $form['horas'],
                        //'anexo_integrante2' => $form['anexo_integrante2'],
                        'reemplazado' => 0,
                        'estado' => 1
                    ]);
                    $accionBitacora = "EDICIÓN DE INTEGRANTE INVESTIGACIÓN";
                    $obsBitacora = "Se editaron datos del integrante ID: {$request->id_deta_invi_proyect} en proyecto: {$codigoProyect}";
                }
            }

            DB::commit();
            // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario'     => $user->ciinfper,
                    'bt_fechahora'   => Carbon::now(),
                    'bt_accion'      => $accionBitacora . " - INVESTIGACIÓN",
                    'bt_ippc'        => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: {$obsBitacora}",
                ]);
            } catch (\Exception $ex) {
                Log::error("Error bitácora en guardarCambios: " . $ex->getMessage());
            }
            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function catalogos()
    {
        $facultad = ['1', '2', '3', '4', '5', '11'];

        return response()->json([
            'funciones' => Invi_funcion::where('estado', 1)
                ->where('tipo_funcion', '=', 'INVESTIGACIÓN')
                ->get(),
            'carreras' => Carreras::where('StatusCarr', '=', 1)
                ->wherein('idfacultad', $facultad)
                ->where('NombCarr', 'NOT LIKE', '%TRABAJO DE INTEGRACIÓN CURRICULAR%')
                ->get(),
        ]);
    }

    public function inhabilitar(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'anexo_integrante' => 'required|string'
        ]);
        $integrante = Invi_deta_inte::findOrFail($request->id);
        // Guardamos los datos necesarios para la bitácora antes de limpiar los campos
        $cedulaAfectada = $integrante->ciinfper_doc ?? $integrante->ciinfper_est;
        $codigoProyect = $integrante->invi_proyectos?->proyect_cod ?? 'S/N';

        $integrante->update([
            'horas' => 0,
            'estado' => 0,
            'id_funcion' => null,
            'anexo_integrante' => $request->anexo_integrante
        ]);
        // --- REGISTRO EN BITÁCORA ---
        try {
            $user = Auth::user();
            Bitacora::create([
                'bt_usuario'     => $user->ciinfper,
                'bt_fechahora'   => Carbon::now(),
                'bt_accion'      => 'INHABILITAR INTEGRANTE - INVESTIGACIÓN',
                'bt_ippc'        => $request->ip(),
                'bt_observacion' => "USUARIO: {$user->NombUsu} INHABILITÓ AL INTEGRANTE CÉDULA: {$cedulaAfectada} DEL PROYECTO: {$codigoProyect}. MOTIVO/ANEXO: {$request->anexo_integrante}",
            ]);
        } catch (\Exception $e) {
            Log::error("Error al registrar bitácora en inhabilitar: " . $e->getMessage());
        }
        // --- FIN REGISTRO EN BITÁCORA ---

        return response()->json(['message' => 'Integrante inhabilitado correctamente']);
    }
    public function uploadArchivo(Request $request)
    {
        if ($request->hasFile('file')) {
            Log::info("Archivo detectado: " . $request->file('file')->getClientOriginalName());
            Log::info("Error de subida PHP: " . $request->file('file')->getError());
            Log::info("Tamaño recibido: " . $request->file('file')->getSize());
        } else {
            Log::warning("No se detectó ningún archivo en la petición.");
        }
        $request->validate([
            'file' => 'required|max:10240', // 10MB
            'ci' => 'required|alpha_dash',
            'old_filename' => 'nullable|string',
        ]);

        try {
            $ci = basename($request->ci);
            $file = $request->file('file');
            if (!$file->isValid()) {
                throw new \Exception("Archivo inválido o corrupto.");
            }
            if ($request->filled('old_filename')) {
                $oldFilename = basename($request->old_filename); // Seguridad extra
                $oldPath = public_path("Documentos/INVESTIGACION/AnexoIntegrante/{$ci}/{$oldFilename}");
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            // Crear carpeta si no existe
            $directory = public_path("Documentos/INVESTIGACION/AnexoIntegrante/{$ci}");

            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            // Generar nombre: CI + _ + aleatorio + _ + fecha (Ymd_His)
            $aleatorio = bin2hex(random_bytes(8)); // 16 caracteres hex
            $fechaHora = date("Ymd_His");          // Ej: 20251112_1741
            $extension = $file->getClientOriginalExtension(); // pdf

            $filename = "{$ci}_{$aleatorio}_{$fechaHora}.{$extension}";

            // Guardar archivo
            $file->move($directory, $filename);

            // URL pública
            $url = url('Documentos/INVESTIGACION/AnexoIntegrante/' . $ci . '/' . $filename);

            return response()->json([
                'status'   => true,
                'filename' => $filename,
                'url'      => $url
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Seguridad: El archivo no pudo ser procesado.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function uploadArchivoDarBaja(Request $request)
    {
        if ($request->hasFile('file')) {
            Log::info("Archivo detectado: " . $request->file('file')->getClientOriginalName());
            Log::info("Error de subida PHP: " . $request->file('file')->getError());
            Log::info("Tamaño recibido: " . $request->file('file')->getSize());
        } else {
            Log::warning("No se detectó ningún archivo en la petición.");
        }
        $request->validate([
            'file' => 'required|max:10240', // 10MB
            'ci' => 'required|alpha_dash',
            'old_filename' => 'nullable|string',
        ]);

        try {
            $ci = basename($request->ci);
            $file = $request->file('file');
            if (!$file->isValid()) {
                throw new \Exception("Archivo inválido o corrupto.");
            }
            if ($request->filled('old_filename')) {
                $oldFilename = basename($request->old_filename); // Seguridad extra
                $oldPath = public_path("Documentos/INVESTIGACION/Bajas_Docentes/Anexo/{$ci}/{$oldFilename}");
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            // Crear carpeta si no existe
            $directory = public_path("Documentos/INVESTIGACION/Bajas_Docentes/Anexo/{$ci}");

            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            // Generar nombre: CI + _ + aleatorio + _ + fecha (Ymd_His)
            $aleatorio = bin2hex(random_bytes(8)); // 16 caracteres hex
            $fechaHora = date("Ymd_His");          // Ej: 20251112_1741
            $extension = $file->getClientOriginalExtension(); // pdf

            $filename = "{$ci}_{$aleatorio}_{$fechaHora}.{$extension}";

            // Guardar archivo
            $file->move($directory, $filename);

            // URL pública
            $url = url('Documentos/INVESTIGACION/Bajas_Docentes/Anexo/' . $ci . '/' . $filename);

            return response()->json([
                'status'   => true,
                'filename' => $filename,
                'url'      => $url
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Seguridad: El archivo no pudo ser procesado.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
