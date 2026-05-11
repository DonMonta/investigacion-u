<?php

namespace App\Http\Controllers;

use App\Models\Politicas_plandne;
use App\Models\Obj_pol_plandne;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class Politicas_plandneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Politicas_plandne::select(
                'politicas_plandne.*'
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
        $objetivopolplandne = Obj_pol_plandne::find($request->id_obj_pol_pladne);

        if (!$objetivopolplandne) {
            return response()->json(['error' => true, 'mensaje' => 'Política no válida'], 404);
        }

        $id_pladne = $objetivopolplandne->id_pladne;

        // 2. Verificar si el código ya existe en objetivos que pertenecen al MISMO PEI
        $existe = Politicas_plandne::where('cod_pol', $request->cod_pol)
            ->whereHas('objetivos_plandne', function ($query) use ($id_pladne) {
                $query->where('id_pladne', $id_pladne);
            })->exists();

        if ($existe) {
            return response()->json([
                'error' => true,
                'mensaje' => "El código {$request->cod_pol} ya está registrado en este PLANDE."
            ], 409);
        }

        $res = Politicas_plandne::create($request->all());

        return response()->json([
            'data' => $res,
            'mensaje' => 'Agregado con Éxito!!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}
    public function listarPorPlandne($id_pladne)
    {
        // Buscamos los objetivos cuyo subsistema pertenezca al PEI enviado
        $objetivos = Politicas_plandne::whereHas('objetivos_plandne', function ($query) use ($id_pladne) {
            $query->where('id_pladne', $id_pladne);
        })
            ->with('objetivos_plandne') // Cargamos el nombre del subsistema para mostrarlo en la tabla
            ->orderBy('cod_pol', 'asc')
            ->get();

        return response()->json($objetivos);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $res = Politicas_plandne::find($id);

        if (isset($res)) {
            // 1. Obtener el PEI a través del subsistema (por si cambiaron de subsistema en el edit)
            $objetivopolplandne = Obj_pol_plandne::find($request->id_obj_pol_pladne);
            $id_pladne = $objetivopolplandne->id_pladne;

            // 2. Validar código duplicado en el mismo PEI, excluyendo el registro actual
            $existe = Politicas_plandne::where('cod_pol', $request->cod_pol)
                ->where('id_pol_pladne', '!=', $id) // Excluir el actual
                ->whereHas('objetivos_plandne', function ($query) use ($id_pladne) {
                    $query->where('id_pladne', $id_pladne);
                })->exists();

            if ($existe) {
                return response()->json([
                    'error' => true,
                    'mensaje' => "El código {$request->cod_pol} ya pertenece a otra política de este PLANDE."
                ], 409);
            }

            $res->id_obj_pol_pladne = $request->id_obj_pol_pladne;
            $res->cod_pol = $request->cod_pol;
            $res->detalle_pol = $request->detalle_pol;

            if ($res->save()) {
                return response()->json([
                    'data' => $res,
                    'mensaje' => "Actualizado con Éxito!!",
                ]);
            }

            return response()->json(['error' => true, 'mensaje' => 'Error al Actualizar'], 500);
        }

        return response()->json(['error' => true, 'mensaje' => "La politica con id: $id no Existe"], 404);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $res = Politicas_plandne::find($id);
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
                    'mensaje' => "La politica no existe (puede que ya la haya eliminado)",
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "La politica con id: $id no Existe",
            ]);
        }
    }
}
