<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Carreras;
use App\Models\informacionpersonal;
use App\Models\InformacionPersonalD;
use App\Models\Invi_deta_inte;
use App\Models\Invi_funcion;
use App\Models\Invi_proyectos;
use App\Models\Objetivos_pei;
use App\Models\Pei;
use App\Models\Subsistemas_pei;
use App\Models\Invi_detalle_obj_pei;
use App\Models\Plandne;
use App\Models\Politicas_plandne;
use App\Models\Invi_detalle_obj_pol_proyect;
use App\Models\Agenda_ODS;
use App\Models\ODS;
use App\Models\Invi_detalle_ods_proyect;
use App\Models\Facultad;
use App\Models\Invi_detalle_fac_proy;
use App\Models\Invi_detalle_carr_proy;
use App\Models\Invi_detalle_dom_hum;
use App\Models\Invi_dom_huma;
use App\Models\Invi_convocatoria;
use App\Models\Invi_linea_investigacion;
use App\Models\Invi_sub_linea_inves;
use App\Models\Invi_detalle_lin_inves;
use App\Models\SubareaUnesco;
use App\Models\Invi_detalle_area_unesco;
use App\Models\Invi_tip_proyect;
use App\Models\Zona_planificacion;
use App\Models\Detalle_zona_planificacion;
use App\Models\Provincia;
use App\Models\Canton;
use App\Models\Parroquia;
use App\Models\Invi_detalle_cobe;
use App\Models\Invi_Obj_Proy;
use App\Models\Invi_indicadores;
use App\Models\Invi_metas;
use App\Models\Invi_supuestos;
use App\Models\Invi_medios_verificacion;
use App\Models\Invi_prod_verificables;
use App\Models\Praempresa;
use App\Models\Invi_detalle_inst_proy;
use App\Models\Invi_detalle_presu_proy;
use App\Models\Invi_aportesutlvt;
use App\Models\Invi_aportesinst;
use App\Models\Invi_detalle_articulacion;
use App\Models\Invi_subactividad;
use App\Models\Invi_actprod_verificables;
use App\Models\Invi_actmedios_verificacion;
use App\Models\Invi_actindicadores;
use App\Models\Invi_actsupuestos;
use App\Models\Invi_actividades;
use App\Models\Invi_adquisicion;
use App\Models\Invi_detalle_adqui;
use App\Models\Invi_detalle_financia;
use App\Models\Invi_rubros;
use App\Models\Invi_impactos;
use App\Models\Invi_det_impactos_esperados;
use App\Models\Invi_det_difusion;
use App\Models\Invi_difusion;
use App\Models\Invi_bibliografias;
use App\Models\Invi_compromiso;
use App\Models\Obj_pol_plandne;
use App\Models\Asignaturas;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Invi_proyectosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try { // 1. Obtener parámetros de búsqueda y paginación
            $searchQuery = $request->input('search_query');
            $idConvocatoria = $request->input('id_convocatoria');
            $query = Invi_proyectos::select(
                'invi_proyectos.*'
            )->where('proyect_tipo', '=', 'INVESTIGACIÓN');
            if (! empty($searchQuery)) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('invi_proyectos.proyect_cod', 'LIKE', "%{$searchQuery}%");
                });
            }
            if (! empty($idConvocatoria)) {
                $query->where('invi_proyectos.id_convocatoria', '=', $idConvocatoria);
            }

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
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                ],

            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar los datos: ' . $e->getMessage()], 500);
        }
    }
    public function getStats()
    {
        try {
            // 1. Total de proyectos de INVESTIGACIÓN
            $totalProyectos = Invi_proyectos::where('proyect_tipo', 'INVESTIGACIÓN')->count();

            // Query base para integrantes activos en proyectos de INVESTIGACIÓN
            $baseIntegrantes = Invi_deta_inte::where('reemplazado', 0)
                ->whereHas('invi_proyectos', function ($query) {
                    $query->where('proyect_tipo', 'INVESTIGACIÓN');
                });

            // 2. Contamos por nombre de función (usando tu lógica semántica)
            $statsIntegrantes = $baseIntegrantes->with('funciones')
                ->get()
                ->groupBy(function ($item) {
                    $nombre = strtoupper($item->funciones->nombre_funcion ?? '');
                    if (str_contains($nombre, 'DIRECTOR') && !str_contains($nombre, 'SUB')) return 'director';
                    if (str_contains($nombre, 'SUBDIRECTOR')) return 'subdirector';
                    if (str_contains($nombre, 'INVESTIGADOR')||str_contains($nombre, 'AYUDANTE')||str_contains($nombre, 'TÉCNICO')) return 'docente';
                    return 'otros';
                });

            return response()->json([
                'status' => true,
                'stats' => [
                    'total_proyectos' => $totalProyectos,
                    'total_directores' => $statsIntegrantes->get('director', collect())->count(),
                    'total_subdirectores' => $statsIntegrantes->get('subdirector', collect())->count(),
                    'total_docentes' => $statsIntegrantes->get('docente', collect())->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    /**
     * Obtiene los datos para llenar el modal de edición.
     */
    public function getEdicionDatos(Request $request, int $id)
    {
        $proyecto = Invi_proyectos::with(
            'invi_detalle_obj_pro_pei',
            'invi_detalle_obj_pol_proyect',
            'invi_detalle_ods_proyect',
            'invi_detalle_fac_proy',
            'invi_detalle_carr_proy',
            'invi_detalle_dom_hum',
            'invi_detalle_lin_inves',
            'invi_detalle_area_unesco',
            'invi_detalle_cobe.provincias',
            'invi_detalle_cobe.cantones',
            'invi_detalle_cobe.parroquias',
            'invi_obj_proyectos.invi_indicadores',
            'invi_obj_proyectos.invi_metas',
            'invi_obj_proyectos.invi_supuestos',
            'invi_obj_proyectos.invi_medios_verificacion',
            'invi_obj_proyectos.invi_prod_verificables',
            'invi_detalle_inst_proy.praempresas',
            'invi_detalle_presu_proy.invi_aportesutlvt',
            'invi_detalle_presu_proy.invi_aportesinst.praempresa',
            'invi_detalle_articulacion',
            'invi_detalle_integrante.funciones',
            'invi_detalle_integrante.carreras.facultades',
            'invi_detalle_integrante.informacionPersonalD.titulos.nivel',
            'invi_detalle_integrante.informacionpersonal',
            'invi_obj_proyectos.invi_actividades.invi_subactividad',
            'invi_obj_proyectos.invi_actividades.invi_actprod_verificables',
            'invi_obj_proyectos.invi_actividades.invi_actmedios_verificacion',
            'invi_obj_proyectos.invi_actividades.invi_actindicadores',
            'invi_obj_proyectos.invi_actividades.invi_actsupuestos',
            'invi_detalle_adqui.invi_adquisicion',
            'invi_detalle_financia.invi_rubros',
            'invi_det_impactos_esperados.invi_impactos',
            'invi_detalle_difusion.invi_difusion',
            'invi_bibliografias'
        )->findOrFail($id);
        $id_departamento = $request->input('id_departamento');
        $queryPei = Pei::query();
        $queryPlandne = Plandne::query();
        $queryAgenda_ods = Agenda_ODS::query();
        if ($id_departamento) {
            // Busca el PEI que esté activo específicamente para este departamento
            $queryPei->whereHas('estadosDepartamentos', function ($query) use ($id_departamento) {
                $query->where('id_departamento', $id_departamento)
                    ->where('estado', 1); // 1 = Habilitado en la pivote
            });
            $queryPlandne->whereHas('estadosDepartamentosPlanDNE', function ($query) use ($id_departamento) {
                $query->where('id_departamento', $id_departamento)
                    ->where('estado', 1); // 1 = Habilitado en la pivote
            });
            $queryAgenda_ods->whereHas('estadosDepartamentosAgendaODS', function ($query) use ($id_departamento) {
                $query->where('id_departamento', $id_departamento)
                    ->where('estado', 1); // 1 = Habilitado en la pivote
            });
        } else {
            // Fallback: Si por alguna razón no envían departamento, busca por el estado general
            $queryPei->where('estado_pei', 1);
            $queryPlandne->where('estado_plandne', 1);
            $queryAgenda_ods->where('estado_ag_ods', 1);
        }
        // 1. Obtener el PEI activo (Asumo que estado_pei = 1 o true significa activo)
        $peiActivo = $queryPei->first();

        $objetivos = [];
        $preseleccionados = [];

        if ($peiActivo) {
            // 2. Obtener los objetivos de este PEI con su subsistema
            $objetivos = Objetivos_pei::with('subsistemas_pei')
                ->whereHas('subsistemas_pei', function ($query) use ($peiActivo) {
                    $query->where('id_pei', $peiActivo->id_pei);
                })->get();

            // 3. Buscar el objetivo de investigación para preseleccionarlo por defecto
            foreach ($objetivos as $obj) {
                $nombreSubsistema = strtolower($obj->subsistemas_pei->nombre_subsistema ?? '');

                // Extraemos los que el proyecto YA tiene asignados
                $asignadosIds = $proyecto->invi_detalle_obj_pro_pei->pluck('id_obj_pei')->toArray();

                // Si el proyecto ya tiene objetivos, usamos esos. 
                // Si está vacío, preseleccionamos el de investigación.
                if (empty($asignadosIds)) {
                    if (str_contains($nombreSubsistema, 'investigación') || str_contains($nombreSubsistema, 'investigación')) {
                        $preseleccionados[] = $obj->id_obj_pei;
                    }
                } else {
                    $preseleccionados = $asignadosIds;
                }
            }
        }
        

        $plandneActivo =  $queryPlandne->first();
        $politicas = [];
        $politicasSeleccionadas = $proyecto->invi_detalle_obj_pol_proyect->pluck('id_pol_pladne')->toArray();
        $objetivosPoliticasSeleccionadas = [];

        if ($plandneActivo) {
            // Obtenemos todas las políticas incluyendo su relación 'objetivos_plandne'
            $politicas = Politicas_plandne::with('objetivos_plandne')
                ->whereHas('objetivos_plandne', function ($query) use ($plandneActivo) {
                    $query->where('id_pladne', $plandneActivo->id_pladne);
                })->get();

            // FILTRADO NUEVO: Si ya hay políticas seleccionadas en el proyecto, obtenemos sus objetivos únicos
            if (!empty($politicasSeleccionadas)) {
                $objetivosPoliticasSeleccionadas = Politicas_plandne::with('objetivos_plandne')
                    ->whereIn('id_pol_pladne', $politicasSeleccionadas)
                    ->get()
                    ->pluck('objetivos_plandne')
                    ->unique('id_obj_pol_pladne')
                    ->values();
            }
        }
        $agenda_odsactiva = $queryAgenda_ods->first();
        $ods = [];
        $odsSeleccionadas = $proyecto->invi_detalle_ods_proyect->pluck('id_ods')->toArray();
        if ($agenda_odsactiva) {
            // Obtenemos todas las políticas cuyo objetivo pertenezca a la Agenda ODS activo
            $ods = ODS::whereHas('agenda_ods', function ($query) use ($agenda_odsactiva) {
                $query->where('id_ag_ods', $agenda_odsactiva->id_ag_ods);
            })->get();
        }
        // NUEVO: Obtener catálogo total de facultades disponibles en el sistema (reutilizando tu lógica de filtros)
        $sedes_atraer = ['1', '2'];
        $facultadesCatalogo = Facultad::whereIn('idsede', $sedes_atraer)
            ->where('idfacultad', '!=', '6')
            ->whereNotNull('decano')
            ->get();

        // NUEVO: Extraer datos de facultades ya guardadas para este proyecto
        $facultadesSeleccionadas = $proyecto->invi_detalle_fac_proy->pluck('idfacultad')->toArray();

        // La facultad prioritaria se extrae del primer registro (o mapeado de la tabla pivote)
        $facultadPrioritaria = $proyecto->invi_detalle_fac_proy->first()
            ? $proyecto->invi_detalle_fac_proy->first()->id_facultad_priori
            : null;
        // NUEVO: Extraer datos de carreras ya guardadas para este proyecto
        $carrerasSeleccionadas = $proyecto->invi_detalle_carr_proy->pluck('id_carr')->toArray();

        $carreraPrioritaria = $proyecto->invi_detalle_carr_proy->first()
            ? $proyecto->invi_detalle_carr_proy->first()->id_carr_priori
            : null;
        // NUEVO: Extraer catálogo total de dominios humanísticos y los seleccionados por el proyecto
        $dominiosCatalogo = Invi_dom_huma::all();
        $dominiosSeleccionados = $proyecto->invi_detalle_dom_hum->pluck('id_dom_hum')->toArray();
        $convocatoriasCatalogo = Invi_convocatoria::where('estado', 1)->where('tipo_convocatoria', 'INVESTIGACIÓN')->get();
        $lineasCatalogo = Invi_linea_investigacion::where('estado_lin_investiga', 1)->get();
        $sublineasCatalogo = Invi_sub_linea_inves::all();
        // NUEVO: Sublíneas que el proyecto ya tiene guardadas
        $sublineasSeleccionadas = $proyecto->invi_detalle_lin_inves->pluck('id_sublin_investiga')->toArray();
        // NUEVO: Obtener y transformar catálogo completo de la UNESCO reutilizando la lógica de tu método index
        $unescoCatalogoRaw = SubareaUnesco::all();
        $unescoCatalogo = $unescoCatalogoRaw->map(function ($item) {
            $id = trim($item->sau_id);
            $pdid = trim($item->sau_pdid ?? '');
            $descripcion = mb_convert_encoding($item->sau_descripcion, 'ISO-8859-1', 'UTF-8');
            $descripcion_limpia = preg_replace('/^' . preg_quote($id, '/') . '\s*/', '', $descripcion);

            $longitud_id = strlen($id);
            $tipo = 'Desconocido';

            if (empty($pdid) && $longitud_id === 2) {
                $tipo = 'Área de conocimiento';
            } elseif (strlen($pdid) === 2 || $longitud_id === 3) {
                $tipo = 'Subárea de conocimiento';
            } elseif (strlen($pdid) >= 3 || $longitud_id >= 4) {
                $tipo = 'Área específica de conocimiento';
            }

            return [
                'sau_id'          => $id,
                'sau_pdid'        => $pdid,
                'sau_descripcion' => trim($descripcion_limpia),
                'tipo_area'       => $tipo
            ];
        });

        // NUEVO: Extraer los ids que el proyecto ya tiene guardados en `invi_detalle_area_unesco`
        $unescoSeleccionadas = $proyecto->invi_detalle_area_unesco->pluck('id_subarea_unesco')->toArray();
        $tip_proyectCatalogo = Invi_tip_proyect::all();
        $zonasCatalogo = Zona_planificacion::all();
        $detallesZonasCatalogo = Detalle_zona_planificacion::all();
        $provinciasCatalogo = Provincia::all();
        $cantonesCatalogo = Canton::all();
        $parroquiasCatalogo = Parroquia::all();

        // NUEVO: Estructuramos los datos de cobertura guardada en arreglos planos y valores únicos
        $id_zona_plan_guardada = '';
        $provincias_guardadas = [];
        $cantones_guardados = [];
        $parroquias_guardadas = [];

        foreach ($proyecto->invi_detalle_cobe as $cobe) {
            // Obtenemos la zona (solo necesitamos hacerlo una vez ya que es selección única)
            if (empty($id_zona_plan_guardada) && $cobe->id_provincia) {
                $detalleZona = Detalle_zona_planificacion::where('id_provincia', $cobe->id_provincia)->first();
                if ($detalleZona) {
                    $id_zona_plan_guardada = $detalleZona->id_zona_plan;
                }
            }

            // Agrupamos IDs únicos
            if ($cobe->id_provincia && !in_array($cobe->id_provincia, $provincias_guardadas)) {
                $provincias_guardadas[] = $cobe->id_provincia;
            }
            if ($cobe->id_canton && !in_array($cobe->id_canton, $cantones_guardados)) {
                $cantones_guardados[] = $cobe->id_canton;
            }
            if ($cobe->idparroquia && !in_array($cobe->idparroquia, $parroquias_guardadas)) {
                $parroquias_guardadas[] = $cobe->idparroquia;
            }
        }
        $zonaPlanData = null;
        if (!empty($id_zona_plan_guardada)) {
            $zonaPlanData = Zona_planificacion::where('id_zona_plan', $id_zona_plan_guardada)->first();
        }
        $empresasSeleccionadas = $proyecto->invi_detalle_inst_proy->map(function ($detalle) {
            return $detalle->praempresas;
        })->filter()->values()->toArray();

        $empresasSeleccionadas2 = $proyecto->invi_detalle_inst_proy->map(function ($detalle) {
            return $detalle->praempresas;
        })->filter(function ($empresa) {
            return $empresa && $empresa->ruc !== '0860000830001';
        })->values()->toArray();
        $aportesUtlvt = [];
        $aportesInst = [];

        if ($proyecto->invi_detalle_presu_proy) {
            foreach ($proyecto->invi_detalle_presu_proy as $detallePresu) {
                // Aportes de la Universidad (UTLVT)
                if ($detallePresu->id_aportes_utlvt && $detallePresu->invi_aportesutlvt) {
                    $aportesUtlvt[] = [
                        'id_det_presupuesto' => $detallePresu->id_det_presupuesto,
                        'actividad' => $detallePresu->invi_aportesutlvt->actividad,
                        'valor' => $detallePresu->invi_aportesutlvt->valor,
                    ];
                }
                // Aportes de Instituciones Externas
                if ($detallePresu->id_aportes_inst && $detallePresu->invi_aportesinst) {
                    $empresa = $detallePresu->invi_aportesinst->praempresa;
                    // Excluimos la empresa con el RUC indicado
                    if ($empresa && $empresa->ruc !== '0860000830001') {
                        $aportesInst[] = [
                            'id_det_presupuesto' => $detallePresu->id_det_presupuesto,
                            'idempresa' => $detallePresu->invi_aportesinst->idempresa,
                            'actividad' => $detallePresu->invi_aportesinst->actividad,
                            'valor' => $detallePresu->invi_aportesinst->valor,
                        ];
                    }
                }
            }
        }
        // Obtener las asignaturas guardadas para este proyecto
        $asignaturasSeleccionadas = $proyecto->invi_detalle_articulacion->pluck('IdAsig')->toArray();

        // Consulta SQL convertida a Query Builder para obtener las asignaturas de la malla
        $asignaturasDisponibles = [];
        if ($carreraPrioritaria) {
            $asignaturasDisponibles = DB::table('carrera as c')
                ->join('malla_carrera as m', 'c.idCarr', '=', 'm.idcarrera')
                ->join('detalle_malla as dm', 'm.id', '=', 'dm.idmalla')
                ->join('asignatura as a', 'dm.idasignatura', '=', 'a.IdAsig')
                ->select(
                    'c.idCarr as Codigo_Carrera',
                    'c.NombCarr as Nombre_Carrera',
                    'm.anio as Anio_Malla',
                    'dm.nivel as Nivel_Asignatura',
                    'a.IdAsig as Codigo_Asignatura',
                    'a.NombAsig as Nombre_Asignatura'
                )
                ->where('c.StatusCarr', 1)
                ->whereNotNull('c.codihicenter')
                ->where('m.anio', '2020')
                ->whereRaw("a.IdAsig NOT REGEXP '^[0-9]+$'")
                ->where('c.idCarr', $carreraPrioritaria) // Filtramos por el ID de la carrera prioritaria
                ->orderBy('c.NombCarr', 'asc')
                ->orderBy('dm.nivel', 'asc')
                ->orderBy('a.NombAsig', 'asc')
                ->get();
        }
        $num_doce_h = 0;
        $num_doce_m = 0;
        $num_est_h  = 0;
        $num_est_m  = 0;

        $listaIntegrantesValidos = [];

        foreach ($proyecto->invi_detalle_integrante as $integrante) {

            // REGLAS DE ESTADO Y REEMPLAZO:
            // Excluir a los que tienen estado 0 y reemplazado 1.
            // Tomar todos los que tienen estado 1 (incluso si fueron reemplazados pero siguen activos).
            if ($integrante->estado != 1) {
                continue; // Saltamos a los que no tienen estado activo (1)
            }

            // Agregamos a la lista de integrantes que devolverá el método
            $listaIntegrantesValidos[] = $integrante;

            $nombreFuncion = trim($integrante->funciones->nombre_funcion ?? '');
            $idFuncion = $integrante->id_funcion;

            // 1. DOCENTES (Buscamos en informacionpersonal_d)
            if ($integrante->informacionPersonalD) {
                $tipoInf = strtoupper($integrante->informacionPersonalD->TipoInfPer ?? '');

                // Excluimos personal administrativo (TipoInfPer == 'A' o id_funcion == 5)
                if ($tipoInf !== 'A' && $idFuncion != 5) {

                    // Validamos funciones de Docentes (Director, Subdirector, Docente, Técnico)
                    // Usamos tanto el id_funcion como Str::startsWith para mayor seguridad
                    if (Str::startsWith($nombreFuncion, ['Director', 'Subdirector', 'Docente', 'Técnico']) || in_array($idFuncion, [1, 2, 3, 4])) {
                        $genero = strtoupper($integrante->informacionPersonalD->GeneroPer ?? '');
                        if ($genero === 'M') {
                            $num_doce_h++;
                        } elseif ($genero === 'F') {
                            $num_doce_m++;
                        }
                    }
                }
            }

            // 2. ESTUDIANTES (Buscamos en informacionpersonal)
            if ($integrante->informacionpersonal) {
                // Validamos funciones de Estudiantes (id_funcion == 6 o empieza con "Estudiante")
                if (Str::startsWith($nombreFuncion, ['Estudiante']) || $idFuncion == 6) {
                    $genero = strtoupper($integrante->informacionpersonal->GeneroPer ?? '');
                    if ($genero === 'H') {
                        $num_est_h++;
                    } elseif ($genero === 'M') {
                        $num_est_m++;
                    }
                }
            }
        }
        $coleccionIntegrantes = collect($listaIntegrantesValidos);

        $integrantesConTitulos = $coleccionIntegrantes->map(function ($integrante) {
            $integranteModificado = clone $integrante;
            $carreraFormateada = '';
            if ($integranteModificado->carreras) {
                $nombreCarreraRaw = trim($integranteModificado->carreras->NombCarr ?? '');
                
                // Quitamos el guión y los números/año del final (ej: " - 2020")
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarreraRaw);

                // Convertimos a Title Case (pasando primero por minúsculas para evitar problemas con TODO MAYUSCULAS)
                $carreraFormateada = mb_convert_case(mb_strtolower($carreraLimpia, 'UTF-8'), MB_CASE_TITLE, "UTF-8");
            }
            
            // Guardamos la carrera formateada como un nuevo atributo
            $integranteModificado->setAttribute('carrera_formateada', $carreraFormateada);
            $persona = $integranteModificado->informacionPersonalD;
            $nombreCompletoConTitulo = '';
            $cargoFormateado = '';

            // Si es un Docente (tiene informacionPersonalD)
            if ($persona) {
                // 2. Lógica de Género para Nombres y Cargos
                $esMujer = strtoupper(trim($persona->GeneroPer ?? '')) === 'F';
                $cargoFormateado = $esMujer ? 'Directora' : 'Director';

                // 3. Procesar títulos académicos filtrando por nv_numnivel
                $tituloGrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
                });

                $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
                });

                $prefijoNombre = '';
                $sufijoNombre = '';

                // A. Mapeo ampliado de PREFIJOS (3er Nivel)
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

                // Formatear Nombres y Apellidos en formato Title Case
                $nombreRaw = trim(($persona->NombInfPer ?? '') . ' ' . ($persona->ApellInfPer ?? '') . ' ' . ($persona->ApellMatInfPer ?? ''));
                $nombresFormateados = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

                // Construcción del nombre con rango académico completo
                $nombreCompletoConTitulo = trim("{$prefijoNombre} {$nombresFormateados}{$sufijoNombre}");
            } 
            // Si es un Estudiante (tiene informacionpersonal)
            else {
                $personaEst = $integranteModificado->informacionpersonal;
                if ($personaEst) {
                    $nombreRaw = trim(($personaEst->NombInfPer ?? '') . ' ' . ($personaEst->ApellInfPer ?? '') . ' ' . ($personaEst->ApellMatInfPer ?? ''));
                    $nombreCompletoConTitulo = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                }
            }

            // Inyectamos las nuevas variables como atributos extra del modelo clonado
            $integranteModificado->setAttribute('nombre_completo_titulo', $nombreCompletoConTitulo);
            $integranteModificado->setAttribute('cargo_genero_formateado', $cargoFormateado);

            return $integranteModificado;
        });

        $num_doce_part = $num_doce_h + $num_doce_m;
        $num_est_part  = $num_est_h + $num_est_m;
        $rubrosCatalogo = Invi_rubros::all();
        $impactosCatalogo = Invi_impactos::all();
        $facultadesData = Facultad::whereIn('idfacultad', $facultadesSeleccionadas)->get();
        // Usamos DB table directo a la tabla de carreras asumiendo tu convención
        $carrerasData = DB::table('carrera')->whereIn('idCarr', $carrerasSeleccionadas)->get();
        $dominiosData = Invi_dom_huma::whereIn('id_dom_huma', $dominiosSeleccionados)->get();
        $objetivosPeiData = Objetivos_pei::whereIn('id_obj_pei', $preseleccionados)->get();
        $politicasData = Politicas_plandne::whereIn('id_pol_pladne', $politicasSeleccionadas)->get();
        $agendaOdsData = ODS::whereIn('id_ods', $odsSeleccionadas)->get();
        $objPolData = Politicas_plandne::with('objetivos_plandne')
                    ->whereIn('id_pol_pladne', $politicasSeleccionadas)
                    ->get()->pluck('objetivos_plandne')
                    ->unique('id_obj_pol_pladne')
                    ->values();
        $convocatoriaData = Invi_convocatoria::where('id_convocatoria', $proyecto->id_convocatoria)->get();
        $lineasData = Invi_sub_linea_inves::with('linea_investigacion')
            ->whereIn('id_sublin_investiga', $sublineasSeleccionadas)
            ->get()->pluck('linea_investigacion')
            ->unique('id_lin_investiga')
            ->values();
        $sublineasData = Invi_sub_linea_inves::whereIn('id_sublin_investiga', $sublineasSeleccionadas)->get();
        
        // Para Unesco filtramos la collection que ya creaste previamente:
        $unescoData = collect($unescoCatalogo)->whereIn('sau_id', $unescoSeleccionadas)->values();
        $tipoproyectoData = Invi_tip_proyect::where('id_tip_invi_proy', $proyecto->id_tip_invi_proy)->get();
        $asignaturasData = Asignaturas::whereIn('IdAsig', $asignaturasSeleccionadas)->get();

        return response()->json([
            'proyecto' => $proyecto,
            'objetivos_pei' => $objetivos,
            'seleccionados' => $preseleccionados,
            'politicas_plandne' => $politicas,
            'politicas_seleccionadas' => $politicasSeleccionadas,
            'objetivos_politicas_seleccionadas' => $objetivosPoliticasSeleccionadas,
            'ods' => $ods,
            'ods_seleccionadas' => $odsSeleccionadas,
            'facultades_catalogo' => $facultadesCatalogo,
            'facultades_seleccionadas' => $facultadesSeleccionadas,
            'id_facultad_priori' => $facultadPrioritaria,
            'carreras_seleccionadas' => $carrerasSeleccionadas,
            'id_carr_priori' => $carreraPrioritaria,
            'dominios_catalogo' => $dominiosCatalogo,
            'dominios_seleccionados' => $dominiosSeleccionados,
            'convocatorias_catalogo' => $convocatoriasCatalogo,
            'lineas_catalogo' => $lineasCatalogo,
            'sublineas_catalogo' => $sublineasCatalogo,
            'sublineas_seleccionadas' => $sublineasSeleccionadas,
            'unesco_catalogo' => $unescoCatalogo,
            'unesco_seleccionadas' => $unescoSeleccionadas,
            'tip_proyect_catalogo' => $tip_proyectCatalogo,
            'zonas_catalogo' => $zonasCatalogo,
            'detalles_zonas_catalogo' => $detallesZonasCatalogo,
            'provincias_catalogo' => $provinciasCatalogo,
            'cantones_catalogo' => $cantonesCatalogo,
            'parroquias_catalogo' => $parroquiasCatalogo,

            // NUEVO: Devolvemos la estructura plana
            'cobertura_guardada' => [
                'id_zona_plan' => $id_zona_plan_guardada,
                'provincias'   => $provincias_guardadas,
                'cantones'     => $cantones_guardados,
                'parroquias'   => $parroquias_guardadas,
            ],
            'empresas_seleccionadas' => $empresasSeleccionadas,
            'empresas_seleccionadas2' => $empresasSeleccionadas2,
            'aportes_utlvt' => $aportesUtlvt,
            'aportes_inst' => $aportesInst,
            'asignaturas_seleccionadas' => $asignaturasSeleccionadas,
            'asignaturas_disponibles' => $asignaturasDisponibles,
            'integrantes_activos'       => $listaIntegrantesValidos,
            'integrantes_titulosactivos'       => $integrantesConTitulos,
            'calculo_integrantes'       => [
                'docentes_h'        => $num_doce_h,
                'docentes_m'        => $num_doce_m,
                'docentes_total'    => $num_doce_part,
                'estudiantes_h'     => $num_est_h,
                'estudiantes_m'     => $num_est_m,
                'estudiantes_total' => $num_est_part,
            ],
            'rubros_catalogo' => $rubrosCatalogo,
            'impactos_catalogo' => $impactosCatalogo,
            'facultades_data' => $facultadesData,
            'carreras_data' => $carrerasData,
            'dominios_data' => $dominiosData,
            'unesco_data' => $unescoData,
            'objetivos_pei_data' => $objetivosPeiData,
            'politicas_data' => $politicasData,
            'agenda_ods_data' => $agendaOdsData,
            'objetivos_politicas_data' => $objPolData,
            'convocatoria_data' => $convocatoriaData,
            'lineas_data' => $lineasData,
            'sublineas_data' => $sublineasData,
            'tipproyectos_data' => $tipoproyectoData,
            'zona_plan_data' => $zonaPlanData,
            'asignaturas_data' => $asignaturasData,
        ]);
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
        $proyecto = Invi_proyectos::with([
            'invi_detalle_fac_proy.facultades',
            'invi_detalle_fac_proy.facultades_priori',
            'invi_detalle_integrante.funciones',
            'invi_detalle_integrante.carreras',
            'invi_detalle_integrante.informacionPersonalD',
            'invi_detalle_integrante.informacionpersonal',
            'invi_detalle_integrante.compromisos',
            'invi_detalle_integrante.informacionPersonalD.titulos.nivel'
        ])->findOrFail($id);

        // 1. Extraer la facultad prioritaria
        $facultadPrincipal = $proyecto->invi_detalle_fac_proy
            ->whereNotNull('id_facultad_priori')
            ->first()?->facultades_priori;

        // 2. Extraer todas las facultades participantes
        $facultadesParticipantes = $proyecto->invi_detalle_fac_proy
            ->map(fn($detalle) => $detalle->facultades)
            ->filter()
            ->unique('idfacultad')
            ->values();

        // 3. Ordenar integrantes por nombre de la función
        $integrantesOrdenados = $proyecto->invi_detalle_integrante->sortBy(function ($integrante) {
            // Obtenemos el nombre de la función en mayúsculas para evitar problemas de case-sensitivity
            $nombreFuncion = strtoupper($integrante->funciones?->nombre_funcion ?? '');

            // Retornamos un peso numérico basado en el texto
            return match (true) {
                str_contains($nombreFuncion, 'DIRECTOR') && ! str_contains($nombreFuncion, 'SUB') => 10,
                str_contains($nombreFuncion, 'SUBDIRECTOR') => 20,
                str_contains($nombreFuncion, 'DOCENTE') => 30,
                str_contains($nombreFuncion, 'ESTUDIANTE') => 100,
                empty($nombreFuncion) => 999, // Integrantes sin función (reemplazados)
                default => 50, // Cualquier otra función intermedia
            };
        })->values();
        // 4. Crear una nueva colección con los títulos (SIN alterar la original)
        $integrantesConTitulos = $integrantesOrdenados->map(function ($integrante) {
            // Clonamos el modelo para no mutar el objeto original de la otra colección
            $integranteModificado = clone $integrante;
            
            $persona = $integranteModificado->informacionPersonalD;
            $nombreCompletoConTitulo = '';
            $cargoFormateado = '';

            if ($persona) {
                // 2. Lógica de Género para Nombres y Cargos
                $esMujer = strtoupper(trim($persona->GeneroPer)) === 'F';
                $cargoFormateado = $esMujer ? 'Directora' : 'Director';

                // 3. Procesar títulos académicos filtrando por nv_numnivel
                $tituloGrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
                });

                $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
                });

                $prefijoNombre = '';
                $sufijoNombre = '';

                // A. Mapeo ampliado de PREFIJOS (3er Nivel)
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

                // Formatear Nombres y Apellidos en formato Title Case
                $nombreRaw = trim($persona->NombInfPer . ' ' . $persona->ApellInfPer . ' ' . $persona->ApellMatInfPer);
                $nombresFormateados = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

                // Construcción del nombre con rango académico completo
                $nombreCompletoConTitulo = trim("{$prefijoNombre} {$nombresFormateados}{$sufijoNombre}");
            }

            // Inyectamos las nuevas variables como atributos extra del modelo clonado
            $integranteModificado->setAttribute('nombre_completo_titulo', $nombreCompletoConTitulo);
            $integranteModificado->setAttribute('cargo_genero_formateado', $cargoFormateado);

            return $integranteModificado;
        });
        // 4. Formatear la respuesta JSON
        return response()->json([
            'proyect_id' => $proyecto->proyect_id,
            'proyect_nombre' => $proyecto->proyect_nombre,
            'proyect_titulo' => $proyecto->proyect_titulo,
            'fechainicio' => $proyecto->fechainicio,
            'fechafin' => $proyecto->fechafin,
            'facultades_priori' => $facultadPrincipal,
            'facultades' => $facultadesParticipantes,
            'invi_detalle_integrante' => $integrantesConTitulos,
            'invi_detalle_integrante_titulos' => $integrantesConTitulos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
       DB::beginTransaction();
        try {
            $proyecto = Invi_proyectos::findOrFail($id);
            $proyecto->proyect_nombre = $request->proyect_nombre;
            $proyecto->proyect_titulo = $request->proyect_titulo;
            $proyecto->proyect_nombre_en = $request->proyect_nombre_en;
            $proyecto->proyect_titulo_en = $request->proyect_titulo_en;
            $proyecto->proyect_multidis = $request->proyect_multidis;
            $proyecto->id_convocatoria = $request->id_convocatoria;
            $proyecto->id_tip_invi_proy = $request->id_tip_invi_proy;
            $proyecto->proyect_cobertura = $request->proyect_cobertura;
            $proyecto->proyect_antecedentes = $request->proyect_antecedentes;
            $proyecto->proyect_justificacion = $request->proyect_justificacion;
            $proyecto->proyect_fecha_pres = $request->proyect_fecha_pres;
            $proyecto->fechainicio = $request->fechainicio;
            $proyecto->fechafin = $request->fechafin;
            $proyecto->proyect_duracion_mes = $request->proyect_duracion_mes;
            $proyecto->proyect_estado = $request->proyect_estado;
            $proyecto->proyect_desc_situ_act = $request->proyect_desc_situ_act;
            $proyecto->proyect_diag_probl = $request->proyect_diag_probl;
            $proyecto->proyect_contribucion_soci = $request->proyect_contribucion_soci;
            $proyecto->proyec_ident_poblaobj = $request->proyec_ident_poblaobj;
            $proyecto->proyect_num_direct_hombres = $request->proyect_num_direct_hombres;
            $proyecto->proyect_num_direct_mujeres = $request->proyect_num_direct_mujeres;
            $proyecto->proyect_total_num_direct = $request->proyect_total_num_direct;
            $proyecto->proyect_total_num_indirect = $request->proyect_total_num_indirect;
            $proyecto->proyect_num_personas_div_fun = $request->proyect_num_personas_div_fun;
            $proyecto->proyect_num_doce_part = $request->proyect_num_doce_part;
            $proyecto->proyect_num_doce_h = $request->proyect_num_doce_h;
            $proyecto->proyect_num_doce_m = $request->proyect_num_doce_m;
            $proyecto->proyect_num_est_part = $request->proyect_num_est_part;
            $proyecto->proyect_num_est_h = $request->proyect_num_est_h;
            $proyecto->proyect_num_est_m = $request->proyect_num_est_m;
            $proyecto->proyect_fact_exito = $request->proyect_fact_exito;
            $proyecto->proyect_rest_supu = $request->proyect_rest_supu;
            $proyecto->proyect_bienes = $request->proyect_bienes;
            $proyecto->proyect_servicios = $request->proyect_servicios;
            $proyecto->proyect_bienes_servicios = $request->proyect_bienes_servicios;
            $proyecto->proyect_categorizacion = $request->proyect_categorizacion;
            $proyecto->proyect_metodologia = $request->proyect_metodologia;
            $proyecto->proyect_viabilidad_tec = $request->proyect_viabilidad_tec;
            $proyecto->proyect_equip_tec = $request->proyect_equip_tec;
            $proyecto->proyect_no_ejecuta = $request->proyect_no_ejecuta;
            $proyecto->proyect_sostenibilidad_soc = $request->proyect_sostenibilidad_soc;
            $proyecto->proyect_transf_tecn = $request->proyect_transf_tecn;
            $proyecto->proyect_art_cientificos = $request->proyect_art_cientificos;
            $proyecto->proyect_prototipos = $request->proyect_prototipos;
            $proyecto->proyect_reg_propin = $request->proyect_reg_propin;
            $proyecto->proyect_empr_spin = $request->proyect_empr_spin;
            $proyecto->proyect_archivo = $request->proyect_archivo;

            $proyecto->save();

            // Actualizar la tabla detalle de objetivos
            Invi_detalle_obj_pei::where('proyect_id', $id)->delete();

            if ($request->has('objetivos') && is_array($request->objetivos)) {
                foreach ($request->objetivos as $id_obj) {
                    // OJO: Tu modelo tiene public $incrementing = false. 
                    // Si la BD no tiene auto-increment en 'id_det_obj_pro_pei', 
                    // deberás generar el ID manualmente aquí.
                    Invi_detalle_obj_pei::insert([
                        'proyect_id' => $id,
                        'id_obj_pei' => $id_obj
                    ]);
                }
            }
            Invi_detalle_obj_pol_proyect::where('proyect_id', $id)->delete();
            if ($request->has('politicas') && is_array($request->politicas)) {
                foreach ($request->politicas as $id_pol) {
                    Invi_detalle_obj_pol_proyect::insert([
                        'proyect_id'   => $id,
                        'id_pol_pladne' => $id_pol
                    ]);
                }
            }
            Invi_detalle_ods_proyect::where('proyect_id', $id)->delete();
            if ($request->has('ods') && is_array($request->ods)) {
                foreach ($request->ods as $id_ods) {
                    Invi_detalle_ods_proyect::insert([
                        'proyect_id'   => $id,
                        'id_ods' => $id_ods
                    ]);
                }
            }
            // NUEVO: --- Persistencia de Facultades (invi_detalle_fac_proy) ---
            Invi_detalle_fac_proy::where('proyect_id', $id)->delete();

            // Prioridad determinada por el cliente
            $idFacultadPriori = $request->id_facultad_priori;

            if ($request->proyect_multidis == 1) {
                // Caso Multidisciplinario: Múltiples registros apuntando al mismo id_facultad_priori
                if ($request->has('facultades') && is_array($request->facultades)) {
                    foreach ($request->facultades as $id_fac) {
                        Invi_detalle_fac_proy::insert([
                            'proyect_id'         => $id,
                            'idfacultad'         => $id_fac,
                            'id_facultad_priori' => $idFacultadPriori
                        ]);
                    }
                }
            } else {
                // Caso Disciplinar Estándar: Un único registro donde la facultad es a su vez la prioritaria
                if ($idFacultadPriori) {
                    Invi_detalle_fac_proy::insert([
                        'proyect_id'         => $id,
                        'idfacultad'         => $idFacultadPriori,
                        'id_facultad_priori' => $idFacultadPriori
                    ]);
                }
            }
            // NUEVO: --- Persistencia de Carreras (invi_detalle_carr_proy) ---
            Invi_detalle_carr_proy::where('proyect_id', $id)->delete();
            $idCarrPriori = $request->id_carr_priori;

            if ($request->proyect_multidis == 1) {
                // Caso Multidisciplinario: Múltiples carreras apuntando al mismo id_carr_priori
                if ($request->has('carreras') && is_array($request->carreras)) {
                    foreach ($request->carreras as $id_carr) {
                        // OJO: Si tu base de datos no tiene autoincrementable en id_det_carr, generarlo aquí manualmente.
                        Invi_detalle_carr_proy::insert([
                            'proyect_id'     => $id,
                            'id_carr'        => $id_carr,
                            'id_carr_priori' => $idCarrPriori
                        ]);
                    }
                }
            } else {
                // Caso Disciplinar Estándar: Un único registro donde id_carr es a su vez la prioritaria
                if ($idCarrPriori) {
                    Invi_detalle_carr_proy::insert([
                        'proyect_id'     => $id,
                        'id_carr'        => $idCarrPriori,
                        'id_carr_priori' => $idCarrPriori
                    ]);
                }
            }
            Invi_detalle_dom_hum::where('proyect_id', $id)->delete();
            if ($request->has('dominios_humanisticos') && is_array($request->dominios_humanisticos)) {
                foreach ($request->dominios_humanisticos as $id_dom) {
                    Invi_detalle_dom_hum::insert([
                        'proyect_id' => $id,
                        'id_dom_hum' => $id_dom
                    ]);
                }
            }
            Invi_detalle_lin_inves::where('proyect_id', $id)->delete();

            if ($request->has('sublineas_investigacion') && is_array($request->sublineas_investigacion)) {
                foreach ($request->sublineas_investigacion as $id_sublin) {
                    // Asegúrate de generar ID manual si tu PK no es auto-increment
                    Invi_detalle_lin_inves::insert([
                        'proyect_id'          => $id,
                        'id_sublin_investiga' => $id_sublin
                    ]);
                }
            }
            Invi_detalle_area_unesco::where('proyect_id', $id)->delete();
            if ($request->has('unesco_areas') && is_array($request->unesco_areas)) {
                foreach ($request->unesco_areas as $id_unesco) {
                    // Al ser public $incrementing = false y no poseer clave primaria autoincremental en el modelo,
                    // usamos insert directo sobre las columnas fillable.
                    Invi_detalle_area_unesco::insert([
                        'proyect_id'        => $id,
                        'id_subarea_unesco' => $id_unesco
                    ]);
                }
            }
            Invi_detalle_cobe::where('proyect_id', $id)->delete();

            $idZona = $request->id_zona_plan;
            $provincias = $request->provincias ?? [];
            $cantones = $request->cantones ?? [];
            $parroquias = $request->parroquias ?? [];

            if ($idZona && count($provincias) > 0) {
                // Obtener datos maestros para armar las relaciones inversas
                $cantonesDB = Canton::whereIn('id_canton', $cantones)->get()->keyBy('id_canton');
                $parroquiasDB = Parroquia::whereIn('idparroquia', $parroquias)->get();

                foreach ($provincias as $idProv) {
                    // Buscamos el ID intermedio de la zona
                    $detalleZona = Detalle_zona_planificacion::where('id_zona_plan', $idZona)
                        ->where('id_provincia', $idProv)->first();
                    $idDetalleZona = $detalleZona ? $detalleZona->id_detalle_zona_plan : null;

                    // Buscar qué cantones de los seleccionados pertenecen a ESTA provincia
                    $cantonesDeEstaProv = $cantonesDB->filter(function ($c) use ($idProv) {
                        return $c->codigoprovincia == $idProv;
                    });

                    if ($cantonesDeEstaProv->isEmpty()) {
                        // El usuario marcó la provincia, pero NO marcó ningún cantón dentro de ella
                        Invi_detalle_cobe::insert([
                            'proyect_id'           => $id,
                            'id_detalle_zona_plan' => $idDetalleZona,
                            'id_provincia'         => $idProv,
                            'id_canton'            => null,
                            'idparroquia'          => null,
                        ]);
                    } else {
                        foreach ($cantonesDeEstaProv as $catObj) {
                            // Buscar qué parroquias seleccionadas pertenecen a ESTE cantón
                            // OJO: Comparamos con el $catObj->codigo como descubrimos en el bug
                            $parroquiasDeEsteCanton = $parroquiasDB->filter(function ($p) use ($catObj) {
                                return $p->codigocanton == $catObj->codigo;
                            });

                            if ($parroquiasDeEsteCanton->isEmpty()) {
                                // Marcó el cantón, pero no sus parroquias específicas
                                Invi_detalle_cobe::insert([
                                    'proyect_id'           => $id,
                                    'id_detalle_zona_plan' => $idDetalleZona,
                                    'id_provincia'         => $idProv,
                                    'id_canton'            => $catObj->id_canton,
                                    'idparroquia'          => null,
                                ]);
                            } else {
                                // Nivel de profundidad máximo: guardar la parroquia
                                foreach ($parroquiasDeEsteCanton as $parrObj) {
                                    Invi_detalle_cobe::insert([
                                        'proyect_id'           => $id,
                                        'id_detalle_zona_plan' => $idDetalleZona,
                                        'id_provincia'         => $idProv,
                                        'id_canton'            => $catObj->id_canton,
                                        'idparroquia'          => $parrObj->idparroquia,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            if ($request->has('objetivos_marco_logico') && is_array($request->objetivos_marco_logico)) {

                $ids_objetivos_recibidos = [];

                foreach ($request->objetivos_marco_logico as $objData) {
                    // 1. Crear o Actualizar el Objetivo
                    $objetivo = Invi_Obj_Proy::updateOrCreate(
                        [
                            'id_obj_proy' => $objData['id_obj_proy'] ?? null,
                            'proyect_id'  => $id
                        ],
                        [
                            'detalle_obj_proy' => $objData['detalle_obj_proy'],
                            'tipo_obj_proy'    => $objData['tipo_obj_proy'], // 'general', 'fin', 'especifico'
                        ]
                    );

                    $ids_objetivos_recibidos[] = $objetivo->id_obj_proy;

                    // 2. Limpiar e Insertar Componentes Anidados (Indicadores)
                    $objetivo->invi_indicadores()->delete();
                    if (!empty($objData['indicadores'])) {
                        $indicadoresInsert = array_map(function ($item) use ($objetivo) {
                            return ['id_obj_proy' => $objetivo->id_obj_proy, 'detalle_indicador' => $item['detalle_indicador']];
                        }, $objData['indicadores']);
                        Invi_indicadores::insert($indicadoresInsert);
                    }

                    // 3. Limpiar e Insertar Metas
                    $objetivo->invi_metas()->delete();
                    if (!empty($objData['metas'])) {
                        $metasInsert = array_map(function ($item) use ($objetivo) {
                            return ['id_obj_proy' => $objetivo->id_obj_proy, 'detalle_metas' => $item['detalle_metas']];
                        }, $objData['metas']);
                        Invi_metas::insert($metasInsert);
                    }

                    // 4. Limpiar e Insertar Supuestos
                    $objetivo->invi_supuestos()->delete();
                    if (!empty($objData['supuestos'])) {
                        $supuestosInsert = array_map(function ($item) use ($objetivo) {
                            return ['id_obj_proy' => $objetivo->id_obj_proy, 'detalle_supuestos' => $item['detalle_supuestos']];
                        }, $objData['supuestos']);
                        Invi_supuestos::insert($supuestosInsert);
                    }

                    // 5. Limpiar e Insertar Medios de Verificación
                    $objetivo->invi_medios_verificacion()->delete();
                    if (!empty($objData['medios_verificacion'])) {
                        $mediosInsert = array_map(function ($item) use ($objetivo) {
                            return ['id_obj_proy' => $objetivo->id_obj_proy, 'detalle_medio_verifica' => $item['detalle_medio_verifica']];
                        }, $objData['medios_verificacion']);
                        Invi_medios_verificacion::insert($mediosInsert);
                    }

                    // 6. Limpiar e Insertar Productos Verificables (Solo para Específicos)
                    $objetivo->invi_prod_verificables()->delete();
                    if ($objData['tipo_obj_proy'] === 'especifico' && !empty($objData['prod_verificables'])) {
                        $prodInsert = array_map(function ($item) use ($objetivo) {
                            return ['id_obj_proy' => $objetivo->id_obj_proy, 'detalle_prod_verif' => $item['detalle_prod_verif']];
                        }, $objData['prod_verificables']);
                        Invi_prod_verificables::insert($prodInsert);
                    }
                }

                // Eliminar objetivos que fueron borrados desde el Frontend
                Invi_Obj_Proy::where('proyect_id', $id)
                    ->whereNotIn('id_obj_proy', $ids_objetivos_recibidos)
                    ->delete();
            }
            Invi_detalle_inst_proy::where('proyect_id', $id)->delete();

            if ($request->has('empresas') && is_array($request->empresas)) {
                foreach ($request->empresas as $empresa) {
                    // SOLUCIÓN BACKEND: Tolerancia a fallos de tipos.
                    // Si viene un array/objeto del frontend extrae 'idempresa', de lo contrario usa el valor directo.
                    $id_empresa = is_array($empresa) ? ($empresa['idempresa'] ?? null) : $empresa;

                    if ($id_empresa) {
                        Invi_detalle_inst_proy::insert([
                            'proyect_id' => $id,
                            'idempresa'  => $id_empresa
                        ]);
                    }
                }
            }
            $detallesPresuAnteriores = Invi_detalle_presu_proy::where('proyect_id', $id)->get();
            $utlvtIdsParaEliminar = $detallesPresuAnteriores->pluck('id_aportes_utlvt')->filter()->toArray();
            $instIdsParaEliminar = $detallesPresuAnteriores->pluck('id_aportes_inst')->filter()->toArray();

            // Eliminamos la tabla pivote de detalle presupuesto
            Invi_detalle_presu_proy::where('proyect_id', $id)->delete();

            // Eliminamos los registros huérfanos correspondientes en las tablas de aportes
            if (!empty($utlvtIdsParaEliminar)) {
                Invi_aportesutlvt::whereIn('id_aportes_utlvt', $utlvtIdsParaEliminar)->delete();
            }
            if (!empty($instIdsParaEliminar)) {
                Invi_aportesinst::whereIn('id_aportes_inst', $instIdsParaEliminar)->delete();
            }
            $totalGeneralAcumulado = 0;
            // 3. Guardar Nuevos Aportes UTLVT (Universidad)
            if ($request->has('aportes_utlvt') && is_array($request->aportes_utlvt)) {
                foreach ($request->aportes_utlvt as $aporte) {
                    $actividad = $aporte['actividad'] ?? '';
                    $valor = $this->parseMoney($aporte['valor'] ?? 0);

                    if (!empty($actividad)) {
                        $totalGeneralAcumulado += $valor; // Acumulamos el valor

                        $nuevoAporteUtlvt = Invi_aportesutlvt::create([
                            'actividad' => $actividad,
                            'valor' => $valor
                        ]);

                        Invi_detalle_presu_proy::create([
                            'proyect_id' => $id,
                            'id_aportes_utlvt' => $nuevoAporteUtlvt->id_aportes_utlvt,
                            'id_aportes_inst' => null,
                            'total_presupuesto' => $valor
                        ]);
                    }
                }
            }

            // 4. Guardar Nuevos Aportes Institucionales
            if ($request->has('aportes_inst') && is_array($request->aportes_inst)) {
                foreach ($request->aportes_inst as $aporte) {
                    $idempresa = $aporte['idempresa'] ?? null;
                    $actividad = $aporte['actividad'] ?? '';
                    $valor = $this->parseMoney($aporte['valor'] ?? 0);

                    if ($idempresa && !empty($actividad)) {
                        // Verificación extra en backend de RUC de la empresa
                        $empresa = Praempresa::find($idempresa);
                        if ($empresa && $empresa->ruc !== '0860000830001') {

                            $totalGeneralAcumulado += $valor; // Acumulamos el valor

                            $nuevoAporteInst = Invi_aportesinst::create([
                                'idempresa' => $idempresa,
                                'actividad' => $actividad,
                                'valor' => $valor
                            ]);

                            Invi_detalle_presu_proy::create([
                                'proyect_id' => $id,
                                'id_aportes_utlvt' => null,
                                'id_aportes_inst' => $nuevoAporteInst->id_aportes_inst,
                                'total_presupuesto' => $valor
                            ]);
                        }
                    }
                }
            }
            Invi_detalle_articulacion::where('proyect_id', $id)->delete();
            if ($request->has('asignaturas') && is_array($request->asignaturas)) {
                foreach ($request->asignaturas as $idAsig) {
                    Invi_detalle_articulacion::insert([
                        'proyect_id' => $id,
                        'IdAsig'     => $idAsig
                    ]);
                }
            }
            if ($request->has('actividades') && is_array($request->actividades)) {
                $ids_actividades_recibidas = [];

                foreach ($request->actividades as $actData) {
                    $actividad = Invi_actividades::updateOrCreate(
                        [
                            'id_actividades' => $actData['id_actividades'] ?? null,
                            'proyect_id'     => $id
                        ],
                        [
                            'id_obj_proy'   => $actData['id_obj_proy'],
                            'nom_actividad' => $actData['nom_actividad'],
                            'responsables'  => $actData['responsables'] ?? null,
                            'fecha_desde'   => $actData['fecha_desde'] ?? null,
                            'fecha_hasta'   => $actData['fecha_hasta'] ?? null,
                            'horas'         => $actData['horas'] ?? 0,
                            'detalle_anio'  => $actData['detalle_anio'] ?? 'Primer Año',
                        ]
                    );

                    $ids_actividades_recibidas[] = $actividad->id_actividades;

                    // Subactividades
                    $actividad->invi_subactividad()->delete();
                    if (!empty($actData['invi_subactividad'])) {
                        $subData = array_map(fn($s) => [
                            'id_actividades' => $actividad->id_actividades,
                            'nom_sub_actv'   => $s['nom_sub_actv'],
                            'fecha_desde'    => $s['fecha_desde'] ?? null,
                            'fecha_hasta'    => $s['fecha_hasta'] ?? null,
                            'horas'          => $s['horas'] ?? 0,
                        ], $actData['invi_subactividad']);
                        Invi_subactividad::insert($subData);
                    }

                    // Productos Verificables
                    $actividad->invi_actprod_verificables()->delete();
                    if (!empty($actData['invi_actprod_verificables'])) {
                        $pvData = array_map(fn($pv) => [
                            'id_actividades'     => $actividad->id_actividades,
                            'detalle_prod_verif' => $pv['detalle_prod_verif'],
                        ], $actData['invi_actprod_verificables']);
                        Invi_actprod_verificables::insert($pvData);
                    }

                    // Medios de Verificación
                    $actividad->invi_actmedios_verificacion()->delete();
                    if (!empty($actData['invi_actmedios_verificacion'])) {
                        $mvData = array_map(fn($mv) => [
                            'id_actividades'         => $actividad->id_actividades,
                            'detalle_medio_verifica' => $mv['detalle_medio_verifica'],
                        ], $actData['invi_actmedios_verificacion']);
                        Invi_actmedios_verificacion::insert($mvData);
                    }

                    // Indicadores
                    $actividad->invi_actindicadores()->delete();
                    if (!empty($actData['invi_actindicadores'])) {
                        $indData = array_map(fn($ind) => [
                            'id_actividades'    => $actividad->id_actividades,
                            'detalle_indicador' => $ind['detalle_indicador'],
                        ], $actData['invi_actindicadores']);
                        Invi_actindicadores::insert($indData);
                    }

                    // Supuestos
                    $actividad->invi_actsupuestos()->delete();
                    if (!empty($actData['invi_actsupuestos'])) {
                        $supData = array_map(fn($sup) => [
                            'id_actividades'    => $actividad->id_actividades,
                            'detalle_supuestos' => $sup['detalle_supuestos'],
                        ], $actData['invi_actsupuestos']);
                        Invi_actsupuestos::insert($supData);
                    }
                }

                // Eliminar actividades removidas desde el frontend
                Invi_actividades::where('proyect_id', $id)
                    ->whereNotIn('id_actividades', $ids_actividades_recibidas)
                    ->delete();
            }
            $adquisicionesReq = $request->adquisiciones ?? [];

            // Obtener IDs de adquisiciones que vienen del frontend (los que ya existen)
            $adquiIdsReq = collect($adquisicionesReq)->pluck('id_adquisicion')->filter()->toArray();

            // Obtener detalles actuales en BD para este proyecto
            $detallesActuales = Invi_detalle_adqui::where('proyect_id', $id)->get();
            $adquiIdsActuales = $detallesActuales->pluck('id_adquisicion')->toArray();

            // Determinar cuáles hay que ELIMINAR (Están en BD pero ya no en el Request)
            $idsToDelete = array_diff($adquiIdsActuales, $adquiIdsReq);
            if (!empty($idsToDelete)) {
                Invi_detalle_adqui::whereIn('id_adquisicion', $idsToDelete)->where('proyect_id', $id)->delete();
                Invi_adquisicion::whereIn('id_adquisicion', $idsToDelete)->delete();
            }

            // CREAR o ACTUALIZAR las adquisiciones recibidas
            foreach ($adquisicionesReq as $adqReq) {
                if (isset($adqReq['id_adquisicion']) && $adqReq['id_adquisicion']) {
                    // Actualizar existente
                    Invi_adquisicion::where('id_adquisicion', $adqReq['id_adquisicion'])->update([
                        'tipo_adqui'          => $adqReq['tipo_adqui'],
                        'detalle'             => $adqReq['detalle'],
                        'porcent_nacio'       => $adqReq['porcent_nacio'],
                        'detalle_iinsu_nac'   => $adqReq['detalle_iinsu_nac'],
                        'porcent_importado'   => $adqReq['porcent_importado'],
                        'detalle_insu_import' => $adqReq['detalle_insu_import'],
                    ]);
                } else {
                    // Crear nueva
                    $nuevaAdqui = Invi_adquisicion::create([
                        'tipo_adqui'          => $adqReq['tipo_adqui'],
                        'detalle'             => $adqReq['detalle'],
                        'porcent_nacio'       => $adqReq['porcent_nacio'],
                        'detalle_iinsu_nac'   => $adqReq['detalle_iinsu_nac'],
                        'porcent_importado'   => $adqReq['porcent_importado'],
                        'detalle_insu_import' => $adqReq['detalle_insu_import'],
                    ]);

                    // Relacionarla al proyecto
                    Invi_detalle_adqui::create([
                        'proyect_id'     => $id,
                        'id_adquisicion' => $nuevaAdqui->id_adquisicion
                    ]);
                }
            }
            $financiamientosReq = $request->financiamientos ?? [];
            
            // Obtener IDs de financiamientos que vienen del frontend
            $finIdsReq = collect($financiamientosReq)->pluck('id_det_financia')->filter()->toArray();

            // Obtener detalles actuales en BD para este proyecto
            $detallesFinActuales = Invi_detalle_financia::where('proyect_id', $id)->get();
            $finIdsActuales = $detallesFinActuales->pluck('id_det_financia')->toArray();

            // Determinar cuáles hay que ELIMINAR (Están en BD pero ya no en el Request)
            $idsFinToDelete = array_diff($finIdsActuales, $finIdsReq);
            if (!empty($idsFinToDelete)) {
                Invi_detalle_financia::whereIn('id_det_financia', $idsFinToDelete)->delete();
            }

            // CREAR o ACTUALIZAR los financiamientos recibidos
            foreach ($financiamientosReq as $finReq) {
                $dataFin = [
                    'id_rubro'       => $finReq['id_rubro'],
                    'cantidad'       => $finReq['cantidad'],
                    'valor'          => $finReq['valor'],
                    'utlvte_anio1'   => $finReq['utlvte_anio1'] ?? 0,
                    'utlvte_anio2'   => $finReq['utlvte_anio2'] ?? 0,
                    'utlvte_anio3'   => $finReq['utlvte_anio3'] ?? 0,
                    'utlvte_anio4'   => $finReq['utlvte_anio4'] ?? 0,
                    'utlvte_anio5'   => $finReq['utlvte_anio5'] ?? 0,
                    'otros_anio1'    => $finReq['otros_anio1'] ?? 0,
                    'otros_anio2'    => $finReq['otros_anio2'] ?? 0,
                    'otros_anio3'    => $finReq['otros_anio3'] ?? 0,
                    'otros_anio4'    => $finReq['otros_anio4'] ?? 0,
                    'otros_anio5'    => $finReq['otros_anio5'] ?? 0,
                    'total_efectivo' => $finReq['total_efectivo'] ?? 0,
                ];

                if (isset($finReq['id_det_financia']) && $finReq['id_det_financia']) {
                    // Actualizar
                    Invi_detalle_financia::where('id_det_financia', $finReq['id_det_financia'])
                                         ->update($dataFin);
                } else {
                    // Crear
                    $dataFin['proyect_id'] = $id;
                    Invi_detalle_financia::create($dataFin);
                }
            }
            $impactosReq = $request->impactos ?? [];
            
            // Obtener IDs de impactos que vienen del frontend
            $impIdsReq = collect($impactosReq)->pluck('id_det_impactos_esp')->filter()->toArray();

            // Obtener detalles actuales en BD para este proyecto
            $detallesImpActuales = Invi_det_impactos_esperados::where('proyect_id', $id)->get();
            $impIdsActuales = $detallesImpActuales->pluck('id_det_impactos_esp')->toArray();

            // Determinar cuáles hay que ELIMINAR
            $idsImpToDelete = array_diff($impIdsActuales, $impIdsReq);
            if (!empty($idsImpToDelete)) {
                Invi_det_impactos_esperados::whereIn('id_det_impactos_esp', $idsImpToDelete)->delete();
            }

            // CREAR o ACTUALIZAR los impactos recibidos
            foreach ($impactosReq as $impReq) {
                $dataImp = [
                    'id_impactos'         => $impReq['id_impactos'],
                    'descripcion_general' => $impReq['descripcion_general']
                ];

                if (isset($impReq['id_det_impactos_esp']) && $impReq['id_det_impactos_esp']) {
                    // Actualizar
                    Invi_det_impactos_esperados::where('id_det_impactos_esp', $impReq['id_det_impactos_esp'])
                                               ->update($dataImp);
                } else {
                    // Crear
                    $dataImp['proyect_id'] = $id;
                    Invi_det_impactos_esperados::create($dataImp);
                }
            }
            $difusionReq = $request->difusion ?? [];
            
            // Obtener IDs de detalles de difusión que vienen del frontend
            $difIdsReq = collect($difusionReq)->pluck('id_det_difusion')->filter()->toArray();

            // Obtener detalles actuales en BD para este proyecto
            $detallesDifActuales = Invi_det_difusion::where('proyect_id', $id)->get();
            $difIdsActuales = $detallesDifActuales->pluck('id_det_difusion')->toArray();

            // Determinar cuáles hay que ELIMINAR
            $idsDifToDelete = array_diff($difIdsActuales, $difIdsReq);
            if (!empty($idsDifToDelete)) {
                // Obtenemos los id_difusion (catálogo) vinculados antes de eliminar el detalle
                $detallesAborrar = Invi_det_difusion::whereIn('id_det_difusion', $idsDifToDelete)->get();
                $idsCatalogoDifusionAborrar = $detallesAborrar->pluck('id_difusion')->filter()->toArray();
                
                // Eliminamos los detalles
                Invi_det_difusion::whereIn('id_det_difusion', $idsDifToDelete)->delete();
                
                // Eliminamos las actividades del catálogo vinculadas (ya que se crean en conjunto)
                if (!empty($idsCatalogoDifusionAborrar)) {
                    Invi_difusion::whereIn('id_difusion', $idsCatalogoDifusionAborrar)->delete();
                }
            }

            // CREAR o ACTUALIZAR los detalles de difusión
            foreach ($difusionReq as $difReq) {
                if (isset($difReq['id_det_difusion']) && $difReq['id_det_difusion']) {
                    // ACTUALIZAR
                    Invi_det_difusion::where('id_det_difusion', $difReq['id_det_difusion'])
                                     ->update(['costo' => $difReq['costo']]);
                    
                    if (isset($difReq['id_difusion']) && $difReq['id_difusion']) {
                        Invi_difusion::where('id_difusion', $difReq['id_difusion'])
                                     ->update(['nombre_actividad' => $difReq['nombre_actividad']]);
                    }
                } else {
                    // CREAR (Primero el catálogo, luego el detalle)
                    $nuevaDifusion = Invi_difusion::create([
                        'nombre_actividad' => $difReq['nombre_actividad']
                    ]);

                    Invi_det_difusion::create([
                        'proyect_id'  => $id,
                        'id_difusion' => $nuevaDifusion->id_difusion,
                        'costo'       => $difReq['costo']
                    ]);
                }
            }
            $bibReq = $request->bibliografias ?? [];
            
            // Obtener IDs de bibliografías que vienen del frontend
            $bibIdsReq = collect($bibReq)->pluck('id_bibliografia')->filter()->toArray();

            // Obtener bibliografías actuales en BD para este proyecto
            $bibActuales = Invi_bibliografias::where('proyect_id', $id)->get();
            $bibIdsActuales = $bibActuales->pluck('id_bibliografia')->toArray();

            // Determinar cuáles hay que ELIMINAR
            $idsBibToDelete = array_diff($bibIdsActuales, $bibIdsReq);
            if (!empty($idsBibToDelete)) {
                Invi_bibliografias::whereIn('id_bibliografia', $idsBibToDelete)->delete();
            }

            // CREAR o ACTUALIZAR las bibliografías
            foreach ($bibReq as $bib) {
                $dataBib = [
                    'seccion_campo'    => $bib['seccion_campo'],
                    'autor'            => $bib['autor'],
                    'anio'             => $bib['anio'],
                    'titulo'           => $bib['titulo'],
                    'editorial_fuente' => $bib['editorial_fuente'],
                ];

                if (isset($bib['id_bibliografia']) && $bib['id_bibliografia']) {
                    // Actualizar
                    Invi_bibliografias::where('id_bibliografia', $bib['id_bibliografia'])
                                      ->update($dataBib);
                } else {
                    // Crear
                    $dataBib['proyect_id'] = $id;
                    Invi_bibliografias::create($dataBib);
                }
            }


            DB::commit();
            return response()->json(['message' => 'Proyecto actualizado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }
    private function parseMoney($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        // Quita puntos de miles y cambia la coma decimal por un punto
        $clean = str_replace('.', '', $value);
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function buscarIntegrante(Request $request)
    {
        $cedula = $request->cedula;
        $proyect_id_actual = $request->proyect_id;
        $es_reemplazo = $request->es_reemplazo;

        // 1. Buscar Datos Personales
        $docente = InformacionPersonalD::where('CIInfPer', $cedula)->first();
        $estudiante = informacionpersonal::where('CIInfPer', $cedula)->first();

        if (!$docente && !$estudiante) {
            return response()->json(['message' => 'Integrante no encontrado en la base de datos institucional.'], 404);
        }

        $persona = $docente ?: $estudiante;
        $tipo = $docente ? 'doc' : 'est';

        // 2. Obtener TODOS los proyectos de INVESTIGACIÓN donde está activo
        $proyectosActivos = Invi_deta_inte::where(function ($q) use ($cedula) {
            $q->where('ciinfper_doc', $cedula)->orWhere('ciinfper_est', $cedula);
        })
            ->where('reemplazado', 0)
            ->where('estado', 1) // Aseguramos que el registro esté vigente
            ->whereHas('invi_proyectos', function ($query) {
                $query->where('proyect_tipo', 'INVESTIGACIÓN');
            })
            ->with('invi_proyectos')
            ->get();

        $conteoProyectos = $proyectosActivos->count();
        $estaEnProyectoActual = $proyectosActivos->contains('proyect_id', $proyect_id_actual);
        $listaNombres = $proyectosActivos->pluck('invi_proyectos.proyect_nombre')->toArray();

        // --- LÓGICA DE VALIDACIÓN BASADA EN EL PUNTO 5 ---

        // CASO ESPECIAL: Ya está en el MISMO proyecto
        if ($estaEnProyectoActual && !$es_reemplazo) {
            return response()->json([
                'message' => "Este integrante ya forma parte de los miembros activos de este proyecto."
            ], 422);
        }

        // REGLA: Máximo 3 proyectos
        if ($conteoProyectos >= 3 && !$estaEnProyectoActual) {
            return response()->json([
                'message' => "El integrante ya alcanzó el límite máximo permitido. Actualmente participa en 3 proyectos: " . implode(', ', $listaNombres),
                'proyectos' => $listaNombres
            ], 422);
        }

        // ADVERTENCIA: Si está en 1 o 2 proyectos (y no es el actual)
        $advertencia = null;
        if ($conteoProyectos > 0 && !$estaEnProyectoActual) {
            $num = $conteoProyectos == 1 ? "un proyecto" : "dos proyectos";
            $advertencia = "Nota: El integrante ya pertenece a {$num}. Según el Punto 5 de la covocatoria de proyectos de Investigación: 'Se podrá participar en un máximo de tres proyectos de investigación', el usuario aún puede ser añadido a este nuevo proyecto.";
        }

        return response()->json([
            'cedula' => $persona->CIInfPer,
            'nombre_completo' => "{$persona->NombInfPer} {$persona->ApellInfPer} {$persona->ApellMatInfPer}",
            'tipo' => $tipo,
            'advertencia' => $advertencia,
            'proyectos_actuales' => $listaNombres,
            'conteo' => $conteoProyectos
        ]);
    }
    public function guardarCambios(Request $request)
    {
        try {
            DB::beginTransaction();

            $modo = $request->modo;
            $form = $request->form;
            $reemplazoConfig = $request->reemplazo_config;
            $proyect_id = $request->proyect_id;
            $compromisos = $form['compromisos'] ?? [];
            $integranteAfectado = null;
            // --- OBTENER CÓDIGO DEL PROYECTO ---
            $proyecto = Invi_proyectos::find($proyect_id);
            $codigoProyect = $proyecto?->proyect_cod ?? 'S/N';
            // Buscamos la función que se intenta asignar
            $funcionSolicitada = Invi_funcion::find($form['id_funcion']);
            $nombreUpper = strtoupper($funcionSolicitada?->nombre_funcion ?? '');

            // Verificamos si es Director o Subdirector por texto
            $esDirectivo = str_contains($nombreUpper, 'DIRECTOR');
            if ($modo === 'nuevo') {
                $cedula = $form['cedula_nueva'];
                $existe = Invi_deta_inte::where('proyect_id', $proyect_id)
                    ->where(function ($q) use ($cedula) {
                        $q->where('ciinfper_doc', $cedula)->orWhere('ciinfper_est', $cedula);
                    })
                    ->where('estado', 1)
                    ->exists();

                if ($existe) {
                    DB::rollBack();
                    return response()->json(['message' => "Esta persona ya figura como integrante activo en este proyecto."], 422);
                }
            }
            if ($modo === 'nuevo') {
                // Validar que no se agregue Director/Subdirector si ya existen
                if ($esDirectivo) {
                    $existe = Invi_deta_inte::where('proyect_id', $request->proyect_id)
                        ->where('id_funcion', $form['id_funcion'])
                        ->where('reemplazado', 0)
                        ->where('estado', 1)
                        ->exists();
                    if ($existe) return response()->json(['message' => "Ya existe un {$funcionSolicitada->nombre_funcion} activo."], 422);
                }

                $integranteAfectado = Invi_deta_inte::create([
                    'proyect_id' => $proyect_id,
                    'ciinfper_doc' => $form['tipo_nuevo'] == 'doc' ? $form['cedula_nueva'] : null,
                    'ciinfper_est' => $form['tipo_nuevo'] == 'est' ? $form['cedula_nueva'] : null,
                    'horas' => $form['horas'],
                    'reemplazado' => 0,
                    'id_funcion' => $form['id_funcion'],
                    'idCarr' => $form['idCarr'],
                    'anexo_integrante2' => $form['anexo_integrante2'],
                    'estado' => 1,
                ]);
                $accionBitacora = "REGISTRO DE NUEVO INTEGRANTE INVESTIGACIÓN";
                $obsBitacora = "Se agregó a la cédula {$form['cedula_nueva']} al proyecto: {$codigoProyect} con función {$nombreUpper}";
            } else {
                // MODO EDICIÓN
                $registroOriginal = Invi_deta_inte::findOrFail($request->id_deta_invi_proyect);

                if ($form['reemplazado'] == 1) {
                    // 1. Procesar al que SALE (Registro Original)
                    if ($reemplazoConfig['mantener_docente']) {
                        $registroOriginal->update([
                            'reemplazado' => 1,
                            'id_funcion'  => $reemplazoConfig['nueva_funcion_reemplazado'],
                            'horas'       => $reemplazoConfig['nuevas_horas_reemplazado'] ?? 0,
                            'estado' => 1
                        ]);
                    } else {
                        $registroOriginal->update([
                            'reemplazado' => 1,
                            'id_funcion'  => null,
                            'horas'       => 0,
                            'estado' => 0
                        ]);
                    }

                    // 2. Procesar al que ENTRA (El reemplazo)
                    $cedulaNueva = $form['cedula_nueva'];

                    // BUSCAMOS si esta persona ya estaba en el proyecto (aunque sea con otro rol)
                    $integranteExistente = Invi_deta_inte::where('proyect_id', $proyect_id)
                        ->where(function ($q) use ($cedulaNueva) {
                            $q->where('ciinfper_doc', $cedulaNueva)
                                ->orWhere('ciinfper_est', $cedulaNueva);
                        })
                        ->first();

                    $datosNuevoRol = [
                        'proyect_id'    => $proyect_id,
                        'ciinfper_doc'  => $form['tipo_nuevo'] == 'doc' ? $cedulaNueva : null,
                        'ciinfper_est'  => $form['tipo_nuevo'] == 'est' ? $cedulaNueva : null,
                        'horas'         => $form['horas'],
                        'reemplazado'   => 0, // El nuevo rol siempre entra como activo
                        'id_funcion'    => $form['id_funcion'],
                        'idCarr'        => $form['idCarr'],
                        'anexo_integrante' => $form['anexo_integrante'],
                        'estado' => 1
                    ];

                    if ($integranteExistente) {
                        // SI YA EXISTÍA: Lo actualizamos en lugar de crear uno nuevo
                        $integranteExistente->update($datosNuevoRol);
                        $integranteAfectado = $integranteExistente;
                        $accionBitacora = "REEMPLAZO DE INTEGRANTE EXISTENTE INVESTIGACIÓN";
                        $obsBitacora = "Reemplazo en proyecto: {$codigoProyect}. Reemplazo del integrante ID: {$request->id_deta_invi_proyect}, por un docente del mismo proyecto con cédula: {$form['cedula_nueva']}";
                    } else {
                        // SI NO EXISTÍA: Lo creamos
                        $integranteAfectado = Invi_deta_inte::create($datosNuevoRol);
                        $accionBitacora = "REEMPLAZO DE INTEGRANTE POR UN DOCENTE NUEVO INVESTIGACIÓN";
                        $obsBitacora = "Reemplazo en proyecto: {$codigoProyect}. Reemplazo del integrante ID: {$request->id_deta_invi_proyect}, por un docente nuevo con cédula: {$form['cedula_nueva']}";
                    }
                } else {
                    // Edición simple sin reemplazo
                    $registroOriginal->update([
                        'id_funcion' => $form['id_funcion'],
                        'idCarr'     => $form['idCarr'],
                        'horas'      => $form['horas'],
                        'anexo_integrante2' => $form['anexo_integrante2'],
                        'reemplazado' => 0,
                        'estado' => 1
                    ]);
                    $integranteAfectado = $registroOriginal;
                    $accionBitacora = "EDICIÓN DE INTEGRANTE INVESTIGACIÓN";
                    $obsBitacora = "Se editaron datos del integrante ID: {$request->id_deta_invi_proyect} en proyecto: {$codigoProyect}";
                }
            }
            if ($integranteAfectado && is_array($compromisos)) {
                // Si la clave primaria no está configurada en el modelo, el ID se guarda en ->id
                // Usamos getKey() que es más seguro, o el fallback (??)
                $idDetalleIntegrante = $integranteAfectado->id_deta_invi_proyect ?? $integranteAfectado->id;
                
                // Eliminamos compromisos previos para evitar duplicados en la edición
                Invi_compromiso::where('id_deta_invi_proyect', $idDetalleIntegrante)->delete();
                
                // Iteramos sobre el arreglo para crear todos los seleccionados
                foreach ($compromisos as $detalle) {
                    Invi_compromiso::create([
                        'id_deta_invi_proyect' => $idDetalleIntegrante,
                        'detalle_compromiso'   => $detalle,
                    ]);
                }
            }

            DB::commit();
            // --- REGISTRO EN BITÁCORA (Post-Commit) ---
            try {
                $user = Auth::user(); // Obtenemos el usuario autenticado
                Bitacora::create([
                    'bt_usuario'     => $user->ciinfper,
                    'bt_fechahora'   => Carbon::now(),
                    'bt_accion'      => $accionBitacora . " - INVESTIGACIÓN",
                    'bt_ippc'        => $request->ip(),
                    'bt_observacion' => "USUARIO: {$user->NombUsu} REALIZÓ: {$obsBitacora}",
                ]);
            } catch (\Exception $ex) {
                Log::error("Error bitácora en guardarCambios: " . $ex->getMessage());
            }
            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function catalogos()
    {
        $facultad = ['1', '2', '3', '4', '5', '11'];

        return response()->json([
            'funciones' => Invi_funcion::where('estado', 1)
                ->where('tipo_funcion', '=', 'INVESTIGACIÓN')
                ->get(),
            'carreras' => Carreras::where('StatusCarr', '=', 1)
                ->wherein('idfacultad', $facultad)
                ->where('NombCarr', 'NOT LIKE', '%TRABAJO DE INTEGRACIÓN CURRICULAR%')
                ->get(),
        ]);
    }

    public function inhabilitar(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'anexo_integrante' => 'required|string'
        ]);
        $integrante = Invi_deta_inte::findOrFail($request->id);
        // Guardamos los datos necesarios para la bitácora antes de limpiar los campos
        $cedulaAfectada = $integrante->ciinfper_doc ?? $integrante->ciinfper_est;
        $codigoProyect = $integrante->invi_proyectos?->proyect_cod ?? 'S/N';

        $integrante->update([
            'horas' => 0,
            'estado' => 0,
            'id_funcion' => null,
            'anexo_integrante' => $request->anexo_integrante
        ]);
        // --- REGISTRO EN BITÁCORA ---
        try {
            $user = Auth::user();
            Bitacora::create([
                'bt_usuario'     => $user->ciinfper,
                'bt_fechahora'   => Carbon::now(),
                'bt_accion'      => 'INHABILITAR INTEGRANTE - INVESTIGACIÓN',
                'bt_ippc'        => $request->ip(),
                'bt_observacion' => "USUARIO: {$user->NombUsu} INHABILITÓ AL INTEGRANTE CÉDULA: {$cedulaAfectada} DEL PROYECTO: {$codigoProyect}. MOTIVO/ANEXO: {$request->anexo_integrante}",
            ]);
        } catch (\Exception $e) {
            Log::error("Error al registrar bitácora en inhabilitar: " . $e->getMessage());
        }
        // --- FIN REGISTRO EN BITÁCORA ---

        return response()->json(['message' => 'Integrante inhabilitado correctamente']);
    }
    public function uploadArchivo(Request $request)
    {
        if ($request->hasFile('file')) {
            Log::info("Archivo detectado: " . $request->file('file')->getClientOriginalName());
            Log::info("Error de subida PHP: " . $request->file('file')->getError());
            Log::info("Tamaño recibido: " . $request->file('file')->getSize());
        } else {
            Log::warning("No se detectó ningún archivo en la petición.");
        }
        $request->validate([
            'file' => 'required|max:10240', // 10MB
            'ci' => 'required|alpha_dash',
            'old_filename' => 'nullable|string',
        ]);

        try {
            $ci = basename($request->ci);
            $file = $request->file('file');
            if (!$file->isValid()) {
                throw new \Exception("Archivo inválido o corrupto.");
            }
            if ($request->filled('old_filename')) {
                $oldFilename = basename($request->old_filename); // Seguridad extra
                $oldPath = public_path("Documentos/INVESTIGACION/AnexoIntegrante/{$ci}/{$oldFilename}");
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            // Crear carpeta si no existe
            $directory = public_path("Documentos/INVESTIGACION/AnexoIntegrante/{$ci}");

            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            // Generar nombre: CI + _ + aleatorio + _ + fecha (Ymd_His)
            $aleatorio = bin2hex(random_bytes(8)); // 16 caracteres hex
            $fechaHora = date("Ymd_His");          // Ej: 20251112_1741
            $extension = $file->getClientOriginalExtension(); // pdf

            $filename = "{$ci}_{$aleatorio}_{$fechaHora}.{$extension}";

            // Guardar archivo
            $file->move($directory, $filename);

            // URL pública
            $url = url('Documentos/INVESTIGACION/AnexoIntegrante/' . $ci . '/' . $filename);

            return response()->json([
                'status'   => true,
                'filename' => $filename,
                'url'      => $url
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Seguridad: El archivo no pudo ser procesado.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function uploadArchivoDarBaja(Request $request)
    {
        if ($request->hasFile('file')) {
            Log::info("Archivo detectado: " . $request->file('file')->getClientOriginalName());
            Log::info("Error de subida PHP: " . $request->file('file')->getError());
            Log::info("Tamaño recibido: " . $request->file('file')->getSize());
        } else {
            Log::warning("No se detectó ningún archivo en la petición.");
        }
        $request->validate([
            'file' => 'required|max:10240', // 10MB
            'ci' => 'required|alpha_dash',
            'old_filename' => 'nullable|string',
        ]);

        try {
            $ci = basename($request->ci);
            $file = $request->file('file');
            if (!$file->isValid()) {
                throw new \Exception("Archivo inválido o corrupto.");
            }
            if ($request->filled('old_filename')) {
                $oldFilename = basename($request->old_filename); // Seguridad extra
                $oldPath = public_path("Documentos/INVESTIGACION/Bajas_Docentes/Anexo/{$ci}/{$oldFilename}");
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            // Crear carpeta si no existe
            $directory = public_path("Documentos/INVESTIGACION/Bajas_Docentes/Anexo/{$ci}");

            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            // Generar nombre: CI + _ + aleatorio + _ + fecha (Ymd_His)
            $aleatorio = bin2hex(random_bytes(8)); // 16 caracteres hex
            $fechaHora = date("Ymd_His");          // Ej: 20251112_1741
            $extension = $file->getClientOriginalExtension(); // pdf

            $filename = "{$ci}_{$aleatorio}_{$fechaHora}.{$extension}";

            // Guardar archivo
            $file->move($directory, $filename);

            // URL pública
            $url = url('Documentos/INVESTIGACION/Bajas_Docentes/Anexo/' . $ci . '/' . $filename);

            return response()->json([
                'status'   => true,
                'filename' => $filename,
                'url'      => $url
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Seguridad: El archivo no pudo ser procesado.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function getDirectoresProyectosInvestigacion(Request $request): JsonResponse
    {
        try {
            // 1. Consulta base utilizando Eager Loading para optimizar el rendimiento e incluir los títulos académicos
            $integrantes = Invi_deta_inte::with([
                'invi_proyectos',
                'funciones',
                'carreras.facultades',
                'informacionPersonalD.titulos.nivel', // <-- Incluimos la relación anidada para evaluar los títulos
            ])
                // Integrantes en estado 1 (Activos)
                ->where('estado', 1)

                // Filtramos por el tipo de proyecto
                ->whereHas('invi_proyectos', function ($query) {
                    $query->where('proyect_tipo', 'INVESTIGACIÓN');
                })

                // Filtramos de forma estricta para evitar "Subdirector"
                ->whereHas('funciones', function ($query) {
                    $query->where('tipo_funcion', 'INVESTIGACIÓN')
                        ->where('nombre_funcion', 'LIKE', '%Director%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Subdirector%'); // <-- Excluimos explícitamente Subdirector
                })
                ->get();

            $resultado = [];

            foreach ($integrantes as $integrante) {
                $persona = $integrante->informacionPersonalD;

                if (! $persona) {
                    continue; // Si el integrante no tiene registro de información de personal, pasamos al siguiente
                }

                // 2. Lógica de Género para Nombres y Cargos
                $esMujer = strtoupper(trim($persona->GeneroPer)) === 'F';
                $cargoFormateado = $esMujer ? 'Directora' : 'Director';

                // 3. Procesar títulos académicos filtrando por nv_numnivel (Eager Loaded)
                $tituloGrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
                });

                $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
                });

                $prefijoNombre = '';
                $sufijoNombre = '';

                // A. Mapeo ampliado de PREFIJOS (3er Nivel)
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

                // Formatear Nombres y Apellidos en formato Title Case
                $nombreRaw = trim($persona->NombInfPer . ' ' . $persona->ApellInfPer . ' ' . $persona->ApellMatInfPer);
                $nombresFormateados = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

                // Construcción del nombre con rango académico completo
                $nombreCompletoConTitulo = trim("{$prefijoNombre} {$nombresFormateados}{$sufijoNombre}");

                // 4. Obtener y formatear el Nombre de la Carrera
                $nombreCarrera = 'No asignada';
                if ($integrante->carreras) {
                    $nombreCarreraRaw = trim($integrante->carreras->NombCarr);
                    $nombreCarrera = mb_convert_case(mb_strtolower($nombreCarreraRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                }
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarrera);

                // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
                $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
                // 5. Obtener la Facultad y sus Siglas
                $nombreFacultad = 'No asignada';
                $siglasFacultad = 'No asignada';
                if ($integrante->carreras && $integrante->carreras->facultades->isNotEmpty()) {
                    $facultadAsociada = $integrante->carreras->facultades->first();
                    $nombreFacultad = mb_convert_case(mb_strtolower(trim($facultadAsociada->facultad), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $siglasFacultad = trim($facultadAsociada->siglas);
                }

                // 6. Estructura del objeto individual
                $resultado[] = [
                    'cedula' => $persona->CIInfPer,
                    'nombres_apellidos' => $nombresFormateados,      // Solo nombres limpios
                    'nombre_con_titulo' => $nombreCompletoConTitulo, // Formato "Ing. Juan Perez, MSc."
                    'funcion' => $integrante->funciones->nombre_funcion ?? 'Desconocida',
                    'cargo_genero' => $cargoFormateado,         // Regresa "Director" o "Directora"
                    'carrera' => $nombreCarreraFinal,
                    'facultad' => $nombreFacultad,
                    'siglas' => $siglasFacultad,
                    'proyecto_nombre' => $integrante->invi_proyectos->proyect_nombre ?? 'No registrado',
                    'titulo_grado' => $tituloGrado ? $tituloGrado->ad_titulo : null,
                    'titulo_posgrado' => $tituloPosgrado ? $tituloPosgrado->ad_titulo : null,
                ];
            }

            // 7. Retornar la respuesta JSON
            return response()->json([
                'success' => true,
                'total' => count($resultado),
                'data' => $resultado,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al consultar los directores de proyectos.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function getDirectoresiNDProyectosInvestigacion(string $id): JsonResponse
    {
        try {
            // 1. Consulta base utilizando Eager Loading para optimizar el rendimiento e incluir los títulos académicos
            $integrantes = Invi_deta_inte::with([
                'invi_proyectos',
                'funciones',
                'carreras.facultades',
                'informacionPersonalD.titulos.nivel', // <-- Incluimos la relación anidada para evaluar los títulos
            ])
                // Integrantes en estado 1 (Activos)
                ->where('estado', 1)
                ->where('proyect_id', $id)
                // Filtramos por el tipo de proyecto
                ->whereHas('invi_proyectos', function ($query) {
                    $query->where('proyect_tipo', 'INVESTIGACIÓN');
                })

                // Filtramos de forma estricta para evitar "Subdirector"
                ->whereHas('funciones', function ($query) {
                    $query->where('tipo_funcion', 'INVESTIGACIÓN')
                        ->where('nombre_funcion', 'LIKE', '%Director%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Subdirector%'); // <-- Excluimos explícitamente Subdirector
                })
                ->get();

            $resultado = [];

            foreach ($integrantes as $integrante) {
                $persona = $integrante->informacionPersonalD;

                if (! $persona) {
                    continue; // Si el integrante no tiene registro de información de personal, pasamos al siguiente
                }

                // 2. Lógica de Género para Nombres y Cargos
                $esMujer = strtoupper(trim($persona->GeneroPer)) === 'F';
                $cargoFormateado = $esMujer ? 'Directora' : 'Director';

                // 3. Procesar títulos académicos filtrando por nv_numnivel (Eager Loaded)
                $tituloGrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
                });

                $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
                });

                $prefijoNombre = '';
                $sufijoNombre = '';

                // A. Mapeo ampliado de PREFIJOS (3er Nivel)
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

                // Formatear Nombres y Apellidos en formato Title Case
                $nombreRaw = trim($persona->NombInfPer . ' ' . $persona->ApellInfPer . ' ' . $persona->ApellMatInfPer);
                $nombresFormateados = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

                // Construcción del nombre con rango académico completo
                $nombreCompletoConTitulo = trim("{$prefijoNombre} {$nombresFormateados}{$sufijoNombre}");

                // 4. Obtener y formatear el Nombre de la Carrera
                $nombreCarrera = 'No asignada';
                if ($integrante->carreras) {
                    $nombreCarreraRaw = trim($integrante->carreras->NombCarr);
                    $nombreCarrera = mb_convert_case(mb_strtolower($nombreCarreraRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                }
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarrera);

                // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
                $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
                // 5. Obtener la Facultad y sus Siglas
                $nombreFacultad = 'No asignada';
                $siglasFacultad = 'No asignada';
                if ($integrante->carreras && $integrante->carreras->facultades->isNotEmpty()) {
                    $facultadAsociada = $integrante->carreras->facultades->first();
                    $nombreFacultad = mb_convert_case(mb_strtolower(trim($facultadAsociada->facultad), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $siglasFacultad = trim($facultadAsociada->siglas);
                }

                // 6. Estructura del objeto individual
                $resultado[] = [
                    'cedula' => $persona->CIInfPer,
                    'nombres_apellidos' => $nombresFormateados,      // Solo nombres limpios
                    'nombre_con_titulo' => $nombreCompletoConTitulo, // Formato "Ing. Juan Perez, MSc."
                    'funcion' => $integrante->funciones->nombre_funcion ?? 'Desconocida',
                    'cargo_genero' => $cargoFormateado,         // Regresa "Director" o "Directora"
                    'carrera' => $nombreCarreraFinal,
                    'facultad' => $nombreFacultad,
                    'siglas' => $siglasFacultad,
                    'proyecto_nombre' => $integrante->invi_proyectos->proyect_nombre ?? 'No registrado',
                    'titulo_grado' => $tituloGrado ? $tituloGrado->ad_titulo : null,
                    'titulo_posgrado' => $tituloPosgrado ? $tituloPosgrado->ad_titulo : null,
                ];
            }

            // 7. Retornar la respuesta JSON
            return response()->json([
                'success' => true,
                'total' => count($resultado),
                'data' => $resultado,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al consultar los directores de proyectos.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function getSubDireProyectosInvestigacion(Request $request): JsonResponse
    {
        try {
            // 1. Consulta base utilizando Eager Loading para optimizar el rendimiento e incluir los títulos académicos
            $integrantes = Invi_deta_inte::with([
                'invi_proyectos',
                'funciones',
                'carreras.facultades',
                'informacionPersonalD.titulos.nivel', // <-- Incluimos la relación anidada para evaluar los títulos
            ])
                // Integrantes en estado 1 (Activos)
                ->where('estado', 1)

                // Filtramos por el tipo de proyecto
                ->whereHas('invi_proyectos', function ($query) {
                    $query->where('proyect_tipo', 'INVESTIGACIÓN');
                })

                // Filtramos de forma estricta para evitar "Subdirector"
                ->whereHas('funciones', function ($query) {
                    $query->where('tipo_funcion', 'INVESTIGACIÓN')
                        ->where('nombre_funcion', 'LIKE', '%Subdirector%'); // <-- Excluimos explícitamente Subdirector
                })
                ->get();

            $resultado = [];

            foreach ($integrantes as $integrante) {
                $persona = $integrante->informacionPersonalD;

                if (! $persona) {
                    continue; // Si el integrante no tiene registro de información de personal, pasamos al siguiente
                }

                // 2. Lógica de Género para Nombres y Cargos
                $esMujer = strtoupper(trim($persona->GeneroPer)) === 'F';
                $cargoFormateado = $esMujer ? 'Subdirectora' : 'Subdirector';

                // 3. Procesar títulos académicos filtrando por nv_numnivel (Eager Loaded)
                $tituloGrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
                });

                $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
                });

                $prefijoNombre = '';
                $sufijoNombre = '';

                // A. Mapeo ampliado de PREFIJOS (3er Nivel)
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

                // Formatear Nombres y Apellidos en formato Title Case
                $nombreRaw = trim($persona->NombInfPer . ' ' . $persona->ApellInfPer . ' ' . $persona->ApellMatInfPer);
                $nombresFormateados = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

                // Construcción del nombre con rango académico completo
                $nombreCompletoConTitulo = trim("{$prefijoNombre} {$nombresFormateados}{$sufijoNombre}");

                // 4. Obtener y formatear el Nombre de la Carrera
                $nombreCarrera = 'No asignada';
                if ($integrante->carreras) {
                    $nombreCarreraRaw = trim($integrante->carreras->NombCarr);
                    $nombreCarrera = mb_convert_case(mb_strtolower($nombreCarreraRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                }
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarrera);

                // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
                $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
                // 5. Obtener la Facultad y sus Siglas
                $nombreFacultad = 'No asignada';
                $siglasFacultad = 'No asignada';
                if ($integrante->carreras && $integrante->carreras->facultades->isNotEmpty()) {
                    $facultadAsociada = $integrante->carreras->facultades->first();
                    $nombreFacultad = mb_convert_case(mb_strtolower(trim($facultadAsociada->facultad), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $siglasFacultad = trim($facultadAsociada->siglas);
                }

                // 6. Estructura del objeto individual
                $resultado[] = [
                    'cedula' => $persona->CIInfPer,
                    'nombres_apellidos' => $nombresFormateados,      // Solo nombres limpios
                    'nombre_con_titulo' => $nombreCompletoConTitulo, // Formato "Ing. Juan Perez, MSc."
                    'funcion' => $integrante->funciones->nombre_funcion ?? 'Desconocida',
                    'cargo_genero' => $cargoFormateado,         // Regresa "Director" o "Directora"
                    'carrera' => $nombreCarreraFinal,
                    'facultad' => $nombreFacultad,
                    'siglas' => $siglasFacultad,
                    'proyecto_nombre' => $integrante->invi_proyectos->proyect_nombre ?? 'No registrado',
                    'titulo_grado' => $tituloGrado ? $tituloGrado->ad_titulo : null,
                    'titulo_posgrado' => $tituloPosgrado ? $tituloPosgrado->ad_titulo : null,
                ];
            }

            // 7. Retornar la respuesta JSON
            return response()->json([
                'success' => true,
                'total' => count($resultado),
                'data' => $resultado,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al consultar los directores de proyectos.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function getSubDireIndProyectosInvestigacion(string $id): JsonResponse
    {
        try {
            // 1. Consulta base utilizando Eager Loading para optimizar el rendimiento e incluir los títulos académicos
            $integrantes = Invi_deta_inte::with([
                'invi_proyectos',
                'funciones',
                'carreras.facultades',
                'informacionPersonalD.titulos.nivel', // <-- Incluimos la relación anidada para evaluar los títulos
            ])
                // Integrantes en estado 1 (Activos)
                ->where('estado', 1)
                ->where('proyect_id', $id)
                // Filtramos por el tipo de proyecto
                ->whereHas('invi_proyectos', function ($query) {
                    $query->where('proyect_tipo', 'INVESTIGACIÓN');
                })

                // Filtramos de forma estricta para evitar "Subdirector"
                ->whereHas('funciones', function ($query) {
                    $query->where('tipo_funcion', 'INVESTIGACIÓN')
                        ->where('nombre_funcion', 'LIKE', '%Subdirector%'); // <-- Excluimos explícitamente Subdirector
                })
                ->get();

            $resultado = [];

            foreach ($integrantes as $integrante) {
                $persona = $integrante->informacionPersonalD;

                if (! $persona) {
                    continue; // Si el integrante no tiene registro de información de personal, pasamos al siguiente
                }

                // 2. Lógica de Género para Nombres y Cargos
                $esMujer = strtoupper(trim($persona->GeneroPer)) === 'F';
                $cargoFormateado = $esMujer ? 'Subdirectora' : 'Subdirector';

                // 3. Procesar títulos académicos filtrando por nv_numnivel (Eager Loaded)
                $tituloGrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
                });

                $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
                });

                $prefijoNombre = '';
                $sufijoNombre = '';

                // A. Mapeo ampliado de PREFIJOS (3er Nivel)
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

                // Formatear Nombres y Apellidos en formato Title Case
                $nombreRaw = trim($persona->NombInfPer . ' ' . $persona->ApellInfPer . ' ' . $persona->ApellMatInfPer);
                $nombresFormateados = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

                // Construcción del nombre con rango académico completo
                $nombreCompletoConTitulo = trim("{$prefijoNombre} {$nombresFormateados}{$sufijoNombre}");

                // 4. Obtener y formatear el Nombre de la Carrera
                $nombreCarrera = 'No asignada';
                if ($integrante->carreras) {
                    $nombreCarreraRaw = trim($integrante->carreras->NombCarr);
                    $nombreCarrera = mb_convert_case(mb_strtolower($nombreCarreraRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                }
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarrera);

                // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
                $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
                // 5. Obtener la Facultad y sus Siglas
                $nombreFacultad = 'No asignada';
                $siglasFacultad = 'No asignada';
                if ($integrante->carreras && $integrante->carreras->facultades->isNotEmpty()) {
                    $facultadAsociada = $integrante->carreras->facultades->first();
                    $nombreFacultad = mb_convert_case(mb_strtolower(trim($facultadAsociada->facultad), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $siglasFacultad = trim($facultadAsociada->siglas);
                }

                // 6. Estructura del objeto individual
                $resultado[] = [
                    'cedula' => $persona->CIInfPer,
                    'nombres_apellidos' => $nombresFormateados,      // Solo nombres limpios
                    'nombre_con_titulo' => $nombreCompletoConTitulo, // Formato "Ing. Juan Perez, MSc."
                    'funcion' => $integrante->funciones->nombre_funcion ?? 'Desconocida',
                    'cargo_genero' => $cargoFormateado,         // Regresa "Director" o "Directora"
                    'carrera' => $nombreCarreraFinal,
                    'facultad' => $nombreFacultad,
                    'siglas' => $siglasFacultad,
                    'proyecto_nombre' => $integrante->invi_proyectos->proyect_nombre ?? 'No registrado',
                    'titulo_grado' => $tituloGrado ? $tituloGrado->ad_titulo : null,
                    'titulo_posgrado' => $tituloPosgrado ? $tituloPosgrado->ad_titulo : null,
                ];
            }

            // 7. Retornar la respuesta JSON
            return response()->json([
                'success' => true,
                'total' => count($resultado),
                'data' => $resultado,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al consultar los directores de proyectos.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function getDocentesProyectosInvestigacion(Request $request): JsonResponse
    {
        try {
            // 1. Consulta base utilizando Eager Loading para optimizar el rendimiento e incluir los títulos académicos
            $integrantes = Invi_deta_inte::with([
                'invi_proyectos',
                'funciones',
                'carreras.facultades',
                'informacionPersonalD.titulos.nivel', // <-- Incluimos la relación anidada para evaluar los títulos
            ])
                // Integrantes en estado 1 (Activos)
                ->where('estado', 1)

                // Filtramos por el tipo de proyecto
                ->whereHas('invi_proyectos', function ($query) {
                    $query->where('proyect_tipo', 'INVESTIGACIÓN');
                })

                // Filtramos de forma estricta para evitar "Subdirector"
                ->whereHas('funciones', function ($query) {
                    $query->where('tipo_funcion', 'INVESTIGACIÓN')
                        ->where('nombre_funcion', 'LIKE', '%Docente%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Director%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Subdirector%'); // <-- Excluimos explícitamente Subdirector
                })
                ->get();

            $resultado = [];

            foreach ($integrantes as $integrante) {
                $persona = $integrante->informacionPersonalD;

                if (! $persona) {
                    continue; // Si el integrante no tiene registro de información de personal, pasamos al siguiente
                }

                // 2. Lógica de Género para Nombres y Cargos
                $esMujer = strtoupper(trim($persona->GeneroPer)) === 'F';
                $cargoFormateado = $esMujer ? 'Docente' : 'Docente';

                // 3. Procesar títulos académicos filtrando por nv_numnivel (Eager Loaded)
                $tituloGrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
                });

                $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
                });

                $prefijoNombre = '';
                $sufijoNombre = '';

                // A. Mapeo ampliado de PREFIJOS (3er Nivel)
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

                // Formatear Nombres y Apellidos en formato Title Case
                $nombreRaw = trim($persona->NombInfPer . ' ' . $persona->ApellInfPer . ' ' . $persona->ApellMatInfPer);
                $nombresFormateados = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

                // Construcción del nombre con rango académico completo
                $nombreCompletoConTitulo = trim("{$prefijoNombre} {$nombresFormateados}{$sufijoNombre}");

                // 4. Obtener y formatear el Nombre de la Carrera
                $nombreCarrera = 'No asignada';
                if ($integrante->carreras) {
                    $nombreCarreraRaw = trim($integrante->carreras->NombCarr);
                    $nombreCarrera = mb_convert_case(mb_strtolower($nombreCarreraRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                }
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarrera);

                // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
                $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
                // 5. Obtener la Facultad y sus Siglas
                $nombreFacultad = 'No asignada';
                $siglasFacultad = 'No asignada';
                if ($integrante->carreras && $integrante->carreras->facultades->isNotEmpty()) {
                    $facultadAsociada = $integrante->carreras->facultades->first();
                    $nombreFacultad = mb_convert_case(mb_strtolower(trim($facultadAsociada->facultad), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $siglasFacultad = trim($facultadAsociada->siglas);
                }

                // 6. Estructura del objeto individual
                $resultado[] = [
                    'cedula' => $persona->CIInfPer,
                    'nombres_apellidos' => $nombresFormateados,      // Solo nombres limpios
                    'nombre_con_titulo' => $nombreCompletoConTitulo, // Formato "Ing. Juan Perez, MSc."
                    'funcion' => $integrante->funciones->nombre_funcion ?? 'Desconocida',
                    'cargo_genero' => $cargoFormateado,         // Regresa "Director" o "Directora"
                    'carrera' => $nombreCarreraFinal,
                    'facultad' => $nombreFacultad,
                    'siglas' => $siglasFacultad,
                    'proyecto_nombre' => $integrante->invi_proyectos->proyect_nombre ?? 'No registrado',
                    'titulo_grado' => $tituloGrado ? $tituloGrado->ad_titulo : null,
                    'titulo_posgrado' => $tituloPosgrado ? $tituloPosgrado->ad_titulo : null,
                ];
            }

            // 7. Retornar la respuesta JSON
            return response()->json([
                'success' => true,
                'total' => count($resultado),
                'data' => $resultado,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al consultar los directores de proyectos.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function getDocentesIndProyectosInvestigacion(string $id): JsonResponse
    {
        try {
            // 1. Consulta base utilizando Eager Loading para optimizar el rendimiento e incluir los títulos académicos
            $integrantes = Invi_deta_inte::with([
                'invi_proyectos',
                'funciones',
                'carreras.facultades',
                'informacionPersonalD.titulos.nivel', // <-- Incluimos la relación anidada para evaluar los títulos
            ])
                // Integrantes en estado 1 (Activos)
                ->where('estado', 1)
                ->where('proyect_id', $id)
                // Filtramos por el tipo de proyecto
                ->whereHas('invi_proyectos', function ($query) {
                    $query->where('proyect_tipo', 'INVESTIGACIÓN');
                })

                // Filtramos de forma estricta para evitar "Subdirector"
                ->whereHas('funciones', function ($query) {
                    $query->where('tipo_funcion', 'INVESTIGACIÓN')
                        ->where('nombre_funcion', 'LIKE', '%Investigador%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Director%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Subdirector%'); // <-- Excluimos explícitamente Subdirector
                })
                ->get();

            $resultado = [];

            foreach ($integrantes as $integrante) {
                $persona = $integrante->informacionPersonalD;

                if (! $persona) {
                    continue; // Si el integrante no tiene registro de información de personal, pasamos al siguiente
                }

                // 2. Lógica de Género para Nombres y Cargos
                $esMujer = strtoupper(trim($persona->GeneroPer)) === 'F';
                $cargoFormateado = $esMujer ? 'Docente' : 'Docente';

                // 3. Procesar títulos académicos filtrando por nv_numnivel (Eager Loaded)
                $tituloGrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 3; // TERCER NIVEL
                });

                $tituloPosgrado = $persona->titulos->first(function ($titulo) {
                    return optional($titulo->nivel)->nv_numnivel == 4; // CUARTO NIVEL
                });

                $prefijoNombre = '';
                $sufijoNombre = '';

                // A. Mapeo ampliado de PREFIJOS (3er Nivel)
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

                // Formatear Nombres y Apellidos en formato Title Case
                $nombreRaw = trim($persona->NombInfPer . ' ' . $persona->ApellInfPer . ' ' . $persona->ApellMatInfPer);
                $nombresFormateados = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

                // Construcción del nombre con rango académico completo
                $nombreCompletoConTitulo = trim("{$prefijoNombre} {$nombresFormateados}{$sufijoNombre}");

                // 4. Obtener y formatear el Nombre de la Carrera
                $nombreCarrera = 'No asignada';
                if ($integrante->carreras) {
                    $nombreCarreraRaw = trim($integrante->carreras->NombCarr);
                    $nombreCarrera = mb_convert_case(mb_strtolower($nombreCarreraRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                }
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarrera);

                // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
                $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
                // 5. Obtener la Facultad y sus Siglas
                $nombreFacultad = 'No asignada';
                $siglasFacultad = 'No asignada';
                if ($integrante->carreras && $integrante->carreras->facultades->isNotEmpty()) {
                    $facultadAsociada = $integrante->carreras->facultades->first();
                    $nombreFacultad = mb_convert_case(mb_strtolower(trim($facultadAsociada->facultad), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $siglasFacultad = trim($facultadAsociada->siglas);
                }

                // 6. Estructura del objeto individual
                $resultado[] = [
                    'cedula' => $persona->CIInfPer,
                    'nombres_apellidos' => $nombresFormateados,      // Solo nombres limpios
                    'nombre_con_titulo' => $nombreCompletoConTitulo, // Formato "Ing. Juan Perez, MSc."
                    'funcion' => $integrante->funciones->nombre_funcion ?? 'Desconocida',
                    'cargo_genero' => $cargoFormateado,         // Regresa "Director" o "Directora"
                    'carrera' => $nombreCarreraFinal,
                    'facultad' => $nombreFacultad,
                    'siglas' => $siglasFacultad,
                    'proyecto_nombre' => $integrante->invi_proyectos->proyect_nombre ?? 'No registrado',
                    'titulo_grado' => $tituloGrado ? $tituloGrado->ad_titulo : null,
                    'titulo_posgrado' => $tituloPosgrado ? $tituloPosgrado->ad_titulo : null,
                ];
            }

            // 7. Retornar la respuesta JSON
            return response()->json([
                'success' => true,
                'total' => count($resultado),
                'data' => $resultado,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al consultar los directores de proyectos.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function getEstProyectosInvestigacion(Request $request): JsonResponse
    {
        try {
            // 1. Consulta base utilizando Eager Loading para optimizar el rendimiento e incluir los títulos académicos
            $integrantes = Invi_deta_inte::with([
                'invi_proyectos',
                'funciones',
                'carreras.facultades',
                'informacionpersonal', // <-- Incluimos la relación anidada para evaluar los títulos
            ])
                // Integrantes en estado 1 (Activos)
                ->where('estado', 1)

                // Filtramos por el tipo de proyecto
                ->whereHas('invi_proyectos', function ($query) {
                    $query->where('proyect_tipo', 'INVESTIGACIÓN');
                })

                // Filtramos de forma estricta para evitar "Subdirector"
                ->whereHas('funciones', function ($query) {
                    $query->where('tipo_funcion', 'INVESTIGACIÓN')
                        ->where('nombre_funcion', 'LIKE', '%Estudiante%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Investigador%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Subdirector%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Director%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Ayudante%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Técnico%'); // <-- Excluimos explícitamente Subdirector
                })
                ->get();

            $resultado = $integrantes->map(function ($integrante) {

                // Los directores generalmente son docentes (informacionPersonalD), 
                // pero validamos ambas relaciones por seguridad.
                $persona = $integrante->informacionpersonal;

                // Formatear Nombres y Apellidos (Title Case)
                $nombresCompletos = 'Datos no registrados';
                if ($persona) {
                    $nombreRaw = trim($persona->NombInfPer . ' ' . $persona->ApellInfPer . ' ' . $persona->ApellMatInfPer);
                    // Convertimos todo a minúscula primero y luego a Title Case
                    $nombresCompletos = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, "UTF-8");
                }

                // 4. Obtener y formatear el Nombre de la Carrera
                $nombreCarrera = 'No asignada';
                if ($integrante->carreras) {
                    $nombreCarreraRaw = trim($integrante->carreras->NombCarr);
                    $nombreCarrera = mb_convert_case(mb_strtolower($nombreCarreraRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                }
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarrera);

                // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
                $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
                // 5. Obtener la Facultad y sus Siglas
                $nombreFacultad = 'No asignada';
                $siglasFacultad = 'No asignada';
                if ($integrante->carreras && $integrante->carreras->facultades->isNotEmpty()) {
                    $facultadAsociada = $integrante->carreras->facultades->first();
                    $nombreFacultad = mb_convert_case(mb_strtolower(trim($facultadAsociada->facultad), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $siglasFacultad = trim($facultadAsociada->siglas);
                }

                // Retornamos el objeto estructurado
                return [
                    'nombres_apellidos' => $nombresCompletos,
                    'funcion'           => $integrante->funciones->nombre_funcion ?? 'Desconocida',
                    'carrera'           => $nombreCarreraFinal,
                    'facultad'          => $nombreFacultad,
                    'siglas'            => $siglasFacultad,
                    // Agrego estos campos extra porque suelen ser muy útiles para el Frontend
                    'proyecto_nombre'   => $integrante->invi_proyectos->proyect_nombre ?? 'No registrado',
                    'cedula'            => $persona ? $persona->CIInfPer : null
                ];
            });

            // 3. Retornar la respuesta JSON
            return response()->json([
                'success' => true,
                'total'   => $resultado->count(),
                'data'    => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al consultar los directores de proyectos.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function getEstIndvProyectosInvestigacion(string $id): JsonResponse
    {
        try {
            // 1. Consulta base utilizando Eager Loading para optimizar el rendimiento e incluir los títulos académicos
            $integrantes = Invi_deta_inte::with([
                'invi_proyectos',
                'funciones',
                'carreras.facultades',
                'informacionpersonal', // <-- Incluimos la relación anidada para evaluar los títulos
            ])
                // Integrantes en estado 1 (Activos)
                ->where('estado', 1)
                ->where('proyect_id', $id)
                // Filtramos por el tipo de proyecto
                ->whereHas('invi_proyectos', function ($query) {
                    $query->where('proyect_tipo', 'INVESTIGACIÓN');
                })

                // Filtramos de forma estricta para evitar "Subdirector"
                ->whereHas('funciones', function ($query) {
                    $query->where('tipo_funcion', 'INVESTIGACIÓN')
                        ->where('nombre_funcion', 'LIKE', '%Estudiante%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Investigador%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Subdirector%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Director%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Ayudante%')
                        ->where('nombre_funcion', 'NOT LIKE', '%Técnico%');
                })
                ->get();

            $resultado = $integrantes->map(function ($integrante) {

                // Los directores generalmente son docentes (informacionPersonalD), 
                // pero validamos ambas relaciones por seguridad.
                $persona = $integrante->informacionpersonal;

                // Formatear Nombres y Apellidos (Title Case)
                $nombresCompletos = 'Datos no registrados';
                if ($persona) {
                    $nombreRaw = trim($persona->NombInfPer . ' ' . $persona->ApellInfPer . ' ' . $persona->ApellMatInfPer);
                    // Convertimos todo a minúscula primero y luego a Title Case
                    $nombresCompletos = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, "UTF-8");
                }

                // 4. Obtener y formatear el Nombre de la Carrera
                $nombreCarrera = 'No asignada';
                if ($integrante->carreras) {
                    $nombreCarreraRaw = trim($integrante->carreras->NombCarr);
                    $nombreCarrera = mb_convert_case(mb_strtolower($nombreCarreraRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                }
                $carreraLimpia = preg_replace('/\s*-\s*\d+.*$/', '', $nombreCarrera);

                // 2. Convertimos de "INGENIERIA QUIMICA" a "Ingeniería Química" (Title Case)
                $nombreCarreraFinal = mb_convert_case($carreraLimpia, MB_CASE_TITLE, "UTF-8");
                // 5. Obtener la Facultad y sus Siglas
                $nombreFacultad = 'No asignada';
                $siglasFacultad = 'No asignada';
                if ($integrante->carreras && $integrante->carreras->facultades->isNotEmpty()) {
                    $facultadAsociada = $integrante->carreras->facultades->first();
                    $nombreFacultad = mb_convert_case(mb_strtolower(trim($facultadAsociada->facultad), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $siglasFacultad = trim($facultadAsociada->siglas);
                }

                // Retornamos el objeto estructurado
                return [
                    'nombres_apellidos' => $nombresCompletos,
                    'funcion'           => $integrante->funciones->nombre_funcion ?? 'Desconocida',
                    'carrera'           => $nombreCarreraFinal,
                    'facultad'          => $nombreFacultad,
                    'siglas'            => $siglasFacultad,
                    // Agrego estos campos extra porque suelen ser muy útiles para el Frontend
                    'proyecto_nombre'   => $integrante->invi_proyectos->proyect_nombre ?? 'No registrado',
                    'cedula'            => $persona ? $persona->CIInfPer : null
                ];
            });

            // 3. Retornar la respuesta JSON
            return response()->json([
                'success' => true,
                'total'   => $resultado->count(),
                'data'    => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al consultar los directores de proyectos.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function getProyectosInvestigacionInfo(): JsonResponse
    {
        try {
            // 1. Consulta base con Eager Loading para toda la jerarquía de relaciones
            $proyectos = Invi_proyectos::with([
                'invi_detalle_fac_proy.facultades_priori.linea_investigacion.sub_linea_investigacion.carreras'
            ])
                ->where('proyect_tipo', 'INVESTIGACIÓN')
                ->get();

            $hoy = Carbon::now();
            $resultado = [];

            foreach ($proyectos as $proyecto) {
                // 2. Lógica de Fechas y Estado
                $estadoProyecto = 'Desconocido';
                $duracionTotalTexto = 'Fechas no definidas';
                $tiempoRestanteTexto = 'No calculable';

                if ($proyecto->fechainicio && $proyecto->fechafin) {
                    $inicio = Carbon::parse($proyecto->fechainicio)->startOfDay();
                    $fin = Carbon::parse($proyecto->fechafin)->endOfDay();

                    // Calculamos la duración total en meses y días
                    $mesesDuracion = $inicio->diffInMonths($fin);
                    $diasDuracion = $inicio->copy()->addMonths($mesesDuracion)->diffInDays($fin);
                    $duracionTotalTexto = "{$mesesDuracion} meses y {$diasDuracion} días";

                    // Evaluamos el estado actual
                    if ($hoy->greaterThan($fin)) {
                        $estadoProyecto = 'Finalizado';
                        $tiempoRestanteTexto = 'El proyecto ya finalizó';
                    } elseif ($hoy->lessThan($inicio)) {
                        $estadoProyecto = 'Por iniciar';
                        $diasParaInicio = $hoy->diffInDays($inicio);
                        $tiempoRestanteTexto = "Inicia en {$diasParaInicio} días";
                    } else {
                        $estadoProyecto = 'Vigente';
                        $mesesRestantes = $hoy->diffInMonths($fin);
                        $diasRestantes = $hoy->copy()->addMonths($mesesRestantes)->diffInDays($fin);

                        $textoRestante = [];
                        if ($mesesRestantes > 0) $textoRestante[] = "{$mesesRestantes} meses";
                        if ($diasRestantes > 0) $textoRestante[] = "{$diasRestantes} días";

                        $tiempoRestanteTexto = !empty($textoRestante)
                            ? implode(' y ', $textoRestante) . ' restantes'
                            : 'Finaliza hoy';
                    }
                }

                // 3. Extracción de Líneas y Sublíneas de Investigación
                $lineasInvestigacion = [];
                $facultadPrincipal = null;

                // Verificamos que tenga detalles y extraemos la facultad prioritaria (principal)
                if ($proyecto->invi_detalle_fac_proy->isNotEmpty()) {
                    $detallePriori = $proyecto->invi_detalle_fac_proy->firstWhere('id_facultad_priori', '!=', null);

                    if ($detallePriori && $detallePriori->facultades_priori) {
                        $facultad = $detallePriori->facultades_priori;
                        $facultadPrincipal = $facultad->facultad . ' (' . $facultad->siglas . ')';

                        // Mapeamos las líneas de investigación de esa facultad
                        foreach ($facultad->linea_investigacion as $linea) {

                            // Mapeamos las sublíneas correspondientes a esta línea
                            $sublineas = $linea->sub_linea_investigacion->map(function ($sublinea) {
                                $nombreCarreraLimpio = 'No asignada';

                                if ($sublinea->carreras) {
                                    $nombreRaw = trim($sublinea->carreras->NombCarr);
                                    // Utilizamos preg_replace para eliminar un guion seguido de espacios opcionales y números al final
                                    // Ej: "Quimica -2020" o "Quimica - 2020" -> "Quimica"
                                    $nombreCarreraLimpio = trim(preg_replace('/-\s*\d+.*$/', '', $nombreRaw));
                                }
                                return [
                                    'nombre_sublinea' => $sublinea->nombre_sublin,
                                    'objeto_estudio'  => $sublinea->objeto_estudio_sublin,
                                    'carrera_aplica'  => $nombreCarreraLimpio
                                ];
                            });

                            $lineasInvestigacion[] = [
                                'nombre_linea' => $linea->nombre_lin,
                                'estado_linea' => $linea->estado_lin_investiga == 1 ? 'Activa' : 'Inactiva',
                                'sublineas'    => $sublineas->toArray()
                            ];
                        }
                    }
                }

                // 4. Construcción del Objeto Final
                $resultado[] = [
                    'codigo_proyecto'   => $proyecto->proyect_cod,
                    'nombre_proyecto'   => $proyecto->proyect_nombre,
                    'estado_temporal'   => $estadoProyecto,
                    'fechas' => [
                        'inicio'        => $proyecto->fechainicio,
                        'fin'           => $proyecto->fechafin,
                    ],
                    'metricas_tiempo' => [
                        'duracion_total'  => $duracionTotalTexto,
                        'tiempo_restante' => $tiempoRestanteTexto
                    ],
                    'facultad_principal' => $facultadPrincipal ?? 'No definida',
                    'lineas_investigacion' => $lineasInvestigacion
                ];
            }

            return response()->json([
                'success' => true,
                'total'   => count($resultado),
                'data'    => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Error al procesar los proyectos de vinculación.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function uploadPDFProyectArchivo(Request $request)
    {
         $request->validate([
            // 'mimetypes:application/pdf' obliga a que el CONTENIDO sea un PDF real
            'file' => 'required|file|mimes:pdf|mimetypes:application/pdf|max:51200',
            'codPro'   => 'required|alpha_dash', // Solo letras, números, guiones
            'old_filename' => 'nullable|string',
        ]);

        try {
            // Sanitizar el código de proyecto para evitar que un hacker use "../" en el campo codPro
            $codPro = basename($request->codPro);
            $file = $request->file('file');

            // 2. Verificar si el archivo es válido y no tiene errores de subida
            if (!$file->isValid()) {
                throw new \Exception("Archivo inválido o corrupto.");
            }

            // ELIMINAR ARCHIVO ANTERIOR (Solo si el nombre es válido)
            if ($request->filled('old_filename')) {
                $oldFilename = basename($request->old_filename); // Seguridad extra
                $oldPath = public_path("Documentos/INVESTIGACION/Evidencia_Proyect/{$codPro}/{$oldFilename}");
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }


            // Crear carpeta si no existe
            $directory = public_path("Documentos/INVESTIGACION/Evidencia_Proyect/{$codPro}");

            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            $aleatorio = bin2hex(random_bytes(8));
            $fechaHora = date("Ymd_His");
            $extension = 'pdf'; // Forzamos la extensión fija

            $filename = "{$codPro}_{$aleatorio}_{$fechaHora}.{$extension}";

            // Guardar archivo
            $file->move($directory, $filename);

            // URL pública
            $url = url("Documentos/INVESTIGACION/Evidencia_Proyect/{$codPro}/{$filename}");

            return response()->json([
                'status'   => true,
                'filename' => $filename,
                'url'      => $url
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Seguridad: El archivo no pudo ser procesado.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
