<?php

namespace App\Http\Controllers;

use App\Models\Obj_pol_plandne;
use App\Models\Plandne;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class Obj_pol_plandneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Obj_pol_plandne::select(
                'obj_pol_plandne.*'
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
        // Validar si el PLADNE existe
        $plan = Plandne::find($request->id_pladne);
        if (!$plan) {
            return response()->json(['error' => true, 'mensaje' => 'PLADNE no válido'], 404);
        }

        // Verificar duplicado en el mismo plan
        $existe = Obj_pol_plandne::where('cod_obj_pol', $request->cod_obj_pol)
            ->where('id_pladne', $request->id_pladne)
            ->exists();

        if ($existe) {
            return response()->json([
                'error' => true,
                'mensaje' => "El código {$request->cod_obj_pol} ya está registrado en este PLADNE."
            ], 409);
        }

        $res = Obj_pol_plandne::create($request->all());
        return response()->json(['data' => $res, 'mensaje' => 'Agregado con Éxito!!']);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $res = Obj_pol_plandne::select(
            'obj_pol_plandne.*'
        )->where('id_pladne', $id)
            ->get();
        if ($res->isEmpty()) {
            return response()->json([
                'data' => [],
                'mensaje' => "El objeto de política con id: $id no Existe",
            ], 404);
        } else {
            return response()->json([
                'data' => $res,
                'mensaje' => "Objeto de política encontrado con id: $id",
            ], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $res = Obj_pol_plandne::find($id);

        if (isset($res)) {
            // 1. Obtener el id_pladne del request (o del registro actual si no viene en el request)
            $id_pladne = $request->id_pladne ?? $res->id_pladne;

            // 2. Validar código duplicado dentro del mismo PLADNE, excluyendo el ID actual
            $existe = Obj_pol_plandne::where('cod_obj_pol', $request->cod_obj_pol)
                ->where('id_obj_pol_pladne', '!=', $id) // Excluir el registro actual
                ->where('id_pladne', $id_pladne)        // Filtrar por el mismo PLADNE
                ->exists();

            if ($existe) {
                return response()->json([
                    'error' => true,
                    'mensaje' => "El código {$request->cod_obj_pol} ya está registrado en este PLADNE."
                ], 409);
            }

            // 3. Asignar valores
            $res->id_pladne = $id_pladne;
            $res->cod_obj_pol = $request->cod_obj_pol;
            $res->detalle_obj_pol = $request->detalle_obj_pol;

            if ($res->save()) {
                return response()->json([
                    'data' => $res,
                    'mensaje' => "Actualizado con Éxito!!",
                ]);
            }

            return response()->json(['error' => true, 'mensaje' => 'Error al Actualizar'], 500);
        }

        return response()->json(['error' => true, 'mensaje' => "El objeto de política con id: $id no Existe"], 404);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $res = Obj_pol_plandne::find($id);
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
                    'mensaje' => "El objeto de política no existe (puede que ya la haya eliminado)",
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "El objeto de política con id: $id no Existe",
            ]);
        }
    }
}
