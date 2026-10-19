<?php

namespace App\Http\Controllers;

use App\Models\Agenda_ODS;
use App\Models\Departamento;
use App\Models\AgendaODSEstadoDepartamento;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Agenda_ODSController extends Controller
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
            $query = Agenda_ODS::withCount(['ods']);
            if ($id_departamento) {
                $query->whereHas('estadosDepartamentosAgendaODS', function ($q) use ($id_departamento) {
                    $q->where('id_departamento', $id_departamento);
                });

                $query->with(['estadosDepartamentosAgendaODS' => function ($q) use ($id_departamento) {
                    $q->where('id_departamento', $id_departamento);
                }]);
            }
            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('nombre_ag_ods', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('anio_ag_ods', 'LIKE', '%' . $searchQuery . '%');
                });
            }

           // --- FUNCION AUXILIAR PARA TRANSFORMAR LA DATA ---
            $transformData = function ($item) use ($id_departamento, $nombre_departamento) {
                $attributes = $item->toArray();

                // Si consultamos por departamento, agregamos variables exclusivas para él
                if ($id_departamento && $item->estadosDepartamentosAgendaODS->isNotEmpty()) {
                    // YA NO sobreescribimos 'estado_pei' para conservar el estado general.
                    // En su lugar, creamos estas dos nuevas llaves:
                    $attributes['estado_departamento'] = $item->estadosDepartamentosAgendaODS->first()->estado;
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
        DB::beginTransaction();
        try{

            $inputs = $request->all();
            $id_departamento = $request->input('id_departamento');
            if (!$id_departamento) {
                return response()->json(['error' => true, 'mensaje' => 'El id_departamento es requerido.'], 400);
            }
            $estado_final = $request->estado_ag_ods ?? 1;
            if ($estado_final == 1) {
                // Verificamos si este DEPARTAMENTO ya tiene un Agenda ODS activo
                $existeActivo = AgendaODSEstadoDepartamento::where('id_departamento', $id_departamento)
                    ->where('estado', 1)->exists();
                if ($existeActivo) {
                    $estado_final = 0;
                }
            }
            $inputs['estado_ag_ods'] = $estado_final;
    
            $res = Agenda_ODS::create($inputs);
    
            // Creamos la relación en la tabla pivote
            AgendaODSEstadoDepartamento::create([
                'id_ag_ods' => $res->id_ag_ods,
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
                    'bt_accion' => 'Creación de una Agenda ODS para el departamento - VINCULACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de una Agenda ODS con ID: {$res->id_ag_ods} y nombre: {$res->nombre_ag_ods}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }
            return response()->json([
                'data' => $res,
                'mensaje' => $res->estado_ag_ods == 0 && $request->estado_ag_ods == 1
                    ? "Agregado, pero se guardó como Inactivo porque ya existe una Agenda ODS activo."
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
        $res = Agenda_ODS::find($id);
        if (isset($res)) {
            return response()->json([
                'data' => $res,
                'mensaje' => "Encontrado con Éxito!!",
            ]);
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "La Agenda ODS con id: $id no Existe",
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

            $res = Agenda_ODS::find($id);
            $id_departamento = $request->input('id_departamento');
            if (!$id_departamento) {
                return response()->json(['error' => true, 'mensaje' => 'El id_departamento es requerido.'], 400);
            }
    
    
            if (isset($res)) {
                $res->nombre_ag_ods = $request->nombre_ag_ods;
                $res->anio_ag_ods = $request->anio_ag_ods;
                // Solo actualizamos el archivo si se proporciona uno nuevo
                if($request->has('archivo_ag_ods')) {
                    $res->archivo_ag_ods = $request->archivo_ag_ods;
                }
                $pivot = AgendaODSEstadoDepartamento::firstOrNew([
                    'id_ag_ods' => $res->id_ag_ods,
                    'id_departamento' => $id_departamento
                ]);
                // Lógica de validación de estado
                if ($request->estado_ag_ods == 1) {
                    // Buscamos si hay otro Agenda ODS activo que NO SEA el que estamos editando
                    $otroActivo = AgendaODSEstadoDepartamento::where('id_departamento', $id_departamento)
                        ->where('estado', 1)
                        ->where('id_ag_ods', '!=', $id)
                        ->exists();
                    
    
                    if ($otroActivo) {
                        $pivot->estado = 0;
                        $mensajeFinal = "Actualizado, pero se cambió a Inactivo porque su departamento ya tiene otra Agenda ODS activa.";
                    } else {
                         $pivot->estado = 1;
                        $mensajeFinal = "Actualizado con Éxito!!";
                    }
                } else {
                    $pivot->estado = 0;
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
                            'bt_accion' => 'Actualización de una Agenda ODS para el departamento - VINCULACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de una Agenda ODS con ID: {$res->id_ag_ods} y nombre: {$res->nombre_ag_ods}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Actualizar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'data' => $res,
                        'mensaje' => $mensajeFinal,
                    ]);
                }
    
                return response()->json(['error' => true, 'mensaje' => 'Error al Actualizar'], 500);
            }
    
            return response()->json(['error' => true, 'mensaje' => "La Agenda ODS con id: $id no Existe"], 404);
        }catch(\Exception $e){
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
            $res = Agenda_ODS::find($id);
            if (isset($res)) {
                $pivot = AgendaODSEstadoDepartamento::where('id_ag_ods', $id)
                    ->where('id_departamento', $id_departamento)
                    ->first();
                if($pivot) {
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
                        'bt_accion' => 'Inhabilitación de una Agenda ODS para el departamento - VINCULACIÓN',
                        'bt_ippc' => $request->ip(),
                        'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Inhabilitación de una Agenda ODS con ID: {$res->id_ag_ods} y nombre: {$res->nombre_ag_ods}",
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
                    'mensaje' => "La Agenda ODS con id: $id no Existe",
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
            // 1. Verificar si ya existe algún PlANDE activo
            $existeActivo = AgendaODSEstadoDepartamento::where('id_departamento', $id_departamento)
                ->where('estado', 1)->exists();
    
            if ($existeActivo) {
                return response()->json([
                    'status' => false,
                    'mensaje' => "No se puede habilitar: Ya existe una Agenda ODS activa actualmente para su departamento. Por favor, desactive la anterior primero."
                ], 422); // Código 422: Entidad no procesable (error de validación de negocio)
            }
    
            // 2. Si no hay activos, procedemos a buscar y habilitar
            $res = Agenda_ODS::find($id);
    
            if (isset($res)) {
                $pivot = AgendaODSEstadoDepartamento::firstOrCreate(
                    ['id_ag_ods' => $res->id_ag_ods, 'id_departamento' => $id_departamento],
                    ['estado' => 1] // Lo crea en 1 si no existía el registro
                );
    
                $pivot->estado = 1;
                $pivot->save();
                $res->estado_ag_ods = 1;
    
                if ($res->save()) {
                    DB::commit();
                        // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Habilitación de una Agenda ODS para el departamento - VINCULACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Habilitación de una Agenda ODS con ID: {$res->id_ag_ods} y nombre: {$res->nombre_ag_ods}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Habilitar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'status' => true,
                        'data' => $res,
                        'mensaje' => "¡Agenda ODS Habilitada con Éxito!",
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
                    'mensaje' => "La Agenda ODS con id: $id no existe o fue eliminada.",
                ], 404);
            }
        }catch(\Exception $e){
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function uploadArchivo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|mimetypes:application/pdf|max:20240',
            'anio_ag_ods' => 'required|string', // Quitamos alpha_dash por si usas guiones como "2024-2030"
            'old_filename' => 'nullable|string',
            'old_anio' => 'nullable|string',
        ]);

        try {
            $anio_folder = str_replace(['/', '\\', ' '], '_', $request->anio_ag_ods);
            $file = $request->file('file');
            if (!$file->isValid()) {
                throw new \Exception("Archivo inválido o corrupto.");
            }

            // --- LÓGICA DE ELIMINACIÓN Y LIMPIEZA ---
            if ($request->filled('old_filename')) {
                $folder_to_clean = $request->filled('old_anio')
                    ? str_replace(['/', '\\', ' '], '_', $request->old_anio)
                    : $anio_folder;

                $oldDirectory = public_path("Documentos/AGENDA_ODS/{$folder_to_clean}");
                $oldPath = $oldDirectory . '/' . basename($request->old_filename);

                // 1. Borrar el archivo
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }

                // 2. Limpiar carpeta si quedó vacía (y no es la misma carpeta donde vamos a guardar ahora)
                // Solo intentamos borrarla si la carpeta existe y es distinta a la nueva o si queremos limpieza total
                if (File::exists($oldDirectory) && count(File::files($oldDirectory)) === 0 && count(File::directories($oldDirectory)) === 0) {
                    File::deleteDirectory($oldDirectory);
                }
            }

            // --- LÓGICA DE GUARDADO ---
            $basePath = "Documentos/AGENDA_ODS/{$anio_folder}";
            $directory = public_path($basePath);

            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // 5. Generar nombre único
            $aleatorio = bin2hex(random_bytes(4));
            $fechaHora = date("Ymd_His");
            $extension = $file->getClientOriginalExtension();
            // Nombre: agenda_ods_2024-2028_a1b2c3d4_20260422.pdf
            $filename = "agenda_ods_{$anio_folder}_{$aleatorio}_{$fechaHora}.{$extension}";

            // 6. Mover archivo
            $file->move($directory, $filename);

            return response()->json([
                'status'   => true,
                'filename' => $filename,
                'url'      => url($basePath . '/' . $filename)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error al procesar el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function deleteArchivo(Request $request)
    {
        $request->validate([
            'filename' => 'required',
            'anio_ag_ods' => 'required',
        ]);

        $filePath = public_path('Documentos/AGENDA_ODS/' . $request->anio_ag_ods . '/' . $request->filename);

        if (File::exists($filePath)) {
            File::delete($filePath);

            return response()->json(['status' => true, 'message' => 'Archivo eliminado']);
        }

        return response()->json(['status' => false, 'message' => 'Archivo no encontrado'], 404);
    }
}
