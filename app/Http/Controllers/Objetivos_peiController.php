<?php

namespace App\Http\Controllers;

use App\Models\Objetivos_pei;
use App\Models\Subsistemas_pei;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class Objetivos_peiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Objetivos_pei::select(
                'objetivos_pei.*'
            );

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
        // 1. Obtener el id_pei al que pertenece el subsistema seleccionado
        $subsistema = Subsistemas_pei::find($request->id_sub_sistema_pei);

        if (!$subsistema) {
            return response()->json(['error' => true, 'mensaje' => 'Subsistema no válido'], 404);
        }

        $id_pei = $subsistema->id_pei;

        // 2. Verificar si el código ya existe en objetivos que pertenecen al MISMO PEI
        $existe = Objetivos_pei::where('cod_obj', $request->cod_obj)
            ->whereHas('subsistemas_pei', function ($query) use ($id_pei) {
                $query->where('id_pei', $id_pei);
            })->exists();

        if ($existe) {
            return response()->json([
                'error' => true,
                'mensaje' => "El código {$request->cod_obj} ya está registrado en este PEI."
            ], 409);
        }

        $res = Objetivos_pei::create($request->all());

        return response()->json([
            'data' => $res,
            'mensaje' => 'Agregado con Éxito!!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}
    public function listarPorPei($id_pei)
    {
        // Buscamos los objetivos cuyo subsistema pertenezca al PEI enviado
        $objetivos = Objetivos_pei::whereHas('subsistemas_pei', function ($query) use ($id_pei) {
            $query->where('id_pei', $id_pei);
        })
            ->with('subsistemas_pei') // Cargamos el nombre del subsistema para mostrarlo en la tabla
            ->orderBy('cod_obj', 'asc')
            ->get();

        return response()->json($objetivos);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $res = Objetivos_pei::find($id);

        if (isset($res)) {
            // 1. Obtener el PEI a través del subsistema (por si cambiaron de subsistema en el edit)
            $subsistema = Subsistemas_pei::find($request->id_sub_sistema_pei);
            $id_pei = $subsistema->id_pei;

            // 2. Validar código duplicado en el mismo PEI, excluyendo el registro actual
            $existe = Objetivos_pei::where('cod_obj', $request->cod_obj)
                ->where('id_obj_pei', '!=', $id) // Excluir el actual
                ->whereHas('subsistemas_pei', function ($query) use ($id_pei) {
                    $query->where('id_pei', $id_pei);
                })->exists();

            if ($existe) {
                return response()->json([
                    'error' => true,
                    'mensaje' => "El código {$request->cod_obj} ya pertenece a otro objetivo de este PEI."
                ], 409);
            }

            $res->id_sub_sistema_pei = $request->id_sub_sistema_pei;
            $res->cod_obj = $request->cod_obj;
            $res->detalle_obj = $request->detalle_obj;

            if ($res->save()) {
                return response()->json([
                    'data' => $res,
                    'mensaje' => "Actualizado con Éxito!!",
                ]);
            }

            return response()->json(['error' => true, 'mensaje' => 'Error al Actualizar'], 500);
        }

        return response()->json(['error' => true, 'mensaje' => "El objetivo con id: $id no Existe"], 404);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $res = Objetivos_pei::find($id);
        if (isset($res)) {
            $res->delete();
            $data = $res->toArray();
            if ($data) {

                return response()->json([
                    'data' => $data,
                    'mensaje' => "Eliminado con Éxito!!",
                ]);
            } else {
                return response()->json([
                    'data' => $data,
                    'mensaje' => "El objetivo no existe (puede que ya la haya eliminado)",
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "El objetivo con id: $id no Existe",
            ]);
        }
    }
}
