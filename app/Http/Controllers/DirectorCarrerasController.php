<?php

namespace App\Http\Controllers;

use App\Models\User;;

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
}
