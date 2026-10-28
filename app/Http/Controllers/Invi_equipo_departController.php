<?php

namespace App\Http\Controllers;

use App\Models\Invi_equipo_depart;
use App\Models\Invi_equipo_roles;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; // Importamos la clase Facultad
use Illuminate\Support\Facades\Log; // Importamos la clase JsonResponse
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Invi_equipo_departController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $searchQuery = $request->input('search_query');
            $query = Invi_equipo_depart::select(
                'invi_equipo_depart.*',
                'invi_equipo_roles.*',
                'informacionpersonal_d.NombInfPer',
                'informacionpersonal_d.ApellInfPer',
                'informacionpersonal_d.ApellMatInfPer'
            )
                ->join('invi_equipo_roles', 'invi_equipo_roles.id_equipo_roles', '=', 'invi_equipo_depart.id_equipo_roles')
                ->join('informacionpersonal_d', 'informacionpersonal_d.CIInfPer', '=', 'invi_equipo_depart.ciinfper_doc')
                ->where('tipo_rol', '=', 'INVESTIGACIÓN');

            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('invi_equipo_depart.ciinfper_doc', 'LIKE', '%' . $searchQuery . '%');
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
    public function getEquipoInvestcion()
    {
        // 1. Traer los miembros de INVESTIGACIÓN con sus relaciones
        $miembros = Invi_equipo_depart::with(['equipo_roles', 'informacionpersonald'])
            ->where('estado_equipo_dep', 1)
            ->whereHas('equipo_roles', function ($query) {
                $query->where('tipo_rol', 'INVESTIGACIÓN')
                    ->where('estado_rol', 1);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'ci' => $item->ciinfper_doc,
                    'nombre' => $item->informacionpersonald->NombInfPer . ' ' . $item->informacionpersonald->ApellInfPer . ' ' . $item->informacionpersonald->ApellMatInfPer,
                    'rol' => $item->equipo_roles->nombre_rol,
                    'detalle' => $item->equipo_roles->detalle_rol,
                    'funciones' => $item->equipo_roles->funciones_rol
                ];
            })->toArray();

        if (count($miembros) === 0) {
            return response()->json([]);
        }

        // 2. Lógica para poner al Director en el centro exacto
        $directorIndex = -1;
        foreach ($miembros as $index => $miembro) {
            // Buscamos la palabra "director" en el nombre del rol
            if (stripos($miembro['rol'], 'director') !== false) {
                $directorIndex = $index;
                break;
            }
        }

        if ($directorIndex !== -1) {
            // Extraemos al director del grupo
            $director = array_splice($miembros, $directorIndex, 1)[0];

            // Calculamos la mitad del array restante
            $mitad = floor(count($miembros) / 2);

            // Insertamos al director en el centro
            array_splice($miembros, $mitad, 0, [$director]);
        }

        return response()->json($miembros);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{

            $request->validate([
                'ciinfper_doc' => 'required|digits:10',
                'id_equipo_roles' => 'required|integer',
            ]);
    
            try {
                // Validamos por BD que no le estén inyectando un rol ocupado por un activo distinto a él
                $existeOcupado = Invi_equipo_depart::where('id_equipo_roles', $request->id_equipo_roles)
                    ->where('estado_equipo_dep', 1)
                    ->where('ciinfper_doc', '!=', $request->ciinfper_doc)
                    ->first();
    
                if ($existeOcupado) {
                    return response()->json(['mensaje' => 'Este rol ya se encuentra asignado a otra persona activa.'], 422);
                }
    
                Invi_equipo_depart::create([
                    'ciinfper_doc' => $request->ciinfper_doc,
                    'id_equipo_roles' => $request->id_equipo_roles,
                    'estado_equipo_dep' => 1,
                    'evidencia_arch' => $request->evidencia_arch
                ]);
                DB::commit();
                    // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                try {
                    $user = Auth::user(); // Obtenemos el usuario autenticado
                    Bitacora::create([
                        'bt_usuario' => $user->NombUsu,
                        'bt_fechahora' => Carbon::now(),
                        'bt_accion' => 'Creación de un Equipo de Departamento para el departamento - INVESTIGACIÓN',
                        'bt_ippc' => $request->ip(),
                        'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de un Equipo de Departamento con ID: y ciinfper_doc: {$request->ciinfper_doc}",
                    ]);
                } catch (\Exception $ex) {
                    Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
                }
                return response()->json(['mensaje' => 'Registrado con éxito']);
            } catch (\Exception $e) {
                return response()->json(['mensaje' => 'Error al guardar los datos'], 500);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function getRolesInvestigacion(Request $request)
    {
        try {
            $idExcluir = $request->input('id_excluir');

            $roles = Invi_equipo_roles::where('tipo_rol', 'INVESTIGACIÓN')
                ->where('estado_rol', 1)
                ->get();

            // Obtenemos los asignados, pero excluimos el ID que estamos editando
            $queryAsignados = Invi_equipo_depart::where('estado_equipo_dep', 1);

            if ($idExcluir) {
                $queryAsignados->where('id_equipo_depart', '!=', $idExcluir);
            }

            $asignados = $queryAsignados->pluck('ciinfper_doc', 'id_equipo_roles')->toArray();

            $roles->transform(function ($rol) use ($asignados) {
                $rol->is_assigned = isset($asignados[$rol->id_equipo_roles]);
                $rol->asignado_a = $asignados[$rol->id_equipo_roles] ?? null;
                return $rol;
            });

            return response()->json(['data' => $roles]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $res = Invi_equipo_depart::find($id);
        if (isset($res)) {
            return response()->json([
                'data' => $res,
                'mensaje' => "Encontrado con Éxito!!",
            ]);
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "El Equipo de Departamento con id: $id no Existe",
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

            $request->validate([
                'ciinfper_doc' => 'required|digits:10',
                'id_equipo_roles' => 'required|integer',
            ]);
    
            try {
                $asignacion = Invi_equipo_depart::findOrFail($id);
    
                // Validamos que el rol no esté ocupado por OTRA persona
                $existeOcupado = Invi_equipo_depart::where('id_equipo_roles', $request->id_equipo_roles)
                    ->where('estado_equipo_dep', 1)
                    ->where('id_equipo_depart', '!=', $id) // ignoramos el registro actual
                    ->first();
    
                if ($existeOcupado) {
                    return response()->json(['mensaje' => 'Este rol ya se encuentra asignado a otra persona.'], 422);
                }
    
                $asignacion->update([
                    'ciinfper_doc' => $request->ciinfper_doc,
                    'id_equipo_roles' => $request->id_equipo_roles,
                    'estado_equipo_dep' => $request->estado_equipo_dep ?? $asignacion->estado_equipo_dep,
                    'evidencia_arch' => $request->evidencia_arch ?? $asignacion->evidencia_arch
                ]);
                DB::commit();
                    // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                try {
                    $user = Auth::user(); // Obtenemos el usuario autenticado
                    Bitacora::create([
                        'bt_usuario' => $user->NombUsu,
                        'bt_fechahora' => Carbon::now(),
                        'bt_accion' => 'Actualización de un Equipo de Roles para el departamento - INVESTIGACIÓN',
                        'bt_ippc' => $request->ip(),
                        'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de un Equipo de Roles con ID: {$asignacion->id_equipo_depart} y nombre: {$asignacion->equipo_roles->nombre_rol}",
                    ]);
                } catch (\Exception $ex) {
                    Log::error('Error bitácora en Actualizar: ' . $ex->getMessage());
                }
                return response()->json(['mensaje' => 'Actualizado con éxito']);
            } catch (\Exception $e) {
                return response()->json(['mensaje' => 'Error al actualizar'], 500);
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

            $res = Invi_equipo_depart::find($id);
            if (isset($res)) {
                $res->estado_equipo_dep = 0;
                $res->save();
                $data = $res->toArray();
                if ($data) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Inhabilitación de un Equipo de Roles para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Inhabilitación de un Equipo de Roles con ID: {$res->id_equipo_depart} y nombre: {$res->equipo_roles->nombre_rol}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Inhabilitar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "Inhabilitado con Éxito!!",
                    ]);
                } else {
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "El Equipo de Roles no existe (puede que ya la haya eliminado)",
                    ]);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'mensaje' => "El Equipo de Roles con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function habilitar(Request $request, string $id)
    {
        try{

            $res = Invi_equipo_depart::find($id);
            if (isset($res)) {
                $res->estado_equipo_dep = 1;
                $res->save();
                $data = $res->toArray();
                if ($data) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Habilitación de un Equipo de Roles para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Habilitación de un Equipo de Roles con ID: {$res->id_equipo_depart} y nombre: {$res->equipo_roles->nombre_rol}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Habilitar: ' . $ex->getMessage());
                    }

                    return response()->json([
                        'data' => $data,
                        'mensaje' => "Habilitado con Éxito!!",
                    ]);
                } else {
                    return response()->json([
                        'data' => $data,
                        'mensaje' => "El Equipo no existe (puede que ya la haya eliminado)",
                    ]);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'mensaje' => "El Equipo con id: $id no Existe",
                ]);
            }
        }catch(\Exception $e){
            Log::error('Error al procesar los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function getDirectorInfo(): JsonResponse
    {
        try {
            // 1. Buscamos el integrante cuyo rol sea Director/Directora
            $director = Invi_equipo_depart::with([
                'informacionpersonald.titulos.nivel',
                'equipo_roles'
            ])
                ->whereHas('equipo_roles', function ($query) {
                    $query->where('nombre_rol', 'LIKE', '%Director%')
                        ->where('estado_rol', 1)
                        ->where('tipo_rol', 'LIKE', '%VINCULACION%');
                })
                ->where('estado_equipo_dep', 1)
                ->first();

            if (!$director) {
                return response()->json(['message' => 'Director no encontrado'], 404);
            }

            $persona = $director->informacionpersonald;
            $rol = $director->equipo_roles;

            // 2. Lógica de Género (M = Mujer, H = Hombre)
            $esMujer = strtoupper($persona->GeneroPer) === 'F';

            $labels = [
                'pronombre'  => $esMujer ? 'Ella' : 'Él',
                'articulo'   => $esMujer ? 'la' : 'el',
                'titulo_rol' => $esMujer ? 'Directora' : 'Director',
                'texto_bienvenida' => $esMujer
                    ? "Ella es la Directora de la Dirección de INVESTIGACIÓN con la Sociedad Actualmente"
                    : "Él es el Director de la Dirección de INVESTIGACIÓN con la Sociedad Actualmente"
            ];

            // 3. Procesar títulos académicos de forma inteligente filtrando por nv_numnivel
            $tituloGrado = $persona->titulos->first(function ($titulo) {
                return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
            });
            
            $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
            });

            $prefijoNombre = '';
            $sufijoNombre = '';

            // A. Definir el PREFIJO (basado únicamente en el título de 3er nivel)
            if ($tituloGrado) {
                $textoGrado = mb_strtolower($tituloGrado->ad_titulo);

                if (str_contains($textoGrado, 'licencia')) {
                    $prefijoNombre = $esMujer ? 'Lcda.' : 'Lcdo.';
                } else {
                    // Si es ingeniería u otro de 3er nivel, va "Ing." por defecto
                    $prefijoNombre = 'Ing.';
                }
            } else {
                // Respaldo en caso de que no existan títulos de 3er nivel en la BD
                $prefijoNombre = $esMujer ? 'Sra.' : 'Sr.';
            }

            // B. Definir el SUFIJO (basado únicamente en el título de 4to nivel)
            if ($tituloPosgrado) {
                $textoPosgrado = mb_strtolower($tituloPosgrado->ad_titulo);

                if (str_contains($textoPosgrado, 'phd') || str_contains($textoPosgrado, 'doctorado') || str_contains($textoPosgrado, 'doctor')) {
                    $sufijoNombre = ', PhD';
                } elseif (str_contains($textoPosgrado, 'msc') || str_contains($textoPosgrado, 'science') || str_contains($textoPosgrado, 'ciencias')) {
                    $sufijoNombre = ', MSc.';
                } else {
                    // Para Magíster, Mgtr, Maestría u otros de 4to nivel por defecto
                    $sufijoNombre = ', Mgtr.';
                }
            }

            // Construcción final: Prefijo + Nombres + Apellidos + Sufijo
            $nombreCompletoConTitulo = "{$prefijoNombre} {$persona->NombInfPer} {$persona->ApellInfPer}{$sufijoNombre}";

            // 4. Separar las funciones por renglones/saltos de línea para el Owl Carousel
            $funcionesArray = [];
            if (!empty($rol->funciones_rol)) {
                $funcionesArray = array_filter(
                    explode("\n", str_replace("\r", "", $rol->funciones_rol)),
                    'trim'
                );
                $funcionesArray = array_values($funcionesArray);
            }

            // 5. Retornar la respuesta estructurada
            return response()->json([
                'ci'              => $persona->CIInfPer,
                'nombre_completo' => $nombreCompletoConTitulo,
                'email'           => $persona->mailInst ?? 'utelvt.edu.ec',
                'telefono'        => $persona->Telf1InfPer ?? $persona->CelularInfPer ?? 'S/N',
                'detalle_rol'     => $rol->detalle_rol,
                'funciones'       => $funcionesArray,
                'genero_labels'   => $labels,
                'titulo_grado'    => $tituloGrado ? $tituloGrado->ad_titulo : null,
                'titulo_posgrado' => $tituloPosgrado ? $tituloPosgrado->ad_titulo : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar la solicitud',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function getTeamInfo(): JsonResponse
    {
        try {
            // 1. Buscamos a todos los integrantes excepto al director
            $miembros = Invi_equipo_depart::with([
                'informacionpersonald.titulos.nivel',
                'equipo_roles'
            ])
                ->whereHas('equipo_roles', function ($query) {
                    $query->where('nombre_rol', 'NOT LIKE', '%Director%')
                        ->where('estado_rol', 1);
                })
                ->where('estado_equipo_dep', 1)
                ->get();

            $resultado = [];

            foreach ($miembros as $miembro) {
                $persona = $miembro->informacionpersonald;
                $rol = $miembro->equipo_roles;

                if (!$persona || !$rol) {
                    continue;
                }

                // 2. Lógica de Género
                $esMujer = strtoupper($persona->GeneroPer) === 'F';

                $labels = [
                    'pronombre'  => $esMujer ? 'Ella' : 'Él',
                    'articulo'   => $esMujer ? 'la' : 'el',
                    'titulo_rol' => $rol->nombre_rol, // Usa el nombre dinámico del rol
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

                    if (str_contains($textoGrado, 'licencia')) {
                        $prefijoNombre = $esMujer ? 'Lcda.' : 'Lcdo.';
                    } elseif (str_contains($textoGrado, 'ingenier') or str_contains($textoGrado, 'ing.')) {
                        $prefijoNombre = 'Ing.';
                    } elseif (str_contains($textoGrado, 'econom') or str_contains($textoGrado, 'econ.')) {
                        $prefijoNombre = $esMujer ? 'Econ.' : 'Econ.';
                    } elseif (str_contains($textoGrado, 'abogad') or str_contains($textoGrado, 'abg.')) {
                        $prefijoNombre = $esMujer ? 'Abg.' : 'Abg.';
                    } elseif (str_contains($textoGrado, 'arquitect') or str_contains($textoGrado, 'arq.')) {
                        $prefijoNombre = $esMujer ? 'Arq.' : 'Arq.';
                    } else {
                        // Cualquier otra profesión o fallback de 3er nivel
                        $prefijoNombre = 'Prof.';
                    }
                } else {
                    // Respaldo por defecto si NO posee títulos registrados
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
                        // Para Magíster, Mgtr, Maestría u otros de 4to nivel por defecto
                        $sufijoNombre = ', Mgtr.';
                    }
                }

                // Construcción del nombre
                $nombreCompletoConTitulo = "{$prefijoNombre} {$persona->NombInfPer} {$persona->ApellInfPer}{$sufijoNombre}";

                // 4. Separar funciones por renglones
                $funcionesArray = [];
                if (!empty($rol->funciones_rol)) {
                    $funcionesArray = array_filter(
                        explode("\n", str_replace("\r", "", $rol->funciones_rol)),
                        'trim'
                    );
                    $funcionesArray = array_values($funcionesArray);
                }

                // Estructura individual del objeto miembro del equipo
                $resultado[] = [
                    'ci'              => $persona->CIInfPer,
                    'nombre_completo' => $nombreCompletoConTitulo,
                    'email'           => $persona->mailInst ?? 'vinculacion@utelvt.edu.ec',
                    'telefono'        => $persona->Telf1InfPer ?? $persona->CelularInfPer ?? 'S/N',
                    'detalle_rol'     => $rol->detalle_rol,
                    'funciones'       => $funcionesArray,
                    'genero_labels'   => $labels,
                    'titulo_grado'    => $tituloGrado ? $tituloGrado->ad_titulo : null,
                    'titulo_posgrado' => $tituloPosgrado ? $tituloPosgrado->ad_titulo : null,
                ];
            }

            return response()->json($resultado);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar la solicitud del equipo',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Función para subir un archivo
     * Esta función recibe un Request con el archivo a subir
     * La función devuelve un array de objetos que representan los resultados de la operación
     * Se valida que el archivo exista y que el tamaño del archivo sea menor que 10MB
     * Si el archivo es válido, se guarda el archivo en la carpeta Documentos/INVESTIGACIÓN/EquipoInvest
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
                $oldPath = public_path("Documentos/INVESTIGACION/EquipoInvest/{$ci}/{$oldFilename}"); // Se obtiene la ruta del archivo antiguo
                if (File::exists($oldPath)) { // Si el archivo antiguo existe
                    File::delete($oldPath); // Se elimina el archivo antiguo
                }
            }

            // Crear carpeta publica si no existe
            $directory = public_path("Documentos/INVESTIGACION/EquipoInvest/{$ci}");

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
            $url = url('Documentos/INVESTIGACION/EquipoInvest/' . $ci . '/' . $filename);

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
}
