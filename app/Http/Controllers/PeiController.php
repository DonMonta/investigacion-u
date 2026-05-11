<?php

namespace App\Http\Controllers;

use App\Models\Pei;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class PeiController extends Controller
{
    /**
     * Display a listing of the resource.
     */ 
    public function index(Request $request)
    {
        try {
            // Usamos withCount para obtener los totales de las relaciones
            $query = Pei::withCount(['subsistemas_pei', 'objetivos']);

            if ($request->has('all') && $request->all === 'true') {
                $data = $query->get();

                // Transformación para UTF-8 y manejo de atributos
                $data->transform(function ($item) {
                    $attributes = $item->toArray(); // Usamos toArray para incluir los campos _count
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
                $attributes = $item->toArray(); // toArray incluye automáticamente subsistemas_count y objetivos_count
                foreach ($attributes as $key => $value) {
                    if (is_string($value)) {
                        $attributes[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                }
                return $attributes;
            });

            return response()->json([
                'data' => $data->items(),
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
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
        $inputs = $request->all();

        // Si el usuario intenta enviar el PEI como ACTIVO (1)
        if ($request->estado_pei == 1) {
            // Verificamos si ya existe AL MENOS UNO activo en la base de datos
            $existeActivo = Pei::where('estado_pei', 1)->exists();

            if ($existeActivo) {
                // Si ya hay uno, forzamos este nuevo a ser INACTIVO (0)
                $inputs['estado_pei'] = 0;
            }
        }

        $res = Pei::create($inputs);

        return response()->json([
            'data' => $res,
            'mensaje' => $res->estado_pei == 0 && $request->estado_pei == 1
                ? "Agregado, pero se guardó como Inactivo porque ya existe un PEI activo."
                : "Agregado con Éxito!!",
        ]);
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
        $res = Pei::find($id);

        if (isset($res)) {
            $res->nombre_pei = $request->nombre_pei;
            $res->anios_pei = $request->anios_pei;

            // Lógica de validación de estado
            if ($request->estado_pei == 1) {
                // Buscamos si hay otro PEI activo que NO SEA el que estamos editando
                $otroActivo = Pei::where('estado_pei', 1)
                    ->where('id_pei', '!=', $id)
                    ->exists();

                if ($otroActivo) {
                    $res->estado_pei = 0;
                    $mensajeFinal = "Actualizado, pero se cambió a Inactivo porque ya existe otro PEI activo.";
                } else {
                    $res->estado_pei = 1;
                    $mensajeFinal = "Actualizado con Éxito!!";
                }
            } else {
                $res->estado_pei = 0;
                $mensajeFinal = "Actualizado con Éxito!!";
            }

            if ($request->has('archivo_pei')) {
                $res->archivo_pei = $request->archivo_pei;
            }

            if ($res->save()) {
                return response()->json([
                    'data' => $res,
                    'mensaje' => $mensajeFinal,
                ]);
            }

            return response()->json(['error' => true, 'mensaje' => 'Error al Actualizar'], 500);
        }

        return response()->json(['error' => true, 'mensaje' => "El pei con id: $id no Existe"], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $res = Pei::find($id);
        if (isset($res)) {
            $res->estado_pei = 0;
            $res->save();
            $data = $res->toArray();
            if ($data) {

                return response()->json([
                    'data' => $data,
                    'mensaje' => "Inhabilitado con Éxito!!",
                ]);
            } else {
                return response()->json([
                    'data' => $data,
                    'mensaje' => "El pei no existe (puede que ya la haya eliminado)",
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "El pei con id: $id no Existe",
            ]);
        }
    }
    public function habilitar(string $id)
    {
        // 1. Verificar si ya existe algún PEI activo
        $existeActivo = Pei::where('estado_pei', 1)->exists();

        if ($existeActivo) {
            return response()->json([
                'status' => false,
                'mensaje' => "No se puede habilitar: Ya existe un PEI activo actualmente. Por favor, desactive el anterior primero."
            ], 422); // Código 422: Entidad no procesable (error de validación de negocio)
        }

        // 2. Si no hay activos, procedemos a buscar y habilitar
        $res = Pei::find($id);

        if (isset($res)) {
            $res->estado_pei = 1;

            if ($res->save()) {
                return response()->json([
                    'status' => true,
                    'data' => $res,
                    'mensaje' => "¡PEI Habilitado con Éxito!",
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
