<?php

namespace App\Http\Controllers;

use App\Models\Praempresa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Bitacora;
use App\Models\Pais;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class PraempresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $searchQuery = $request->input('search_query');
            $query = Praempresa::select('praempresa.*');

            $direction = $request->input('direction', 'desc');
            if (!in_array($direction, ['asc', 'desc'])) {
                $direction = 'desc';
            }

            $query->orderBy('fechafin', $direction);

            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('ruc', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('empresa', 'LIKE', '%' . $searchQuery . '%');
                });
            }

            // --- RESPUESTA PARA TODOS LOS REGISTROS ---
            if ($request->has('all') && $request->all === 'true') {
                $data = $query->get();

                $data->transform(function ($item) {
                    $attributes = $item->getAttributes();
                    foreach ($attributes as $key => $value) {
                        if ($key === 'imagen') {
                            // ✅ Optimización: Solo enviamos si tiene o no imagen (true/false)
                            $attributes[$key] = !empty($value);
                        } elseif (is_string($value)) {
                            $attributes[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                        }
                    }
                    return $attributes;
                });

                return response()->json(['data' => $data]);
            }

            // --- RESPUESTA CON PAGINACIÓN ---
            $data = $query->paginate(20);

            if ($data->isEmpty()) {
                return response()->json(['error' => 'No se encontraron datos'], 404);
            }

            $data->getCollection()->transform(function ($item) {
                $attributes = $item->getAttributes();
                foreach ($attributes as $key => $value) {
                    if ($key === 'imagen') {
                        // ✅ Enviar true/false para no inflar el JSON de la tabla con megabytes de imágenes
                        $attributes[$key] = !empty($value);
                    } elseif (is_string($value) && $key !== 'imagen') {
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
            return response()->json(['error' => 'Error en el servidor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $inputs = $request->input();

            if (!empty($inputs['imagen'])) {
                $inputs['imagen'] = base64_decode($inputs['imagen']);
            }
            $inputs['usuario_id'] = $user->ciinfper;

            $res = Praempresa::create($inputs);

            Bitacora::create([
                'bt_usuario'     => $user->ciinfper,
                'bt_fechahora'   => Carbon::now(),
                'bt_accion'      => "Crear Empresa - INVESTIGACIÓN",
                'bt_ippc'        => $request->ip(),
                'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Crear Empresa CON NOMBRE: {$request->empresa}", // ✅ Corregido campo nombre
            ]);

            DB::commit();

            $data = $res->toArray();
            if (!empty($res->imagen)) {
                $data['imagen'] = $request->imagen; // Retornamos el mismo base64 que envió el cliente
            }

            return response()->json([
                'message' => 'Registro creado exitosamente',
                'data' => $data
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $res = Praempresa::find($id);
        if (isset($res)) {
            return response()->json([
                'data' => $res,
                'mensaje' => "Encontrado con Éxito!!",
            ]);
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "La Empresa con id: $id no Existe",
            ]);
        }
    }
    public function buscarEmpresa(Request $request)
    {
        $termino = $request->query('q');
        
        if (empty($termino)) {
            return response()->json(null);
        }

        // Busca por RUC, nombre de empresa o empresa corta
        $empresa = Praempresa::where('ruc', 'LIKE', "%{$termino}%")
            ->orWhere('empresa', 'LIKE', "%{$termino}%")
            ->orWhere('empresacorta', 'LIKE', "%{$termino}%")
            ->first();

        return response()->json($empresa); // Retorna la primera que coincida o null
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $res = Praempresa::find($id);
        $user = Auth::user();

        if (!$res) {
            return response()->json([
                'error' => true,
                'mensaje' => "La Empresa con id: $id no Existe",
            ], 404);
        }

        DB::beginTransaction(); // ✅ Agregado inicio de transacción
        try {
            $res->ruc = $request->ruc;
            $res->empresa = $request->empresa;
            $res->empresacorta = $request->empresacorta;
            $res->lugar = $request->lugar;
            $res->direccion = $request->direccion;
            $res->telefono = $request->telefono;
            $res->email = $request->email;
            $res->url = $request->url;
            $res->tipo = $request->tipo;
            $res->titulo = $request->titulo;
            $res->representante = $request->representante;
            $res->cargo = $request->cargo;
            $res->actividad = $request->actividad;
            $res->fechafin = $request->fechafin;
            $res->tipoinstitucion = $request->tipoinstitucion;
            $res->pais = $request->pais;
            $res->ciudad = $request->ciudad;
            $res->estado_empr = $request->estado_empr;
            $res->vision = $request->vision;
            $res->mision = $request->mision;
            $res->usuario_id = $user->ciinfper;
            if ($request->filled('archivo')) {
                $res->archivo = $request->archivo;
            }

            if (!empty($request->imagen)) {
                $res->imagen = base64_decode($request->imagen);
            }

            $res->save(); // ✅ Guardar primero antes del commit

            Bitacora::create([
                'bt_usuario'     => $user->ciinfper,
                'bt_fechahora'   => Carbon::now(),
                'bt_accion'      => "Actualizar Empresa - INVESTIGACIÓN",
                'bt_ippc'        => $request->ip(),
                'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Actualizar Empresa CON NOMBRE: {$request->empresa}", // ✅ Corregido campo nombre
            ]);

            DB::commit(); // ✅ Hacer commit al final de todo el bloque exitoso

            $data = $res->toArray();
            if (!empty($request->imagen)) {
                $data['imagen'] = $request->imagen;
            }

            return response()->json([
                'data' => $data,
                'mensaje' => "Actualizado con Éxito!!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // ✅ Si algo falla deshace los cambios de forma segura
            return response()->json([
                'error' => true,
                'mensaje' => "Error al Actualizar: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->id;
        $res = Praempresa::find($id);
        $user = Auth::user();
        if (isset($res)) {
            $res->estado_empr = 0;
            $res->save();
            $data = $res->toArray();
            DB::commit();
            Bitacora::create([
                'bt_usuario'     => $user->ciinfper,
                'bt_fechahora'   => Carbon::now(),
                'bt_accion'      => "Inhabilitar Empresa" . " - INVESTIGACIÓN",
                'bt_ippc'        => $request->ip(),
                'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Inhabilitar Empresa CON NOMBRE: {$request->NOMBRE}",
            ]);
            if ($data) {

                return response()->json([
                    'data' => $data,
                    'mensaje' => "Inhabilitado con Éxito!!",
                ]);
            } else {
                return response()->json([
                    'data' => $data,
                    'mensaje' => "La Empresa no existe (puede que ya la haya eliminado)",
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "La Empresa con id: $id no Existe",
            ]);
        }
    }
    public function habilitar(Request $request)
    {
        $id = $request->id;
        $res = Praempresa::find($id);
        $user = Auth::user();

        if (isset($res)) {
            $res->estado_empr = 1;

            DB::commit();
            Bitacora::create([
                'bt_usuario'     => $user->ciinfper,
                'bt_fechahora'   => Carbon::now(),
                'bt_accion'      => "Habilitar Empresa" . " - INVESTIGACIÓN",
                'bt_ippc'        => $request->ip(),
                'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: Habilitar Empresa CON NOMBRE: {$request->NOMBRE}",
            ]);
            if ($res->save()) {
                return response()->json([
                    'status' => true,
                    'data' => $res,
                    'mensaje' => "¡Empresa Habilitada con Éxito!",
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
                'mensaje' => "La Empresa con id: $id no existe o fue eliminado.",
            ], 404);
        }
    }
    //Metodo para subir archivo sin eliminar el anterior
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
            'ruc' => 'required|alpha_dash',
            'old_filename' => 'nullable|string',
        ]);

        try {
            $ruc = basename($request->ruc);
            $file = $request->file('file');

            if (!$file->isValid()) {
                throw new \Exception("Archivo inválido o corrupto.");
            }

            // --- SE ELIMINÓ EL BLOQUE QUE BORRABA EL ARCHIVO VIEJO ---
            // Ambos archivos (el viejo y el nuevo) ahora se mantendrán en el servidor.

            // Crear carpeta si no existe
            $directory = public_path("Documentos/INVESTIGACIÓN/Archivos_Empresas/{$ruc}");

            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            // Generar nombre: RUC + _ + aleatorio + _ + fecha (Ymd_His)
            $aleatorio = bin2hex(random_bytes(8)); // 16 caracteres hex
            $fechaHora = date("Ymd_His");          // Ej: 20260602_1741
            $extension = $file->getClientOriginalExtension(); // pdf

            $filename = "{$ruc}_{$aleatorio}_{$fechaHora}.{$extension}";

            // Guardar archivo
            $file->move($directory, $filename);

            // URL pública
            $url = url('Documentos/INVESTIGACIÓN/Archivos_Empresas/' . $ruc . '/' . $filename);

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
    public function getFotografia($ci)
    {
        try {
            $empresa = Praempresa::where('idempresa', $ci)
                ->select('imagen')
                ->first();

            if (!$empresa || empty($empresa->imagen)) {
                return response()->json(['error' => 'Fotografía no encontrada.'], 404);
            }

            $fotoBinaria = $empresa->imagen;

            // ✅ CORRECCIÓN CLAVE: Si la base de datos retorna un recurso stream para el BLOB, lo leemos
            if (is_resource($fotoBinaria)) {
                $fotoBinaria = stream_get_contents($fotoBinaria);
            }

            $mime = 'image/jpeg'; // Por defecto

            if (extension_loaded('fileinfo')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = finfo_buffer($finfo, $fotoBinaria);
                finfo_close($finfo);

                if ($detectedMime && strpos($detectedMime, 'image') === 0) {
                    $mime = $detectedMime;
                }
            }

            // Devolver la respuesta binaria limpia (STREAM)
            return Response::make($fotoBinaria, 200)
                ->header('Content-Type', $mime)
                ->header('Content-Disposition', 'inline; filename="foto_' . $ci . '"');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la fotografía: ' . $e->getMessage()], 500);
        }
    }
    public function getPais(){
        $paises = Pais::all();
        return response()->json($paises);
    }
}
