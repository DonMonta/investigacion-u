<?php

namespace App\Http\Controllers;

use App\Models\SubareaUnesco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SubAreaUnescoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = SubareaUnesco::select('subarea_unesco.*');

            // Función anónima (Closure) para transformar cada registro dinámicamente
            $transformarRegistro = function ($item) {
                $id = trim($item->sau_id);
                $pdid = trim($item->sau_pdid ?? '');
                
                // 1. Reparar la codificación (Corrige problemas como "GenÃ©ricos" -> "Genéricos")
                $descripcion = mb_convert_encoding($item->sau_descripcion, 'ISO-8859-1', 'UTF-8');
                
                // Si la BD ya retorna bien en algunos casos, puedes dejar solo un trim() o usar:
                // $descripcion = mb_detect_encoding($descripcion, 'UTF-8', true) ? $descripcion : utf8_encode($descripcion);

                // 2. Limpiar la descripción: Quitar la numeración (el ID) del inicio
                // Busca exactamente el ID al inicio de la cadena seguido de espacios y lo elimina
                $descripcion_limpia = preg_replace('/^' . preg_quote($id, '/') . '\s*/', '', $descripcion);

                // 3. Determinar el Nivel/Tipo (Área, Subárea, Área Específica)
                $longitud_id = strlen($id);
                $tipo = 'Desconocido';

                // Lógica de clasificación basada en la longitud del ID y si tiene padre (pdid)
                if (empty($pdid) && $longitud_id === 2) {
                    $tipo = 'Área de conocimiento';
                } elseif (strlen($pdid) === 2 || $longitud_id === 3) {
                    $tipo = 'Subárea de conocimiento';
                } elseif (strlen($pdid) >= 3 || $longitud_id >= 4) {
                    $tipo = 'Área específica de conocimiento';
                }

                // 4. Retornar el objeto con la estructura limpia
                return [
                    'sau_id'          => $id,
                    'sau_pdid'        => $pdid,
                    'sau_descripcion' => trim($descripcion_limpia), // Descripción sin número
                    'tipo_area'       => $tipo // Nuevo campo identificador
                ];
            };

            // Caso: Obtener todos los registros sin paginar
            if ($request->has('all') && $request->all === 'true') {
                // Obtenemos los datos y usamos map() para aplicar la transformación
                $data = $query->get()->map($transformarRegistro);

                return response()->json(['data' => $data]);
            }

            // Caso: Paginación (CORREGIDO: Se debe usar paginate(), no get())
            $perPage = $request->input('per_page', 15); // Permite cambiar la cantidad, por defecto 15
            $data = $query->paginate($perPage);

            if ($data->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'message' => 'No se encontraron datos'
                ], 200);
            }

            // Transformar solo los items de la página actual
            $itemsTransformados = collect($data->items())->map($transformarRegistro);

            return response()->json([
                'data'         => $itemsTransformados,
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'last_page'    => $data->lastPage(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar los datos a JSON: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
