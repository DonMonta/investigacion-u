<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DirectorCarrerasController;
use App\Http\Controllers\InformacionPersonal_DController;
use App\Http\Controllers\PeiController;
use App\Http\Controllers\Subsistemas_peiController;
use App\Http\Controllers\Objetivos_peiController;
use App\Http\Controllers\PlandneController;
use App\Http\Controllers\Obj_pol_plandneController;
use App\Http\Controllers\Politicas_plandneController;
use App\Http\Controllers\Invi_proyectosController;
use App\Http\Controllers\InformacionPersonalController;
use App\Http\Controllers\Agenda_ODSController;
use App\Http\Controllers\ODSController;
use App\Http\Controllers\Invi_dom_humaController;
use App\Http\Controllers\Invi_linea_investigaController;
use App\Http\Controllers\Invi_sub_linea_invesController;
use App\Http\Controllers\SubAreaUnescoController;
use App\Http\Controllers\Zona_planificacionController;
use App\Http\Controllers\PraempresaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('inves')->group(function () {
    Route::get('getFotoDocente/{ci}', [InformacionPersonal_DController::class, 'getFotografia']);

    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:api,docente,estudiante')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::apiResource("directorescarr", DirectorCarrerasController::class);
        /**Apis PEI */
        //Definición de endpoint para el recurso Pei, perimitiendo operaciones CRUD
        Route::apiResource("pei", PeiController::class);
        //Definición de la ruta endpoint para habilitar un pei
        Route::delete('habilitar_pei/{id}', [PeiController::class, 'habilitar']);
        //Definición de la ruta endpoint para deshabilitar un pei
        Route::delete('inhabilitar_pei/{id}', [PeiController::class, 'destroy']);
        //Definición de la ruta endpoint para subir un archivo
        Route::post('subir_archivo_pei', [PeiController::class, 'uploadArchivo']);
        //Definición de la ruta endpoint para eliminar un archivo
        Route::post('eliminar_archivo', [PeiController::class, 'deleteArchivo']);
        //Definición de la ruta endpoint para el recurso Subsistemas_pei, perimitiendo operaciones CRUD
        Route::apiResource("subsistemas_pei", Subsistemas_peiController::class);
        //Definición de la ruta endpoint para el recurso Objetivos_pei, perimitiendo operaciones CRUD
        Route::apiResource("objetivos_pei", Objetivos_peiController::class);
        Route::get('objetivos_por_pei/{id_pei}', [Objetivos_peiController::class, 'listarPorPei']);
        /**Apis Plandne */
        //Definición de la ruta endpoint para el recurso Plandne, perimitiendo operaciones CRUD
        Route::apiResource("plandne", PlandneController::class);
        //Definicio de endpoint para habilitar un plandne
        Route::delete('habilitar_plandne/{id}', [PlandneController::class, 'habilitar']);
        //Definición de endpoint para deshabilitar un plandne
        Route::delete('inhabilitar_plandne/{id}', [PlandneController::class, 'destroy']);
        //Definición de ruta endpoint para el recurso Obj_pol_plandne, perimitiendo operaciones CRUD
        Route::apiResource("obj_pol_plandne", Obj_pol_plandneController::class);
        //Definición de ruta endpoint para el recurso Politicas_plandne, perimitiendo operaciones CRUD
        Route::apiResource("politicas_plandne", Politicas_plandneController::class);
        //Definición de ruta endpoint para listar las politicas de un plandne
        Route::get('politicas_por_plandne/{id_pladne}', [Politicas_plandneController::class, 'listarPorPlandne']);
        /**
         * API AGENDA ODS
         */
         //Definición de endpoint para el recurso Agenda_ODS, perimitiendo operaciones CRUD
        Route::apiResource("agenda_ods", Agenda_ODSController::class);
        //Definición de endpoint para el recurso ODS, perimitiendo operaciones CRUD
        Route::apiResource("ods", ODSController::class);
        //Denificion de endpoint para habilitar e inhabilitar un Agenda ODS
        Route::delete('habilitar_agenda_ods/{id}', [Agenda_ODSController::class, 'habilitar']);
        Route::delete('inhabilitar_agenda_ods/{id}', [Agenda_ODSController::class, 'destroy']);
         Route::post('subir_archivo_agenda_ods', [Agenda_ODSController::class, 'uploadArchivo']);
        //Definición de la ruta endpoint para eliminar un archivo
        Route::post('eliminar_archivo_agenda_ods', [Agenda_ODSController::class, 'deleteArchivo']);
        /**
         * API DOMINIO HUMANISTICO
         */
        //Definción del recurso Dominio Humano, perimitiendo operaciones CRUD
        Route::apiResource("invi_dom_huma", Invi_dom_humaController::class);
        /**
         * API INVESTIGACIÓN
         */
        //Definición del recurso Línea de Investigación, perimitiendo operaciones CRUD
        Route::apiResource("invi_linea_investiga", Invi_linea_investigaController::class);
        //Definición de endpoint para habilitar una línea de investigación
        Route::delete('habilitar_linea_investiga/{id}', [Invi_linea_investigaController::class, 'habilitar']);
        //Definición de endpoint para deshabilitar una línea de investigación
        Route::delete('inhabilitar_linea_investiga/{id}', [Invi_linea_investigaController::class, 'destroy']);
        //Definición de endpoint para obtener las facultades de una sede
        Route::get('getFacultades', [Invi_linea_investigaController::class, 'getFacultades']);
        //Definición de endpoint para obtener las carreras de una facultad
        Route::get('getCarreras/{idfacultad}', [Invi_sub_linea_invesController::class, 'getCarrerasPorFacultad']);
        //Definición de endpoint para obtener las sublineas de una línea de investigación
        Route::get('getSublineas/{id_lin_investiga}', [Invi_sub_linea_invesController::class, 'getSublineasPorLinea']);
        //Definición de recurso para las sublineas de una línea de investigación
        Route::apiResource("invi_sub_linea_inves", Invi_sub_linea_invesController::class);
        /**
         * API UNESCO
         */
        //Definición para obtener las Subareas de UNESCO
        Route::get('getSubareas', [SubAreaUnescoController::class, 'index']);
        /**
         * API ZONA DE PLANIFICACIÓN
         */
        //Definición de recurso para las zonas de planificación, perimitiendo operaciones CRUD
        Route::apiResource("zona_planificacion", Zona_planificacionController::class);
        //Definición de endpoint para obtener las provincias
        Route::get('getProvincias', [Zona_planificacionController::class, 'getProvincias']);
        //Definición de endpoint para obtener las provincias asignadas a una zona de planificación
        Route::get('getProvinciasPorZona/{id_zona_plan}', [Zona_planificacionController::class, 'getProvinciasPorZona']);
        //Definición de endpoint para guardar las provincias asignadas a una zona de planificación
        Route::post('guardarProvincias', [Zona_planificacionController::class, 'guardarProvincias']);

        /**
         * API EMPRESAS
         */
        //Definiciónd del recurso Empresa, perimitiendo operaciones CRUD
        Route::apiResource("empresa", PraempresaController::class);
        //Definición de endpoint para subir un archivo
        Route::post('subir_archivo', [PraempresaController::class, 'uploadArchivo']);
        //Definición de endpoint para habilitar un empresa
        Route::delete('habilitar_empresa/{id}', [PraempresaController::class, 'habilitar']);
        //Definición de endpoint para inhabilitar un empresa
        Route::delete('inhabilitar_empresa/{id}', [PraempresaController::class, 'destroy']);
        //Definición de endpoint para buscar una empresa
        Route::get('buscar_empresa', [PraempresaController::class, 'buscarEmpresa']);

        //Definición de ruta endpoint para el recurso Invi_proyectos, perimitiendo operaciones CRUD
        Route::apiResource("invi_proyectos", Invi_proyectosController::class);
        //definción de ruta para el catalogo de integrantes
        Route::get('catalogos-integrantes', [Invi_proyectosController::class, 'catalogos']);
        Route::put('actualizar-integrante/{id}', [Invi_proyectosController::class, 'actualizarIntegrante']);
        //Definición de la ruta para buscar integrantes
        Route::get('buscar-integrantes', [Invi_proyectosController::class, 'buscarIntegrante']);
        //Definición de la ruta para inhabilitar un integrante
        Route::post('inhabilitar-integrante', [Invi_proyectosController::class, 'inhabilitar']);
        //Definición de la ruta para reemplazar un integrante
        Route::post('reemplazar-integrante', [Invi_proyectosController::class, 'reemplazarIntegrante']);
        //Definicion para obtener foto del estudiante
        Route::get('getFoto/{ci}', [InformacionPersonalController::class, 'getFoto']);
        //Definicion para obtener la foto del docente
        
        //Definición de ruta para subir un archivo
        Route::post('subir_archivo_anexo', [Invi_proyectosController::class, 'uploadArchivo']);
        //Definición de ruta para subir un archivo
        Route::post('subir_archivo_anexo_darbaja', [Invi_proyectosController::class, 'uploadArchivoDarBaja']);
        //Definición de endpoint para guardar/reemplazar un integrante (modo nuevo o reemplazo)
        Route::post('integrantes/guardar', [Invi_proyectosController::class, 'guardarCambios']);
        //Definición de endpoint para obtener las estadísticas de proyectos y integrantes
        Route::get('dashboard/stats', [Invi_proyectosController::class, 'getStats']);
        //Definición de endpoint para obtener la foto del docente
    });
}); 

//Route::get('/',[AuthController::class,'unauthorized'])->name('login');
