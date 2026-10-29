<?php

namespace App\Http\Controllers;

use App\Models\User;;

use App\Models\Carreras;
use App\Models\InformacionPersonalD;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirectorCarrerasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = User::select(
                'usuario.*',
                'perfil.nombperfil as perfil_nombre',
                'carrera.NombCarr as carrera_nombre',
                'facultad.siglas as facultad_siglas',
                'informacionpersonal_d.fotografia',
                'informacionpersonal_d.NombInfPer',
                'informacionpersonal_d.ApellInfPer',
                'informacionpersonal_d.ApellMatInfPer'
            )
                ->join('perfil', 'perfil.idperfil', '=', 'usuario.idperfil')
                ->join('carrera', 'carrera.idCarr', '=', 'usuario.idcarr')
                ->leftJoin('facultad', 'facultad.idfacultad', '=', 'carrera.idfacultad')
                ->join('informacionpersonal_d', 'informacionpersonal_d.CIInfPer', '=', 'usuario.ciinfper')
                ->where('usuario.StatusUsu', '=', 1)
                ->where('usuario.idperfil', '=', 'coord')
                ->where('perfil.status', '=', 1)
                ->where('carrera.StatusCarr', '=', 1);

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function obtnercarreraindv(string $id)
    {
        try {
            $query = Carreras::select('idCarr', 'NombCarr', 'titulo', 'director', 'idfacultad')
                ->where('StatusCarr', 1)
                ->where('idCarr', $id)
                ->with('facultades')
                ->get();
            if ($query->isEmpty()) {
                return response()->json(['error' => 'No se encontraron datos'], 404);
            }
            $resultado = $query->first();
            $nombreCarreraRaw = trim($resultado->NombCarr);
            // 1. Quitamos el guión y los números/año del final (ej: " - 2020")
            $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarreraRaw);

            // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
            $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
            return response()->json([
                'data' => $resultado,
                'nombre_carrera' => $nombreCarreraFinal,
                'mensaje' => 'Encontrado con Éxito!!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al codificar los datos a JSON: ' . $e->getMessage()], 500);
        }
    }

    public function ObtCarrerasFacultad(string $idfacultad)
    {
        try {
            $carreras = Carreras::select('idCarr', 'NombCarr', 'titulo', 'director', 'idfacultad')
                ->whereNotNull('codihicenter')
                ->where('StatusCarr', 1)
                ->where('idfacultad', $idfacultad)
                ->whereNotIn('idCarr', ['7109']) // <-- Filtro recuperado
                ->where('NombCarr', 'NOT LIKE', '%TRABAJO DE INTEGRACIÓN CURRICULAR%') // <-- Filtro recuperado
                ->with('facultades')
                ->get();
            $resultado = [];

            // Preparamos las estructuras SQL normalizadas de las columnas
            $sqlNomb = $this->construirSqlNormalizado('NombInfPer');
            $sqlApell = $this->construirSqlNormalizado('ApellInfPer');
            $sqlApellMat = $this->construirSqlNormalizado('ApellMatInfPer');

            foreach ($carreras as $carrera) {
                $tituloRaw = trim($carrera->titulo);
                $directorRaw = trim($carrera->director);
                $nombreCarreraRaw = trim($carrera->NombCarr); // <-- Nombre original de la carrera

                if (empty($directorRaw)) {
                    continue;
                }

                // =================================================================
                // NUEVO: PROCESAMIENTO Y FORMATEO DEL NOMBRE DE LA CARRERA
                // =================================================================
                // 1. Quitamos el guión y los números/año del final (ej: " - 2020")
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarreraRaw);

                // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
                $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
                // =================================================================

                // 2. APLICACIÓN DE CASOS (Validación de Título y Director)
                $directorFinal = $directorRaw;

                if (!empty($tituloRaw)) {
                    $tituloBusqueda = rtrim($tituloRaw, '.');

                    if (!str_contains(mb_strtolower($directorRaw), mb_strtolower($tituloBusqueda))) {
                        $directorFinal = $tituloRaw . ' ' . $directorRaw;
                    }
                }

                // 3. LIMPIEZA ULTRA-COMPLETA DE CARACTERES (Soporta múltiples títulos consecutivos)
                $nombreLimpio = preg_replace('/^((abg|ing|lic|lcdo|lcda|econ|dr|dra|prof|mgt|mgs|msc|mag|mstr|master|phd)\.?\s*)+/i', '', $directorRaw);
                $nombreLimpio = preg_replace('/,?\s*(m\.?sc|msc|ph\.?d|phd|mgtr|mag|ing|lic)\.?$/i', '', $nombreLimpio);
                $nombreLimpio = trim($nombreLimpio);

                // 4. BÚSQUEDA ADAPTATIVA EN CASCADA
                $cedulaEncontrada = null;
                $cargoFormateado = 'Director(a)';

                if (!empty($nombreLimpio)) {
                    $palabras = array_filter(explode(' ', $nombreLimpio), function ($palabra) {
                        return strlen(trim($palabra)) > 2;
                    });

                    if (!empty($palabras)) {
                        $palabrasArray = array_values($palabras);
                        $persona = null;

                        // --- NIVEL 1: Coincidencia Estricta ---
                        $queryStrict = InformacionPersonalD::select('CIInfPer', 'GeneroPer');
                        foreach ($palabrasArray as $palabra) {
                            $palabraPatron = $this->limpiarTextoCadena($palabra);
                            $queryStrict->where(function ($q) use ($sqlNomb, $sqlApell, $sqlApellMat, $palabraPatron) {
                                $q->whereRaw("{$sqlNomb} LIKE ?", ["%{$palabraPatron}%"])
                                    ->orWhereRaw("{$sqlApell} LIKE ?", ["%{$palabraPatron}%"])
                                    ->orWhereRaw("{$sqlApellMat} LIKE ?", ["%{$palabraPatron}%"]);
                            });
                        }
                        $persona = $queryStrict->first();

                        // --- NIVEL 2: Coincidencia Flexible ---
                        if (!$persona && count($palabrasArray) >= 3) {
                            $primerNombre = $this->limpiarTextoCadena($palabrasArray[0]);
                            $primerApellido = $this->limpiarTextoCadena(count($palabrasArray) == 4 ? $palabrasArray[2] : $palabrasArray[1]);

                            $queryFlexible = InformacionPersonalD::select('CIInfPer', 'GeneroPer')
                                ->where(function ($q) use ($sqlNomb, $primerNombre) {
                                    $q->whereRaw("{$sqlNomb} LIKE ?", ["%{$primerNombre}%"]);
                                })
                                ->where(function ($q) use ($sqlApell, $sqlApellMat, $primerApellido) {
                                    $q->whereRaw("{$sqlApell} LIKE ?", ["%{$primerApellido}%"])
                                        ->orWhereRaw("{$sqlApellMat} LIKE ?", ["%{$primerApellido}%"]);
                                });

                            $persona = $queryFlexible->first();
                        }

                        // --- NIVEL 3: Coincidencia por Apellido Principal ---
                        if (!$persona && count($palabrasArray) >= 2) {
                            $primerNombre = $this->limpiarTextoCadena($palabrasArray[0]);
                            $apellidoProbable = $this->limpiarTextoCadena(end($palabrasArray));

                            $queryUltimoRecurso = InformacionPersonalD::select('CIInfPer', 'GeneroPer')
                                ->whereRaw("{$sqlNomb} LIKE ?", ["%{$primerNombre}%"])
                                ->where(function ($q) use ($sqlApell, $sqlApellMat, $apellidoProbable) {
                                    $q->whereRaw("{$sqlApell} LIKE ?", ["%{$apellidoProbable}%"])
                                        ->orWhereRaw("{$sqlApellMat} LIKE ?", ["%{$apellidoProbable}%"]);
                                });

                            $persona = $queryUltimoRecurso->first();
                        }

                        if ($persona) {
                            $cedulaEncontrada = $persona->CIInfPer;
                            // Evaluamos el género y asignamos el cargo
                            $genero = strtoupper(trim($persona->GeneroPer));
                            if ($genero === 'F') {
                                $cargoFormateado = 'Directora';
                            } elseif ($genero === 'M') {
                                $cargoFormateado = 'Director';
                            }
                        } else {
                            $cedulaEncontrada = 'NO ENCONTRADO';
                        }
                    }
                }

                // 5. OBTENER DATOS DE LA FACULTAD
                $facultadAsociada = $carrera->facultades->first();

                // 6. ESTRUCTURAR RESPUESTA INDIVIDUAL
                $resultado[] = [
                    'id_carrera'        => $carrera->idCarr,
                    'nombre_carrera'    => $nombreCarreraFinal, // <-- Enviamos el nombre formateado
                    'director_original' => $directorRaw,
                    'director_procesado' => $directorFinal,
                    'cedula_director'   => $cedulaEncontrada,
                    'cargo_genero'      => $cargoFormateado,
                    'facultad' => $facultadAsociada ? [
                        'id'     => $facultadAsociada->idfacultad,
                        'nombre' => $facultadAsociada->facultad,
                        'siglas' => $facultadAsociada->siglas
                    ] : null
                ];
            }
            return response()->json([
                'success' => true,
                'data'    => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al procesar el mapeo de directores de carreras',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Obtiene las carreras, normaliza el nombre del director según su título
     * y busca su Cédula de Identidad mediante coincidencia de texto.
     */
    public function getCarrerasDirectores(Request $request): JsonResponse
    {
        try {
            // 1. Ejecutar la consulta base con relaciones de facultad
            // NOTA: Como 'codihicenter' vino incompleto en tu enunciado, lo dejamos listo
            // para que reciba el parámetro o lo adaptes a la columna correspondiente.
            $carreras = Carreras::select('idCarr', 'NombCarr', 'titulo', 'director', 'idfacultad')
                ->distinct()
                ->whereNotNull('codihicenter') // Descomenta y adapta según tu columna real
                ->where('StatusCarr', 1)
                ->with('facultades')
                ->get();

            $resultado = [];

            // Preparamos las estructuras SQL normalizadas de las columnas
            $sqlNomb = $this->construirSqlNormalizado('NombInfPer');
            $sqlApell = $this->construirSqlNormalizado('ApellInfPer');
            $sqlApellMat = $this->construirSqlNormalizado('ApellMatInfPer');

            foreach ($carreras as $carrera) {
                $tituloRaw = trim($carrera->titulo);
                $directorRaw = trim($carrera->director);
                $nombreCarreraRaw = trim($carrera->NombCarr); // <-- Nombre original de la carrera

                if (empty($directorRaw)) {
                    continue;
                }

                // =================================================================
                // NUEVO: PROCESAMIENTO Y FORMATEO DEL NOMBRE DE LA CARRERA
                // =================================================================
                // 1. Quitamos el guión y los números/año del final (ej: " - 2020")
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarreraRaw);

                // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
                $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
                // =================================================================

                // 2. APLICACIÓN DE CASOS (Validación de Título y Director)
                $directorFinal = $directorRaw;

                if (!empty($tituloRaw)) {
                    $tituloBusqueda = rtrim($tituloRaw, '.');

                    if (!str_contains(mb_strtolower($directorRaw), mb_strtolower($tituloBusqueda))) {
                        $directorFinal = $tituloRaw . ' ' . $directorRaw;
                    }
                }

                // 3. LIMPIEZA ULTRA-COMPLETA DE CARACTERES (Soporta múltiples títulos consecutivos)
                $nombreLimpio = preg_replace('/^((abg|ing|lic|lcdo|lcda|econ|dr|dra|prof|mgt|mgs|msc|mag|mstr|master|phd)\.?\s*)+/i', '', $directorRaw);
                $nombreLimpio = preg_replace('/,?\s*(m\.?sc|msc|ph\.?d|phd|mgtr|mag|ing|lic)\.?$/i', '', $nombreLimpio);
                $nombreLimpio = trim($nombreLimpio);

                // 4. BÚSQUEDA ADAPTATIVA EN CASCADA
                $cedulaEncontrada = null;
                $cargoFormateado = 'Director(a)';

                if (!empty($nombreLimpio)) {
                    $palabras = array_filter(explode(' ', $nombreLimpio), function ($palabra) {
                        return strlen(trim($palabra)) > 2;
                    });

                    if (!empty($palabras)) {
                        $palabrasArray = array_values($palabras);
                        $persona = null;

                        // --- NIVEL 1: Coincidencia Estricta ---
                        $queryStrict = InformacionPersonalD::select('CIInfPer', 'GeneroPer');
                        foreach ($palabrasArray as $palabra) {
                            $palabraPatron = $this->limpiarTextoCadena($palabra);
                            $queryStrict->where(function ($q) use ($sqlNomb, $sqlApell, $sqlApellMat, $palabraPatron) {
                                $q->whereRaw("{$sqlNomb} LIKE ?", ["%{$palabraPatron}%"])
                                    ->orWhereRaw("{$sqlApell} LIKE ?", ["%{$palabraPatron}%"])
                                    ->orWhereRaw("{$sqlApellMat} LIKE ?", ["%{$palabraPatron}%"]);
                            });
                        }
                        $persona = $queryStrict->first();

                        // --- NIVEL 2: Coincidencia Flexible ---
                        if (!$persona && count($palabrasArray) >= 3) {
                            $primerNombre = $this->limpiarTextoCadena($palabrasArray[0]);
                            $primerApellido = $this->limpiarTextoCadena(count($palabrasArray) == 4 ? $palabrasArray[2] : $palabrasArray[1]);

                            $queryFlexible = InformacionPersonalD::select('CIInfPer', 'GeneroPer')
                                ->where(function ($q) use ($sqlNomb, $primerNombre) {
                                    $q->whereRaw("{$sqlNomb} LIKE ?", ["%{$primerNombre}%"]);
                                })
                                ->where(function ($q) use ($sqlApell, $sqlApellMat, $primerApellido) {
                                    $q->whereRaw("{$sqlApell} LIKE ?", ["%{$primerApellido}%"])
                                        ->orWhereRaw("{$sqlApellMat} LIKE ?", ["%{$primerApellido}%"]);
                                });

                            $persona = $queryFlexible->first();
                        }

                        // --- NIVEL 3: Coincidencia por Apellido Principal ---
                        if (!$persona && count($palabrasArray) >= 2) {
                            $primerNombre = $this->limpiarTextoCadena($palabrasArray[0]);
                            $apellidoProbable = $this->limpiarTextoCadena(end($palabrasArray));

                            $queryUltimoRecurso = InformacionPersonalD::select('CIInfPer', 'GeneroPer')
                                ->whereRaw("{$sqlNomb} LIKE ?", ["%{$primerNombre}%"])
                                ->where(function ($q) use ($sqlApell, $sqlApellMat, $apellidoProbable) {
                                    $q->whereRaw("{$sqlApell} LIKE ?", ["%{$apellidoProbable}%"])
                                        ->orWhereRaw("{$sqlApellMat} LIKE ?", ["%{$apellidoProbable}%"]);
                                });

                            $persona = $queryUltimoRecurso->first();
                        }

                        if ($persona) {
                            $cedulaEncontrada = $persona->CIInfPer;
                            // Evaluamos el género y asignamos el cargo
                            $genero = strtoupper(trim($persona->GeneroPer));
                            if ($genero === 'F') {
                                $cargoFormateado = 'Directora';
                            } elseif ($genero === 'M') {
                                $cargoFormateado = 'Director';
                            }
                        } else {
                            $cedulaEncontrada = 'NO ENCONTRADO';
                        }
                    }
                }

                // 5. OBTENER DATOS DE LA FACULTAD
                $facultadAsociada = $carrera->facultades->first();

                // 6. ESTRUCTURAR RESPUESTA INDIVIDUAL
                $resultado[] = [
                    'id_carrera'        => $carrera->idCarr,
                    'nombre_carrera'    => $nombreCarreraFinal, // <-- Enviamos el nombre formateado
                    'director_original' => $directorRaw,
                    'director_procesado' => $directorFinal,
                    'cedula_director'   => $cedulaEncontrada,
                    'cargo_genero'      => $cargoFormateado,
                    'facultad' => $facultadAsociada ? [
                        'id'     => $facultadAsociada->idfacultad,
                        'nombre' => $facultadAsociada->facultad,
                        'siglas' => $facultadAsociada->siglas
                    ] : null
                ];
            }

            return response()->json([
                'success' => true,
                'data'    => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al procesar el mapeo de directores de carreras',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Helper PHP: Convierte un texto a minúsculas, remueve tildes, diéresis y cambia Ñ por N.
     */
    private function limpiarTextoCadena(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $mapaREPLACE = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
            'Á' => 'a',
            'É' => 'e',
            'Í' => 'i',
            'Ó' => 'o',
            'Ú' => 'u',
            'Ü' => 'u',
            'Ñ' => 'n'
        ];
        return str_replace(array_keys($mapaREPLACE), array_values($mapaREPLACE), $texto);
    }

    /**
     * Helper SQL: Construye una cadena anidada de funciones REPLACE de MySQL 
     * para limpiar dinámicamente las columnas de la base de datos en tiempo de ejecución.
     */
    private function construirSqlNormalizado(string $columna): string
    {
        $mapaREPLACE = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
            'Á' => 'a',
            'É' => 'e',
            'Í' => 'i',
            'Ó' => 'o',
            'Ú' => 'u',
            'Ü' => 'u',
            'Ñ' => 'n'
        ];

        $sql = "LOWER(`{$columna}`)";
        foreach ($mapaREPLACE as $buscar => $reemplazo) {
            $sql = "REPLACE({$sql}, '{$buscar}', '{$reemplazo}')";
        }

        return $sql;
    }
}
