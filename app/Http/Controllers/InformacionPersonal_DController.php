<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InformacionPersonalD;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class InformacionPersonal_DController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Aplica paginación al resultado del filtro
        $data = InformacionPersonalD::select('informacionpersonal_d.*')
            ->where('informacionpersonal_d.CIInfPer', $id)
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
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function getFotografia($ci)
    {
        try {
            // 1. Obtener SÓLO la columna 'fotografia' para el CI específico
            $persona = InformacionPersonalD::where('CIInfPer', $ci)
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
    // Metodo para buscar un docente por su cédula sin fotografía
    public function getDocente(Request $request)
    {
        $cedula = $request->cedula;
        $docente = InformacionPersonalD::select(
            'informacionpersonal_d.CIInfPer',
            'informacionpersonal_d.ApellInfPer',
            'informacionpersonal_d.ApellMatInfPer',
            'informacionpersonal_d.NombInfPer'
        )
            ->where('CIInfPer', $cedula)
            ->where('StatusPer', 1)
            ->first();
        if (! $docente) {
            return response()->json(['message' => 'Docente no encontrado en la base de datos institucional.'], 404);
        }

        return response()->json([
            'data' => $docente,
            'mensaje' => 'Encontrado con Éxito!!',
        ]);
    }
    public function getAllDocente(Request $request)
    {
        $cedula = $request->cedula;

        // Agregamos el with(['titulos.nivel']) para Eager Loading y evitar el problema N+1
        // Traemos también GeneroPer ya que lo necesitamos para validar si es Sra/Sr Lcda/Lcdo
        $docente = InformacionPersonalD::with(['titulos.nivel'])->select(
            'informacionpersonal_d.CIInfPer',
            'informacionpersonal_d.ApellInfPer',
            'informacionpersonal_d.ApellMatInfPer',
            'informacionpersonal_d.NombInfPer',
            'informacionpersonal_d.GeneroPer'
        )
            ->where('CIInfPer', $cedula)
            ->first();

        if (! $docente) {
            return response()->json(['message' => 'Docente no encontrado en la base de datos institucional.'], 404);
        }

        // Lógica para Títulos y Formateo de Nombres
        $esMujer = strtoupper(trim($docente->GeneroPer)) === 'F';

        // Buscar Tercer Nivel
        $tituloGrado = $docente->titulos->first(function ($titulo) {
            return optional($titulo->nivel)->nv_numnivel == 3;
        });

        // Buscar Cuarto Nivel
        $tituloPosgrado = $docente->titulos->first(function ($titulo) {
            return optional($titulo->nivel)->nv_numnivel == 4;
        });

        $prefijoNombre = '';
        $sufijoNombre = '';

        // A. Mapeo de PREFIJOS (3er Nivel)
        if ($tituloGrado) {
            $textoGrado = mb_strtolower($tituloGrado->ad_titulo);

            if (str_contains($textoGrado, 'licencia')) {
                $prefijoNombre = $esMujer ? 'Lcda.' : 'Lcdo.';
            } elseif (
                str_contains($textoGrado, 'ingenier') || str_contains($textoGrado, 'ing.') ||
                str_contains($textoGrado, 'ingeniería') || str_contains($textoGrado, 'tecnolog')
            ) {
                $prefijoNombre = 'Ing.';
            } elseif (str_contains($textoGrado, 'econom') || str_contains($textoGrado, 'econ.')) {
                $prefijoNombre = 'Econ.';
            } elseif (str_contains($textoGrado, 'abogad') || str_contains($textoGrado, 'abg.')) {
                $prefijoNombre = 'Abg.';
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

        // C. Formatear Nombres y Apellidos (Title Case)
        $nombreRaw = trim($docente->NombInfPer . ' ' . $docente->ApellInfPer . ' ' . $docente->ApellMatInfPer);
        $nombresFormateados = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

        // Asignar el nombre completo a un nuevo atributo virtual en la respuesta JSON
        $docente->nombre_formateado = trim("{$prefijoNombre} {$nombresFormateados}{$sufijoNombre}");

        // Ocultamos la relación títulos para no saturar la respuesta JSON del frontend, ya sacamos lo que necesitábamos.
        $docente->makeHidden(['titulos']);

        return response()->json([
            'data' => $docente,
            'mensaje' => 'Encontrado con Éxito!!',
        ]);
    }
}
