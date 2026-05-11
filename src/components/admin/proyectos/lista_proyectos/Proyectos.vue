<template>
    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
        <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <form class="flex-grow" @submit.prevent>
                    <div class="relative">
                        <button class="absolute -translate-y-1/2 left-4 top-1/2">
                            <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                            </svg>
                        </button>
                        <input type="text" placeholder="Ingresa el nombre del pei a buscar..." v-model="searchQuery"
                            @input="debouncedFilter"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 xl:w-[430px]" />
                    </div>
                </form>
            </div>
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="min-w-full">
                <thead>
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <th class="py-5 px-4 text-left">
                            <p class="font-semibold text-gray-500 text-sm">Cod</p>
                        </th>
                        <th class="py-5 px-4 text-left">
                            <p class="font-semibold text-gray-500 text-sm">Proyecto / Título</p>
                        </th>
                        <th class="py-5 px-4 text-left">
                            <p class="font-semibold text-gray-500 text-sm">Fecha Inicio/Fin</p>
                        </th>
                        <th class="py-5 px-4 text-right">
                            <p class="font-semibold text-gray-500 text-sm">Acciones</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="cargando">
                        <td class="px-5 py-6" colspan="4">
                            <div class="flex justify-center items-center gap-2">
                                <span
                                    class="animate-spin h-5 w-5 border-2 border-brand-500 border-t-transparent rounded-full"></span>
                                <h3 class="text-gray-500 font-medium">Cargando datos...</h3>
                            </div>
                        </td>
                    </tr>
                    <tr v-else v-for="post in filteredarray" :key="post.proyect_id"
                        class="border-t border-gray-100 hover:bg-gray-50/50 dark:border-gray-800 transition-colors">
                        <td class="py-5 px-4 whitespace-nowrap">
                            <p class="text-gray-600 text-sm font-medium dark:text-gray-400">{{ post.proyect_cod }}</p>
                        </td>

                        <td class="py-5 px-4 min-w-[300px] max-w-[500px]">
                            <div class="break-words">
                                <p class="font-bold text-gray-800 text-sm dark:text-white/90 leading-tight">
                                    {{ post.proyect_nombre }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1 italic leading-relaxed" v-if="post.proyect_titulo">
                                    Título: {{ post.proyect_titulo }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1 italic leading-relaxed" v-else>
                                    NO HAY TITULO DE PROYECTO REGISTRADO
                                </p>
                            </div>
                        </td>

                        <td class="py-5 px-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1 text-[11px]">
                                <span
                                    class="font-medium text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full w-fit">Inicio:
                                    {{ post.fechainicio }}</span>
                                <span class="font-medium text-cyan-600 bg-cyan-50 px-2 py-0.5 rounded-full w-fit">Fin:
                                    {{ post.fechafin }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <button @click="abrirDetallesProyecto(post.proyect_id)"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                title="Ver Detalles">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 pt-5 dark:border-gray-800">
            <div class="flex items-center gap-4">
                <button @click="previousPage" :disabled="currentPage === 1 || cargando"
                    class="flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-600 shadow-theme-xs hover:bg-gray-50 disabled:opacity-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-400">
                    Página {{ currentPage }} de {{ lastPage }}
                </span>
                <button @click="nextPage" :disabled="currentPage === lastPage || cargando"
                    class="flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-600 shadow-theme-xs hover:bg-gray-50 disabled:opacity-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <button class="btn btn-primary text-white text-xs px-4 py-2" @click="actualizar" :disabled="cargando">
                {{ cargando ? 'Cargando...' : 'Actualizar Lista' }}
            </button>
        </div>
        <div v-if="showModalDetalles"
            class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 z-99999">
            <div
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">

                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50">
                    <div class="max-w-[80%]">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white truncate">{{
                            proyectoSeleccionado.proyect_nombre }}</h2>
                        <p class="text-sm text-gray-500 italic" v-if="proyectoSeleccionado.proyect_titulo">Título: {{
                            proyectoSeleccionado.proyect_titulo }}</p>
                        <p class="text-sm text-gray-500 italic" v-else>NO HAY TITULO DE PROYECTO REGISTRADO</p>
                    </div>
                    <button @click="cerraModal" class="p-2 hover:bg-gray-100 rounded-full transition-colors">✕</button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-brand-50 border border-brand-100 dark:bg-brand-500/5">
                            <h4 class="text-[10px] font-bold uppercase text-brand-600 mb-1">Facultad Prioritaria</h4>
                            <p class="text-sm font-bold text-gray-800 dark:text-white">{{
                                proyectoSeleccionado.facultades_priori?.siglas }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-gray-800">
                            <h4 class="text-[10px] font-bold uppercase text-gray-500 mb-1">Participantes</h4>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="fac in proyectoSeleccionado.facultades" :key="fac.idfacultad"
                                    class="px-2 py-0.5 bg-white dark:bg-gray-700 border rounded text-[11px] font-medium">
                                    {{ fac.siglas }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="integranteEdit || modoNuevo"
                        class="p-6 border-2 border-blue-100 rounded-2xl bg-blue-50/20 animate-fadeIn">
                        <div class="flex justify-between items-start mb-4">
                            <h4 class="font-bold text-blue-800">
                                {{ modoNuevo ? 'Añadir Nuevo Integrante' : `Editando ${integranteEdit.funcion}:
                                ${integranteEdit.nombre} ${integranteEdit.apellido} ${integranteEdit.apellidomaterno}`
                                }}
                            </h4>
                            <button @click="cancelarEdicion"
                                class="text-xs text-red-500 font-bold hover:underline">Cancelar</button>
                        </div>
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="flex flex-col items-center space-y-2">
                                <div
                                    class="w-32 h-32 rounded-xl border-2 border-dashed border-blue-200 bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden shadow-sm">

                                    <template v-if="nuevoIntegranteData || integranteEdit">
                                        <img :src="getPhotoUrl(nuevoIntegranteData?.cedula || integranteEdit?.cedula)"
                                            class="w-full h-full object-cover animate-fadeIn" alt="Foto de perfil" />
                                    </template>

                                    <template v-else>
                                        <span class="text-[10px] text-gray-400 text-center px-2">
                                            Esperando integrante...
                                        </span>
                                    </template>

                                </div>
                                <p class="text-[10px] font-bold text-blue-500 uppercase">Perfil</p>
                            </div>
                            <div class="flex-1">

                                <div v-if="modoNuevo || (formInt.reemplazado == 1)"
                                    class="mb-6 flex gap-3 p-4 bg-white dark:bg-gray-800 rounded-xl border border-blue-100 shadow-sm">
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Buscar
                                            por
                                            Cédula</label>
                                        <input type="text" v-model="cedulaBusqueda" placeholder="Ej: 08xxxxxxx"
                                            class="w-full border rounded-lg p-2 text-sm focus:ring-2 ring-blue-200 outline-none">
                                    </div>
                                    <button @click="buscarNuevoIntegrante"
                                        class="mt-5 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold">
                                        Verificar
                                    </button>
                                </div>
                                <div v-if="!modoNuevo || (modoNuevo && nuevoIntegranteData)"
                                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div v-if="nuevoIntegranteData" class="md:col-span-3 space-y-3">
                                        <div
                                            class="p-3 bg-green-50 border border-green-100 rounded-lg text-sm text-green-700 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span>
                                                Confirmado: <b>{{ nuevoIntegranteData.nombre_completo }}</b>
                                                ({{ nuevoIntegranteData.tipo == 'doc' ? 'Docente' : 'Estudiante' }})
                                            </span>
                                        </div>

                                        <div v-if="nuevoIntegranteData.advertencia"
                                            class="p-4 bg-amber-50 border-l-4 border-amber-400 rounded-r-lg text-sm text-amber-800 animate-pulse-subtle">
                                            <div class="flex gap-3">
                                                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <div>
                                                    <p class="font-bold mb-1">Aviso de Participación Múltiple</p>
                                                    <p class="leading-relaxed">{{ nuevoIntegranteData.advertencia }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <label class="block text-[10px] font-bold mb-1">Función</label>
                                        <select v-model="formInt.id_funcion" @change="calcularHoras"
                                            class="w-full border rounded-lg p-2 text-sm bg-white dark:bg-gray-800 max-w-full overflow-hidden truncate">
                                            <option :value="null">Seleccione Función</option>
                                            <option v-for="f in funciones" :key="f.id_funcion" :value="f.id_funcion">{{
                                                f.nombre_funcion }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold mb-1">Horas (Automático)</label>
                                        <input type="number" v-model="formInt.horas" disabled
                                            class="w-full border rounded-lg p-2 text-sm bg-gray-100 dark:bg-gray-700 cursor-not-allowed font-bold text-blue-600">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-[10px] font-bold mb-1">Carrera</label>
                                        <select v-model="formInt.idCarr"
                                            class="w-full border rounded-lg p-2 text-sm bg-white dark:bg-gray-800 max-w-full overflow-hidden truncate">
                                            <option v-for="c in carreras" :key="c.idCarr" :value="c.idCarr">{{
                                                c.NombCarr }}
                                            </option>
                                        </select>
                                    </div>

                                    <div v-if="formInt.reemplazado == 1 || modoNuevo" class="md:col-span-2">
                                        <label class="block text-[10px] font-bold mb-1">Documento Respaldo (PDF)</label>
                                        <div @click="$refs.fileFoto.click()"
                                            class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition-all"
                                            :class="archivoPreviewName ? 'border-brand-500 bg-brand-50/20' : 'border-gray-300 hover:border-brand-400 bg-gray-50 dark:bg-gray-800/50'">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg v-if="!archivoPreviewName" class="w-8 h-8 mb-3 text-gray-400"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <svg v-else class="w-8 h-8 mb-3 text-brand-600" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path
                                                        d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                                    <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                                </svg>

                                                <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">
                                                    <span class="font-semibold" v-if="!archivoPreviewName">Haga clic
                                                        para
                                                        cargar</span>
                                                    <span class="font-semibold text-brand-600" v-else>{{
                                                        archivoPreviewName
                                                        }}</span>
                                                </p>
                                                <p class="text-xs text-gray-400" v-if="!archivoPreviewName">PDF (Máx.
                                                    10MB)</p>
                                            </div>

                                            <input type="file" ref="fileFoto" class="hidden" accept="application/pdf"
                                                @change="handleFileChange" />
                                        </div>
                                    </div>

                                    <div v-if="!modoNuevo" class="flex items-center pt-4">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" v-model="formInt.reemplazado" :true-value="1"
                                                :false-value="0">
                                            <span class="text-sm font-bold text-red-600">Reemplazar Integrante</span>
                                        </label>
                                    </div>
                                    <div v-if="!modoNuevo && formInt.reemplazado == 1" class="md:col-span-3 space-y-4">

                                        <div class="flex items-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" v-model="continuarEnProyecto" :true-value="true"
                                                    :false-value="false" class="w-4 h-4 text-blue-600 rounded">
                                                <span class="text-sm font-bold text-gray-700">
                                                    ¿El integrante al que estás reemplazando seguirá en el proyecto?
                                                </span>
                                            </label>
                                        </div>

                                        <div v-if="continuarEnProyecto"
                                            class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border rounded-xl bg-gray-50 animate-fadeIn">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold mb-1 uppercase text-blue-600">Nueva
                                                    Función para el integrante saliente</label>
                                                <select v-model="formInt.id_funcion_reemplazado"
                                                    @change="calcularHorasReemplazo"
                                                    class="w-full border rounded-lg p-2 text-sm bg-white">
                                                    <option :value="null">Seleccione nueva función</option>
                                                    <option v-for="f in funcionesFiltradasSinDireccion"
                                                        :key="f.id_funcion" :value="f.id_funcion">
                                                        {{ f.nombre_funcion }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold mb-1 uppercase text-blue-600">Nuevas
                                                    Horas</label>
                                                <input type="number" v-model="formInt.horas_reemplazado" disabled
                                                    class="w-full border rounded-lg p-2 text-sm">
                                            </div>
                                            <div class="md:col-span-2">
                                                <label
                                                    class="block text-[10px] font-bold mb-1 uppercase text-blue-600">Nueva
                                                    Carrera (Opcional)</label>
                                                <select v-model="formInt.idCarr_reemplazado"
                                                    class="w-full border rounded-lg p-2 text-sm bg-white">
                                                    <option v-for="c in carreras" :key="c.idCarr" :value="c.idCarr">{{
                                                        c.NombCarr }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end gap-3" v-if="buscando">
                                    <button @click="guardarCambios" :disabled="enviando"
                                        class="bg-blue-700 text-white px-8 py-2 rounded-xl font-bold text-sm shadow-lg disabled:opacity-50">
                                        {{ enviando ? 'Procesando...' : (modoNuevo ? 'Registrar Integrante' :
                                            'Guardar Cambios') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-4">
                            <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                Integrantes Activos
                                <span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full text-xs">{{
                                    integrantesFiltrados.length }}</span>
                            </h3>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <input type="text" v-model="filtroIntegrante" placeholder="Filtrar cédula..."
                                    class="text-xs border rounded-xl px-4 py-2 flex-1 outline-none focus:border-blue-400">
                                <button @click="activarModoNuevo"
                                    class="bg-green-600 text-white px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap">
                                    + Añadir
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50 dark:bg-gray-800 text-[11px] uppercase text-gray-400">
                                    <tr>
                                        <th class="p-3 text-left">Cédula</th>
                                        <th class="p-3 text-left">Integrante</th>
                                        <th class="p-3 text-center">Horas</th>
                                        <th class="p-3 text-center">Reemplazado</th>
                                        <th class="p-3 text-left">Función / Carrera</th>
                                        <th class="p-3 text-left">Registro / Act.</th>
                                        <th class="p-3 text-center">Anexo Reemplazo</th>
                                        <th class="p-3 text-center">Anexo Original</th>
                                        <th class="p-3 text-center">Estado</th>
                                        <th class="p-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr v-for="int in integrantesFiltrados" :key="int.id_deta_invi_proyect"
                                        class="hover:bg-gray-50/50 transition-colors">

                                        <td class="p-3 font-mono text-xs text-gray-500">
                                            {{ int.ciinfper_doc || int.ciinfper_est }}
                                        </td>

                                        <td class="p-3">
                                            <p class="font-bold text-gray-700 dark:text-gray-200">
                                                {{ (int.informacion_personal_d || int.informacionpersonal)?.NombInfPer
                                                }}
                                                {{ (int.informacion_personal_d || int.informacionpersonal)?.ApellInfPer
                                                }}
                                            </p>
                                        </td>

                                        <td class="p-3 text-center font-bold text-blue-600">
                                            {{ int.horas }}h
                                        </td>

                                        <td class="p-3 text-center">
                                            <span
                                                :class="int.reemplazado ? 'text-red-500 bg-red-50' : 'text-green-600 bg-green-50'"
                                                class="px-2 py-1 rounded-full text-[10px] font-bold uppercase">
                                                {{ int.reemplazado ? 'Si' : 'No' }}
                                            </span>
                                        </td>

                                        <td class="p-3">
                                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                                {{ int.funciones?.nombre_funcion || '---' }}
                                            </p>
                                            <p class="text-[10px] text-gray-400 truncate max-w-[150px]">
                                                {{ int.carreras?.NombCarr }}
                                            </p>
                                        </td>

                                        <td class="p-3 whitespace-nowrap">
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center gap-1 text-[10px] text-gray-500">
                                                    <span class="font-bold text-blue-500">CRE:</span>
                                                    {{ formatDate(int.created_at) }}
                                                </div>
                                                <div class="flex items-center gap-1 text-[10px] text-gray-400">
                                                    <span class="font-bold text-orange-400">ACT:</span>
                                                    {{ formatDate(int.updated_at) }}
                                                </div>
                                            </div>
                                        </td>

                                        <td class="p-3 text-center" v-if="int.estado === 0">
                                            <div v-if="int.anexo_integrante" class="flex justify-center">
                                                <a :href="`http://investigacionback.test/Documentos/INVESTIGACION/Bajas_Docentes/Anexo/${int.ciinfper_doc || int.ciinfper_est}/${int.anexo_integrante}`"
                                                    target="_blank"
                                                    class="group relative flex items-center justify-center p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm"
                                                    title="Ver documento PDF">
                                                    <svg width="18" height="18" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        <path d="M9 15h6M9 11h6" />
                                                    </svg>
                                                </a>
                                            </div>
                                            <span v-else class="text-[10px] text-gray-300 italic">Sin anexo</span>
                                        </td>
                                        <td class="p-3 text-center" v-else>
                                            <div v-if="int.anexo_integrante" class="flex justify-center">
                                                <a :href="`http://investigacionback.test/Documentos/INVESTIGACION/AnexoIntegrante/${int.ciinfper_doc || int.ciinfper_est}/${int.anexo_integrante}`"
                                                    target="_blank"
                                                    class="group relative flex items-center justify-center p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm"
                                                    title="Ver documento PDF">
                                                    <svg width="18" height="18" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        <path d="M9 15h6M9 11h6" />
                                                    </svg>
                                                </a>
                                            </div>
                                            <span v-else class="text-[10px] text-gray-300 italic">Sin anexo</span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <div v-if="int.anexo_integrante2" class="flex justify-center">
                                                <a :href="`http://investigacionback.test/Documentos/INVESTIGACION/AnexoIntegrante/${int.ciinfper_doc || int.ciinfper_est}/${int.anexo_integrante2}`"
                                                    target="_blank"
                                                    class="group relative flex items-center justify-center p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm"
                                                    title="Ver documento PDF">
                                                    <svg width="18" height="18" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        <path d="M9 15h6M9 11h6" />
                                                    </svg>
                                                </a>
                                            </div>
                                            <span v-else class="text-[10px] text-gray-300 italic">Sin anexo</span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <span
                                                :class="int.estado === 0 ? 'text-red-500 bg-red-50' : 'text-green-600 bg-green-50'"
                                                class="px-2 py-1 rounded-full text-[10px] font-bold uppercase">
                                                {{ int.estado === 0 ? 'Inac' : 'Act' }}
                                            </span>
                                        </td>

                                        <td class="p-3 text-right">
                                            <div class="flex justify-end gap-2" v-if="int.estado === 1">
                                                <button @click="seleccionarIntegrante(int)"
                                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button @click="inhabilitarIntegrante(int)" v-if="int.estado === 1"
                                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="showModalBaja"
            class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 z-99999">
            <div
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
                <div class="p-6 border-b dark:border-gray-800 flex justify-between items-center bg-red-50/50">
                    <h3 class="text-lg font-bold text-red-700">Confirmar Baja de Integrante</h3>
                    <button @click="showModalBaja = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase font-bold">Integrante seleccionado:</p>
                        <p class="text-sm font-bold text-gray-800 dark:text-white">
                            {{ (integranteBaja.informacion_personal_d || integranteBaja.informacionpersonal)?.NombInfPer
                            }}
                            {{ (integranteBaja.informacion_personal_d ||
                                integranteBaja.informacionpersonal)?.ApellInfPer }}
                        </p>
                        <p class="text-[10px] text-gray-400 font-mono">{{ integranteBaja.ciinfper_doc ||
                            integranteBaja.ciinfper_est }}</p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold mb-1">Documento Respaldo (PDF)</label>
                        <div @click="$refs.fileInputBaja.click()"
                            class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition-all"
                            :class="archivoBajaName ? 'border-brand-500 bg-brand-50/20' : 'border-gray-300 hover:border-brand-400 bg-gray-50 dark:bg-gray-800/50'">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg v-if="!archivoBajaName" class="w-8 h-8 mb-3 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <svg v-else class="w-8 h-8 mb-3 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                    <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                </svg>

                                <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold" v-if="!archivoBajaName">Haga clic para
                                        cargar</span>
                                    <span class="font-semibold text-brand-600" v-else>{{ archivoBajaName
                                    }}</span>
                                </p>
                                <p class="text-xs text-gray-400" v-if="!archivoBajaName">PDF (Máx. 10MB)</p>
                            </div>

                            <input type="file" ref="fileInputBaja" class="hidden" accept="application/pdf"
                                @change="handleFileBajaChange" />
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 flex gap-2">
                    <button @click="showModalBaja = false"
                        class="flex-1 px-4 py-2 text-sm font-bold text-gray-500 hover:bg-gray-200 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button @click="confirmarInhabilitar" :disabled="!archivoBaja || cargandoBaja"
                        class="flex-1 px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-lg disabled:opacity-50 transition-all">
                        {{ cargandoBaja ? 'Procesando...' : 'Confirmar Baja' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import Modal from '@/components/Modal/Modal.vue'

const isProfileAddressModal = ref(false)
const isEditModalOpen = ref(false)
const showPassword = ref(false)
// Creamos una función para que el bloque de abajo pueda cerrar el modal
const cerrarModalDesdeAfuera = () => {
    isProfileAddressModal.value = false
}

// IMPORTANTE: Exponemos la variable y la función
defineExpose({
    isProfileAddressModal,
    isEditModalOpen,
    cerrarModalDesdeAfuera
})
</script>
<script>
import API from "@/assets/js/services/axios";
import { useRoute } from "vue-router";
import debounce from 'lodash.debounce';
import Modal from '@/components/Modal/Modal.vue'
import { mostraralertas2, enviarsolig } from '@/assets/js/function/funciones';

export default {
    data() {
        return {
            idus: 0,
            baseUrl: "/inves",
            filteredarray: [],
            searchQuery: "",
            isProfileAddressModal: false,
            cargando: false,
            currentPage: 1,
            lastPage: 1,
            //buscando: false, // Mantenido, pero no se usa en la lógica de paginación actual
            debouncedFilter: null,
            showModalDetalles: false,
            proyectoSeleccionado: { invi_detalle_fac_proy: [], invi_detalle_integrante: [] },
            filtroIntegrante: '',
            integranteEdit: null,
            formInt: {
                id_deta_invi_proyect: null,
                horas: 0,
                id_funcion: null,
                idCarr: null,
                reemplazado: 0,
                anexo_integrante: null,
                id_funcion_reemplazado: null,
                horas_reemplazado: 0,
                idCarr_reemplazado: null

            },
            funciones: [],
            carreras: [],
            cedulaBusqueda: '',
            nuevoIntegranteData: null,
            buscando: false,
            modoNuevo: false,
            archivoSeleccionado: null,
            archivoPreviewName: '',
            uploading: false,
            continuarEnProyecto: true,
            showModalBaja: false,
            integranteBaja: null,
            archivoBaja: null,
            archivoBajaName: '',
            cargandoBaja: false,
            enviando: false,

        };
    },
    created() {
        // Ahora sí puedes usar this.filterAndFetch
        this.debouncedFilter = debounce(() => {
            this.filterAndFetch();
        }, 900);
    },
    async mounted() {
        const ruta = useRoute();
        this.GetData(1, this.searchQuery);

    },
    computed: {
        integrantesFiltrados() {
            if (!this.filtroIntegrante) return this.proyectoSeleccionado.invi_detalle_integrante;
            return this.proyectoSeleccionado.invi_detalle_integrante.filter(i => {
                const ced = i.ciinfper_doc || i.ciinfper_est;
                return ced.includes(this.filtroIntegrante);
            });
        },
        funcionesFiltradasSinDireccion() {
            return this.funciones.filter(f => {
                const nombre = f.nombre_funcion.toUpperCase();
                return !nombre.includes('DIRECTOR');
            });
        },
    },
    methods: {
        getPhotoUrl(ci) {
            // Si no hay CI, retornamos una imagen vacía o un placeholder
            if (!ci) return '';

            const baseURL2 = API.defaults.baseURL;
            // Usamos el timestamp para evitar problemas de caché al cambiar de integrante
            return `${baseURL2}/inves/getFotoDocente/${ci}?t=${new Date().getTime()}`;
        },
        formatDate(date) {
            if (!date) return '---';

            const fecha = new Date(date);

            // Formatear la fecha: "29 abr 2026"
            const fechaLegible = fecha.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            // Formatear la hora: "09:44 AM"
            const horaLegible = fecha.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            return `${fechaLegible} - ${horaLegible}`;
        },
        handleFileChange(event) {
            //Obtener el archivo seleccionado por el usuario
            const file = event.target.files[0];
            //Validar que el archivo seleccionado sea un archivo PDF, si no se cumple se muestra una alerta y se limpia el archivo seleccionado
            if (!file) return;
            // validación básica: pdf y tamaño si quieres
            if (file.type !== 'application/pdf') {
                //Mostrar una alerta de advertencia si el archivo seleccionado no es un archivo PDF, se usa la función mostraralertas2 para mostrar un mensaje de advertencia
                mostraralertas2('Solo se permiten archivos PDF', 'warning');
                //Limpiar el archivo seleccionado
                this.$refs.fileFoto.value = null;
                //Devolver sin hacer nada más
                return;
            }
            //Validar que el tamaño del archivo no exceda el límite de 10 MB, si no se cumple se muestra una alerta y se limpia el archivo seleccionado
            const maxMB = 10;
            //Si el tamaño del archivo es mayor que el límite de 10 MB, se muestra una alerta y se limpia el archivo seleccionado
            if (file.size > maxMB * 1024 * 1024) {
                //Mostrar una alerta de advertencia si el tamaño del archivo es mayor que el límite de 10 MB, se usa la función mostraralertas2 para mostrar un mensaje de advertencia
                mostraralertas2(`Archivo muy grande. Máx ${maxMB} MB`, 'warning');
                //Limpiar el archivo seleccionado
                this.$refs.fileFoto.value = null;
                //Devolver sin hacer nada más
                return;
            }
            //Asignar el archivo seleccionado a la variable archivoSeleccionado
            this.archivoSeleccionado = file;
            //Asignar el nombre del archivo seleccionado a la variable archivoPreviewName
            this.archivoPreviewName = file.name;
        },
        handleFileBajaChange(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.type !== 'application/pdf') {
                mostraralertas2('Solo se permiten archivos PDF', 'warning');
                this.$refs.fileInputBaja.value = null; // Ref corregido
                return;
            }

            const maxMB = 10;
            if (file.size > maxMB * 1024 * 1024) {
                mostraralertas2(`Archivo muy grande. Máx ${maxMB} MB`, 'warning');
                this.$refs.fileInputBaja.value = null; // Ref corregido
                return;
            }

            this.archivoBaja = file;
            this.archivoBajaName = file.name;
        },
        async uploadarchivo(ci, oldFilename = null) {
            if (!this.archivoSeleccionado) return null; // nada que subir
            try {
                this.uploading = true;
                const form = new FormData();
                form.append('file', this.archivoSeleccionado);
                form.append('ci', ci);
                if (oldFilename) {
                    form.append('old_filename', oldFilename); // Enviamos el nombre del archivo viejo
                }

                // Si tu backend exige otros campos (ej: tipo), añade aquí
                const resp = await API.post(`${this.baseUrl}/subir_archivo_anexo`, form, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                if (resp && resp.data && resp.data.filename) {
                    this.archivoSeleccionado = null;
                    this.archivoPreviewName = '';
                    this.$refs.fileFoto.value = null;
                    return resp.data; // { filename, url }
                } else {
                    mostraralertas2('Error subiendo archivo', 'danger');
                    return null;
                }
            } catch (error) {
                mostraralertas2('Error subiendo archivo', 'danger');
                return null;
            } finally {
                this.uploading = false;
            }
        },
        async uploadarchivoBaja(ci, oldFilename = null) {
            if (!this.archivoBaja) return null;
            try {
                this.cargandoBaja = true;
                const form = new FormData();
                form.append('file', this.archivoBaja);
                form.append('ci', ci);

                // Verifica que la URL del backend sea la correcta (que ya creaste en PHP)
                const resp = await API.post(`${this.baseUrl}/subir_archivo_anexo_darbaja`, form, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                // IMPORTANTE: Verifica qué devuelve exactamente tu backend. 
                // Si el backend devuelve { status: true, filename: "..." }, úsalo así:
                if (resp && resp.data && resp.data.filename) {
                    const dataRetornada = resp.data;

                    // Limpiamos DESPUÉS de guardar el nombre en una constante
                    this.archivoBaja = null;
                    this.archivoBajaName = '';
                    if (this.$refs.fileInputBaja) this.$refs.fileInputBaja.value = null;

                    return dataRetornada;
                } else {
                    console.error("Respuesta inesperada del servidor:", resp.data);
                    return null;
                }
            } catch (error) {
                console.error("Error en petición Axios:", error);
                return null;
            } finally {
                this.cargandoBaja = false;
            }
        },
        async confirmarInhabilitar() {
            if (!this.archivoBaja || this.cargandoBaja) return;

            try {
                const ciarchvi = this.integranteBaja.ciinfper_doc || this.integranteBaja.ciinfper_est;

                // Esperamos la subida del archivo
                const anexoData = await this.uploadarchivoBaja(ciarchvi);

                if (!anexoData || !anexoData.filename) {
                    mostraralertas2("Error al subir el archivo de respaldo", "error");
                    return;
                }

                // 1. Preparar Payload según lo que espera tu función inhabilitar(Request $request)
                const payload = {
                    id: this.integranteBaja.id_deta_invi_proyect, // Tu controlador usa $request->id
                    anexo_integrante: anexoData.filename
                };

                const resp = await API.post(`${this.baseUrl}/inhabilitar-integrante`, payload);

                if (resp && resp.status === 200) {
                    this.showModalBaja = false;
                    await this.abrirDetallesProyecto(this.proyectoSeleccionado.proyect_id);
                    mostraralertas2("Integrante inhabilitado con éxito", "success");
                }

            } catch (error) {
                console.error("❌ Error completo:", error.response?.data || error);
                mostraralertas2("Error al procesar la baja", "danger");
            }
        },
        async cerraModal() {
            this.cancelarEdicion();
            this.showModalDetalles = false;
        },
        calcularHoras() {
            const funcionObj = this.funciones.find(f => f.id_funcion === this.formInt.id_funcion);
            if (!funcionObj) return;

            const nombre = funcionObj.nombre_funcion.toLowerCase();
            if (nombre.includes('director') && !nombre.includes('sub')) {
                this.formInt.horas = 8;
            } else if (nombre.includes('subdirector')) {
                this.formInt.horas = 6;
            } else {
                this.formInt.horas = 4;
            }
        },
        calcularHorasReemplazo() {
            const funcionObj = this.funciones.find(f => f.id_funcion === this.formInt.id_funcion_reemplazado);
            if (!funcionObj) return;

            const nombre = funcionObj.nombre_funcion.toLowerCase();
            if (nombre.includes('director') && !nombre.includes('sub')) {
                this.formInt.horas_reemplazado = 8;
            } else if (nombre.includes('subdirector')) {
                this.formInt.horas_reemplazado = 6;
            } else {
                this.formInt.horas_reemplazado = 4;
            }
        },
        async abrirDetallesProyecto(id) {
            try {
                const [resProj, resCat] = await Promise.all([
                    API.get(`${this.baseUrl}/invi_proyectos/${id}`),
                    API.get(`${this.baseUrl}/catalogos-integrantes`)
                ]);
                this.proyectoSeleccionado = resProj.data;
                this.funciones = resCat.data.funciones;
                this.carreras = resCat.data.carreras;
                this.showModalDetalles = true;
            } catch (e) {
                console.error(e);
            }
        },
        seleccionarIntegrante(int) {
            this.integranteEdit = {
                id: int.id_deta_invi_proyect,
                id_deta_invi_proyect: int.id_deta_invi_proyect,
                nombre: (int.informacion_personal_d?.NombInfPer || int.informacionpersonal?.NombInfPer),
                apellido: (int.informacion_personal_d?.ApellInfPer || int.informacionpersonal?.ApellInfPer),
                apellidomaterno: (int.informacion_personal_d?.ApellMatInfPer || int.informacionpersonal?.ApellMatInfPer),
                funcion: int.funciones?.nombre_funcion || 'N/A',
                cedula: int.ciinfper_doc || int.ciinfper_est,
            };
            this.formInt = {
                id_deta_invi_proyect: int.id_deta_invi_proyect,
                horas: int.horas,
                id_funcion: int.id_funcion,
                idCarr: int.idCarr,
                reemplazado: 0,
                anexo_integrante: int.anexo_integrante,
                // Campos para el que se queda:
                id_funcion_reemplazado: null,
                horas_reemplazado: 0,
                idCarr_reemplazado: int.idCarr
            };
            this.nuevoIntegranteData = null;
            this.cedulaBusqueda = '';
            this.buscando = true;
        },
        async buscarNuevoIntegrante() {
            if (!this.cedulaBusqueda) return;

            // Validación local: No reemplazarse a sí mismo
            if (this.integranteEdit && this.cedulaBusqueda === this.integranteEdit.cedula) {
                this.buscando = false;
                mostraralertas2("No puedes reemplazar a un integrante por sí mismo.", "warning");
                return;
            }

            try {
                const res = await API.get(`${this.baseUrl}/buscar-integrantes`, {
                    params: {
                        cedula: this.cedulaBusqueda,
                        proyect_id: this.proyectoSeleccionado.proyect_id,
                        // Si modoNuevo es falso y reemplazado es 1, entonces es un reemplazo real
                        es_reemplazo: (!this.modoNuevo && this.formInt.reemplazado == 1) ? 1 : 0
                    }
                });

                this.nuevoIntegranteData = res.data;
                this.buscando = true;
            } catch (e) {
                const mensajeError = e.response?.data?.message || "Error al buscar integrante.";
                mostraralertas2(mensajeError, "warning");
                this.nuevoIntegranteData = null;
                this.buscando = false;
                this.cedulaBusqueda = '';
            }
        },
        activarModoNuevo() {
            this.cancelarEdicion();
            this.modoNuevo = true;
            this.buscando = false;
        },
        cancelarEdicion() {
            this.integranteEdit = null;
            this.modoNuevo = false;
            this.nuevoIntegranteData = null;
            this.cedulaBusqueda = '';
            this.pdfFile = null;
            this.formInt = { horas: 0, id_funcion: null, idCarr: null, reemplazado: 0 };
            this.buscando = false;
        },

        async inhabilitarIntegrante(int) {
            this.integranteBaja = int;
            this.archivoBaja = null;
            this.archivoBajaName = '';
            this.showModalBaja = true;
        },

        async guardarCambios() {
            // 1. Validaciones Previas
            if (!this.formInt.id_funcion || !this.formInt.idCarr) {
                return mostraralertas2("Complete función y carrera.", "warning");
            }

            // Si es nuevo o reemplazo, el archivo es obligatorio
            if ((this.modoNuevo || this.formInt.reemplazado == 1) && !this.archivoSeleccionado && !this.formInt.anexo_integrante) {
                return mostraralertas2("El documento de respaldo PDF es obligatorio.", "warning");
            }
            // 2. Bloqueo de doble clic
            if (this.enviando) return;

            // 2. Validación de Director/Subdirector 
            const funcionSeleccionada = this.funciones.find(f => f.id_funcion === this.formInt.id_funcion);
            const nombreFun = funcionSeleccionada?.nombre_funcion.toUpperCase() || '';

            if (nombreFun.includes('DIRECTOR')) {
                const existeYa = this.integrantesFiltrados.find(i =>
                    i.id_funcion === this.formInt.id_funcion &&
                    i.reemplazado == 0 &&
                    (!this.integranteEdit || i.id_deta_invi_proyect !== this.integranteEdit.id_deta_invi_proyect)
                );

                if (existeYa) {
                    return mostraralertas2(`Ya existe un ${funcionSeleccionada.nombre_funcion} activo en este proyecto.`, "warning");
                }
            }

            try {
                this.enviando = true;
                let anexoData = null;
                const ciABuscar = this.modoNuevo ? this.nuevoIntegranteData.cedula : (this.formInt.reemplazado == 1 ? this.nuevoIntegranteData.cedula : (this.integranteEdit.ciinfper_doc || this.integranteEdit.ciinfper_est));

                // 3. Subir archivo si hay uno nuevo seleccionado
                if (this.archivoSeleccionado) {
                    anexoData = await this.uploadarchivo(ciABuscar);
                    if (!anexoData) {
                        this.enviando = false;
                        return;
                    }
                }

                // 4. Preparar Payload
                const payload = {
                    modo: this.modoNuevo ? 'nuevo' : 'editar',
                    proyect_id: this.proyectoSeleccionado.proyect_id,
                    id_deta_invi_proyect: this.integranteEdit?.id_deta_invi_proyect,
                    form: {
                        ...this.formInt,
                        cedula_nueva: this.nuevoIntegranteData?.cedula,
                        tipo_nuevo: this.nuevoIntegranteData?.tipo,
                        // Si es reemplazo, el archivo va a 'anexo_integrante' (el que entra)
                        anexo_integrante: (this.formInt.reemplazado == 1 && anexoData) ? anexoData.filename : this.formInt.anexo_integrante,
                        // Si es nuevo o edición simple, va a 'anexo_integrante2'
                        anexo_integrante2: (this.formInt.reemplazado == 0 && anexoData) ? anexoData.filename : this.formInt.anexo_integrante2
                    },
                    reemplazo_config: {
                        mantener_docente: this.continuarEnProyecto,
                        // Si el docente se queda, enviamos los datos del combo de edición que el usuario ajustó
                        nueva_funcion_reemplazado: this.formInt.id_funcion_reemplazado,
                        nuevas_horas_reemplazado: this.formInt.horas_reemplazado
                    }
                };

                const res = await API.post(`${this.baseUrl}/integrantes/guardar`, payload);

                if (res.data.status) {
                    mostraralertas2("Cambios guardados correctamente", "success");
                    this.cancelarEdicion() // O recargar la lista
                    await this.abrirDetallesProyecto(this.proyectoSeleccionado.proyect_id);
                    // Aquí deberías refrescar la lista de integrantes del proyecto
                }
            } catch (error) {
                mostraralertas2(error.response?.data?.message || "Error al procesar la solicitud", "danger");
            } finally {
                this.enviando = false; // Liberamos el botón siempre, sea éxito o error
            }
        },

        async GetData(page = 1, searchQuery = "") {
            this.cargando = true;

            try {
                const params = {
                    page: page,
                    search_query: searchQuery // Parámetro para búsqueda
                };
                const response = await API.get(`${this.baseUrl}/invi_proyectos`, { params });

                const data = response.data?.data || [];
                this.filteredarray = data
                const pagination = response.data?.pagination || {};
                this.currentPage = pagination.current_page || 1;
                this.lastPage = pagination.last_page || 1;
            } catch (error) {
                console.warn("⚠️ Error al obtener datos:", error?.response?.data || error);
                this.filteredarray = [];
                this.currentPage = 1;
                this.lastPage = 1;
            } finally {
                this.cargando = false;
            }
        },

        filterAndFetch() {
            this.currentPage = 1;
            this.GetData(this.currentPage, this.searchQuery);
        },

        nextPage() {
            if (this.currentPage < this.lastPage && !this.cargando) {
                this.GetData(this.currentPage + 1, this.searchQuery);
            }
        },

        previousPage() {
            if (this.currentPage > 1 && !this.cargando) {
                this.GetData(this.currentPage - 1, this.searchQuery);
            }
        },

        actualizar() {
            // Simplemente recarga la página actual de datos
            this.GetData(this.currentPage, this.searchQuery);
        },

    },
};
</script>
