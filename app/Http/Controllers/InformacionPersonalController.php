<?php

namespace App\Http\Controllers;

use App\Models\curso_capacitacion;
use App\Models\informacionpersonal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;


class InformacionPersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }
    public function checkUpdateStatus(Request $request, $CIInfPer)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Aplica paginación al resultado del filtro
        $data = informacionpersonal::select('informacionpersonal.*')
            ->where('informacionpersonal.CIInfPer', $id)
            ->paginate(20);
        if ($data->isEmpty()) {
            return response()->json(['error' => 'No se encontraron datos para el ID especificado'], 404);
        }

        // Convertir los campos a UTF-8 válido para cada página
        $data->getCollection()->transform(function ($item) {
            $attributes = $item->getAttributes();

            foreach ($attributes as $key => $value) {
                if (in_array($key, ['fotografia']) && !empty($value)) {
                    // ✅ Convertir BLOB a base64
                    $attributes[$key] = base64_encode($value);
                } elseif (is_string($value) && !in_array($key, ['fotografia'])) {
                    $attributes[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            }

            return $attributes;
        });

        // Retornar la respuesta JSON con los metadatos de paginación
        try {
            return response()->json([
                'data' => $data->items(),
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al codificar los datos a JSON: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }
    public function getFotografia($ci)
    {
        try {
            // 1. Obtener SÓLO la columna 'fotografia' para el CI específico
            $persona = informacionpersonal::where('CIInfPer', $ci)
                ->select('fotografia')
                ->first();

            // 2. Verificar si el usuario existe y si tiene foto
            if (!$persona || empty($persona->fotografia)) {
                // Devolver una respuesta HTTP 404 o una foto predeterminada pequeña
                return response()->json(['error' => 'Fotografía no encontrada.'], 404);
            }

            $fotoBinaria = $persona->fotografia;

            // 3. Determinar el MIME type (es un paso crítico, asume que es JPEG/PNG si no tienes metadata)
            // Opcional: Si almacenas el MIME type en la DB, úsalo aquí. Si no, usa finfo para detectarlo (esto es más seguro).
            $mime = 'image/jpeg'; // MIME type por defecto

            // Intenta determinar el MIME type si el ambiente lo permite y no satura
            if (extension_loaded('fileinfo')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = finfo_buffer($finfo, $fotoBinaria);
                finfo_close($finfo);

                if ($detectedMime && strpos($detectedMime, 'image') === 0) {
                    $mime = $detectedMime;
                }
            }

            // 4. Devolver la imagen como una respuesta binaria (STREAM)
            // Esto evita convertir el BLOB entero a Base64 en el servidor, lo que previene la saturación de memoria.
            return Response::make($fotoBinaria, 200)
                ->header('Content-Type', $mime)
                ->header('Content-Disposition', 'inline; filename="foto_' . $ci . '"');
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json(['error' => 'Error al obtener la fotografía: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

}
