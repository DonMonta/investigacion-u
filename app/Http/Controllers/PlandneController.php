<?php

namespace App\Http\Controllers;

use App\Models\Plandne;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class PlandneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Usamos withCount para obtener los totales de las relaciones
            $query = Plandne::withCount(['objetivos_plandne', 'politicas_plandne']);

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
        if ($request->estado_plandne == 1) {
            // Verificamos si ya existe AL MENOS UNO activo en la base de datos
            $existeActivo = Plandne::where('estado_plandne', 1)->exists();

            if ($existeActivo) {
                // Si ya hay uno, forzamos este nuevo a ser INACTIVO (0)
                $inputs['estado_plandne'] = 0;
            }
        }

        $res = Plandne::create($inputs);

        return response()->json([
            'data' => $res,
            'mensaje' => $res->estado_plandne == 0 && $request->estado_plandne == 1
                ? "Agregado, pero se guardó como Inactivo porque ya existe un PEI activo."
                : "Agregado con Éxito!!",
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $res = Plandne::find($id);
        if (isset($res)) {
            return response()->json([
                'data' => $res,
                'mensaje' => "Encontrado con Éxito!!",
            ]);
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "El PlANDE con id: $id no Existe",
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $res = Plandne::find($id);

        if (isset($res)) {
            $res->nombre_plandne = $request->nombre_plandne;
            $res->anio_plandne = $request->anio_plandne;
            $res->link_plandne = $request->link_plandne;

            // Lógica de validación de estado
            if ($request->estado_plandne == 1) {
                // Buscamos si hay otro PEI activo que NO SEA el que estamos editando
                $otroActivo = Plandne::where('estado_plandne', 1)
                    ->where('id_pladne', '!=', $id)
                    ->exists();

                if ($otroActivo) {
                    $res->estado_plandne = 0;
                    $mensajeFinal = "Actualizado, pero se cambió a Inactivo porque ya existe otro PlANDE activo.";
                } else {
                    $res->estado_plandne = 1;
                    $mensajeFinal = "Actualizado con Éxito!!";
                }
            } else {
                $res->estado_plandne = 0;
                $mensajeFinal = "Actualizado con Éxito!!";
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
        $res = Plandne::find($id);
        if (isset($res)) {
            $res->estado_plandne = 0;
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
                    'mensaje' => "El plandne no existe (puede que ya la haya eliminado)",
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'mensaje' => "El plandne con id: $id no Existe",
            ]);
        }
    }
    public function habilitar(string $id)
    {
        // 1. Verificar si ya existe algún PlANDE activo
        $existeActivo = Plandne::where('estado_plandne', 1)->exists();

        if ($existeActivo) {
            return response()->json([
                'status' => false,
                'mensaje' => "No se puede habilitar: Ya existe un PlANDE activo actualmente. Por favor, desactive el anterior primero."
            ], 422); // Código 422: Entidad no procesable (error de validación de negocio)
        }

        // 2. Si no hay activos, procedemos a buscar y habilitar
        $res = Plandne::find($id);

        if (isset($res)) {
            $res->estado_plandne = 1;

            if ($res->save()) {
                return response()->json([
                    'status' => true,
                    'data' => $res,
                    'mensaje' => "¡PlANDE Habilitado con Éxito!",
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
                'mensaje' => "El PlANDE con id: $id no existe o fue eliminado.",
            ], 404);
        }
    }
}
