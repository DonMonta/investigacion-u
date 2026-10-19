<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
//use App\Models\RegistroTitulos;
use App\Models\informacionpersonal;
use App\Models\InformacionPersonalD;
use App\Models\Invi_deta_inte;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'LoginUsu' => 'required|string',
            'ClaveUsu' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $CIInfPer = $request->input('LoginUsu');
        $codigo_dactilar = $request->input('ClaveUsu');
        $perfilesPermitidos = ['sotics', 'atics', 'sa', 'ainv'];
        $user = User::select('ciinfper', 'NombUsu', 'LoginUsu', 'email', 'idperfil', 'ClaveUsu', 'StatusUsu')
            ->where('LoginUsu', $CIInfPer)
            ->where('StatusUsu', 1)
            ->whereIn('idperfil', $perfilesPermitidos)
            ->first();
        $estudiante = informacionpersonal::where('CIInfPer', $CIInfPer)->first();
        $docente  = InformacionPersonalD::select('CIInfPer', 'LoginUsu', 'ClaveUsu', 'ApellInfPer', 'mailPer', 'TipoInfPer')
            ->where('LoginUsu', $CIInfPer)
            ->where('StatusPer', 1)
            ->first();

        if ($user) {

            if (md5($codigo_dactilar) !== $user->ClaveUsu) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Usuario correcto pero la clave es incorrecta',
                ], Response::HTTP_UNAUTHORIZED);
            }
            // --- INICIO DE REGISTRO EN BITÁCORA ---
            try {
                Bitacora::create([
                    'bt_usuario'     => $user->ciinfper,
                    'bt_fechahora'   => Carbon::now(), // Fecha y hora actual
                    'bt_accion'      => 'INICIO DE SESIÓN INVESTIGACIÓN-DASHBOARD',
                    'bt_ippc'        => $request->ip(), // Obtiene la IP del dispositivo
                    'bt_observacion' => 'INICIO DE SESIÓN DEL USUARIO: ' . $user->NombUsu,
                ]);
            } catch (\Exception $e) {
                // Logueamos el error por si falla la inserción en bitácora, 
                // pero permitimos que el login continúe.
                Log::error("Error al registrar bitácora: " . $e->getMessage());
            }
            // --- FIN DE REGISTRO EN BITÁCORA ---

            $token = auth()->login($user);

            return response()->json([
                'mensaje' => 'Autenticación exitosa',
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'name' => $user->NombUsu,
                'email' => $user->email,
                'Role' => $user->idperfil,
                'IdDepeart' => 23,
                'cedula' => $user->ciinfper,
            ]);
        } elseif ($docente) {

            if (md5($codigo_dactilar) !== $docente->ClaveUsu) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Usuario correcto pero la clave es incorrecta',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // --- REGISTRO EN BITÁCORA DOCENTE ---
            try {
                Bitacora::create([
                    'bt_usuario'     => $docente->CIInfPer,
                    'bt_fechahora'   => Carbon::now(),
                    'bt_accion'      => 'INICIO DE SESIÓN DOCENTE INVESTIGACIÓN',
                    'bt_ippc'        => $request->ip(),
                    'bt_observacion' => 'INICIO DE SESIÓN DOCENTE: ' . $docente->ApellInfPer,
                ]);
            } catch (\Exception $e) {
                Log::error("Error al registrar bitácora DOCENTE: " . $e->getMessage());
            }
            // --- BÚSQUEDA DE PROYECTOS Y FUNCIONES DEL DOCENTE ---
            // Traemos las relaciones 'invi_proyectos' y 'funciones' usando Eager Loading
            $asignacionesProyectos = Invi_deta_inte::with(['invi_proyectos.invi_convocatoria', 'funciones'])
                ->where('ciinfper_doc', $docente->CIInfPer)
                ->whereHas('invi_proyectos', function ($query) {
                    // IMPORTANTE: Cambia 'tipo_proyecto' por el nombre real de tu columna en la BD
                    $query->where('proyect_tipo', 'INVESTIGACIÓN')
                        ->orWhere('proyect_tipo', 'INVESTIGACION');
                })
                // ->where('estado', 1) // Opcional: Descomenta si necesitas filtrar solo integrantes activos
                ->get();
            if ($asignacionesProyectos->isEmpty()) {
                $token = auth('docente')->login($docente);

                return response()->json([
                    'mensaje' => 'No se encontraron proyectos asignados al docente',
                    'token'   => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                    'name'       => $docente->NombInfPer . ' ' .  $docente->ApellInfPer . ' ' . $docente->ApellMatInfPer,
                    'email'      => $docente->mailPer,
                    'cedula'     => $docente->CIInfPer,
                    'Role'       => $docente->TipoInfPer,
                    'IdDepeart' => 23,
                ]);
            }
            $ids_proyectos = [];
            $detalles_proyectos = [];

            foreach ($asignacionesProyectos as $asignacion) {
                if ($asignacion->invi_proyectos) {
                    $ids_proyectos[] = $asignacion->proyect_id;
                    $detalles_proyectos[] = [
                        'proyect_id'     => $asignacion->proyect_id,
                        'proyect_cod'    => $asignacion->invi_proyectos->proyect_cod,
                        'proyect_titulo' => $asignacion->invi_proyectos->proyect_titulo,
                        'id_funcion'     => $asignacion->id_funcion,
                        'id_convocatoria' => $asignacion->invi_proyectos->id_convocatoria,
                        'funcion'        => $asignacion->funciones ? $asignacion->funciones->nombre_funcion : 'Sin función asignada'
                    ];
                }
            }

            // Aseguramos que los IDs de proyectos sean únicos por si está registrado 2 veces en un mismo proyecto
            $ids_proyectos = array_values(array_unique($ids_proyectos));
            // --- FIN BÚSQUEDA DE PROYECTOS ---
            // Generamos Token explícitamente para este modelo alternativo
            $token = auth('docente')->login($docente);

            return response()->json([
                'mensaje'            => 'Autenticación exitosa',
                'token'              => $token,
                'token_type'         => 'bearer',
                'expires_in'         => config('jwt.ttl') * 60,
                'name'               => $docente->NombInfPer . ' ' .  $docente->ApellInfPer . ' ' . $docente->ApellMatInfPer,
                'email'              => $docente->mailPer,
                'cedula'             => $docente->CIInfPer,
                'Role'               => $docente->TipoInfPer,
                'IdDepeart'          => 23,
                'proyectos_ids'      => $ids_proyectos,          // Retorna array ej: [1, 5, 8]
                'proyectos_detalles' => $detalles_proyectos      // Retorna array de objetos con nombre de proyecto y función
            ]);
        } else {

            return response()->json([
                'error' => true,
                'mensaje' => "El Usuario: $CIInfPer no Existe",
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function me()
    {
        return response()->json(auth()->user());
    }
    public function logout()
    {
        //auth()->logout();
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['error' => 'No hay token'], Response::HTTP_BAD_REQUEST);
            }
            JWTAuth::invalidate($token);
            return response()->json(['message' => 'Has cerrado sesion'], Response::HTTP_OK);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo cerrar sesion'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function refresh()
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['error' => 'No hay token'], Response::HTTP_BAD_REQUEST);
            }
            $nuevo_token = JWTAuth::refresh();
            JWTAuth::invalidate($token);
            return $this->respondWithToken($nuevo_token);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo refrescar sesion'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ], Response::HTTP_OK);
    }
}
