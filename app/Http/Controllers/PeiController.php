<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Bitacora;
use App\Models\Pei;
use App\Models\PeiEstadoDepartamento;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeiController extends Controller
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

            $query = Pei::withCount(['subsistemas_pei', 'objetivos']);

            if ($id_departamento) {
                $query->whereHas('estadosDepartamentos', function ($q) use ($id_departamento) {
                    $q->where('id_departamento', $id_departamento);
                });

                $query->with(['estadosDepartamentos' => function ($q) use ($id_departamento) {
                    $q->where('id_departamento', $id_departamento);
                }]);
            }

            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('nombre_pei', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('anios_pei', 'LIKE', '%' . $searchQuery . '%');
                });
            }

            // --- FUNCION AUXILIAR PARA TRANSFORMAR LA DATA ---
            $transformData = function ($item) use ($id_departamento, $nombre_departamento) {
                $attributes = $item->toArray();

                // Si consultamos por departamento, agregamos variables exclusivas para él
                if ($id_departamento && $item->estadosDepartamentos->isNotEmpty()) {
                    // YA NO sobreescribimos 'estado_pei' para conservar el estado general.
                    // En su lugar, creamos estas dos nuevas llaves:
                    $attributes['estado_departamento'] = $item->estadosDepartamentos->first()->estado;
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

        // --- VALIDACIÓN DE DUPLICADOS PARA EL DEPARTAMENTO ---
        $existeDuplicado = Pei::where('nombre_pei', $request->nombre_pei)
            ->where('anios_pei', $request->anios_pei)
            ->whereHas('estadosDepartamentos', function ($q) use ($id_departamento) {
                $q->where('id_departamento', $id_departamento)
                    ->where('estado', 1);
            })
            ->exists();

        if ($existeDuplicado) {
            return response()->json([
                'status' => false,
                'mensaje' => 'Ya existe un PEI habilitado con el mismo nombre y período de años para su departamento.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $inputs = $request->all();
            $estado_final = $request->estado_pei ?? 1;

            if ($estado_final == 1) {
                // Verificamos si este DEPARTAMENTO ya tiene un PEI activo
                $existeActivo = PeiEstadoDepartamento::where('id_departamento', $id_departamento)
                    ->where('estado', 1)
                    ->exists();

                if ($existeActivo) {
                    $estado_final = 0;
                }
            }

            $inputs['estado_pei'] = $estado_final;

            $res = Pei::create($inputs);

            // Creamos la relación en la tabla pivote
            PeiEstadoDepartamento::create([
                'id_pei' => $res->id_pei,
                'id_departamento' => $id_departamento,
                'estado' => $estado_final
            ]);

            DB::commit();

            // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user();
                Bitacora::create([
                    'bt_usuario' => $user->NombUsu,
                    'bt_fechahora' => Carbon::now(),
                    'bt_accion' => 'Creación de un nuevo PEI para el departamento - INVESTIGACIÓN',
                    'bt_ippc' => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Creación de un nuevo PEI con ID: {$res->id_pei} y nombre: {$res->nombre_pei}",
                ]);
            } catch (\Exception $ex) {
                Log::error('Error bitácora en Guardar: ' . $ex->getMessage());
            }

            return response()->json([
                'data' => $res,
                'mensaje' => ($estado_final == 0 && $request->estado_pei == 1)
                    ? "Agregado, pero se guardó como Inactivo porque su departamento ya tiene un PEI activo."
                    : "Agregado con Éxito!!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $res = Pei::find($id);
        if (isset($res)) {
            return response()->json([
                'data' => $res,
                'mensaje' => "Encontrado con Éxito!!",
            ]);
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "El PEI con id: $id no Existe",
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

        // --- VALIDACIÓN DE DUPLICADOS AL EDITAR (Excluyendo el PEI actual) ---
        $existeDuplicado = Pei::where('id_pei', '!=', $id)
            ->where('nombre_pei', $request->nombre_pei)
            ->where('anios_pei', $request->anios_pei)
            ->whereHas('estadosDepartamentos', function ($q) use ($id_departamento) {
                $q->where('id_departamento', $id_departamento)
                    ->where('estado', 1);
            })
            ->exists();

        if ($existeDuplicado) {
            return response()->json([
                'status' => false,
                'mensaje' => 'Ya existe otro PEI habilitado con el mismo nombre y período de años en su departamento.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $res = Pei::find($id);

            if (isset($res)) {
                $res->nombre_pei = $request->nombre_pei;
                $res->anios_pei = $request->anios_pei;

                if ($request->has('archivo_pei')) {
                    $res->archivo_pei = $request->archivo_pei;
                }

                // Buscar o crear la relación pivote
                $pivot = PeiEstadoDepartamento::firstOrNew([
                    'id_pei' => $id,
                    'id_departamento' => $id_departamento
                ]);

                if ($request->estado_pei == 1) {
                    // Buscamos si ESTE DEPARTAMENTO tiene otro PEI activo que NO SEA el actual
                    $otroActivo = PeiEstadoDepartamento::where('id_departamento', $id_departamento)
                        ->where('id_pei', '!=', $id)
                        ->where('estado', 1)
                        ->exists();

                    if ($otroActivo) {
                        $pivot->estado = 0;
                        $mensajeFinal = "Actualizado, pero se cambió a Inactivo porque su departamento ya tiene otro PEI activo.";
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
                        $user = Auth::user();
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Actualización de un PEI para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualización de un PEI con ID: {$res->id_pei} y nombre: {$res->nombre_pei}",
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

            return response()->json(['error' => true, 'mensaje' => "El pei con id: $id no Existe"], 404);
        } catch (\Exception $e) {
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

            $id_departamento = $request->input('id_departamento');

            if (!$id_departamento) {
                return response()->json(['error' => true, 'mensaje' => 'El id_departamento es requerido.'], 400);
            }

            $res = Pei::find($id);

            if (isset($res)) {
                // Buscamos el estado en el departamento e inhabilitamos SOLO para ellos
                $pivot = PeiEstadoDepartamento::where('id_pei', $id)
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
                        'bt_accion' => 'Inhabilitación de un PEI para el departamento - INVESTIGACIÓN',
                        'bt_ippc' => $request->ip(),
                        'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Inhabilitación de un PEI con ID: {$res->id_pei} y nombre: {$res->nombre_pei}",
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
                    'mensaje' => "El pei con id: $id no Existe",
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function habilitar(Request $request, string $id)
    {
        DB::beginTransaction();
        try {

            $id_departamento = $request->input('id_departamento');

            if (!$id_departamento) {
                return response()->json(['error' => true, 'mensaje' => 'El id_departamento es requerido.'], 400);
            }

            // 1. Verificar si ESTE DEPARTAMENTO ya tiene algún PEI activo
            $existeActivo = PeiEstadoDepartamento::where('id_departamento', $id_departamento)
                ->where('estado', 1)->exists();

            if ($existeActivo) {
                return response()->json([
                    'status' => false,
                    'mensaje' => "No se puede habilitar: Su departamento ya tiene un PEI activo actualmente. Por favor, desactive el anterior primero."
                ], 422);
            }

            // 2. Si no hay activos, procedemos a buscar y habilitar el pivote
            $res = Pei::find($id);

            if (isset($res)) {
                $pivot = PeiEstadoDepartamento::firstOrCreate(
                    ['id_pei' => $id, 'id_departamento' => $id_departamento],
                    ['estado' => 1] // Lo crea en 1 si no existía el registro
                );

                $pivot->estado = 1;
                $pivot->save();

                // Sincronizamos globalmente si lo ves necesario
                $res->estado_pei = 1;
                if ($res->save()) {
                    DB::commit();
                    // --- REGISTRO EN BITÁCORA (Post-Commit) ---
                    try {
                        $user = Auth::user(); // Obtenemos el usuario autenticado
                        Bitacora::create([
                            'bt_usuario' => $user->NombUsu,
                            'bt_fechahora' => Carbon::now(),
                            'bt_accion' => 'Habilitación de un PEI para el departamento - INVESTIGACIÓN',
                            'bt_ippc' => $request->ip(),
                            'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Habilitación de un PEI con ID: {$res->id_pei} y nombre: {$res->nombre_pei}",
                        ]);
                    } catch (\Exception $ex) {
                        Log::error('Error bitácora en Habilitar: ' . $ex->getMessage());
                    }
                    return response()->json([
                        'status' => true,
                        'data' => $res,
                        'mensaje' => "¡PEI Habilitado con Éxito para su departamento!",
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
                    'mensaje' => "El PEI con id: $id no existe o fue eliminado.",
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function uploadArchivo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|mimetypes:application/pdf|max:10240',
            'anio_pei' => 'required|string', // Quitamos alpha_dash por si usas guiones como "2024-2030"
            'old_filename' => 'nullable|string',
            'old_anio' => 'nullable|string',
        ]);

        try {
            $anio_folder = str_replace(['/', '\\', ' '], '_', $request->anio_pei);
            $file = $request->file('file');
            if (!$file->isValid()) {
                throw new \Exception("Archivo inválido o corrupto.");
            }

            // --- LÓGICA DE ELIMINACIÓN Y LIMPIEZA ---
            if ($request->filled('old_filename')) {
                $folder_to_clean = $request->filled('old_anio')
                    ? str_replace(['/', '\\', ' '], '_', $request->old_anio)
                    : $anio_folder;

                $oldDirectory = public_path("Documentos/Pei/{$folder_to_clean}");
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
            $basePath = "Documentos/Pei/{$anio_folder}";
            $directory = public_path($basePath);

            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // 5. Generar nombre único
            $aleatorio = bin2hex(random_bytes(4));
            $fechaHora = date("Ymd_His");
            $extension = $file->getClientOriginalExtension();
            // Nombre: pei_2024-2028_a1b2c3d4_20260422.pdf
            $filename = "pei_{$anio_folder}_{$aleatorio}_{$fechaHora}.{$extension}";

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
            'anio_pei' => 'required',
        ]);

        $filePath = public_path('Documentos/Pei/' . $request->anio_pei . '/' . $request->filename);

        if (File::exists($filePath)) {
            File::delete($filePath);

            return response()->json(['status' => true, 'message' => 'Archivo eliminado']);
        }

        return response()->json(['status' => false, 'message' => 'Archivo no encontrado'], 404);
    }
}
