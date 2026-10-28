<template>
    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
        <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center w-full sm:w-auto">
                <!-- Buscador por código -->
                <form class="w-full sm:w-auto" @submit.prevent>
                    <div class="relative">
                        <button class="absolute -translate-y-1/2 left-4 top-1/2">
                            <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                            </svg>
                        </button>
                        <input type="text" placeholder="Ingresa el código del proyecto a buscar..."
                            v-model="searchQuery" @input="debouncedFilter"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 sm:w-[320px] xl:w-[380px]" />
                    </div>
                </form>

                <!-- Filtro por Convocatoria -->
                <div class="w-full sm:w-auto">
                    <select v-model="selectedConvocatoria" @change="filtrarPorConvocatoria"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:text-gray-300 dark:border-gray-800 sm:w-[260px]">
                        <option value="">Todas las convocatorias</option>
                        <option v-for="conv in listaConvocatorias" :key="conv.id_convocatoria" :value="conv.id_convocatoria">
                            {{ conv.num_convocatoria ? conv.num_convocatoria + ' - ' : '' }}
                        </option>
                    </select>
                </div>
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
                            <div class="flex justify-end gap-2">
                                
                                <!-- Botón 1: Editar Proyecto (Lápiz) -->
                                <button @click="abrirEdicion(post.proyect_id)"
                                    :disabled="botonCargando === 'editar_' + post.proyect_id"
                                    class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center"
                                    title="Editar Proyecto">
                                    <!-- Spinner -->
                                    <svg v-if="botonCargando === 'editar_' + post.proyect_id" class="animate-spin h-5 w-5 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <!-- Icono Lápiz -->
                                    <svg v-else width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>

                                <!-- Botón 2: Ver Detalles / Integrantes (Usuarios) -->
                                <button @click="abrirDetallesProyecto(post.proyect_id)"
                                    :disabled="botonCargando === 'detalles_' + post.proyect_id"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center"
                                    title="Ver Integrantes">
                                    <!-- Spinner -->
                                    <svg v-if="botonCargando === 'detalles_' + post.proyect_id" class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <!-- Icono Grupo de Usuarios -->
                                    <svg v-else width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </button>

                                <!-- Botón 3: Generar Anexo 2 (Documento PDF) -->
                                <button @click="generarPDFCronograma(post.proyect_id)"
                                    :disabled="botonCargando === 'anexo2_' + post.proyect_id"
                                    class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center"
                                    title="Generar Anexo 2">
                                    <!-- Spinner -->
                                    <svg v-if="botonCargando === 'anexo2_' + post.proyect_id" class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <!-- Icono Documento Texto -->
                                    <svg v-else width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </button>
                                <button @click="PDFAnexo1(post.proyect_id)"
                                    :disabled="botonCargando === 'anexo_1_' + post.proyect_id"
                                    class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center"
                                    title="Generar Carta Aval">
                                    <!-- Spinner -->
                                    <svg v-if="botonCargando === 'anexo_1_' + post.proyect_id" class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <!-- Icono Documento Texto -->
                                    <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22h6a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v10"></path>
                                        <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                        <path d="M9 16l2 2 4-4"></path> <!-- Check de Aval -->
                                    </svg>
                                </button>

                                <!-- Botón 4: Generar Anexo 3 (Documento PDF Variante) -->
                                <button @click="abrirModalAreaTematica(post.proyect_id)"
                                    :disabled="botonCargando === 'anexo3_' + post.proyect_id"
                                    class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center"
                                    title="Generar Anexo 3">
                                    <!-- Spinner -->
                                    <svg v-if="botonCargando === 'anexo3_' + post.proyect_id" class="animate-spin h-5 w-5 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <!-- Icono Documento Check -->
                                    <svg v-else width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <path d="M9 15l2 2 4-4"></path>
                                    </svg>
                                </button>
                                <button @click="PDFProyect(post.proyect_id)"
                                    :disabled="botonCargando === 'pdf_completo_' + post.proyect_id"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center"
                                    title="Generar PDF Completo">
                                    <!-- Spinner -->
                                    <svg v-if="botonCargando === 'pdf_completo_' + post.proyect_id" class="animate-spin h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <!-- Icono Documento de Descarga / PDF -->
                                    <svg v-else width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <path d="M12 18v-6"></path>
                                        <path d="M9 15l3 3 3-3"></path>
                                    </svg>
                                </button>
                                
                            </div>
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
                                    <div class="md:col-span-3 mt-2 p-4 border rounded-xl bg-gray-50 dark:bg-gray-700/30" v-if="habilitarcompro">
                                        <label class="block text-[10px] font-bold mb-3 uppercase text-blue-600">
                                            Compromisos a entregar
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                            <label v-for="comp in listaCompromisos" :key="comp" 
                                                   class="flex items-start gap-2 cursor-pointer group">
                                                <input type="checkbox" :value="comp" v-model="formInt.compromisos"
                                                    class="mt-1 w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                                <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 transition-colors">
                                                    {{ comp }}
                                                </span>
                                            </label>
                                        </div>
                                        
                                        <!-- Input dinámico para "Otros" -->
                                        <div v-if="formInt.compromisos.includes('Otros')" class="mt-4 animate-fadeIn">
                                            <label class="block text-[10px] font-bold mb-1 uppercase text-gray-500">
                                                Especifique el otro compromiso
                                            </label>
                                            <input type="text" v-model="formInt.compromiso_otro" 
                                                placeholder="Ej: Manual de usuario, Prototipo funcional..."
                                                class="w-full border rounded-lg p-2 text-sm focus:ring-2 ring-blue-200 outline-none bg-white dark:bg-gray-800">
                                        </div>
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
                                       class="bg-blue-700 text-white px-8 py-2 rounded-xl font-bold text-sm shadow-lg shadow-blue-100 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                        <span v-if="enviando">
                                            Procesando...
                                        </span>
                                        <span v-else>
                                            {{ modoNuevo ? 'Registrar Integrante' : 'Guardar Cambios' }}
                                        </span>
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
                        <div class="flex justify-end mb-4">
                            <button @click="descargarTodosCompromisos()"
                                :disabled="botonCargando === 'descarga_masiva'"
                                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                
                                <!-- Spinner -->
                                <svg v-if="botonCargando === 'descarga_masiva'" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>

                                <!-- Icono Descarga Multiple -->
                                <svg v-else width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Descarga Masiva de Anexos de Compromiso
                            </button>
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
                                        <th class="p-3 text-center">Anexo Compromiso</th>
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
                                            <div v-if="int.compromisos.length > 0 || int.funciones?.nombre_funcion =='Estudiante del proyecto de investigación'" class="flex justify-center">
                                                <button @click="descargarcompromiso(int.ciinfper_doc || int.ciinfper_est)"
                                                    :disabled="botonCargando === 'compromiso_' + (int.ciinfper_doc || int.ciinfper_est)"
                                                    target="_blank"
                                                    class="group relative flex items-center justify-center p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Ver documento PDF">
                                                    
                                                    <!-- Spinner -->
                                                    <svg v-if="botonCargando === 'compromiso_' + (int.ciinfper_doc || int.ciinfper_est)" class="animate-spin h-[18px] w-[18px]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>

                                                    <!-- Ícono de Documento PDF (se oculta si está cargando) -->
                                                    <svg v-else width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        <path d="M9 15h6M9 11h6" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <span v-else class="text-[10px] text-gray-300 italic">Sin anexo Compromiso</span>
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
        <div v-if="showEditModal"
            class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 z-[99999] transition-all duration-300">
            <div
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col border border-gray-100 dark:border-gray-800 transform scale-100 transition-transform duration-300">

                <div
                    class="flex justify-between items-center p-6 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-gray-50 to-white dark:from-gray-900 dark:to-gray-850">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="text-brand-500" width="22" height="22" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Gestión y Edición del Proyecto
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Modifica la información estructural del proyecto y su
                            alineación
                            estratégica.</p>
                    </div>
                    <button @click="cerrarEdicion"
                        class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div
                        ref="scrollContainer"
                        @mousedown="startDrag"
                        @mouseleave="stopDrag"
                        @mouseup="stopDrag"
                        @mousemove="doDrag"
                        :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'"
                        class="custom-scrollbar flex-shrink-0 flex border-b border-gray-100 dark:border-gray-800 px-6 pt-3 pb-2 bg-gray-50/50 dark:bg-gray-900 gap-2 overflow-x-auto flex-nowrap"
                    >
                    <button @click="activeTab = 'generales'"
                        :class="activeTab === 'generales'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">1</span>
                        Datos Generales
                    </button>
                    <button @click="activeTab = 'cobertura'"
                        :class="activeTab === 'cobertura'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">2</span>
                        Cobertura y Localización
                    </button>
                    <button @click="activeTab = 'objetivos'"
                        :class="activeTab === 'objetivos'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">3</span>
                        Objetivos del Proyecto
                    </button>
                    <button @click="activeTab = 'antecedentes_justifi'"
                        :class="activeTab === 'antecedentes_justifi'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">4</span>
                        Antecedentes y Justificación
                    </button>
                    <button @click="activeTab = 'instituciones'"
                        :class="activeTab === 'instituciones'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">5</span>
                        Instituciones y Organismos participantes
                    </button>
                    <button @click="activeTab = 'presupuesto_inst'"
                        :class="activeTab === 'presupuesto_inst'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">6</span>
                        Presupuesto Institucional
                    </button>
                    <button @click="activeTab = 'plazo_ejecucion'"
                        :class="activeTab === 'plazo_ejecucion'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">7</span>
                        Plazo de Ejecución 
                    </button>
                    <button @click="activeTab = 'personalproyect'"
                        :class="activeTab === 'personalproyect'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">8</span>
                        Personal del proyecto 
                    </button>
                    <button @click="activeTab = 'diagnostico_prob'"
                        :class="activeTab === 'diagnostico_prob'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">9</span>
                        Diagnóstico y Problema
                    </button>
                    <button @click="activeTab = 'cronograma_act'"
                        :class="activeTab === 'cronograma_act'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">10</span>
                        Cronograma de Actividades
                    </button>
                    <button @click="activeTab = 'bienes_servi_proyec'"
                        :class="activeTab === 'bienes_servi_proyec'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">11</span>
                        Detalles de bienes y servicios 
                    </button>
                    <button @click="activeTab = 'metodologia'"
                        :class="activeTab === 'metodologia'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">12</span>
                        Metodología 
                    </button>
                    <button @click="activeTab = 'financia'"
                        :class="activeTab === 'financia'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">13</span>
                        Financiamiento 
                    </button>
                    <button @click="activeTab = 'viabiliadad_sos'"
                        :class="activeTab === 'viabiliadad_sos'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">14</span>
                        Viabilidad y Sostenibilidad
                    </button>
                    <button @click="activeTab = 'difusion'"
                        :class="activeTab === 'difusion'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">15</span>
                        Difusión
                    </button>
                    <button @click="activeTab = 'bibliografias'"
                        :class="activeTab === 'bibliografias'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">16</span>
                        Bibliografías
                    </button>
                    <button @click="activeTab = 'evidencia_pdf'"
                        :class="activeTab === 'evidencia_pdf'
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-850 shadow-sm rounded-t-xl border-t border-x'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-800 rounded-t-xl border-transparent'"
                        class="flex-shrink-0 whitespace-nowrap pb-3 pt-2.5 px-4 text-sm font-medium transition-all duration-200 border-b-2 -mb-[1px] flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-[11px] font-bold text-brand-600 dark:text-brand-400">17</span>
                        Evidencia PDF
                    </button>
                </div>

                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-white dark:bg-gray-900">
                    <div v-if="cargandoEdicion" class="flex flex-col justify-center items-center py-20 gap-3">
                        <span
                            class="animate-spin h-10 w-10 border-4 border-brand-500 border-t-transparent rounded-full"></span>
                        <p class="text-sm text-gray-500 font-medium">Sincronizando información del Proyecto...</p>
                    </div>
                    <div v-else-if="activeTab === 'generales'" class="space-y-6 progress-fade">
                        <div
                            class="bg-gray-50/60 dark:bg-gray-800/30 p-4 rounded-xl border border-gray-100 dark:border-gray-800/60">
                            <div class="flex items-start gap-2.5 mb-4">
                                <span class="p-1 rounded-md bg-blue-50 text-blue-600 mt-0.5">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.25 11.25l.041-.02a.75.75 0 111.063 1.06l-.042.02a.75.75 0 01-1.063-1.06zm0 0a8.25 8.25 0 11-16.5 0 8.25 8.25 0 0116.5 0zM12 7h.008v.008H12V7z" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Identificación
                                        del
                                        Proyecto</h4>
                                    <p class="text-xs text-gray-500">Asegúrate de que los títulos coincidan
                                        rigurosamente con la
                                        documentación oficial aprobada.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <div class="group flex flex-col">
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 transition-colors group-focus-within:text-brand-600">
                                        Nombre del Proyecto (ES)
                                    </label>
                                    <textarea v-model="editForm.proyect_nombre" @input="yaTraducido = false" rows="4"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-850 p-3 text-sm text-gray-800 dark:text-gray-100 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all duration-200 outline-none resize-none"
                                        placeholder="Escribe el nombre del proyecto..."></textarea>
                                    <p class="mt-2 text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                                        <span class="font-semibold text-gray-600 dark:text-gray-300">Guía:</span> Se
                                        refiere a
                                        un término más informal o distintivo que se utiliza para identificar el proyecto
                                        de
                                        manera única. Puede ser más creativo y no necesariamente describir el contenido
                                        del
                                        proyecto de manera detallada.
                                    </p>
                                </div>

                                <div class="flex flex-col">
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">
                                        Nombre del Proyecto (EN)
                                    </label>
                                    <textarea v-model="editForm.proyect_nombre_en" rows="4"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/50 p-3 text-sm text-gray-700 dark:text-gray-300 shadow-inner focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all duration-200 outline-none resize-none"
                                        placeholder="Traducción automática o manual..."></textarea>
                                    <p class="mt-2 text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                                        <span class="font-semibold text-gray-600 dark:text-gray-300">Guía:</span>
                                        Traducción al
                                        inglés del nombre del proyecto. Debe mantener el tono distintivo e informal del
                                        original.
                                    </p>
                                </div>

                                <div class="group flex flex-col">
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 transition-colors group-focus-within:text-brand-600">
                                        Título del proyecto (ES)
                                    </label>
                                    <textarea v-model="editForm.proyect_titulo" @input="yaTraducido = false" rows="3"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-850 p-3 text-sm text-gray-800 dark:text-gray-100 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all duration-200 outline-none resize-none"
                                        placeholder="Escribe el Título del proyecto ..."></textarea>
                                    <p class="mt-2 text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                                        <span class="font-semibold text-gray-600 dark:text-gray-300">Guía:</span> Es una
                                        frase
                                        corta y descriptiva que resume el propósito o enfoque central del proyecto. El
                                        título
                                        proporciona una visión general y a menudo incluye palabras clave que destacan la
                                        naturaleza y el alcance del proyecto.
                                    </p>
                                </div>

                                <div class="flex flex-col">
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">
                                        Título del proyecto (EN)
                                    </label>
                                    <textarea v-model="editForm.proyect_titulo_en" rows="3"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/50 p-3 text-sm text-gray-700 dark:text-gray-300 shadow-inner focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all duration-200 outline-none resize-none"
                                        placeholder="Traducción automática o manual..."></textarea>
                                    <p class="mt-2 text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                                        <span class="font-semibold text-gray-600 dark:text-gray-300">Guía:</span>
                                        Traducción al
                                        inglés del título técnico. Debe conservar las palabras clave y la descripción
                                        precisa
                                        del alcance establecidas en la versión en español.
                                    </p>
                                </div>

                            </div>

                            <div
                                class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200/70 dark:border-amber-900/40 p-3.5 rounded-xl">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-amber-600 dark:text-amber-400 shrink-0">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <p class="text-xs text-amber-800 dark:text-amber-300 font-medium">
                                        <span class="font-bold">Nota de Calidad:</span> Para garantizar la precisión
                                        técnica,
                                        verifique el resultado traducido antes de guardar los cambios finales.
                                    </p>
                                </div>
                                <button @click="traducirTextos"
                                    :disabled="yaTraducido || (!editForm.proyect_nombre && !editForm.proyect_titulo)"
                                    :class="yaTraducido
                                        ? 'bg-emerald-100 text-emerald-700 border border-emerald-200 cursor-not-allowed opacity-80'
                                        : 'bg-gray-900 dark:bg-gray-800 text-white hover:bg-gray-800 dark:hover:bg-gray-700 active:scale-95 shadow-md shadow-gray-900/10'"
                                    class="text-xs px-4 py-2.5 font-semibold rounded-lg transition-all duration-200 flex items-center gap-2 shrink-0 w-full sm:w-auto justify-center">
                                    <span v-if="yaTraducido"
                                        class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    {{ yaTraducido ? '✓ Textos Traducidos' : 'Auto-Traducir Textos' }}
                                </button>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="p-1 rounded-md bg-purple-50 text-purple-600 mt-0.5 dark:bg-purple-950/40 dark:text-purple-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Objetivos del
                                        Plan
                                        Estratégico Institucional</h4>
                                    <p class="text-xs text-gray-500">Vincule los objetivos operativos de este proyecto
                                        con las
                                        metas globales del PEI vigente. Por defecto queda seleccionado el objetivo que
                                        lleva
                                        relación con la vinculción con la sociedad</p>
                                </div>
                            </div>

                            <div
                                class="border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-950">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                                        <tr>
                                            <th
                                                class="px-5 py-3 text-left w-14 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                                Sel.</th>
                                            <th
                                                class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/4">
                                                Subsistema</th>
                                            <th
                                                class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Detalle del Objetivo Estratégico</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="obj in objetivosPeiDisponibles" :key="obj.id_obj_pei" :class="editForm.objetivos.includes(obj.id_obj_pei)
                                            ? 'bg-brand-50/40 dark:bg-brand-950/20 transition-colors'
                                            : 'hover:bg-gray-50/70 dark:hover:bg-gray-800/30 transition-colors'"
                                            class="cursor-pointer" @click="toggleObjetivo(obj.id_obj_pei)">
                                            <td class="px-5 py-4 text-center" @click.stop>
                                                <input type="checkbox" :value="obj.id_obj_pei"
                                                    v-model="editForm.objetivos"
                                                    class="w-4 h-4 text-brand-600 rounded border-gray-300 dark:border-gray-700 focus:ring-brand-500/30 transition-all duration-150">
                                            </td>
                                            <td class="px-5 py-4 text-xs font-bold text-gray-700 dark:text-gray-300">
                                                <span
                                                    :class="obj.subsistemas_pei?.nombre_subsistema.toLowerCase().includes('vinculación') ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'"
                                                    class="px-2.5 py-1 rounded-md tracking-wide">
                                                    {{ obj.subsistemas_pei ? obj.subsistemas_pei.nombre_subsistema :
                                                        'N/A' }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                                <span
                                                    class="font-mono text-xs text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/50 px-1.5 py-0.5 rounded mr-1.5 font-bold">{{
                                                        obj.cod_obj }}</span>
                                                {{ obj.detalle_obj }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div v-if="editForm.objetivos.length === 0"
                                class="flex items-center gap-2 text-red-500 dark:text-red-400 text-xs font-semibold p-1 animate-pulse">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Restricción: Es obligatorio seleccionar como mínimo un objetivo estratégico
                                institucional.
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="p-1 rounded-md bg-emerald-50 text-emerald-600 mt-0.5 dark:bg-emerald-950/40 dark:text-emerald-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Políticas del
                                        Plan de Desarrollo para el Nuevo Ecuador (PLANDE)</h4>
                                    <p class="text-xs text-gray-500">Vincule las políticas del PLANDE habilitado
                                        vigentes que
                                        dan soporte normativo y estratégico a las actividades del proyecto.</p>
                                </div>
                            </div>

                            <div
                                class="border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-950">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                                        <tr>
                                            <th
                                                class="px-5 py-3 text-left w-14 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                                Sel.</th>
                                            <th
                                                class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/4">
                                                Código Política</th>
                                            <th
                                                class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Detalle de la Política de Desarrollo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="pol in politicasPlandeDisponibles" :key="pol.id_pol_pladne"
                                            :class="editForm.politicas.includes(pol.id_pol_pladne) ? 'bg-brand-50/40 dark:bg-brand-950/20 transition-colors' : 'hover:bg-gray-50/70 dark:hover:bg-gray-800/30 transition-colors'"
                                            class="cursor-pointer" @click="togglePolitica(pol.id_pol_pladne)">

                                            <td class="px-5 py-4 text-center" @click.stop>
                                                <input type="checkbox" :value="pol.id_pol_pladne"
                                                    v-model="editForm.politicas"
                                                    class="w-4 h-4 text-brand-600 rounded border-gray-300 dark:border-gray-700 focus:ring-brand-500/30 transition-all duration-150">
                                            </td>
                                            <td class="px-5 py-4 text-xs font-bold text-gray-700 dark:text-gray-300">
                                                <span
                                                    class="bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 px-2.5 py-1 rounded-md tracking-wide font-mono">
                                                    {{ pol.cod_pol }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                                {{ pol.detalle_pol }}
                                            </td>
                                        </tr>
                                        <tr v-if="politicasPlandeDisponibles.length === 0">
                                            <td colspan="3" class="px-5 py-8 text-center text-sm text-gray-400">
                                                No hay políticas disponibles para el PLANDE activo.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="p-1 rounded-md bg-emerald-50 text-emerald-600 mt-0.5 dark:bg-emerald-950/40 dark:text-emerald-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.6 9h16.8M3.6 15h16.8M12 3a16.5 16.5 0 00-4 18M12 3a16.5 16.5 0 014 18" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                        Agenda 2030 y los Objetivos de desarrollo sostenible una oportunidad para
                                        América Latina
                                        y el Caribe
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Vincule los objetivos de la Agenda ODS que estén relacionados con su proyecto.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-950">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                                        <tr>
                                            <th
                                                class="px-5 py-3 text-left w-14 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                                Sel.</th>
                                            <th
                                                class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/4">
                                                Código ODS</th>
                                            <th
                                                class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Detalle del ODS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="ods in objetivosODSDisponibles" :key="ods.id_ods"
                                            :class="editForm.ods.includes(ods.id_ods) ? 'bg-brand-50/40 dark:bg-brand-950/20 transition-colors' : 'hover:bg-gray-50/70 dark:hover:bg-gray-800/30 transition-colors'"
                                            class="cursor-pointer" @click="toggleODS(ods.id_ods)">
                                            <td class="px-5 py-4 text-center" @click.stop>
                                                <input type="checkbox" :value="ods.id_ods" v-model="editForm.ods"
                                                    class="w-4 h-4 text-brand-600 rounded border-gray-300 dark:border-gray-700 focus:ring-brand-500/30 transition-all duration-150">
                                            </td>
                                            <td class="px-5 py-4 text-xs font-bold text-gray-700 dark:text-gray-300">
                                                <span
                                                    class="bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 px-2.5 py-1 rounded-md tracking-wide font-mono">
                                                    {{ ods.cod_ods }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                                {{ ods.detalle_ods }}
                                            </td>
                                        </tr>
                                        <tr v-if="objetivosODSDisponibles.length === 0">
                                            <td colspan="3" class="px-5 py-8 text-center text-sm text-gray-400">
                                                No hay ODS disponibles para la Agenda ODS.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="p-1 rounded-md bg-blue-50 text-blue-600 mt-0.5 dark:bg-blue-950/40 dark:text-blue-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Objetivos del
                                        Plan de
                                        Desarrollo para el Nuevo Ecuador (PLANDE)</h4>
                                    <p class="text-xs text-gray-500">Objetivos que corresponden a las políticas que ha
                                        seleccionado previamente.</p>
                                </div>
                            </div>

                            <div v-if="!editForm.politicas || editForm.politicas.length === 0"
                                class="p-4 border border-blue-100 bg-blue-50/50 dark:border-blue-950/50 dark:bg-blue-950/20 rounded-xl flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-xs text-blue-700 dark:text-blue-400">
                                    Debe seleccionar al menos una **Política del PlanDNE** en la sección anterior para
                                    que
                                    aparezcan sus objetivos correspondientes de forma automática.
                                </p>
                            </div>

                            <div v-else
                                class="border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-950">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                                        <tr>
                                            <th
                                                class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/4">
                                                Código Objetivo</th>
                                            <th
                                                class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Detalle del Objetivo de la Política</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="objPol in objetivosPoliticasDisponibles"
                                            :key="objPol.id_obj_pol_pladne" class="bg-brand-50/10 dark:bg-brand-950/5">
                                            <td class="px-5 py-4 text-xs font-bold text-gray-700 dark:text-gray-300">
                                                <span
                                                    class="bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 px-2.5 py-1 rounded-md tracking-wide font-mono">
                                                    {{ objPol.cod_obj_pol }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                                {{ objPol.detalle_obj_pol }}
                                            </td>
                                        </tr>
                                        <tr v-if="objetivosPoliticasDisponibles.length === 0">
                                            <td colspan="2" class="px-5 py-8 text-center text-sm text-gray-400">
                                                Las políticas seleccionadas no tienen objetivos registrados.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="p-1 rounded-md bg-indigo-50 text-indigo-600 mt-0.5 dark:bg-indigo-950/40 dark:text-indigo-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Enfoque del
                                        Proyecto</h4>
                                    <p class="text-xs text-gray-500">Determine si la naturaleza de la investigación
                                        requiere la
                                        intervención de múltiples áreas del conocimiento.</p>
                                </div>
                            </div>

                            <div
                                class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-4">
                                <label
                                    class="block text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                    ¿El proyecto es multidisciplinario?
                                </label>

                                <div class="flex flex-wrap gap-6">
                                    <label
                                        class="flex items-center gap-2 cursor-pointer group text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <input type="radio" :value="1" v-model="editForm.proyect_multidis"
                                            class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 transition-all">
                                        <span class="group-hover:text-brand-600 transition-colors">Sí, es
                                            multidisciplinario</span>
                                    </label>

                                    <label
                                        class="flex items-center gap-2 cursor-pointer group text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <input type="radio" :value="0" v-model="editForm.proyect_multidis"
                                            class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 transition-all">
                                        <span class="group-hover:text-brand-600 transition-colors">No, pertenece a una
                                            sola
                                            disciplina</span>
                                    </label>
                                </div>

                                <div class="transition-all duration-300">
                                    <div v-if="editForm.proyect_multidis === 1"
                                        class="p-4 border border-emerald-100 bg-emerald-50/40 dark:border-emerald-950/40 dark:bg-emerald-950/10 rounded-xl space-y-2">
                                        <div
                                            class="flex items-center gap-2 text-emerald-800 dark:text-emerald-400 font-semibold text-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Configuración multidisciplinaria activa</span>
                                        </div>
                                        <p class="text-xs text-emerald-700 dark:text-emerald-400/90 leading-relaxed">
                                            Has seleccionado que **sí**. Eso significa que tu proyecto trabajará en
                                            conjunto con
                                            varias facultades y carreras.
                                        </p>
                                        <div
                                            class="pt-1 text-[11px] text-gray-500 dark:text-gray-400 italic border-t border-emerald-200/40 dark:border-emerald-900/40">
                                            **¿Qué implica?** Los proyectos multidisciplinarios integran metodologías,
                                            marcos
                                            teóricos y herramientas de distintas áreas profesionales para resolver un
                                            problema
                                            complejo común, enriqueciendo los resultados desde diferentes perspectivas
                                            académicas.
                                        </div>
                                    </div>

                                    <div v-else-if="editForm.proyect_multidis === 0"
                                        class="p-4 border border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-900/30 rounded-xl space-y-2">
                                        <div
                                            class="flex items-center gap-2 text-gray-700 dark:text-gray-400 font-semibold text-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Enfoque disciplinar estándar</span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                            El proyecto **solo pertenece a una facultad y una carrera en específica**.
                                            El
                                            desarrollo metodológico y operativo se concentrará de forma interna en su
                                            propia
                                            área técnica de estudios.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="p-1 rounded-md bg-purple-50 text-purple-600 mt-0.5 dark:bg-purple-950/40 dark:text-purple-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Facultades
                                        Participantes
                                    </h4>
                                    <p class="text-xs text-gray-500">Gestione la filiación de las facultades asociadas
                                        directamente a la ejecución del proyecto.</p>
                                </div>
                            </div>

                            <div
                                class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-5">

                                <div v-if="editForm.proyect_multidis === 1" class="space-y-5 animate-fadeIn">
                                    <div class="space-y-2">
                                        <label
                                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            1. Seleccione la Facultad Prioritaria (A la que pertenece el proyecto) <span
                                                class="text-red-500">*</span>
                                        </label>
                                        <div
                                            class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                                            <div v-for="fac in facultadesCatalogo" :key="'priori-' + fac.idfacultad"
                                                class="flex items-start p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all"
                                                :class="editForm.id_facultad_priori === fac.idfacultad ? 'border-brand-500 bg-brand-50/10 dark:border-brand-900/30 ring-2 ring-brand-500/20' : 'border-gray-200 dark:border-gray-800'">
                                                <div class="flex items-center h-5">
                                                    <input type="radio" :id="'priori-' + fac.idfacultad"
                                                        name="facultad_prioritaria" :value="fac.idfacultad"
                                                        v-model="editForm.id_facultad_priori"
                                                        @change="handleFacultadPrioriChange"
                                                        class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                                </div>
                                                <label :for="'priori-' + fac.idfacultad"
                                                    class="ml-3 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer w-full">
                                                    <span class="block font-semibold"
                                                        :class="editForm.id_facultad_priori === fac.idfacultad ? 'text-brand-600 dark:text-brand-400' : ''">
                                                        {{ fac.facultad }} ({{ fac.siglas }})
                                                        <small v-if="editForm.id_facultad_priori === fac.idfacultad"
                                                            class="ml-1 text-[10px] bg-brand-100 text-brand-700 dark:bg-brand-950/80 dark:text-brand-300 px-1.5 py-0.5 rounded font-bold">Principal</small>
                                                    </span>
                                                    <span class="text-[11px] text-gray-400 block mt-0.5">Decano: {{
                                                        fac.decano
                                                        || 'No asignado' }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="editForm.id_facultad_priori"
                                        class="space-y-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                                        <label
                                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            2. Seleccione las otras Facultades que participan en conjunto:
                                        </label>

                                        <div
                                            class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                                            <div v-for="fac in facultadesCatalogo" :key="fac.idfacultad"
                                                class="flex items-start p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all"
                                                :class="editForm.id_facultad_priori === fac.idfacultad ? 'border-brand-200 bg-brand-50/10 dark:border-brand-900/30' : 'border-gray-200 dark:border-gray-800'">
                                                <div class="flex items-center h-5">
                                                    <input type="checkbox" :id="'fac-' + fac.idfacultad"
                                                        :value="fac.idfacultad" v-model="editForm.facultades"
                                                        :disabled="editForm.id_facultad_priori === fac.idfacultad"
                                                        class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                                </div>
                                                <label :for="'fac-' + fac.idfacultad"
                                                    class="ml-3 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                                    <span class="block font-semibold"
                                                        :class="editForm.id_facultad_priori === fac.idfacultad ? 'text-brand-600 dark:text-brand-400' : ''">
                                                        {{ fac.facultad }} ({{ fac.siglas }})
                                                        <small v-if="editForm.id_facultad_priori === fac.idfacultad"
                                                            class="ml-1 text-[10px] bg-brand-100 text-brand-700 dark:bg-brand-950/80 dark:text-brand-300 px-1.5 py-0.5 rounded font-bold">Principal</small>
                                                    </span>
                                                    <span class="text-[11px] text-gray-400 block mt-0.5">Decano: {{
                                                        fac.decano
                                                        || 'No asignado' }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="space-y-3 animate-fadeIn">
                                    <label
                                        class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Seleccione la Facultad Única a la que pertenece el proyecto <span
                                            class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                                        <div v-for="fac in facultadesCatalogo" :key="'unica-' + fac.idfacultad"
                                            class="flex items-start p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all"
                                            :class="editForm.id_facultad_priori === fac.idfacultad ? 'border-brand-500 bg-brand-50/10 dark:border-brand-900/30 ring-2 ring-brand-500/20' : 'border-gray-200 dark:border-gray-800'">
                                            <div class="flex items-center h-5">
                                                <input type="radio" :id="'unica-' + fac.idfacultad"
                                                    name="facultad_unica" :value="fac.idfacultad"
                                                    v-model="editForm.id_facultad_priori"
                                                    class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                            </div>
                                            <label :for="'unica-' + fac.idfacultad"
                                                class="ml-3 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer w-full">
                                                <span class="block font-semibold"
                                                    :class="editForm.id_facultad_priori === fac.idfacultad ? 'text-brand-600 dark:text-brand-400' : ''">
                                                    {{ fac.facultad }} ({{ fac.siglas }})
                                                    <small v-if="editForm.id_facultad_priori === fac.idfacultad"
                                                        class="ml-1 text-[10px] bg-brand-100 text-brand-700 dark:bg-brand-950/80 dark:text-brand-300 px-1.5 py-0.5 rounded font-bold">Seleccionada</small>
                                                </span>
                                                <span class="text-[11px] text-gray-400 block mt-0.5">Decano: {{
                                                    fac.decano ||
                                                    'No asignado' }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 italic">
                                        * Nota: Al no ser un proyecto multidisciplinario, la facultad seleccionada se
                                        definirá
                                        automáticamente como el núcleo prioritario y único del desarrollo de la
                                        investigación.
                                    </p>
                                </div>

                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="p-1 rounded-md bg-indigo-50 text-indigo-600 mt-0.5 dark:bg-indigo-950/40 dark:text-indigo-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 14l9-5-9-5-9 5 9 5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Carreras
                                        Participantes
                                    </h4>
                                    <p class="text-xs text-gray-500">Gestione la filiación de las carreras asociadas
                                        directamente a la ejecución del proyecto.</p>
                                </div>
                            </div>

                            <div
                                class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-5">

                                <div v-if="editForm.proyect_multidis === 1" class="space-y-5 animate-fadeIn">
                                    <div class="space-y-2">
                                        <label
                                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            1. Seleccione la Carrera Prioritaria (Debe pertenecer a la facultad
                                            prioritaria)
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <div
                                            class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                                            <div v-for="carr in carrerasCatalogo.filter(c => c.facultad?.id == editForm.id_facultad_priori)"
                                                :key="'carr-priori-' + carr.id_carrera"
                                                class="flex items-start p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all"
                                                :class="editForm.id_carr_priori === carr.id_carrera ? 'border-brand-500 bg-brand-50/10 dark:border-brand-900/30 ring-2 ring-brand-500/20' : 'border-gray-200 dark:border-gray-800'">
                                                <div class="flex items-center h-5">
                                                    <input type="radio" :id="'carr-priori-' + carr.id_carrera"
                                                        name="carrera_prioritaria" :value="carr.id_carrera"
                                                        v-model="editForm.id_carr_priori"
                                                        @change="handleCarreraPrioriChange"
                                                        class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                                </div>
                                                <label :for="'carr-priori-' + carr.id_carrera"
                                                    class="ml-3 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer w-full">
                                                    <span class="block font-semibold"
                                                        :class="editForm.id_carr_priori === carr.id_carrera ? 'text-brand-600 dark:text-brand-400' : ''">
                                                        {{ carr.nombre_carrera }}
                                                        <small v-if="editForm.id_carr_priori === carr.id_carrera"
                                                            class="ml-1 text-[10px] bg-brand-100 text-brand-700 dark:bg-brand-950/80 dark:text-brand-300 px-1.5 py-0.5 rounded font-bold">Principal</small>
                                                    </span>
                                                    <span class="text-[11px] text-gray-400 block mt-0.5">Director: {{
                                                        carr.director_procesado
                                                        }}</span>
                                                </label>
                                            </div>
                                            <div v-if="carrerasCatalogo.filter(c => c.facultad?.id == editForm.id_facultad_priori).length === 0"
                                                class="col-span-2 text-xs text-gray-400 italic p-2">
                                                No hay carreras cargadas. Por favor, seleccione una facultad prioritaria
                                                primero.
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="editForm.id_carr_priori"
                                        class="space-y-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                                        <label
                                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            2. Seleccione las otras Carreras que participan en conjunto:
                                        </label>
                                        <div
                                            class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                                            <div v-for="carr in carrerasCatalogo" :key="carr.id_carrera"
                                                class="flex items-start p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all"
                                                :class="editForm.id_carr_priori === carr.id_carrera ? 'border-brand-200 bg-brand-50/10 dark:border-brand-900/30' : 'border-gray-200 dark:border-gray-800'">
                                                <div class="flex items-center h-5">
                                                    <input type="checkbox" :id="'carr-' + carr.id_carrera"
                                                        :value="carr.id_carrera" v-model="editForm.carreras"
                                                        :disabled="editForm.id_carr_priori === carr.id_carrera"
                                                        class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                                </div>
                                                <label :for="'carr-' + carr.id_carrera"
                                                    class="ml-3 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                                    <span class="block font-semibold"
                                                        :class="editForm.id_carr_priori === carr.id_carrera ? 'text-brand-600 dark:text-brand-400' : ''">
                                                        {{ carr.nombre_carrera }}
                                                        <small v-if="editForm.id_carr_priori === carr.id_carrera"
                                                            class="ml-1 text-[10px] bg-brand-100 text-brand-700 dark:bg-brand-950/80 dark:text-brand-300 px-1.5 py-0.5 rounded font-bold">Principal</small>
                                                    </span>
                                                    <span class="text-[11px] text-gray-400 block mt-0.5">Director: {{
                                                        carr.director_procesado
                                                        }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="space-y-3 animate-fadeIn">
                                    <label
                                        class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Seleccione la Carrera Única a la que pertenece el proyecto <span
                                            class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                                        <div v-for="carr in carrerasCatalogo" :key="'unica-carr-' + carr.id_carrera"
                                            class="flex items-start p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all"
                                            :class="editForm.id_carr_priori === carr.id_carrera ? 'border-brand-500 bg-brand-50/10 dark:border-brand-900/30 ring-2 ring-brand-500/20' : 'border-gray-200 dark:border-gray-800'">
                                            <div class="flex items-center h-5">
                                                <input type="radio" :id="'unica-carr-' + carr.id_carrera"
                                                    name="carrera_unica" :value="carr.id_carrera"
                                                    v-model="editForm.id_carr_priori"
                                                    class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                            </div>
                                            <label :for="'unica-carr-' + carr.id_carrera"
                                                class="ml-3 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer w-full">
                                                <span class="block font-semibold"
                                                    :class="editForm.id_carr_priori === carr.id_carrera ? 'text-brand-600 dark:text-brand-400' : ''">
                                                    {{ carr.nombre_carrera }}
                                                    <small v-if="editForm.id_carr_priori === carr.id_carrera"
                                                        class="ml-1 text-[10px] bg-brand-100 text-brand-700 dark:bg-brand-950/80 dark:text-brand-300 px-1.5 py-0.5 rounded font-bold">Seleccionada</small>
                                                </span>
                                                <span class="text-[11px] text-gray-400 block mt-0.5">Director: {{
                                                    carr.director_procesado
                                                    }}</span>
                                            </label>
                                        </div>
                                        <div v-if="carrerasCatalogo.length === 0"
                                            class="col-span-2 text-xs text-gray-400 italic p-2">
                                            No hay carreras disponibles. Asegúrese de haber seleccionado la Facultad.
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 italic">
                                        * Nota: Al no ser un proyecto multidisciplinario, la carrera seleccionada se
                                        definirá
                                        automáticamente como el núcleo prioritario y único del desarrollo de la
                                        investigación.
                                    </p>
                                </div>

                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="p-1 rounded-md bg-amber-50 text-amber-600 mt-0.5 dark:bg-amber-950/40 dark:text-amber-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Dominios
                                        Humanísticos
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Los dominios humanísticos representan áreas del conocimiento enfocadas en el
                                        desarrollo
                                        social, ético y cultural. En vinculación, guían el impacto del proyecto hacia el
                                        bienestar comunitario y la transformación del entorno social.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-4">

                                <div v-if="dominiosCatalogo.length > 0"
                                    class="p-3 bg-gradient-to-r from-indigo-50 to-brand-50/30 dark:from-indigo-950/20 dark:to-transparent rounded-lg border border-indigo-100/50 dark:border-indigo-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="space-y-0.5">
                                        <span
                                            class="text-[10px] font-bold tracking-wider text-indigo-600 dark:text-indigo-400 uppercase block">Asistente
                                            Inteligente</span>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">¿No está seguro de qué
                                            dominios
                                            elegir? Analice la información de su proyecto.</p>
                                    </div>
                                    <button type="button" @click="recomendarDominio"
                                        class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors shrink-0 gap-1.5">
                                        <svg class="w-3.5 h-3.5 animate-pulse" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9.813 15.904L9 21l8.982-8.982M18 12a6 6 0 11-12 0 6 6 0 0112 0z" />
                                        </svg>
                                        Recomendar Dominio
                                    </button>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Seleccione uno o más Dominios Humanísticos aplicables: <span
                                            class="text-red-500">*</span>
                                    </label>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                                        <div v-for="dom in dominiosCatalogo" :key="'dom-' + dom.id_dom_huma"
                                            class="flex items-start p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all cursor-pointer"
                                            :class="editForm.dominios_humanisticos.includes(dom.id_dom_huma) ? 'border-emerald-500 bg-emerald-50/10 dark:border-emerald-900/30 ring-1 ring-emerald-500/30' : 'border-gray-200 dark:border-gray-800'"
                                            @click="toggleDominio(dom.id_dom_huma)">

                                            <div class="flex items-center h-5" @click.stop>
                                                <input type="checkbox" :id="'dom-check-' + dom.id_dom_huma"
                                                    :value="dom.id_dom_huma" v-model="editForm.dominios_humanisticos"
                                                    class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900">
                                            </div>

                                            <label :for="'dom-check-' + dom.id_dom_huma"
                                                class="ml-3 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer w-full"
                                                @click.stop="toggleDominio(dom.id_dom_huma)">
                                                <span class="block font-semibold"
                                                    :class="editForm.dominios_humanisticos.includes(dom.id_dom_huma) ? 'text-emerald-700 dark:text-emerald-400' : ''">
                                                    {{ dom.detalle_dom_huma }}
                                                </span>
                                                <span v-if="dominioRecomendadoId === dom.id_dom_huma"
                                                    class="inline-block mt-1 text-[9px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 font-bold px-1.5 py-0.5 rounded">
                                                    ⭐ Sugerido para este Proyecto
                                                </span>
                                            </label>
                                        </div>

                                        <div v-if="dominiosCatalogo.length === 0"
                                            class="col-span-2 text-xs text-gray-400 italic p-2 text-center">
                                            Cargando catálogo de dominios...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span
                                    class="p-1 rounded-md bg-blue-50 text-blue-600 mt-0.5 dark:bg-blue-950/40 dark:text-blue-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Convocatoria del
                                        Proyecto
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Seleccione el número de la convocatoria activa bajo la cual se rige el
                                        desarrollo del
                                        proyecto.
                                    </p>
                                </div>
                            </div>

                            <div class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-4">
                                <div class="space-y-2">
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Seleccione la Convocatoria: <span class="text-red-500">*</span>
                                    </label>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                                        <div v-for="conv in convocatoriasCatalogo" :key="'conv-' + conv.id_convocatoria"
                                            class="flex items-start p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all cursor-pointer"
                                            :class="editForm.id_convocatoria === conv.id_convocatoria ? 'border-green-500 bg-green-50/10 dark:border-green-900/30 ring-1 ring-green-500/30' : 'border-gray-200 dark:border-gray-800'"
                                            @click="editForm.id_convocatoria = conv.id_convocatoria">

                                            <div class="flex items-center h-5" @click.stop>
                                                <input type="radio" :id="'conv-radio-' + conv.id_convocatoria"
                                                    :value="conv.id_convocatoria" v-model="editForm.id_convocatoria"
                                                    class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900">
                                            </div>

                                            <label :for="'conv-radio-' + conv.id_convocatoria"
                                                class="ml-3 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer w-full"
                                                @click.stop="editForm.id_convocatoria = conv.id_convocatoria">
                                                <span class="block font-semibold"
                                                    :class="editForm.id_convocatoria === conv.id_convocatoria ? 'text-green-700 dark:text-green-400' : ''">
                                                    Nº {{ conv.num_convocatoria }}
                                                </span>
                                                <span class="block text-[11px] text-gray-500 dark:text-gray-400 font-normal mt-0.5 line-clamp-2">
                                                    {{ conv.titulo_convocatoria }}
                                                </span>
                                            </label>
                                        </div>

                                        <div v-if="convocatoriasCatalogo.length === 0"
                                            class="col-span-2 text-xs text-gray-400 italic p-2 text-center">
                                            No hay convocatorias activas disponibles en el sistema...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span class="p-1 rounded-md bg-indigo-50 text-indigo-600 mt-0.5 dark:bg-indigo-950/40 dark:text-indigo-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Investigación</h4>
                                    <p class="text-xs text-gray-500">Líneas y sublíneas vinculadas al proyecto.</p>
                                </div>
                            </div>

                            <div class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-4">
                                <div class="flex p-3 text-sm text-amber-800 rounded-lg bg-amber-50 dark:bg-gray-800 dark:text-amber-300" role="alert">
                                    <svg aria-hidden="true" class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <span class="font-medium">Selección Automática:</span> 
                                        Estos campos se asignan de manera automática basándose en la <strong>Facultad Prioritaria</strong> y 
                                        <strong>Carrera Prioritaria</strong>. 
                                        Modifique dichos parámetros arriba si desea cambiar las líneas.
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="p-3 border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 rounded-lg opacity-80">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                                            Líneas de Investigación (Facultad)
                                        </label>
                                        <ul v-if="lineasFiltradas.length > 0" class="space-y-1">
                                            <li v-for="lin in lineasFiltradas" :key="lin.id_lin_investiga" class="flex items-start text-xs text-gray-600 dark:text-gray-400 font-medium">
                                                <span class="text-indigo-500 mr-1.5">✓</span> {{ lin.nombre_lin }}
                                            </li>
                                        </ul>
                                        <div v-else class="text-xs text-gray-400 italic">No hay líneas disponibles para esta facultad...</div>
                                    </div>

                                    <div class="p-3 border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 rounded-lg opacity-80">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                                            Sublíneas de Investigación (Carrera)
                                        </label>
                                        <ul v-if="sublineasFiltradas.length > 0" class="space-y-1">
                                            <li v-for="sub in sublineasFiltradas" :key="sub.id_sublin_investiga" class="flex items-start text-xs text-gray-600 dark:text-gray-400 font-medium">
                                                <span class="text-indigo-500 mr-1.5">✓</span> {{ sub.nombre_sublin }}
                                            </li>
                                        </ul>
                                        <div v-else class="text-xs text-gray-400 italic">No hay sublíneas disponibles para esta carrera...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span class="p-1 rounded-md bg-emerald-50 text-emerald-600 mt-0.5 dark:bg-emerald-950/40 dark:text-emerald-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Clasificación UNESCO</h4>
                                    <p class="text-xs text-gray-500">Áreas, Subáreas y Áreas específicas de conocimiento vinculadas.</p>
                                </div>
                            </div>

                            <div class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    
                                    <div class="p-3 border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 rounded-lg">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                                            1. Áreas de Conocimiento
                                        </label>
                                        <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1 structure-scrollbar">
                                            <label v-for="area in unescoAreasPadre" :key="area.sau_id" class="flex items-start gap-2 p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-900 cursor-pointer text-xs">
                                                <input type="checkbox" 
                                                    :value="area.sau_id" 
                                                    v-model="editForm.unesco_areas"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                                <span class="text-gray-700 dark:text-gray-300 font-medium">({{ area.sau_id }}) {{ area.sau_descripcion }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="p-3 border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 rounded-lg">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                                            2. Subáreas de Conocimiento
                                        </label>
                                        <div v-if="unescoSubareasFiltradas.length > 0" class="max-h-48 overflow-y-auto space-y-1.5 pr-1 structure-scrollbar">
                                            <label v-for="subarea in unescoSubareasFiltradas" :key="subarea.sau_id" class="flex items-start gap-2 p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-900 cursor-pointer text-xs">
                                                <input type="checkbox" 
                                                    :value="subarea.sau_id" 
                                                    v-model="editForm.unesco_areas"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                                <span class="text-gray-600 dark:text-gray-400">({{ subarea.sau_id }}) {{ subarea.sau_descripcion }}</span>
                                            </label>
                                        </div>
                                        <div v-else class="text-xs text-gray-400 italic p-2">Seleccione un Área para desplegar sus subáreas...</div>
                                    </div>

                                    <div class="p-3 border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 rounded-lg">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                                            3. Áreas Específicas
                                        </label>
                                        <div v-if="unescoEspecificasFiltradas.length > 0" class="max-h-48 overflow-y-auto space-y-1.5 pr-1 structure-scrollbar">
                                            <label v-for="esp in unescoEspecificasFiltradas" :key="esp.sau_id" class="flex items-start gap-2 p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-900 cursor-pointer text-xs">
                                                <input type="checkbox" 
                                                    :value="esp.sau_id" 
                                                    v-model="editForm.unesco_areas"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                                <span class="text-gray-600 dark:text-gray-400">({{ esp.sau_id }}) {{ esp.sau_descripcion }}</span>
                                            </label>
                                        </div>
                                        <div v-else class="text-xs text-gray-400 italic p-2">Seleccione una Subárea para desplegar sus áreas específicas...</div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="space-y-3">
                                <div class="flex items-start gap-2.5">
                                    <span class="p-1 rounded-md bg-blue-50 text-blue-600 mt-0.5 dark:bg-blue-950/40 dark:text-blue-400">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tipo de proyecto de vinculación</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Seleccione la modalidad o categoría específica que regirá el desarrollo del proyecto. Esta clasificación es fundamental ya que determina los lineamientos normativos, el formato de los informes técnicos y los criterios institucionales de evaluación aplicables.
                                        </p>
                                    </div>
                                </div>

                                <div class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-4">
                                    <div class="space-y-2">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            Seleccione el tipo de proyecto de vinculación: <span class="text-red-500">*</span>
                                        </label>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                                            <div v-for="conv in tip_proyectCatalogo" :key="'conv-' + conv.id_tip_invi_proy"
                                                class="flex items-start p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all cursor-pointer"
                                                :class="editForm.id_tip_invi_proy === conv.id_tip_invi_proy ? 'border-green-500 bg-green-50/10 dark:border-green-900/30 ring-1 ring-green-500/30' : 'border-gray-200 dark:border-gray-800'"
                                                @click="editForm.id_tip_invi_proy = conv.id_tip_invi_proy">

                                                <div class="flex items-center h-5" @click.stop>
                                                    <input type="radio" :id="'conv-radio-' + conv.id_tip_invi_proy"
                                                        :value="conv.id_tip_invi_proy" v-model="editForm.id_tip_invi_proy"
                                                        class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900">
                                                </div>

                                                <label :for="'conv-radio-' + conv.id_tip_invi_proy"
                                                    class="ml-3 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer w-full"
                                                    @click.stop="editForm.id_tip_invi_proy = conv.id_tip_invi_proy">
                                                    <span class="block font-semibold"
                                                        :class="editForm.id_tip_invi_proy === conv.id_tip_invi_proy ? 'text-green-700 dark:text-green-400' : ''">
                                                        {{ conv.detalle_invi_proyect }}
                                                    </span>
                                                </label>
                                            </div>

                                            <div v-if="tip_proyectCatalogo.length === 0"
                                                class="col-span-2 text-xs text-gray-400 italic p-2 text-center">
                                                No hay tipos de proyecto de vinculación disponibles en el sistema...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'cobertura'" class="space-y-6 progress-fade">
                        <div class="space-y-3 mt-6">
                            <div class="flex items-start gap-2.5">
                                <span class="p-1 rounded-md bg-blue-50 text-blue-600 mt-0.5 dark:bg-blue-950/40 dark:text-blue-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A11.952 11.952 0 0 1 12 16.5c-2.998 0-5.74-1.1-7.843-2.918m0 0A8.959 8.959 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Cobertura y localización</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Especifique el alcance territorial y la delimitación geográfica del impacto del proyecto. Esto permite clasificar el radio de acción de las actividades y la vinculación de los beneficiarios mapeados.
                                    </p>
                                </div>
                            </div>

                            <div class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-4">
                                <div class="space-y-2">
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Seleccione la cobertura del proyecto: <span class="text-red-500">*</span>
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                        <div v-for="cob in ['Local', 'Regional', 'Nacional', 'Internacional']" :key="'cob-' + cob"
                                            class="flex items-center p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all cursor-pointer"
                                            :class="editForm.proyect_cobertura === cob ? 'border-green-500 bg-green-50/10 dark:border-green-900/30 ring-1 ring-green-500/30' : 'border-gray-200 dark:border-gray-800'"
                                            @click="editForm.proyect_cobertura = cob">

                                            <div class="flex items-center h-5" @click.stop>
                                                <input type="radio" :id="'cob-radio-' + cob"
                                                    :value="cob" v-model="editForm.proyect_cobertura"
                                                    class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900">
                                            </div>

                                            <label :for="'cob-radio-' + cob"
                                                class="ml-3 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer w-full"
                                                @click.stop="editForm.proyect_cobertura = cob">
                                                <span :class="editForm.proyect_cobertura === cob ? 'text-green-700 dark:text-green-400' : ''">
                                                    {{ cob }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-6 mt-4">
    
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Delimitación Territorial Específica</h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Seleccione la jerarquía geográfica. Puede marcar múltiples provincias, cantones y parroquias.</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">1. Zona de Planificación</label>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="zona in zonasCatalogo" :key="zona.id_zona_plan" 
                                        class="cursor-pointer transition-all border rounded-lg px-3 py-1.5 text-xs font-medium"
                                        :class="editForm.id_zona_plan === zona.id_zona_plan ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100 dark:bg-gray-900 dark:border-gray-800 dark:text-gray-300'">
                                        <input type="radio" :value="zona.id_zona_plan" v-model="editForm.id_zona_plan" @change="alCambiarZona" class="hidden">
                                        {{ zona.nombre_zona }}
                                    </label>
                                </div>
                            </div>

                            <div v-if="editForm.id_zona_plan" class="space-y-2 pt-3 border-t border-gray-100 dark:border-gray-800 animate-fade-in-up">
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">2. Provincias <span class="text-[10px] text-gray-400 font-normal ml-1">(Puede seleccionar varias)</span></label>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="prov in provinciasDisponibles" :key="prov.id_provincia" 
                                        class="cursor-pointer transition-all border rounded-lg px-3 py-1.5 text-xs font-medium flex items-center gap-1.5"
                                        :class="editForm.provincias.includes(prov.id_provincia) ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100 dark:bg-gray-900 dark:border-gray-800 dark:text-gray-300'">
                                        <input type="checkbox" :value="prov.id_provincia" v-model="editForm.provincias" @change="alCambiarProvincia" class="hidden">
                                        <svg v-if="editForm.provincias.includes(prov.id_provincia)" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        {{ prov.detalle }}
                                    </label>
                                </div>
                            </div>

                            <div v-if="editForm.provincias.length > 0" class="space-y-2 pt-3 border-t border-gray-100 dark:border-gray-800 animate-fade-in-up">
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">3. Cantones</label>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="can in cantonesDisponibles" :key="can.id_canton" 
                                        class="cursor-pointer transition-all border rounded-lg px-3 py-1.5 text-xs font-medium flex items-center gap-1.5"
                                        :class="editForm.cantones.includes(can.id_canton) ? 'bg-teal-600 text-white border-teal-600 shadow-md' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100 dark:bg-gray-900 dark:border-gray-800 dark:text-gray-300'">
                                        <input type="checkbox" :value="can.id_canton" v-model="editForm.cantones" @change="alCambiarCanton" class="hidden">
                                        <svg v-if="editForm.cantones.includes(can.id_canton)" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        {{ can.detalle }}
                                    </label>
                                </div>
                            </div>

                            <div v-if="editForm.cantones.length > 0" class="space-y-3 pt-3 border-t border-gray-100 dark:border-gray-800 animate-fade-in-up">
                                
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">4. Parroquias</label>
                                    
                                    <div class="relative w-full sm:w-64">
                                        <svg class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                        <input type="text" v-model="busquedaParroquia" placeholder="Buscar parroquia..." 
                                            class="w-full pl-9 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-800 dark:text-gray-200">
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 mb-2">
                                    <button v-for="prov in provinciasSeleccionadasObj" :key="'all-' + prov.id_provincia" type="button"
                                        @click="seleccionarTodasLasParroquiasDeProvincia(prov.id_provincia)"
                                        class="px-2.5 py-1 bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded text-[11px] font-semibold transition-colors">
                                        Marcar TODAS en {{ prov.detalle }}
                                    </button>
                                </div>

                                <div class="flex flex-wrap gap-2 max-h-60 overflow-y-auto p-1">
                                    <label v-for="par in parroquiasFiltradas" :key="par.idparroquia" 
                                        class="cursor-pointer transition-all border rounded-lg px-2.5 py-1 text-[11px] font-medium flex items-center gap-1"
                                        :class="editForm.parroquias.includes(par.idparroquia) ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 dark:bg-gray-950 dark:border-gray-800 dark:text-gray-400'">
                                        <input type="checkbox" :value="par.idparroquia" v-model="editForm.parroquias" class="hidden">
                                        <svg v-if="editForm.parroquias.includes(par.idparroquia)" width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        {{ par.parroquia }}
                                        <span class="opacity-60 text-[9px] ml-1">({{ par.tipoparroquia }})</span>
                                    </label>
                                    
                                    <div v-if="parroquiasFiltradas.length === 0" class="text-xs text-gray-400 italic">
                                        No se encontraron parroquias para la búsqueda o cantones actuales.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'objetivos'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Matriz de Marco Lógico</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Configure los niveles de objetivos del proyecto, sus indicadores, metas y supuestos.</p>
                        </div>

                        <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm overflow-hidden group">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Nivel Superior</span>
                                        <h4 class="text-base font-bold text-gray-800 dark:text-gray-100">Fin del Proyecto</h4>
                                    </div>
                                    
                                    <div v-if="obtenerObjetivo('fin')" class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                                        <p class="mb-3 font-medium">{{ obtenerObjetivo('fin').detalle_obj_proy }}</p>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[11px] px-2 py-1 rounded-md">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> 
                                            {{ obtenerObjetivo('fin').indicadores?.length || 0 }} Indicadores</span>
                                            <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[11px] px-2 py-1 rounded-md">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg> 
                                            {{ obtenerObjetivo('fin').supuestos?.length || 0 }} Supuestos</span>
                                            <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[11px] px-2 py-1 rounded-md">                                            
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg> 
                                            {{ obtenerObjetivo('fin').medios_verificacion?.length || 0 }} Medios de Verificación</span>
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-gray-400 italic mt-2">
                                        Aún no se ha definido el Fin del proyecto.
                                    </div>
                                </div>
                                
                                <button v-if="!obtenerObjetivo('fin')" @click="abrirModalMarcoLogico('fin')" class="shrink-0 inline-flex items-center gap-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg> Definir Fin
                                </button>
                                <button v-else @click="abrirModalMarcoLogico('fin', 0)" class="shrink-0 inline-flex items-center gap-2 bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.89 1.14l-2.81.93.93-2.81a4.5 4.5 0 011.14-1.89l8.93-8.94z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L22.125 9.375"/></svg> Editar
                                </button>
                            </div>
                        </div>

                        <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm overflow-hidden">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500"></div>
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Nivel Principal</span>
                                        <h4 class="text-base font-bold text-gray-800 dark:text-gray-100">Objetivo General</h4>
                                    </div>
                                    
                                    <div v-if="obtenerObjetivo('general')" class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                                        <p class="mb-3 font-medium">{{ obtenerObjetivo('general').detalle_obj_proy }}</p>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[11px] px-2 py-1 rounded-md">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> 
                                            {{ obtenerObjetivo('general').indicadores?.length || 0 }} Indicadores</span>
                                            <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[11px] px-2 py-1 rounded-md">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg> 
                                            {{ obtenerObjetivo('general').metas?.length || 0 }} Metas</span>
                                            <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[11px] px-2 py-1 rounded-md">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg> 
                                            {{ obtenerObjetivo('general').supuestos?.length || 0 }} Supuestos</span>
                                            <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[11px] px-2 py-1 rounded-md">                                            
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg> 
                                            {{ obtenerObjetivo('general').medios_verificacion?.length || 0 }} Medios de Verificación</span>
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-gray-400 italic mt-2">
                                        Aún no se ha definido el Objetivo General.
                                    </div>
                                </div>
                                
                                <button v-if="!obtenerObjetivo('general')" @click="abrirModalMarcoLogico('general')" class="shrink-0 inline-flex items-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:hover:bg-blue-500/20 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg> Añadir General
                                </button>
                                <button v-else @click="abrirModalMarcoLogico('general', 0)" class="shrink-0 inline-flex items-center gap-2 bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.89 1.14l-2.81.93.93-2.81a4.5 4.5 0 011.14-1.89l8.93-8.94z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L22.125 9.375"/></svg> Editar
                                </button>
                            </div>
                        </div>

                        <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm overflow-hidden">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-500"></div>
                            
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 pb-4 border-b border-gray-100 dark:border-gray-800">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Nivel Operativo</span>
                                        <h4 class="text-base font-bold text-gray-800 dark:text-gray-100">Objetivos Específicos</h4>
                                    </div>
                                    <p class="text-[11px] text-gray-500">Agregue hasta 4 objetivos específicos y sus productos verificables.</p>
                                </div>
                                
                                <button v-if="obtenerEspecificos().length < 4" @click="abrirModalMarcoLogico('especifico')" class="shrink-0 inline-flex items-center gap-2 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg> Añadir Específico ({{ obtenerEspecificos().length }}/4)
                                </button>
                            </div>

                            <div v-if="obtenerEspecificos().length === 0" class="text-center py-6 text-sm text-gray-400 italic">
                                No se han agregado objetivos específicos.
                            </div>
                            
                            <div v-else class="space-y-4">
                                <div v-for="(esp, index) in obtenerEspecificos()" :key="'esp-'+index" 
                                    class="group p-4 bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 rounded-xl transition-all hover:shadow-sm relative">
                                    
                                    <div class="flex justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-start gap-2">
                                                <span class="flex items-center justify-center w-5 h-5 mt-0.5 rounded-full bg-amber-200 dark:bg-amber-900 text-amber-800 dark:text-amber-300 text-xs font-bold">{{ index + 1 }}</span>
                                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ esp.detalle_obj_proy }}</p>
                                            </div>
                                            
                                            <div class="flex flex-wrap gap-2 mt-3 ml-7">
                                                <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-950 px-2 py-1 rounded border border-gray-200 dark:border-gray-800">
                                                    {{ esp.indicadores?.length || 0 }} Indicadores
                                                </span>
                                                <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-950 px-2 py-1 rounded border border-gray-200 dark:border-gray-800">
                                                    {{ esp.metas?.length || 0 }} Metas
                                                </span>
                                                <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-950 px-2 py-1 rounded border border-gray-200 dark:border-gray-800">
                                                    {{ esp.supuestos?.length || 0 }} Supuestos
                                                </span>
                                                <span class="text-[10px] font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 px-2 py-1 rounded border border-brand-200 dark:border-brand-800">
                                                    {{ esp.medios_verificacion?.length || 0 }} Medios Verificación
                                                </span>
                                                <span class="text-[10px] font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 px-2 py-1 rounded border border-brand-200 dark:border-brand-800">
                                                    {{ esp.prod_verificables?.length || 0 }} Prod. Verificables
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex flex-col sm:flex-row gap-2 shrink-0 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                            <button @click="abrirModalMarcoLogico('especifico', index, esp)" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-800 rounded transition-colors" title="Editar">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.89 1.14l-2.81.93.93-2.81a4.5 4.5 0 011.14-1.89l8.93-8.94z"/></svg>
                                            </button>
                                            <button @click="eliminarObjetivo(esp)" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-gray-800 rounded transition-colors" title="Eliminar">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-1.5 14.25a2.25 2.25 0 01-2.244 2.077H8.244a2.25 2.25 0 01-2.244-2.077L4.5 8.25m15 0a2.25 2.25 0 00-2.25-2.25h-13.5A2.25 2.25 0 002.25 8.25m15 0V4.5A2.25 2.25 0 0013.5 2.25h-3a2.25 2.25 0 00-2.25 2.25v3m6.75 0h-1.5"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="showModalMarcoLogico" id="modalMarcoLogico" class="fixed inset-0 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm z-[99999] p-4 sm:p-6 transition-all">
                            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-6xl max-h-full flex flex-col border border-gray-200 dark:border-gray-800 animate-zoom-in">
                                
                                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 rounded-t-2xl shrink-0">
                                    <div>
                                        <h2 class="text-lg font-bold text-gray-800 dark:text-white uppercase tracking-wide">
                                            Configurar: <span class="text-brand-600">{{ modalFormML.tipo_obj_proy }}</span>
                                        </h2>
                                        <p class="text-xs text-gray-500">Defina la descripción y los atributos correspondientes al nivel del marco lógico.</p>
                                    </div>
                                    <button @click="showModalMarcoLogico = false" class="p-2 text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800 rounded-full transition-colors">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6 bg-white dark:bg-gray-950">
                                    
                                    <div class="flex flex-col gap-5">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Detalle / Enunciado del <span class="capitalize">{{ modalFormML.tipo_obj_proy }}</span> <span class="text-red-500">*</span>
                                            </label>
                                            <textarea 
                                                v-model="modalFormML.detalle_obj_proy" 
                                                rows="1" 
                                                @input="ajustarAlturaTextarea"
                                                class="w-full p-3 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden transition-shadow" 
                                                placeholder="Ej: Contribuir a la mejora de la calidad de vida de las comunidades vulnerables..."
                                            ></textarea>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                        
                                        <div class="border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 overflow-hidden flex flex-col h-64">
                                            <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-900 shrink-0">
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Indicadores</span>
                                                <button @click="agregarAtributoML('indicadores')" class="text-[10px] font-bold bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded shadow-sm transition-colors">+ Añadir</button>
                                            </div>
                                            <div class="p-3 overflow-y-auto custom-scrollbar flex-1 space-y-2">
                                                <div v-for="(item, i) in modalFormML.indicadores" :key="'ind'+i" class="flex gap-2 items-start animate-fade-in-up">
                                                    <span class="text-xs font-bold text-gray-400 mt-2">{{ i + 1 }}.</span>
                                                    <textarea 
                                                        v-model="item.detalle_indicador" 
                                                        rows="1" 
                                                        @input="ajustarAlturaTextarea"
                                                        class="w-full p-2 text-xs border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-hidden" 
                                                        placeholder="Los indicadores a nivel de Fin miden el impacto general que tendrá el... "
                                                    />
                                                    <button @click="eliminarAtributoML('indicadores', i)" class="shrink-0 p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-1.5 14.25H6l-1.5-14.25M15 4.5h3M6 4.5H3m6-2.25h6m-6 0v2.25m6-2.25v2.25"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div v-if="modalFormML.indicadores.length === 0" class="text-center text-xs text-gray-400 italic py-4">Sin indicadores registrados.</div>
                                            </div>
                                        </div>

                                        <div v-if="modalFormML.tipo_obj_proy !== 'fin'" class="border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 overflow-hidden flex flex-col h-64">
                                            <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-900 shrink-0">
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Metas</span>
                                                <button @click="agregarAtributoML('metas')" class="text-[10px] font-bold bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded shadow-sm transition-colors">+ Añadir</button>
                                            </div>
                                            <div class="p-3 overflow-y-auto custom-scrollbar flex-1 space-y-2">
                                                <div v-for="(item, i) in modalFormML.metas" :key="'met'+i" class="flex gap-2 items-start animate-fade-in-up">
                                                    <span class="text-xs font-bold text-gray-400 mt-2">{{ i + 1 }}.</span>
                                                    <textarea 
                                                        v-model="item.detalle_metas" 
                                                        rows="1" 
                                                        @input="ajustarAlturaTextarea"
                                                        class="w-full p-2 text-xs border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-hidden" 
                                                        placeholder="Escriba la meta..."
                                                    />
                                                    <button @click="eliminarAtributoML('metas', i)" class="shrink-0 p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-1.5 14.25H6l-1.5-14.25M15 4.5h3M6 4.5H3m6-2.25h6m-6 0v2.25m6-2.25v2.25"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div v-if="modalFormML.metas.length === 0" class="text-center text-xs text-gray-400 italic py-4">Sin metas registradas.</div>
                                            </div>
                                        </div>

                                        <div class="border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 overflow-hidden flex flex-col h-64">
                                            <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-900 shrink-0">
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Supuestos</span>
                                                <button @click="agregarAtributoML('supuestos')" class="text-[10px] font-bold bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded shadow-sm transition-colors">+ Añadir</button>
                                            </div>
                                            <div class="p-3 overflow-y-auto custom-scrollbar flex-1 space-y-2">
                                                <div v-for="(item, i) in modalFormML.supuestos" :key="'sup'+i" class="flex gap-2 items-start animate-fade-in-up">
                                                    <span class="text-xs font-bold text-gray-400 mt-2">{{ i + 1 }}.</span>
                                                    <textarea 
                                                        v-model="item.detalle_supuestos" 
                                                        rows="1" 
                                                        @input="ajustarAlturaTextarea"
                                                        class="w-full p-2 text-xs border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-hidden" 
                                                        placeholder="Los supuestos indican los eventos, las condiciones o las decisiones..."
                                                    />
                                                    <button @click="eliminarAtributoML('supuestos', i)" class="shrink-0 p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-1.5 14.25H6l-1.5-14.25M15 4.5h3M6 4.5H3m6-2.25h6m-6 0v2.25m6-2.25v2.25"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div v-if="modalFormML.supuestos.length === 0" class="text-center text-xs text-gray-400 italic py-4">Sin supuestos registrados.</div>
                                            </div>
                                        </div>

                                        <div class="border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 overflow-hidden flex flex-col h-64">
                                            <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-900 shrink-0">
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Medios Verificación</span>
                                                <button @click="agregarAtributoML('medios_verificacion')" class="text-[10px] font-bold bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded shadow-sm transition-colors">+ Añadir</button>
                                            </div>
                                            <div class="p-3 overflow-y-auto custom-scrollbar flex-1 space-y-2">
                                                <div v-for="(item, i) in modalFormML.medios_verificacion" :key="'med'+i" class="flex gap-2 items-start animate-fade-in-up">
                                                    <span class="text-xs font-bold text-gray-400 mt-2">{{ i + 1 }}.</span>
                                                    <textarea 
                                                        v-model="item.detalle_medio_verifica" 
                                                        rows="1" 
                                                        @input="ajustarAlturaTextarea"
                                                        class="w-full p-2 text-xs border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-hidden" 
                                                        placeholder="Los medios de verificación son las fuentes de información..."
                                                    />
                                                    <button @click="eliminarAtributoML('medios_verificacion', i)" class="shrink-0 p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-1.5 14.25H6l-1.5-14.25M15 4.5h3M6 4.5H3m6-2.25h6m-6 0v2.25m6-2.25v2.25"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div v-if="modalFormML.medios_verificacion.length === 0" class="text-center text-xs text-gray-400 italic py-4">Sin medios registrados.</div>
                                            </div>
                                        </div>

                                        <div v-if="modalFormML.tipo_obj_proy === 'especifico'" class="lg:col-span-2 border border-brand-200 dark:border-brand-900/30 rounded-xl bg-brand-50/30 dark:bg-brand-900/10 overflow-hidden flex flex-col max-h-64">
                                            <div class="flex items-center justify-between px-3 py-2 border-b border-brand-100 dark:border-brand-900/30 bg-brand-50 dark:bg-brand-900/20 shrink-0">
                                                <span class="text-xs font-bold text-brand-700 dark:text-brand-400 uppercase tracking-wider">Productos Verificables <span class="text-red-500">*</span></span>
                                                <button @click="agregarAtributoML('prod_verificables')" class="text-[10px] font-bold bg-brand-600 hover:bg-brand-700 text-white px-2 py-1 rounded shadow-sm transition-colors">+ Añadir</button>
                                            </div>
                                            <div class="p-3 overflow-y-auto custom-scrollbar flex-1 space-y-2">
                                                <div v-for="(item, i) in modalFormML.prod_verificables" :key="'prod'+i" class="flex gap-2 items-start animate-fade-in-up">
                                                    <span class="text-xs font-bold text-brand-400 mt-2">{{ i + 1 }}.</span>
                                                    <textarea 
                                                        v-model="item.detalle_prod_verif" 
                                                        rows="1" 
                                                        @input="ajustarAlturaTextarea"
                                                        class="w-full p-2 text-xs border border-brand-200 dark:border-brand-800 rounded-lg dark:bg-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 resize-none overflow-hidden" 
                                                        placeholder="Escriba el producto esperado..."
                                                    />
                                                    <button @click="eliminarAtributoML('prod_verificables', i)" class="shrink-0 p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-1.5 14.25H6l-1.5-14.25M15 4.5h3M6 4.5H3m6-2.25h6m-6 0v2.25m6-2.25v2.25"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div v-if="modalFormML.prod_verificables.length === 0" class="text-center text-xs text-brand-500 italic py-4">Los objetivos específicos requieren obligatoriamente de productos verificables.</div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 rounded-b-2xl shrink-0 flex justify-end gap-3">
                                    <button @click="showModalMarcoLogico = false" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors">Cancelar</button>
                                    <button @click="guardarMarcoLogico" class="px-5 py-2.5 text-sm font-semibold text-white bg-brand-600 rounded-xl shadow-md shadow-brand-500/20 hover:bg-brand-700 transition-colors">
                                        Guardar Configuración
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>
                    <div v-else-if="activeTab === 'antecedentes_justifi'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Antecedentes y Justificacionnes</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Detalle los antecedentes y justificaciones de su proyecto
                                </p>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <!-- Tarjeta de Antecedentes -->
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Antecedentes <span class="text-red-500">*</span>
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-justify">
                                    Hacer referencia a las investigaciones realizadas sobre el tema, incluyendo los resultados de proyectos de vinculación o investigación anteriores realizados en la UTLVTE.
                                </p>
                                <textarea 
                                    ref="antecedentesTextarea"
                                    v-model="editForm.proyect_antecedentes" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_antecedentes')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Deben incluirse todos los aspectos y circunstancias que motivaron la identificación y preparación del proyecto..."
                                    rows="4"
                                ></textarea>
                                <!-- Contador de palabras -->
                                <div class="flex justify-between items-center mt-2 text-sm font-semibold transition-colors" :class="colorContadorAntecedentes">
                                    <span>Mínimo: 500 / Máximo: 800 palabras</span>
                                    <span>{{ palabrasAntecedentes }} palabras</span>
                                </div>
                            </div>

                            <!-- Tarjeta de Justificación -->
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Justificación <span class="text-red-500">*</span>
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-justify">
                                    Indicar por qué es necesario este proyecto argumentando desde la legislación vigente, con datos objetivos y/o peticiones realizadas por las instituciones (copaternales), las cuales financian el proyecto.
                                </p>
                                <textarea 
                                    ref="justificacionTextarea"
                                    v-model="editForm.proyect_justificacion" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_justificacion')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Argumento que apoya o sustenta la idea. En otras palabras, es una forma de explicar algo que sirve como complemento o aclaración de una afirmación previa."
                                    rows="4"
                                ></textarea>
                                <!-- Contador de palabras -->
                                <div class="flex justify-between items-center mt-2 text-sm font-semibold transition-colors" :class="colorContadorJustificacion">
                                    <span>Mínimo: 600 / Máximo: 800 palabras</span>
                                    <span>{{ palabrasJustificacion }} palabras</span>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'instituciones'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Instituciones Involucradas Que Brindan Financiamiento Interno Y Externo</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Busque las instituciones que apoyen o financian el proyecto, así como las que reciben financiamiento.</p>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-2">
                                    Buscar Institución / Empresa (Por RUC o Nombre)
                                </label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        v-model="searchEmpresaQuery" 
                                        @input="ejecutarBusquedaEmpresa"
                                        placeholder="Ej: 0991234567001 o Nombre de la Empresa..." 
                                        class="w-full p-3 pl-10 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                    >
                                    <div class="absolute left-3 top-3.5 text-gray-400">
                                        <svg v-if="!buscandoEmpresa" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                        <svg v-else class="animate-spin h-5 w-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </div>
                                </div>

                                <div v-if="empresaNoEncontrada" class="mt-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200 rounded">
                                    <p class="font-semibold">La empresa ingresada no existe.</p>
                                    <p class="text-sm mt-1">Comunícate con alguien de la dirección de vinculación para que añadan los datos de la empresa a la base de datos. O si estás con el rol de analista de vinculación puedes dirigirte a <strong>Mantenimiento > Empresas Registradas</strong> y registrarla.</p>
                                </div>
                                
                                <div v-if="empresaBuscada && !empresaNoEncontrada" class="mt-4 border border-brand-200 dark:border-brand-700 rounded-lg p-4 bg-brand-50 dark:bg-brand-900/20">
                                    <h4 class="font-bold text-lg text-brand-700 dark:text-brand-300 mb-4">{{ empresaBuscada.empresacorta }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700 dark:text-gray-300">
                                        <p><strong>Representante:</strong> {{ empresaBuscada.representante || 'N/A' }}</p>
                                        <p><strong>C.I. Representante:</strong> {{ empresaBuscada.ci_representante || 'N/A' }}</p>
                                        <p><strong>Teléfonos:</strong> {{ empresaBuscada.telefono || 'N/A' }}</p>
                                        <p><strong>Correo Electrónico:</strong> {{ empresaBuscada.email || 'N/A' }}</p>
                                        <p><strong>Dirección:</strong> {{ empresaBuscada.direccion || 'N/A' }}</p>
                                        <p><strong>Página Web:</strong> <a v-if="empresaBuscada.url" :href="empresaBuscada.url" target="_blank" class="text-blue-500 hover:underline">{{ empresaBuscada.url }}</a><span v-else>N/A</span></p>
                                        <p class="md:col-span-2"><strong>Órgano Ejecutor:</strong> Dirección de Vinculación UTLVTE</p>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <button @click="agregarEmpresaSeleccionada" type="button" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg shadow transition-colors">
                                            Añadir Institución
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="empresasAgregadas.length > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                                    <h4 class="font-bold text-gray-800 dark:text-gray-200">Instituciones Añadidas al Proyecto</h4>
                                </div>
                                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <li v-for="(empresa, index) in empresasAgregadas" :key="empresa.idempresa" class="p-4 flex flex-col md:flex-row justify-between md:items-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <div class="mb-2 md:mb-0">
                                            <p class="font-bold text-gray-800 dark:text-white">{{ empresa.empresacorta }} <span class="text-xs font-normal text-gray-500 ml-2">RUC: {{ empresa.ruc }}</span></p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Rep: {{ empresa.representante }} | Órgano Ejecutor: Dirección de Vinculación UTLVTE</p>
                                        </div>
                                        <button @click="quitarEmpresa(index)" type="button" class="px-3 py-1 bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 rounded-lg text-sm font-semibold transition-colors">
                                            Quitar
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'presupuesto_inst'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Presupuesto Institucional</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Defina detalladamente las actividades y los montos de financiamiento del proyecto divididos por la Universidad (UTLVT) y las Entidades Cooperantes autorizadas.
                            </p>
                        </div>

                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-850 border-b border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-bold tracking-wider text-blue-600 dark:text-blue-400 uppercase">
                                    APORTES UNIVERSIDAD TÉCNICA “LUIS VARGAS TORRES” DE ESMERALDAS
                                </h4>
                            </div>
                            <div class="p-4">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 uppercase">
                                            <th class="py-3 px-4 w-2/3">Actividad</th>
                                            <th class="py-3 px-4 w-1/4 text-right">Valor ($)</th>
                                            <th class="py-3 px-4 w-12 text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(aporte, index) in editForm.aportes_utlvt" :key="'utlvt-' + index" class="border-b border-gray-100 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                            <td class="py-2 px-4">
                                                <input 
                                                    v-model="aporte.actividad" 
                                                    type="text" 
                                                    placeholder="Escriba la actividad o rubro..." 
                                                    class="w-full bg-transparent border-0 focus:ring-0 text-sm text-gray-800 dark:text-gray-200"
                                                />
                                            </td>
                                            <td class="py-2 px-4 text-right">
                                                <input 
                                                    v-model="aporte.valor" 
                                                    @input="validarMonto(aporte, 'valor')"
                                                    type="text" 
                                                    placeholder="0,00" 
                                                    class="w-full bg-transparent border-0 focus:ring-0 text-sm text-right text-gray-800 dark:text-gray-200 font-medium"
                                                />
                                            </td>
                                            <td class="py-2 px-4 text-center">
                                                <button 
                                                    @click="eliminarAporteUtlvt(index)" 
                                                    type="button" 
                                                    class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="editForm.aportes_utlvt.length === 0">
                                            <td colspan="3" class="py-6 text-center text-sm text-gray-400 italic">No hay actividades registradas. Haga clic en Agregar Actividad.</td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <div class="mt-4 flex justify-start">
                                    <button 
                                        @click="agregarAporteUtlvt" 
                                        type="button" 
                                        class="inline-flex items-center px-4 py-2 border border-blue-500 text-sm font-semibold rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Agregar Actividad UTLVT
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-for="empresa in cooperadoresFiltrados" :key="empresa.idempresa" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-850 border-b border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-bold tracking-wider text-emerald-600 dark:text-emerald-400 uppercase">
                                    APORTES ENTIDAD COOPERANTE: {{ empresa.empresacorta }}
                                </h4>
                            </div>
                            <div class="p-4">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 uppercase">
                                            <th class="py-3 px-4 w-2/3">Actividad</th>
                                            <th class="py-3 px-4 w-1/4 text-right">Valor ($)</th>
                                            <th class="py-3 px-4 w-12 text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="(aporte, index) in editForm.aportes_inst" :key="'inst-' + index">
        
                                            <tr 
                                                v-if="aporte.idempresa === empresa.idempresa"
                                                class="border-b border-gray-100 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30"
                                            >
                                                <td class="py-2 px-4">
                                                    <textarea 
                                                        v-model="aporte.actividad" 
                                                        @input="ajustarAlturaTextarea"
                                                        rows="1"
                                                        placeholder="Escriba la actividad o rubro..." 
                                                        class="w-full bg-transparent border-0 focus:ring-0 text-sm text-gray-800 dark:text-gray-200 resize-none overflow-hidden"
                                                        style="min-height: 38px;"
                                                    ></textarea>
                                                </td>
                                                <td class="py-2 px-4 text-right align-top">
                                                    <input 
                                                        v-model="aporte.valor" 
                                                        @input="validarMonto(aporte, 'valor')"
                                                        type="text" 
                                                        placeholder="0,00" 
                                                        class="w-full bg-transparent border-0 focus:ring-0 text-sm text-right text-gray-800 dark:text-gray-200 font-medium"
                                                    />
                                                </td>
                                                <td class="py-2 px-4 text-center align-top">
                                                    <button 
                                                        @click="eliminarAporteInst(index)" 
                                                        type="button" 
                                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors mt-1"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>

                                        <tr v-if="obtenerCantidadAportesEmpresa(empresa.idempresa) === 0">
                                            <td colspan="3" class="py-6 text-center text-sm text-gray-400 italic">No hay actividades registradas para esta institución.</td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <div class="mt-4 flex justify-start">
                                    <button 
                                        @click="agregarAporteInst(empresa.idempresa)" 
                                        type="button" 
                                        class="inline-flex items-center px-4 py-2 border border-emerald-500 text-sm font-semibold rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-colors"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Agregar Actividad Cooperante
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="cooperadoresFiltrados.length === 0" class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-gray-500 text-sm">
                            No existen Entidades Cooperantes seleccionadas en este proyecto (RUC 0860037590001 excluido).
                        </div>

                        <div class="bg-gray-900 dark:bg-gray-800 text-white rounded-xl p-6 shadow-md flex flex-col md:flex-row justify-between items-center">
                            <div class="mb-4 md:mb-0">
                                <span class="text-xs font-semibold tracking-wider text-gray-400 uppercase block">Resumen del Financiamiento</span>
                                <span class="text-lg font-bold">Consolidación General de Presupuesto</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold tracking-wider text-gray-400 uppercase block">TOTAL DEL PROYECTO ($)</span>
                                <span class="text-3xl font-extrabold text-blue-400">{{ formatearMonedaVisual(totalProyecto) }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'plazo_ejecucion'" class="space-y-6 animate-fade-in-up">
    
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex gap-4">
                            <svg class="text-green-500 w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Plazo de Ejecución del Proyecto</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    La duración debe estar obligatoriamente en un rango de <strong>3 a 5 años (36 a 60 meses)</strong>. La fecha de inicio no puede ser anterior a la presentación.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <!-- Fecha de Presentación -->
                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Presentación <span class="text-red-500">*</span></label>
                                <input type="date" 
                                    v-model="editForm.proyect_fecha_pres" 
                                    @change="validarFechas"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none transition">
                            </div>

                            <!-- Estado del Proyecto (Diseño Intuitivo tipo Tarjetas) -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado del Proyecto <span class="text-red-500">*</span></label>
                                
                                <div class="grid grid-cols-3 gap-3">
                                    <!-- Opcion 1: Nuevo -->
                                    <button type="button" 
                                        @click="editForm.proyect_estado = 'Nuevo'"
                                        :class="editForm.proyect_estado === 'Nuevo' 
                                            ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 ring-1 ring-brand-500 shadow-sm' 
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                        class="flex items-center justify-center py-2 px-3 border rounded-xl transition-all duration-200 outline-none">
                                        <div class="flex items-center gap-2">
                                            <svg v-if="editForm.proyect_estado === 'Nuevo'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="font-semibold text-sm">Nuevo</span>
                                        </div>
                                    </button>

                                    <!-- Opcion 2: En ejecución -->
                                    <button type="button" 
                                        @click="editForm.proyect_estado = 'En ejecución'"
                                        :class="editForm.proyect_estado === 'En ejecución' 
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-1 ring-blue-500 shadow-sm' 
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                        class="flex items-center justify-center py-2 px-3 border rounded-xl transition-all duration-200 outline-none">
                                        <div class="flex items-center gap-2">
                                            <svg v-if="editForm.proyect_estado === 'En ejecución'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="font-semibold text-sm">En ejecución</span>
                                        </div>
                                    </button>

                                    <!-- Opcion 3: Continuación -->
                                    <button type="button" 
                                        @click="editForm.proyect_estado = 'Continuación'"
                                        :class="editForm.proyect_estado === 'Continuación' 
                                            ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 ring-1 ring-purple-500 shadow-sm' 
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                        class="flex items-center justify-center py-2 px-3 border rounded-xl transition-all duration-200 outline-none">
                                        <div class="flex items-center gap-2">
                                            <svg v-if="editForm.proyect_estado === 'Continuación'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="font-semibold text-sm text-center leading-tight">Continuación</span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Fecha de Inicio (Mínimo: Fecha de Presentación) -->
                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Inicio <span class="text-red-500">*</span></label>
                                <input type="date" 
                                    v-model="editForm.fechainicio" 
                                    :min="editForm.proyect_fecha_pres"
                                    @change="validarFechas"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none transition">
                            </div>

                            <!-- Fecha de Fin (Restringida por Vue de 3 a 5 años) -->
                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Finalización <span class="text-red-500">*</span></label>
                                <input type="date" 
                                    v-model="editForm.fechafin" 
                                    :min="limitesFechaFin.min"
                                    :max="limitesFechaFin.max"
                                    :disabled="!editForm.fechainicio"
                                    @change="validarFechas"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <span v-if="!editForm.fechainicio" class="text-xs text-orange-500">Seleccione primero la fecha de inicio</span>
                            </div>

                            <!-- Duración en Meses (Automático y de solo lectura) -->
                            <div class="space-y-1 md:col-span-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Duración del Proyecto (Meses)</label>
                                <div class="relative">
                                    <input type="number" 
                                        v-model="editForm.proyect_duracion_mes" 
                                        readonly
                                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white font-bold text-lg focus:outline-none shadow-inner cursor-not-allowed text-center">
                                    
                                    <!-- Indicador visual de si cumple la regla -->
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2">
                                        <span v-if="editForm.proyect_duracion_mes >= 36 && editForm.proyect_duracion_mes <= 60" class="flex items-center gap-1 text-sm text-green-600 bg-green-100 px-2 py-1 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Plazo Correcto
                                        </span>
                                        <span v-else-if="editForm.proyect_duracion_mes > 0" class="flex items-center gap-1 text-sm text-red-600 bg-red-100 px-2 py-1 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Debe ser 3 a 5 años
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'personalproyect'" class="space-y-6 animate-fade-in-up">
                       <div class="mb-4 p-5 bg-green-50 dark:bg-gray-800/80 rounded-xl border-l-4 border-green-500 flex flex-col sm:flex-row gap-5 items-start sm:items-center shadow-sm">
        
                            <!-- Ícono actualizado a "Usuarios/Equipo" para mayor coherencia visual -->
                            <div class="p-3 bg-green-100 dark:bg-green-900/40 rounded-full flex-shrink-0">
                                <svg class="text-green-600 dark:text-green-400 w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            
                            <!-- Texto informativo y Botón de acción -->
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Gestión del Personal Responsable</h3>
                                
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 leading-relaxed text-justify">
                                    En esta sección podrá administrar y supervisar a todo el equipo de trabajo involucrado en la ejecución del proyecto. Es fundamental mantener actualizado el registro de los responsables, asignando roles estratégicos como el director, subdirector y demás participantes, para garantizar el correcto desarrollo, seguimiento y cumplimiento de los objetivos establecidos.
                                </p>
                                
                                <button type="button" 
                                    @click="abrirDetallesProyecto(editForm.proyect_id)"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white text-sm font-semibold rounded-lg transition-all duration-200 shadow-sm hover:shadow focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 focus:outline-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Registrar o monitorear integrantes
                                </button>
                            </div>

                        </div>
                    </div>
                    <div v-else-if="activeTab === 'diagnostico_prob'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex gap-4">
                           
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Diagnóstico y Problemas</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Describa la si situación actual para contribuir a la satisfacción de necesidades y la solución de problemáticas del entorno desde el ámbito académico e investigativo.
                                </p>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <!-- Tarjeta de Descripción de Situación Actual -->
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Descripción de la situación actual <span class="text-red-500">*</span>
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-justify">
                                    Descripción de la situación actual para contribuir a la satisfacción de necesidades y la solución de problemáticas del entorno desde el ámbito académico e investigativo
                                </p>
                                <textarea 
                                    ref="descripcionTextarea"
                                    v-model="editForm.proyect_desc_situ_act" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_desc_situ_act')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Descripción de la realidad existente de una zona determinada, problemas o necesidades de su población; que inciden en el lento o nulo desarrollo económico, social, cultural y ambiental; falta de integración física, comercial y social en pro del bienestar de sus habitantes."
                                    rows="4"
                                ></textarea>
                                <!-- Contador de palabras -->
                                <div class="flex justify-between items-center mt-2 text-sm font-semibold transition-colors" :class="colorContadorDescripcion">
                                    <span>Mínimo: 550 / Máximo: 650 palabras</span>
                                    <span>{{ palabrasDescrip }} palabras</span>
                                </div>
                            </div>

                            <!-- Tarjeta de Identificación, descripción y diagnóstico del problema -->
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Identificación, descripción y diagnóstico del problema <span class="text-red-500">*</span>
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-justify">
                                    Identificar el problema que impulsa el proyecto, con datos objetivos y/o peticiones realizadas por las instituciones (copaternales), las cuales financian el proyecto.
                                </p>
                                <textarea 
                                    ref="diagnostico_problemaTextarea"
                                    v-model="editForm.proyect_diag_probl" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_diag_probl')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="En los proyectos sociales el problema social se define como una carencia o déficits existentes que afecta a un segmento de la población de una determinado zona o lugar. En el proceso de identificación, descripción y diagnóstico del problema es fundamental realizar el análisis de participación de los/as involucrados/as con la finalidad que la comunidad beneficiaria en el futuro se involucre y comprometa en la implementación del proyecto"
                                    rows="4"
                                ></textarea>
                                <!-- Contador de palabras -->
                                <div class="flex justify-between items-center mt-2 text-sm font-semibold transition-colors" :class="colorContadorDiagnostico">
                                    <span>Mínimo: 550 / Máximo: 650 palabras</span>
                                    <span>{{ palabrasDiagnostico }} palabras</span>
                                </div>
                            </div>
                            <!-- Tarjeta de Articulación del proyecto de vinculación con el programa de la carrera -->
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Articulación del proyecto de vinculación con el programa de la carrera <span class="text-red-500">*</span>
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-justify">
                                    Contribución a la sociedad
                                </p>
                                <textarea 
                                    ref="articulaTextarea"
                                    v-model="editForm.proyect_contribucion_soci" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_contribucion_soci')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Detalle como contribuye a la sociedad su proyecto de vinculación"
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="p-4 bg-green-50 dark:bg-gray-800 rounded-xl border-l-4 border-green-500 flex gap-4">
                                <svg class="text-green-500 w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Articulación con Asignaturas</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Seleccione las asignaturas de la carrera prioritaria que se articulan con este proyecto. Puede utilizar el buscador para encontrarlas rápidamente.
                                    </p>
                                </div>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input type="text" 
                                    v-model="searchAsignatura" 
                                    placeholder="Buscar asignatura por nombre o código..." 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 outline-none transition shadow-sm">
                            </div>

                            <!-- Lista de Asignaturas -->
                            <div v-if="asignaturasDisponibles.length > 0" class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700 h-96 overflow-y-auto">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <label v-for="asig in filteredAsignaturas" :key="asig.Codigo_Asignatura" 
                                        :class="{'border-green-500 bg-green-50 dark:bg-green-900/30 ring-1 ring-green-500': editForm.asignaturas.includes(asig.Codigo_Asignatura)}"
                                        class="flex items-start p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                                        
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" 
                                                :value="asig.Codigo_Asignatura" 
                                                v-model="editForm.asignaturas"
                                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        </div>
                                        <div class="ml-3 text-sm flex-1">
                                            <span class="font-bold text-gray-900 dark:text-white block">{{ asig.Nombre_Asignatura }}</span>
                                            <div class="flex justify-between items-center mt-1">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">Cód: {{ asig.Codigo_Asignatura }}</span>
                                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                                    Nivel {{ asig.Nivel_Asignatura }}
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div v-if="filteredAsignaturas.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    No se encontraron asignaturas que coincidan con "{{ searchAsignatura }}"
                                </div>
                            </div>

                            <div v-else class="text-center py-8 px-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin asignaturas disponibles</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Verifique que haya seleccionado una carrera prioritaria en la pestaña de Datos Generales y guardado los cambios.</p>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Identificación y caracterización de la población objetiva beneficiarios (as)  <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="identPoblaobjTextarea"
                                    v-model="editForm.proyec_ident_poblaobj" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyec_ident_poblaobj')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Para determinar la población objetiva o beneficiaria, el/la director(a) debe realizar la siguiente división: 
                                    Población de Referencia: Población total del área de intervención del proyecto.
                                    Población Potencial: Parte de la población de referencia que necesita el bien o servicios, pero no necesariamente lo requerirá del proyecto.
                                    Población Objetiva/beneficiaria directa: Población que necesita y requiere el bien o servicio ofrecido por el proyecto."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-6 transition-all hover:shadow-md">
                                <div class="mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        Desglose Cuantitativo de Beneficiarios Directos
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ingrese la cantidad de personas beneficiadas. El total se calculará automáticamente. Obtención de datos de página oficial del INEC censo 2022</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                                    
                                    <!-- Hombres -->
                                    <div class="space-y-1">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            Número Directos Hombres
                                        </label>
                                        <input type="number" 
                                            v-model.number="editForm.proyect_num_direct_hombres" 
                                            @input="calcularTotalBeneficiarios"
                                            min="0"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition"
                                            placeholder="Ej. 150">
                                    </div>

                                    <!-- Mujeres -->
                                    <div class="space-y-1">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            Número Directos Mujeres
                                        </label>
                                        <input type="number" 
                                            v-model.number="editForm.proyect_num_direct_mujeres" 
                                            @input="calcularTotalBeneficiarios"
                                            min="0"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-pink-500 outline-none transition"
                                            placeholder="Ej. 180">
                                    </div>

                                    

                                    <!-- Total (Solo Lectura) -->
                                    <div class="space-y-1">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            Total, Número Directos:
                                        </label>
                                        <input type="number" 
                                            v-model="editForm.proyect_total_num_direct" 
                                            readonly
                                            class="w-full px-4 py-2.5 border border-indigo-200 dark:border-indigo-900/50 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 font-bold text-lg focus:outline-none shadow-inner cursor-not-allowed text-center"
                                            placeholder="0">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2" title="Personas con Diversidad Funcional">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                            Total, Número Indirectos
                                        </label>
                                        <input type="number" 
                                            v-model.number="editForm.proyect_total_num_indirect" 
                                            min="0"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 outline-none transition"
                                            placeholder="Ej. 15">
                                    </div>

                                    <!-- Personas con Diversidad Funcional -->
                                    <div class="space-y-1">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2" title="Personas con Diversidad Funcional">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                            Personas con diversidad funcional (capacidades especiales)
                                        </label>
                                        <input type="number" 
                                            v-model.number="editForm.proyect_num_personas_div_fun" 
                                            min="0"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 outline-none transition"
                                            placeholder="Ej. 15">
                                    </div>
                                    

                                </div>
                            </div>
                            <div
                                class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <div class="mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                                    <h3
                                        class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z">
                                            </path>
                                        </svg>
                                        Participación de Docentes en el Proyecto
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Conteo de docentes
                                        (Directores, Subdirectores y Docentes Participantes) registrados.</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                    <!-- Hombres Docentes -->
                                    <div class="space-y-1">
                                        <label
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            Docentes Hombres
                                        </label>
                                        <input type="number" v-model.number="editForm.proyect_num_doce_h" disabled
                                            @input="calcularTotalDocentes" min="0"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                                            placeholder="0">
                                    </div>

                                    <!-- Mujeres Docentes -->
                                    <div class="space-y-1">
                                        <label
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            Docentes Mujeres
                                        </label>
                                        <input type="number" v-model.number="editForm.proyect_num_doce_m" disabled
                                            @input="calcularTotalDocentes" min="0"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                                            placeholder="0">
                                    </div>

                                    <!-- Total Docentes -->
                                    <div class="space-y-1">
                                        <label
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                            Total Docentes Participantes
                                        </label>
                                        <input type="number" v-model="editForm.proyect_num_doce_part" readonly
                                            class="w-full px-4 py-2.5 border border-indigo-200 dark:border-indigo-900/50 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 font-bold text-lg text-center cursor-not-allowed"
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>

                            <!-- BLOQUE: PARTICIPACIÓN DE ESTUDIANTES -->
                            <div
                                class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <div class="mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                                    <h3
                                        class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                            </path>
                                        </svg>
                                        Participación de Estudiantes en el Proyecto
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Conteo de estudiantes
                                        registrados que colaboran en el proyecto.</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                    <!-- Hombres Estudiantes -->
                                    <div class="space-y-1">
                                        <label
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            Estudiantes Hombres
                                        </label>
                                        <input type="number" v-model.number="editForm.proyect_num_est_h" disabled
                                            @input="calcularTotalEstudiantes" min="0"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition"
                                            placeholder="0">
                                    </div>

                                    <!-- Mujeres Estudiantes -->
                                    <div class="space-y-1">
                                        <label
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            Estudiantes Mujeres
                                        </label>
                                        <input type="number" v-model.number="editForm.proyect_num_est_m" disabled
                                            @input="calcularTotalEstudiantes" min="0"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition"
                                            placeholder="0">
                                    </div>

                                    <!-- Total Estudiantes -->
                                    <div class="space-y-1">
                                        <label
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                            Total Estudiantes Participantes
                                        </label>
                                        <input type="number" v-model="editForm.proyect_num_est_part" readonly
                                            class="w-full px-4 py-2.5 border border-emerald-200 dark:border-emerald-900/50 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 font-bold text-lg text-center cursor-not-allowed"
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Factores críticos de éxito  <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="facExitTextarea"
                                    v-model="editForm.proyect_fact_exito" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_fact_exito')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Los factores críticos de éxito son puntos clave que, cuando están bien ejecutados, definen y garantizan el desarrollo y ejecución del proyecto, logrando sus objetivos. Por el contrario, cuando estos mismos factores se pasan por alto o se ignoran, contribuyen al fracaso de la organización. ¿Qué recursos económicos, tecnológicos, talento humano, infraestructura física, etc., cuenta la institución para lograr el éxito del proyecto?"
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Restricciones/Supuestos  <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="restSupuTextarea"
                                    v-model="editForm.proyect_rest_supu" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_rest_supu')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Identificar las circunstancias y eventos que deben ocurrir para que el proyecto sea exitoso e identificar los elementos que restringen, limitan o regulan la gestión del proyecto."
                                    rows="4"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'cronograma_act'" class="space-y-6 animate-fade-in-up">
                        <!-- Encabezado Informativo -->
                        <div class="p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex justify-between items-center gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Cronograma de Actividades</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Detalle las actividades que se realizarán para alcanzar los Objetivos Específicos.
                                </p>
                            </div>
                            <!-- Indicador de Horas Totales -->
                            <div class="flex items-center gap-4">
                                <div class="text-right bg-white dark:bg-gray-900 px-4 py-2 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                    <span class="text-xs text-gray-500 uppercase font-bold block">Horas {{ anioSeleccionadoTab }}</span>
                                    <span class="text-lg font-black" :class="totalHorasAnioSeleccionado > 240 ? 'text-red-600' : 'text-green-600'">
                                        {{ totalHorasAnioSeleccionado }} / 240 hrs
                                    </span>
                                </div>
                                <!-- BOTÓN GENERAR PDF -->
                               <button 
                                    @click="generarPDFCronograma" 
                                    :disabled="isGeneratingPDF"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold shadow flex items-center gap-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    title="Generar Anexo 2"
                                >
                                    <!-- Muestra el ícono normal si NO está cargando -->
                                    <i v-if="!isGeneratingPDF" class="fas fa-file-pdf"></i>
                                    <!-- Muestra el spinner girando si ESTÁ cargando -->
                                    <i v-else class="fas fa-spinner fa-spin"></i>
                                    
                                    <!-- Cambia el texto dinámicamente -->
                                    {{ isGeneratingPDF ? 'Generando...' : 'Generar PDF' }}
                                </button>
                            </div>
                        </div>

                        <!-- Pestañas de Años del Proyecto -->
                        <div class="flex border-b border-gray-200 dark:border-gray-700 gap-2 overflow-x-auto">
                            <button 
                                v-for="itemAnio in aniosProyecto" 
                                :key="itemAnio.id"
                                @click="anioSeleccionadoTab = itemAnio.id"
                                :class="anioSeleccionadoTab === itemAnio.id 
                                    ? 'border-green-600 text-green-600 font-bold border-b-2' 
                                    : 'text-gray-500 hover:text-gray-700'"
                                class="py-2 px-4 text-sm transition-all whitespace-nowrap"
                            >
                                {{ itemAnio.label }}
                            </button>
                        </div>

                        <!-- Accion de Agregar Actividad -->
                        <div class="flex justify-end gap-3">
                            <button 
                                @click="showModalDuplicar = true"
                                class="px-4 py-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                                Duplicar Año
                            </button>
                            <button 
                                @click="abrirModalActividad()"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Agregar Actividad a {{ anioSeleccionadoTab }}
                            </button>
                        </div>

                        <!-- Listado de Actividades Organizadas por Objetivo Específico -->
                        <div class="space-y-6">
                            <div 
                                v-for="obj in obtenerEspecificos()" 
                                :key="obj.id_obj_proy" 
                                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm"
                            >
                                <!-- Objetivo Especifico Header -->
                                <div class="bg-gray-50 dark:bg-gray-850 p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                    <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                                        <span class="text-green-600 dark:text-green-400">Objetivo Específico:</span> {{ obj.detalle_obj_proy }}
                                    </h4>
                                </div>

                                <!-- Tabla de Actividades de este Objetivo -->
                                <div class="p-4">
                                    <div v-if="editForm.actividades.filter(a => a.id_obj_proy === obj.id_obj_proy && a.detalle_anio === anioSeleccionadoTab).length === 0" class="text-center py-6 text-gray-400 text-sm">
                                        No hay actividades registradas para este objetivo en el {{ anioSeleccionadoTab }}.
                                    </div>

                                    <div v-else class="space-y-4">
                                        <div 
                                            v-for="(act, idx) in editForm.actividades" 
                                            :key="idx" 
                                            v-show="act.id_obj_proy === obj.id_obj_proy && act.detalle_anio === anioSeleccionadoTab"
                                            class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 space-y-3"
                                        >
                                            <!-- Titulo y Detalle de Actividad -->
                                            <div class="flex justify-between items-start gap-4">
                                                <div>
                                                    <h5 class="font-bold text-base text-gray-900 dark:text-white">{{ act.nom_actividad }}</h5>
                                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                                        {{ act.horas }} Horas
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ act.fecha_desde }} al {{ act.fecha_hasta }}
                                                    </span>
                                                    <p class="text-xs text-gray-500">Responsables: {{ act.responsables || 'No asignado' }}</p>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button @click="abrirModalActividad(act, idx)" class="text-blue-600 hover:text-blue-800 text-xs font-semibold ml-2">Editar</button>
                                                    <button @click="eliminarActividad(idx)" class="text-red-600 hover:text-red-800 text-xs font-semibold">Eliminar</button>
                                                </div>
                                            </div>

                                            <!-- Subactividades -->
                                            <div v-if="act.invi_subactividad && act.invi_subactividad.length > 0" class="pl-4 border-l-2 border-green-500 my-2 space-y-1">
                                                <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Subactividades:</p>
                                                <div v-for="(sub, sIdx) in act.invi_subactividad" :key="sIdx" class="text-xs text-gray-600 dark:text-gray-400 flex justify-between">
                                                    <span>• {{ sub.nom_sub_actv }} ({{ sub.fecha_desde }} / {{ sub.fecha_hasta }})</span>
                                                    <span class="font-semibold">{{ sub.horas }} hrs</span>
                                                </div>
                                            </div>

                                            <!-- Desglose de Atributos (Medios, Productos, Indicadores, Supuestos) -->
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs pt-2 border-t border-gray-200 dark:border-gray-800">
                                                <div>
                                                    <span class="font-bold text-gray-700 dark:text-gray-300">Indicadores:</span>
                                                    <ul class="list-disc list-inside text-gray-500"><li v-for="(i, k) in act.invi_actindicadores" :key="k">{{ i.detalle_indicador }}</li></ul>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-700 dark:text-gray-300">Productos:</span>
                                                    <ul class="list-disc list-inside text-gray-500"><li v-for="(p, k) in act.invi_actprod_verificables" :key="k">{{ p.detalle_prod_verif }}</li></ul>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-700 dark:text-gray-300">Medios Verificación:</span>
                                                    <ul class="list-disc list-inside text-gray-500"><li v-for="(m, k) in act.invi_actmedios_verificacion" :key="k">{{ m.detalle_medio_verifica }}</li></ul>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-700 dark:text-gray-300">Supuestos:</span>
                                                    <ul class="list-disc list-inside text-gray-500"><li v-for="(s, k) in act.invi_actsupuestos" :key="k">{{ s.detalle_supuestos }}</li></ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="showModalDuplicar" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
                        <div class="bg-white dark:bg-gray-850 rounded-xl shadow-xl max-w-md w-full p-6 space-y-4 animate-fade-in-up">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b pb-2">Duplicar Actividades</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Seleccione el año origen y el año destino. Se copiarán todas las actividades y las fechas se actualizarán automáticamente al nuevo año.</p>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold mb-1 dark:text-white">Año Origen</label>
                                    <select v-model="anioOrigenDuplicar" class="w-full border rounded-lg p-2 dark:bg-gray-800 dark:text-white">
                                        <option v-for="item in aniosProyecto" :key="item.id" :value="item.id">{{ item.label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold mb-1 dark:text-white">Año Destino</label>
                                    <select v-model="anioDestinoDuplicar" class="w-full border rounded-lg p-2 dark:bg-gray-800 dark:text-white">
                                        <option v-for="item in aniosProyecto" :key="item.id" :value="item.id">{{ item.label }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t">
                                <button @click="showModalDuplicar = false" class="px-4 py-2 border rounded-lg text-sm text-gray-600 dark:text-gray-300">Cancelar</button>
                                <button @click="ejecutarDuplicacion" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold">Duplicar Datos</button>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'bienes_servi_proyec'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex gap-4">
                           
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Detalles de bienes y servicios proporcionados por proyecto</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Describa aquí los bienes y servicios proporcionados por el proyecto.
                                </p>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Bienes <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="bienesTextarea"
                                    v-model="editForm.proyect_bienes" 
                                    @input="ajustarAlturaTextarea"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Detallar los bienes tangibles o intangibles que el proyecto generará durante la ejecución del proyecto (resultado/producto)."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Servicios <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="serviTextarea"
                                    v-model="editForm.proyect_servicios" 
                                    @input="ajustarAlturaTextarea"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Detallar el servició que el proyecto generará durante la ejecución del proyecto (resultado/producto)."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Bienes y Servicios <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="servibienTextarea"
                                    v-model="editForm.proyect_bienes_servicios" 
                                    @input="ajustarAlturaTextarea"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Un bien y un servicio en conjunto se pueden considerar como una solución integral que ofrece tanto productos tangibles como intangibles para satisfacer una necesidad específica."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                    <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                        Detalle de adquisiciones del proyecto <span class="text-red-500">*</span>
                                    </label>
                                <div class="overflow-x-auto">
                                    
                                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50/80 dark:bg-gray-800/80 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                            <tr>
                                                <th class="px-4 py-4 min-w-[210px] font-semibold">Tipo</th>
                                                <th class="px-4 py-4 min-w-[220px] font-semibold">Detalle General</th>
                                                <!-- Ampliado a 140px -->
                                                <th class="px-4 py-4 min-w-[140px] font-semibold">% Nacional</th>
                                                <th class="px-4 py-4 min-w-[220px] font-semibold">Detalle Nacional</th>
                                                <!-- Ampliado a 140px -->
                                                <th class="px-4 py-4 min-w-[140px] font-semibold">% Importado</th>
                                                <th class="px-4 py-4 min-w-[220px] font-semibold">Detalle Importado</th>
                                                <th class="px-4 py-4 text-center font-semibold">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            <tr v-if="editForm.adquisiciones.length === 0">
                                                <td colspan="7" class="px-4 py-12 text-center">
                                                    <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                                        <i class="fas fa-inbox text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                                        <p>No hay adquisiciones registradas.</p>
                                                        <p class="text-xs mt-1">Haz clic en "Agregar Adquisición" para comenzar.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-for="(item, index) in editForm.adquisiciones" :key="index" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                                
                                                <!-- 1. TIPO -->
                                                <td class="px-4 py-3 align-top">
                                                    <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800/80 rounded-lg border border-gray-200 dark:border-gray-700 gap-0.5">
                                                        <button type="button" @click="item.tipo_adqui = 'bien'" :class="item.tipo_adqui === 'bien' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm font-semibold border border-gray-200/50 dark:border-gray-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'" class="px-2.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap">Bien</button>
                                                        <button type="button" @click="item.tipo_adqui = 'servicio'" :class="item.tipo_adqui === 'servicio' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm font-semibold border border-gray-200/50 dark:border-gray-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'" class="px-2.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap">Servicio</button>
                                                        <button type="button" @click="item.tipo_adqui = 'bienes y servicios'" :class="item.tipo_adqui === 'bienes y servicios' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm font-semibold border border-gray-200/50 dark:border-gray-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'" class="px-2.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap">Ambos</button>
                                                    </div>
                                                </td>

                                                <!-- 2. DETALLE GENERAL -->
                                                <td class="px-4 py-3 align-top">
                                                    <textarea ref="detallebTextarea" v-model="item.detalle" rows="2" @input="ajustarAlturaTextarea"
                                                        class="w-full p-2.5 text-sm bg-white dark:bg-gray-800/50 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden transition-all shadow-inner text-gray-700 dark:text-gray-200 placeholder-gray-400" 
                                                        placeholder="Ej. Equipos de cómputo..."></textarea>
                                                </td>

                                                <!-- 3. PORCENTAJE NACIONAL (Con disparador de actualización) -->
                                                <td class="px-4 py-3 align-top">
                                                    <div class="relative w-full">
                                                        <input type="number" v-model="item.porcent_nacio" @input="actualizarPorcentajes(item)" min="0" max="100" step="0.01" 
                                                            class="w-full pl-3 pr-7 py-2.5 bg-white dark:bg-gray-800/50 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-inner" 
                                                            placeholder="0">
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                            <span class="text-gray-400 dark:text-gray-500 font-bold">%</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- 4. DETALLE NACIONAL -->
                                                <td class="px-4 py-3 align-top">
                                                    <textarea ref="detalleporcTextarea" v-model="item.detalle_iinsu_nac" rows="2" @input="ajustarAlturaTextarea"
                                                        class="w-full p-2.5 text-sm bg-white dark:bg-gray-800/50 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden transition-all shadow-inner text-gray-700 dark:text-gray-200 placeholder-gray-400" 
                                                        placeholder="Especificar detalle..."></textarea>
                                                </td>

                                                <!-- 5. PORCENTAJE IMPORTADO (Bloqueado/Solo lectura) -->
                                                <td class="px-4 py-3 align-top">
                                                    <div class="relative w-full">
                                                        <input type="number" v-model="item.porcent_importado" readonly
                                                            class="w-full pl-3 pr-7 py-2.5 bg-gray-100 dark:bg-gray-700/60 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed transition-all shadow-inner" 
                                                            placeholder="0">
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                            <span class="text-gray-400 dark:text-gray-500 font-bold">%</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- 6. DETALLE IMPORTADO (Bloqueo y estilos dinámicos) -->
                                                <td class="px-4 py-3 align-top">
                                                    <textarea ref="detalleimportTextarea" v-model="item.detalle_insu_import" rows="2" @input="ajustarAlturaTextarea"
                                                        :disabled="item.porcent_importado <= 0"
                                                        :class="item.porcent_importado <= 0 ? 'bg-gray-100 dark:bg-gray-700/60 cursor-not-allowed text-gray-500 opacity-80' : 'bg-white dark:bg-gray-800/50 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-inner text-gray-700 dark:text-gray-200'"
                                                        class="w-full p-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg resize-none overflow-hidden transition-all placeholder-gray-400" 
                                                        placeholder="Especificar detalle..."></textarea>
                                                </td>

                                                <!-- 7. ACCIÓN -->
                                                <td class="px-4 py-3 text-center align-middle">
                                                    <button @click="eliminarAdquisicion(index)" type="button" 
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white transition-all focus:ring-2 focus:ring-red-500/40" 
                                                        title="Eliminar ítem">
                                                        <i class="fas fa-trash-alt text-sm"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- FOOTER DE LA TABLA CON BOTÓN -->
                                <div class="p-4 bg-gray-50/50 dark:bg-gray-800/30 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                                    <button @click="agregarAdquisicion" type="button" 
                                        class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-500/30 shadow-sm hover:shadow transition-all flex items-center gap-2">
                                        <i class="fas fa-plus"></i> 
                                        Agregar Adquisición
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="p-1 rounded-md bg-blue-50 text-blue-600 mt-0.5 dark:bg-blue-950/40 dark:text-blue-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A11.952 11.952 0 0 1 12 16.5c-2.998 0-5.74-1.1-7.843-2.918m0 0A8.959 8.959 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Categorización del Proyecto</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        La categorización del proyecto es un indicador de la importancia que tiene para el proyecto en relación con la actividad económica y social del país. Este indicador se utiliza para clasificar el impacto del proyecto en la economía y la sociedad.
                                    </p>
                                </div>
                            </div>

                            <div class="p-5 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-gray-950 space-y-4">
                                <div class="space-y-2">
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Seleccione la categorización del proyecto: <span class="text-red-500">*</span>
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                        
                                        <!-- Opción 1: Indispensable -->
                                        <div class="flex items-center p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all cursor-pointer"
                                            :class="editForm.proyect_categorizacion === 'Indispensable' ? 'border-green-500 bg-green-50/10 dark:border-green-900/30 ring-1 ring-green-500/30' : 'border-gray-200 dark:border-gray-800'"
                                            @click="editForm.proyect_categorizacion = 'Indispensable'">
                                            <div class="flex items-center h-5" @click.stop>
                                                <input type="radio" id="cat-radio-indispensable" value="Indispensable" v-model="editForm.proyect_categorizacion"
                                                    class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900">
                                            </div>
                                            <label for="cat-radio-indispensable" class="ml-3 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer w-full"
                                                @click.stop="editForm.proyect_categorizacion = 'Indispensable'">
                                                <span :class="editForm.proyect_categorizacion === 'Indispensable' ? 'text-green-700 dark:text-green-400' : ''">Indispensable</span>
                                            </label>
                                        </div>

                                        <!-- Opción 2: Necesario -->
                                        <div class="flex items-center p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all cursor-pointer"
                                            :class="editForm.proyect_categorizacion === 'Necesario' ? 'border-green-500 bg-green-50/10 dark:border-green-900/30 ring-1 ring-green-500/30' : 'border-gray-200 dark:border-gray-800'"
                                            @click="editForm.proyect_categorizacion = 'Necesario'">
                                            <div class="flex items-center h-5" @click.stop>
                                                <input type="radio" id="cat-radio-necesario" value="Necesario" v-model="editForm.proyect_categorizacion"
                                                    class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900">
                                            </div>
                                            <label for="cat-radio-necesario" class="ml-3 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer w-full"
                                                @click.stop="editForm.proyect_categorizacion = 'Necesario'">
                                                <span :class="editForm.proyect_categorizacion === 'Necesario' ? 'text-green-700 dark:text-green-400' : ''">Necesario</span>
                                            </label>
                                        </div>

                                        <!-- Opción 3: Deseable -->
                                        <div class="flex items-center p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all cursor-pointer"
                                            :class="editForm.proyect_categorizacion === 'Deseable' ? 'border-green-500 bg-green-50/10 dark:border-green-900/30 ring-1 ring-green-500/30' : 'border-gray-200 dark:border-gray-800'"
                                            @click="editForm.proyect_categorizacion = 'Deseable'">
                                            <div class="flex items-center h-5" @click.stop>
                                                <input type="radio" id="cat-radio-deseable" value="Deseable" v-model="editForm.proyect_categorizacion"
                                                    class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900">
                                            </div>
                                            <label for="cat-radio-deseable" class="ml-3 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer w-full"
                                                @click.stop="editForm.proyect_categorizacion = 'Deseable'">
                                                <span :class="editForm.proyect_categorizacion === 'Deseable' ? 'text-green-700 dark:text-green-400' : ''">Deseable</span>
                                            </label>
                                        </div>

                                        <!-- Opción 4: Admisible -->
                                        <div class="flex items-center p-3 border rounded-xl bg-gray-50/50 dark:bg-gray-900/30 transition-all cursor-pointer"
                                            :class="editForm.proyect_categorizacion === 'Admisible' ? 'border-green-500 bg-green-50/10 dark:border-green-900/30 ring-1 ring-green-500/30' : 'border-gray-200 dark:border-gray-800'"
                                            @click="editForm.proyect_categorizacion = 'Admisible'">
                                            <div class="flex items-center h-5" @click.stop>
                                                <input type="radio" id="cat-radio-admisible" value="Admisible" v-model="editForm.proyect_categorizacion"
                                                    class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900">
                                            </div>
                                            <label for="cat-radio-admisible" class="ml-3 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer w-full"
                                                @click.stop="editForm.proyect_categorizacion = 'Admisible'">
                                                <span :class="editForm.proyect_categorizacion === 'Admisible' ? 'text-green-700 dark:text-green-400' : ''">Admisible</span>
                                            </label>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div v-else-if="activeTab === 'metodologia'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex gap-4">
                           
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Metodología para la Vinculación</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Diseño del Estudio: Detallar diseño experimental, tipo de análisis estadístico, otros
                                </p>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Metodología <span class="text-red-500">*</span>
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-justify">
                                    Exponer de forma clara y concisa la metodología que se empleará para el desarrollo del proyecto, considerando los procesos que se emplearán para la recolección de información, las variables que serán consideradas y los análisis que se utilizarán para la obtención de los resultados.
                                </p>
                                <textarea 
                                    ref="metodologiaTextarea"
                                    v-model="editForm.proyect_metodologia" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_metodologia')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Exponer de forma clara y concisa la metodología que se empleará para el desarrollo del proyecto, considerando los procesos que se emplearán para la recolección de información, las variables que serán consideradas y los análisis que se utilizarán para la obtención de los resultados. TIPOS DE INVESTIGACIÓN URL: https://goo.gl/F65YtN"
                                    rows="4"
                                ></textarea>
                                <!-- Contador de palabras -->
                                <div class="flex justify-between items-center mt-2 text-sm font-semibold transition-colors" :class="colorContadorMetodo">
                                    <span>Mínimo: 600 / Máximo: 800 palabras</span>
                                    <span>{{ palabrasMetodo }} palabras</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'financia'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shadow-sm">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Financiamiento del Proyecto</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    1. Haga clic en "Agregar Financiamiento" para seleccionar un Rubro.<br>
                                    2. Ingrese la cantidad, el valor, y distribuya los aportes (UTLVTE y Otros) según los años de duración del proyecto.<br>
                                    3. Los totales y porcentajes se calcularán automáticamente.
                                </p>
                            </div>
                            
                            <!-- Botón Generar PDF Anexo 3 (Solo se muestra si hay al menos 1 rubro) -->
                            <div v-if="editForm.financiamientos && editForm.financiamientos.length > 0" class="shrink-0">
                               <button @click="abrirModalAreaTematica" :disabled="isGeneratingPDFFinancia" type="button" class="px-5 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-500/30 transition-all shadow-sm flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                                    <i v-if="isGeneratingPDFFinancia" class="fas fa-spinner fa-spin"></i>
                                    <i v-else class="fas fa-file-pdf"></i>
                                    {{ isGeneratingPDFFinancia ? 'Procesando...' : 'Generar PDF' }}
                                </button>
                            </div>
                        </div>

                        <!-- Si NO hay financiamientos -->
                        <div v-if="editForm.financiamientos.length === 0" class="flex flex-col items-center justify-center p-12 bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl">
                            <i class="fas fa-coins text-5xl mb-4 text-gray-300 dark:text-gray-600"></i>
                            <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300">No hay rubros de financiamiento</h4>
                            <p class="text-sm text-gray-500 mb-6 mt-1 text-center max-w-md">Comience a detallar el presupuesto agregando el primer rubro de financiamiento.</p>
                            <button @click="abrirModalFinancia" type="button" class="px-6 py-2.5 bg-brand-600 text-white font-medium rounded-lg hover:bg-brand-700 focus:ring-4 focus:ring-brand-500/30 transition-all shadow-sm">
                                <i class="fas fa-plus mr-2"></i> Agregar Financiamiento
                            </button>
                        </div>

                        <!-- Si SÍ hay financiamientos (Tabla Principal) -->
                        <div v-else class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
                            <div class="p-4 bg-gray-50/50 dark:bg-gray-800/30 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Detalle de Rubros</h4>
                                <button @click="abrirModalFinancia" type="button" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-500/30 transition-all shadow-sm">
                                    <i class="fas fa-plus mr-1"></i> Añadir Rubro
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs text-left text-gray-600 dark:text-gray-300 border-collapse">
                                    <thead class="text-gray-700 uppercase bg-gray-100 dark:bg-gray-800 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                        <tr>
                                            <th rowspan="2" class="px-3 py-2 border border-gray-200 dark:border-gray-700 min-w-[200px]">RUBROS</th>
                                            <th rowspan="2" class="px-3 py-2 text-center border border-gray-200 dark:border-gray-700">CANT.</th>
                                            <th rowspan="2" class="px-3 py-2 text-center border border-gray-200 dark:border-gray-700">VALOR</th>
                                            
                                            <!-- Cabecera dinámica Aportes UTLVTE -->
                                            <th :colspan="aniosProyecto.length" class="px-3 py-2 text-center border border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300">
                                                APORTES UTLVTE (EFECTIVO)
                                            </th>
                                            
                                            <!-- Cabecera dinámica Otros Aportes -->
                                            <th :colspan="aniosProyecto.length" class="px-3 py-2 text-center border border-gray-200 dark:border-gray-700 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300">
                                                OTROS APORTES (EFECTIVO)
                                            </th>
                                            
                                            <th rowspan="2" class="px-3 py-2 text-center font-bold border border-gray-200 dark:border-gray-700">TOTAL<br>EFECTIVO</th>
                                            <th rowspan="2" class="px-2 py-2 text-center border border-gray-200 dark:border-gray-700"><i class="fas fa-cog"></i></th>
                                        </tr>
                                        <tr>
                                            <!-- Subcabeceras de Años UTLVTE -->
                                            <th v-for="(anio, index) in aniosProyecto" :key="'u-'+index" class="px-2 py-1 text-center border border-gray-200 dark:border-gray-700 bg-blue-50/50 dark:bg-blue-900/10">Año {{index + 1}}</th>
                                            <!-- Subcabeceras de Años Otros -->
                                            <th v-for="(anio, index) in aniosProyecto" :key="'o-'+index" class="px-2 py-1 text-center border border-gray-200 dark:border-gray-700 bg-green-50/50 dark:bg-green-900/10">Año {{index + 1}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in editForm.financiamientos" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                            <td class="px-3 py-2 border border-gray-200 dark:border-gray-700 font-medium">
                                                {{ index + 1 }}. {{ getNombreRubro(item.id_rubro) }}
                                            </td>
                                            <td class="px-3 py-2 text-center border border-gray-200 dark:border-gray-700">{{ item.cantidad }}</td>
                                            <td class="px-3 py-2 text-right border border-gray-200 dark:border-gray-700">${{ formatoDinero(item.valor) }}</td>
                                            
                                            <!-- Valores UTLVTE -->
                                            <td v-for="(_, i) in aniosProyecto" :key="'uv-'+i" class="px-3 py-2 text-right border border-gray-200 dark:border-gray-700 text-blue-600 dark:text-blue-400">
                                                ${{ formatoDinero(item[`utlvte_anio${i+1}`]) }}
                                            </td>
                                            
                                            <!-- Valores Otros -->
                                            <td v-for="(_, i) in aniosProyecto" :key="'ov-'+i" class="px-3 py-2 text-right border border-gray-200 dark:border-gray-700 text-green-600 dark:text-green-400">
                                                ${{ formatoDinero(item[`otros_anio${i+1}`]) }}
                                            </td>
                                            
                                            <td class="px-3 py-2 text-right font-bold border border-gray-200 dark:border-gray-700">${{ formatoDinero(item.total_efectivo) }}</td>
                                            <td class="px-2 py-2 text-center border border-gray-200 dark:border-gray-700">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button @click="editarFinanciamiento(item, index)" type="button" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button @click="eliminarFinanciamiento(index)" type="button" class="text-red-500 hover:text-red-700 transition-colors" title="Eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <!-- Pie de tabla con Totales y Porcentajes -->
                                    <tfoot class="bg-gray-50 dark:bg-gray-800 font-bold">
                                        <tr>
                                            <td colspan="3" class="px-3 py-2 text-right border border-gray-200 dark:border-gray-700 uppercase">Total</td>
                                            
                                            <td v-for="(_, i) in aniosProyecto" :key="'ut-'+i" class="px-3 py-2 text-right border border-gray-200 dark:border-gray-700 text-blue-700 dark:text-blue-400">
                                                ${{ formatoDinero(totalesFinanciamiento[`utlvte_anio${i+1}`]) }}
                                            </td>
                                            
                                            <td v-for="(_, i) in aniosProyecto" :key="'ot-'+i" class="px-3 py-2 text-right border border-gray-200 dark:border-gray-700 text-green-700 dark:text-green-400">
                                                ${{ formatoDinero(totalesFinanciamiento[`otros_anio${i+1}`]) }}
                                            </td>
                                            
                                            <td class="px-3 py-2 text-right border border-gray-200 dark:border-gray-700 text-brand-600">${{ formatoDinero(totalesFinanciamiento.granTotal) }}</td>
                                            <td class="border border-gray-200 dark:border-gray-700"></td>
                                        </tr>
                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                            <td colspan="3" class="px-3 py-2 text-right border border-gray-200 dark:border-gray-700 uppercase">Porcentajes</td>
                                            
                                            <td v-for="(_, i) in aniosProyecto" :key="'up-'+i" class="px-3 py-2 text-center border border-gray-200 dark:border-gray-700">
                                                {{ calcularPorcentaje(totalesFinanciamiento[`utlvte_anio${i+1}`]) }}%
                                            </td>
                                            
                                            <td v-for="(_, i) in aniosProyecto" :key="'op-'+i" class="px-3 py-2 text-center border border-gray-200 dark:border-gray-700">
                                                {{ calcularPorcentaje(totalesFinanciamiento[`otros_anio${i+1}`]) }}%
                                            </td>
                                            
                                            <td class="px-3 py-2 text-center border border-gray-200 dark:border-gray-700">100%</td>
                                            <td class="border border-gray-200 dark:border-gray-700"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'viabiliadad_sos'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex gap-4">
                           
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Viabilidad y Plan de Sostenibilidad</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Detalle de la viabilidad y plan de sostenibilidad del proyecto.
                                </p>
                            </div>
                        </div>
                        <div class="space-y-3 mt-6">
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Viabilidad Técnica <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="viabTextarea"
                                    v-model="editForm.proyect_viabilidad_tec" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_viabilidad_tec')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Detalle la viabilidad técnica del proyecto."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Equipamiento Tecnológico Disponible <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="quipteTextarea"
                                    v-model="editForm.proyect_equip_tec" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_equip_tec')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Detalle el equipamiento tecnológico disponible del proyecto."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    ¿Qué perdería la provincia si el proyecto no se ejecuta en este periodo? <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="perderproTextarea"
                                    v-model="editForm.proyect_no_ejecuta" 
                                    @input="ajustarAlturaTextarea"
                                    @contextmenu.prevent="abrirMenuContextual($event, 'proyect_no_ejecuta')"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Detalle que perdería la provincia si el proyecto no se ejecuta en este periodo."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Impactos Esperados</h3>
                                    <ul class="text-sm text-gray-600 dark:text-gray-400 mt-2 space-y-1 list-disc list-inside">
                                        <li>Haga clic en <strong>"Añadir Impacto"</strong> para registrar uno nuevo.</li>
                                        <li>Seleccione el tipo de impacto haciendo clic en la tarjeta correspondiente.</li>
                                        <li>Redacte la descripción del impacto y guarde. Puede registrar varios impactos de un mismo tipo.</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Botón Agregar -->
                            <div class="flex justify-end">
                                <button @click="abrirModalImpacto" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-all flex items-center gap-2">
                                    <i class="fas fa-plus"></i> Añadir Impacto
                                </button>
                            </div>

                            <!-- Lista de Impactos Agrupados -->
                            <div class="space-y-6 mt-6">
                                <div v-if="Object.keys(impactosAgrupados).length === 0" class="text-center py-8 text-gray-500 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                                    <i class="fas fa-leaf text-4xl mb-3 text-gray-400"></i>
                                    <p>Aún no existen impactos registrados para este proyecto.</p>
                                </div>
                                
                                <!-- Renderizamos por Grupo de Impacto (Ej: Social, Ambiental...) -->
                                <div v-for="(listaDetalles, nombreImpacto) in impactosAgrupados" :key="nombreImpacto" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
                                    <div class="bg-gray-100 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                                        <h4 class="text-lg font-bold text-gray-800 dark:text-white capitalize"><i class="fas fa-bullseye text-brand-600 mr-2"></i> Impacto {{ nombreImpacto }}</h4>
                                    </div>
                                    <ul class="divide-y divide-gray-100 dark:divide-gray-700 p-4">
                                        <li v-for="(item, index) in listaDetalles" :key="index" class="py-3 flex justify-between items-start gap-4">
                                            <p class="text-gray-700 dark:text-gray-300 text-justify w-full">
                                                {{ item.descripcion_general }}
                                            </p>
                                            <div class="flex items-center gap-1 flex-shrink-0">
                                                <button @click="editarImpacto(item, item.originalIndex)" class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-gray-600 p-2 rounded transition-colors" title="Editar este detalle">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="eliminarImpacto(item.originalIndex)" class="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-gray-600 p-2 rounded transition-colors" title="Eliminar este detalle">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Sostenibilidad social: equidad, género, participación ciudadana <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="sost_socTextarea"
                                    v-model="editForm.proyect_sostenibilidad_soc" 
                                    @input="ajustarAlturaTextarea"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Detalle la sostenibilidad social: equidad, género, participación ciudadana."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Transferencia Tecnológica<span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="trasnf_tTextarea"
                                    v-model="editForm.proyect_transf_tecn" 
                                    @input="ajustarAlturaTextarea"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Exponer claramente cuáles serán los medios para realizar la transferencia de los resultados del proyecto. Para la transferencia de resultados se pueden considerar los siguientes medios: publicaciones científicas, publicaciones técnicas, organización de talleres con participación de los beneficiarios del proyecto, participación de los investigadores en congresos nacionales e internacionales, etc. En caso de que el proyecto incluya componentes de desarrollo tecnológico, ya sea en forma de producto o proceso, describa la manera mediante la cual se transferirá dicho resultado al sector productivo"
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Artículos Científicos<span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="artciTextarea"
                                    v-model="editForm.proyect_art_cientificos" 
                                    @input="ajustarAlturaTextarea"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Detalle los artículos científicos que se incluirán en el proyecto."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Prototipos<span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="protoTextarea"
                                    v-model="editForm.proyect_prototipos" 
                                    @input="ajustarAlturaTextarea"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Detalle los prototipos que se incluirán en el proyecto."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Registro de Propiedad Intelectual<span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="reproTextarea"
                                    v-model="editForm.proyect_reg_propin" 
                                    @input="ajustarAlturaTextarea"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Detalle el registro de propiedad intelectual del proyecto."
                                    rows="4"
                                ></textarea>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
                                <label class="block text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Empresas Spin OffS<span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    ref="spinofTextarea"
                                    v-model="editForm.proyect_empr_spin" 
                                    @input="ajustarAlturaTextarea"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="¿Qué es una spin-off?
                                        Las spin-off son iniciativas empresariales promovidas por miembros de la comunidad universitaria, que se caracterizan por basar su actividad en la explotación de nuevos procesos, productos o servicios a partir del conocimiento adquirido y los resultados obtenidos en la propia Universidad.
                                        La investigación aplicada es la base de estas empresas, cuya importancia radica en el desarrollo de nuevas tecnologías, la creación de empleo de calidad, la capacidad de generar un alto valor añadido en la actividad económica y la aportación al desarrollo regional.
                                        ¿Por qué crear una spin-off?
                                        Una spin off tiene ventajas para todos los implicados:
                                        - Los emprendedores podrán seguir desarrollando la tecnología que se generó en la universidad hasta el nivel de producto final, contratar personal investigador muy valioso y obtener rendimientos económicos del proceso.
                                        - La universidad podrá impulsa a través de las spin off su labor de transferencia de resultados de la investigación. Además obtendrá retornos económicos por contratos de transferencia con las spin off de investigaciones que si no llegan al mercado no le reportarán beneficios monetarios.
                                        - La sociedad se beneficiará de los puestos de trabajos cualificados que generan las spin off, de los impuestos que pagan y los productos novedosos que desarrollen
                                        "
                                    rows="4"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'difusion'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Difusión</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Detalle de qué manera se difundirá el proyecto.</p>
                                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                    <li>Haga clic en <strong>"Añadir Actividad"</strong> para abrir el formulario.</li>
                                    <li>Escriba el nombre de la actividad y asigne su costo estimado.</li>
                                    <li>Puede registrar y modificar múltiples actividades.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Botón Agregar -->
                        <div class="flex justify-end">
                            <button @click="abrirModalDifusion" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-all flex items-center gap-2">
                                <i class="fas fa-plus"></i> Añadir Actividad
                            </button>
                        </div>

                        <!-- Contenido: Mensaje vacío o Tabla -->
                        <div class="space-y-3 mt-6">
                            <div v-if="editForm.difusion.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                                <i class="fas fa-bullhorn text-4xl mb-3 text-gray-400"></i>
                                <p>Aún no existen actividades de difusión registradas para este proyecto.</p>
                            </div>

                            <div v-else class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-200 dark:bg-gray-700">
                                        <!-- Fila Superior: Tipo de Evento (Referencia imagen) -->
                                        <tr>
                                            <th colspan="3" class="px-6 py-2 text-left text-sm font-extrabold text-gray-800 dark:text-white uppercase tracking-wider border-b border-gray-300 dark:border-gray-600">
                                                Tipo de Evento / Actividades
                                            </th>
                                        </tr>
                                        <!-- Columnas -->
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider w-[60%] border-r border-gray-300 dark:border-gray-600">Actividad</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider w-[25%] border-r border-gray-300 dark:border-gray-600">Costo ($)</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider w-[15%]">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="(item, index) in editForm.difusion" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                            <td class="px-6 py-4 whitespace-normal text-sm text-gray-800 dark:text-gray-200 border-r border-gray-200 dark:border-gray-700">
                                                {{ item.nombre_actividad }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-center font-medium border-r border-gray-200 dark:border-gray-700">
                                                {{ parseFloat(item.costo).toFixed(2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                                <button @click="editarDifusion(item, index)" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 p-2 rounded-lg transition-colors" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="eliminarDifusion(index)" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-gray-600 p-2 rounded-lg transition-colors" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'bibliografias'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Bibliografías</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Listado de todas las referencias citadas en el proyecto.</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mt-6">
                            <div v-if="editForm.bibliografias.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                                <i class="fas fa-book text-4xl mb-3 text-gray-400"></i>
                                <p>No hay bibliografías registradas. Puede añadirlas haciendo clic derecho sobre los textos del proyecto.</p>
                            </div>
                            
                            <ul v-else class="space-y-4">
                                <!-- Formato: Autor. (Año). Título. Editorial o fuente. -->
                                <li v-for="(bib, index) in editForm.bibliografias" :key="index" class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-start gap-4 transition-all hover:shadow-md">
                                    <div class="mt-1 text-brand-500">
                                        <i class="fas fa-bookmark"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-800 dark:text-gray-200 text-sm leading-relaxed text-justify">
                                            <span class="font-bold">{{ bib.autor }}.</span> 
                                            ({{ bib.anio }}). 
                                            <span class="italic">{{ bib.titulo }}.</span> 
                                            {{ bib.editorial_fuente }}.
                                        </p>
                                        <span class="inline-block mt-2 px-2 py-1 bg-gray-100 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 rounded">
                                            Citado en: {{ formatearNombreCampo(bib.seccion_campo) }}
                                        </span>
                                    </div>
                                    <button @click="eliminarBibliografia(index)" class="text-red-400 hover:text-red-600 p-2 transition-colors" title="Eliminar bibliografía">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div v-else-if="activeTab === 'evidencia_pdf'" class="space-y-8 animate-fade-in-up">
                        <div class="mb-4 p-4 bg-green-50 dark:bg-gray-800 rounded-lg border-l-4 border-green-500 flex gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Evidencia PDF del proyecto</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400" v-if="editForm.proyect_archivo">Edite el pdf anteriormente subido</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400" v-else>Suba la evidencia del proyecto en pdf</p>
                            </div>
                        </div>
                        <!-- VISUALIZADOR DEL PDF ACTUAL -->
                        <div v-if="editForm.pdfurlarchivo && !archivopdfSeleccionado" class="mt-4">
                            <label class="block text-xs font-bold mb-2 text-gray-700 dark:text-gray-300">Documento Actual Guardado:</label>
                            <div class="w-full h-[400px] border border-gray-300 dark:border-gray-700 rounded-xl overflow-hidden bg-gray-50">
                                <iframe :src="editForm.pdfurlarchivo" class="w-full h-full border-0"></iframe>
                            </div>
                            <div class="mt-2 text-right">
                                <a :href="editForm.pdfurlarchivo" target="_blank" class="text-sm text-brand-600 hover:underline">Abrir en nueva pestaña</a>
                            </div>
                        </div>

                        <!-- ZONA PARA SUBIR/REEMPLAZAR PDF -->
                        <div class="space-y-3 mt-6">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold mb-2 text-gray-700 dark:text-gray-300">
                                    {{ editForm.pdfurlarchivo ? 'Subir un nuevo documento para reemplazar el actual (PDF)' : 'Documento Respaldo (PDF)' }}
                                </label>
                                <div @click="$refs.filePDF.click()"
                                    class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition-all"
                                    :class="archivopdfPreviewName ? 'border-brand-500 bg-brand-50/20' : 'border-gray-300 hover:border-brand-400 bg-gray-50 dark:bg-gray-800/50'">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg v-if="!archivopdfPreviewName" class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <svg v-else class="w-8 h-8 mb-3 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                            <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                        </svg>
                                        <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="font-semibold" v-if="!archivopdfPreviewName">
                                                {{ editForm.pdfurlarchivo ? 'Haga clic para reemplazar' : 'Haga clic para cargar' }}
                                            </span>
                                            <span class="font-semibold text-brand-600" v-else>{{ archivopdfPreviewName }}</span>
                                        </p>
                                        <p class="text-xs text-gray-400" v-if="!archivopdfPreviewName">PDF (Máx. 30MB)</p>
                                    </div>
                                    <!-- Cuidado aquí: El ref que usabas en @click era filePDF, pero en tu input era fileFoto. Lo he estandarizado a filePDF -->
                                    <input type="file" ref="filePDF" class="hidden" accept="application/pdf" @change="handlepdfFileChange" />
                                </div>
                                
                                <!-- Botón para cancelar el archivo recién seleccionado y dejar el anterior -->
                                <div class="mt-2 text-right" v-if="archivopdfPreviewName && editForm.pdfurlarchivo">
                                    <button @click="cancelarNuevoPDF" class="text-sm text-red-500 hover:underline">
                                        Cancelar nuevo archivo y mantener el actual
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal para Área Temática -->
                <div v-if="showModalAreaTematica" class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto bg-black/50 backdrop-blur-sm transition-opacity">
                    <div class="relative w-full max-w-md p-4 mx-auto bg-white rounded-xl shadow-lg dark:bg-gray-800">
                        <!-- Header -->
                        <div class="flex items-center justify-between p-4 border-b rounded-t dark:border-gray-700">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                Área Temática
                            </h3>
                            <button @click="cerrarModalAreaTematica" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                        <!-- Body -->
                        <div class="p-5 space-y-4">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Ingrese el Área Temática del proyecto para incluirla en el Anexo 3.
                            </p>
                            <div>
                                <input v-model="areaTematicaInput" @keyup.enter="generarPDFFinanciamiento" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Ej: Educación y Ambiente" autofocus>
                            </div>
                        </div>
                        <!-- Footer -->
                        <div class="flex items-center justify-end p-4 border-t border-gray-200 rounded-b dark:border-gray-700 gap-3">
                            <button @click="cerrarModalAreaTematica" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white">
                                Cancelar
                            </button>
                            <button @click="generarPDFFinanciamiento" type="button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed" :disabled="!areaTematicaInput.trim() || isGeneratingPDFFinancia">
                                <i v-if="isGeneratingPDFFinancia" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-download"></i>
                                Generar y Descargar
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="showModalFinancia" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 overflow-y-auto font-sans transition-opacity">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full p-0 overflow-hidden flex flex-col max-h-[90vh] animate-fade-in-up">
                        
                        <!-- Encabezado del Modal -->
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex justify-between items-center">
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                                <i class="fas fa-file-invoice-dollar text-brand-600"></i>
                                {{ indexFinanciaEditando !== null ? 'Editar Rubro de Financiamiento' : 'Añadir Rubro de Financiamiento' }}
                            </h3>
                            <button @click="cerrarModalFinancia" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors dark:hover:bg-gray-700">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>

                        <!-- Cuerpo del Modal -->
                        <div class="p-6 overflow-y-auto space-y-6">
                            <!-- Formulario Superior: Rubro, Cantidad, Valor -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                                
                                <!-- Select de Rubro -->
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Rubro <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <!-- Select estilizado (se oculta la flecha nativa con appearance-none y se dibuja una con Tailwind) -->
                                        <select v-model="formFinancia.id_rubro" class="block w-full pl-3 pr-10 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm transition-all cursor-pointer">
                                            <option value="" disabled>Seleccione un rubro...</option>
                                            <option v-for="rubro in rubrosDisponiblesFiltrados" :key="rubro.id_rubro" :value="rubro.id_rubro" :disabled="rubro.disabled" :class="{'text-gray-400': rubro.disabled}">
                                                {{ rubro.nombre_rubro }} {{ rubro.disabled ? '(Ya agregado)' : '' }}
                                            </option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Input Cantidad -->
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Cantidad <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-hashtag text-gray-400 text-sm"></i>
                                        </div>
                                        <!-- appearance:textfield y variantes webkit ocultan las flechas numéricas nativas -->
                                        <input type="number" v-model.number="formFinancia.cantidad" min="1" step="1" @input="limpiarNumeros" class="block w-full pl-9 pr-3 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="0">
                                    </div>
                                </div>

                                <!-- Input Valor Unitario -->
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Valor Unitario <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-dollar-sign text-gray-400 text-sm"></i>
                                        </div>
                                        <input type="number" v-model.number="formFinancia.valor" min="0" step="0.01" @input="limpiarNumeros" class="block w-full pl-8 pr-3 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <!-- Tarjeta de Distribución por Años -->
                            <div class="bg-gray-50/80 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-inner">
                                <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50">
                                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fas fa-calendar-alt text-gray-400"></i> Distribución por Años (Efectivo)
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1">Distribuya los montos. El sistema calculará el total automáticamente.</p>
                                </div>
                                
                                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <!-- Columna UTLVTE -->
                                    <div class="space-y-4">
                                        <div class="inline-block px-3 py-1.5 bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 text-xs font-bold rounded-lg flex items-center w-fit gap-2">
                                            <i class="fas fa-university"></i> APORTES UTLVTE
                                        </div>
                                        <div class="space-y-3">
                                            <div v-for="(anio, index) in aniosProyecto" :key="'form-u-'+index" class="flex items-center gap-4 group">
                                                <label class="w-16 text-sm text-gray-600 dark:text-gray-400 font-medium group-hover:text-blue-600 transition-colors">Año {{ index + 1 }}</label>
                                                <div class="relative flex-1">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-400 sm:text-sm">$</span>
                                                    </div>
                                                    <input type="number" v-model.number="formFinancia[`utlvte_anio${index+1}`]" @input="calcularTotalModal" min="0" step="0.01" class="block w-full pl-7 pr-3 py-2 text-gray-700 bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-all shadow-sm [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none hover:border-blue-300" placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Columna OTROS APORTES -->
                                    <div class="space-y-4">
                                        <div class="inline-block px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 text-xs font-bold rounded-lg flex items-center w-fit gap-2">
                                            <i class="fas fa-hand-holding-usd"></i> OTROS APORTES
                                        </div>
                                        <div class="space-y-3">
                                            <div v-for="(anio, index) in aniosProyecto" :key="'form-o-'+index" class="flex items-center gap-4 group">
                                                <label class="w-16 text-sm text-gray-600 dark:text-gray-400 font-medium group-hover:text-emerald-600 transition-colors">Año {{ index + 1 }}</label>
                                                <div class="relative flex-1">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-400 sm:text-sm">$</span>
                                                    </div>
                                                    <input type="number" v-model.number="formFinancia[`otros_anio${index+1}`]" @input="calcularTotalModal" min="0" step="0.01" class="block w-full pl-7 pr-3 py-2 text-gray-700 bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-all shadow-sm [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none hover:border-emerald-300" placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tarjeta Total Footer -->
                                <div class="px-5 py-4 bg-gray-100 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex justify-end items-center gap-4">
                                    <span class="text-sm text-gray-600 dark:text-gray-300 font-medium uppercase">Total Efectivo de este Rubro:</span>
                                    <div class="bg-white dark:bg-gray-800 px-4 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                                        <span class="text-xl font-black text-brand-600 dark:text-brand-400">${{ formatoDinero(formFinancia.total_efectivo) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción Footer -->
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex justify-end gap-3">
                            <button @click="cerrarModalFinancia" type="button" class="px-5 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 shadow-sm text-sm font-semibold transition-all dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                Cancelar
                            </button>
                            <button @click="guardarFinanciamientoEnTabla" type="button" :disabled="!formFinancia.id_rubro" class="px-5 py-2.5 bg-brand-600 text-white rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-sm text-sm font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <i class="fas fa-save"></i> Guardar Rubro
                            </button>
                        </div>

                    </div>
                </div>
                <div v-if="showModalImpacto" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 overflow-y-auto font-sans transition-opacity">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full p-0 overflow-hidden flex flex-col max-h-[90vh] animate-fade-in-up">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex justify-between items-center">
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                                <i class="fas fa-file-invoice-dollar text-brand-600"></i>
                                {{ indexImpactoEditando !== null ? 'Editar Impacto' : 'Añadir Imnpacto' }}
                            </h3>
                            <button @click="cerrarModalImpacto" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors dark:hover:bg-gray-700">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                        <div class="p-6 overflow-y-auto space-y-6">
                            <div class="mb-6">
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-3">1. Seleccione el Tipo de Impacto:</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <button 
                                        v-for="cat in listaImpactosDisponibles" 
                                        :key="cat.id_impactos"
                                        @click="formImpacto.id_impactos = cat.id_impactos"
                                        :class="[
                                            'p-3 border-2 rounded-xl text-center transition-all font-medium', 
                                            formImpacto.id_impactos === cat.id_impactos 
                                                ? 'bg-brand-50 border-brand-500 text-brand-700 shadow-sm' 
                                                : 'bg-white border-gray-200 text-gray-600 hover:border-brand-300 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200'
                                        ]"
                                    >
                                        {{ cat.nombre_impacto }}
                                    </button>
                                </div>
                            </div>

                            <!-- Área de Texto -->
                            <div class="mb-6">
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">2. Describa el impacto esperado:</label>
                                <textarea 
                                    v-model="formImpacto.descripcion_general" 
                                    rows="4" 
                                    ref="impactodesTextarea"
                                    @input="ajustarAlturaTextarea"
                                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none overflow-hidden text-justify transition-shadow" 
                                    placeholder="Escriba aquí los detalles..."
                                ></textarea>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex justify-end gap-3">
                            <button @click="cerrarModalImpacto" type="button" class="px-5 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 shadow-sm text-sm font-semibold transition-all dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                Cancelar
                            </button>
                            <button @click="guardarImpactoEnTabla" type="button" :disabled="!formImpacto.id_impactos || !formImpacto.descripcion_general.trim()" class="px-5 py-2.5 bg-brand-600 text-white rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-sm text-sm font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <i class="fas fa-save"></i> Guardar Impacto
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="showDivModalDifusion" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 overflow-y-auto font-sans transition-opacity">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden animate-fade-in-up">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                <i class="fas fa-bullhorn text-brand-500 mr-2"></i>
                                {{ indexDifusionEditando !== null ? 'Editar Actividad de Difusión' : 'Nueva Actividad de Difusión' }}
                            </h3>
                            <button @click="cerrarModalDifusion" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <!-- Body -->
                        <div class="p-6 space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Actividad / Tipo de Evento:</label>
                                <textarea 
                                    v-model="formDifusion.nombre_actividad" 
                                    rows="2"
                                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg p-3 focus:ring-2 focus:ring-brand-500 outline-none transition-shadow" 
                                    placeholder="Ej. Presentación de resultados en congreso internacional..."
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Costo Estimado ($):</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                                    <input 
                                        type="text" 
                                        v-model="formDifusion.costo" 
                                        @input="validarNumeroCosto"
                                        class="w-full pl-8 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg p-3 focus:ring-2 focus:ring-brand-500 outline-none transition-shadow" 
                                        placeholder="0.00"
                                    >
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Solo se permiten valores numéricos.</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                            <button @click="cerrarModalDifusion" class="px-5 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 font-medium transition-colors">Cancelar</button>
                            <button @click="guardarDifusionEnTabla" :disabled="!formDifusion.nombre_actividad.trim() || formDifusion.costo === ''" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Guardar Actividad
                            </button>
                        </div>
                    </div>
                </div>
                <div 
                    v-show="mostrarMenu" 
                    ref="contextMenu"
                    :style="{ top: menuY + 'px', left: menuX + 'px' }" 
                    class="absolute z-[9999] w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                >
                    <ul class="py-1">
                        <li>
                            <button 
                                @click="prepararModalBibliografia" 
                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 transition-colors font-medium"
                            >
                                <i class="fas fa-quote-right text-brand-500"></i> Añadir cita
                            </button>
                        </li>
                    </ul>
                </div>
                <div v-if="showModalBib" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 overflow-y-auto font-sans transition-opacity">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden animate-fade-in-up">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Añadir Bibliografía</h3>
                            <button @click="showModalBib = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Autor(es):</label>
                                <input v-model="formBib.autor" type="text" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-500 outline-none" placeholder="Ej. Pérez, J. y Gómez, A.">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Año:</label>
                                <input v-model="formBib.anio" type="text" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-500 outline-none" placeholder="Ej. 2023">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Título:</label>
                                <input v-model="formBib.titulo" type="text" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-500 outline-none" placeholder="Título del artículo o libro">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Editorial o Fuente:</label>
                                <input v-model="formBib.editorial_fuente" type="text" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-500 outline-none" placeholder="Nombre de la revista, url o editorial">
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                            <button @click="showModalBib = false" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 font-medium">Cancelar</button>
                            <button @click="guardarCitaEnTexto" :disabled="!formBib.autor || !formBib.anio || !formBib.titulo" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50">Insertar Cita</button>
                        </div>
                    </div>
                </div>

                <div
                    class="p-5 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3 bg-gray-50/50 dark:bg-gray-900/50">
                    <button @click="cerrarEdicion"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-750 active:scale-95 transition-all duration-150">
                        Cancelar
                    </button>
                    <button @click="guardarEdicion" :disabled="guardando || editForm.objetivos.length === 0"
                        class="px-6 py-2.5 text-sm font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100 shadow-md shadow-brand-500/10 transition-all duration-150 flex items-center gap-2">
                        <span v-if="guardando"
                            class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                        {{ guardando ? 'Almacenando...' : 'Finalizar y Guardar Cambios' }}
                    </button>
                </div>
            </div>
            <div v-if="showModalActividad" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 overflow-y-auto">
                <div class="bg-white dark:bg-gray-850 rounded-xl shadow-xl max-w-3xl w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b pb-2">
                        {{ indiceActividadEditando !== null ? 'Editar Actividad' : 'Registrar Actividad' }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <!-- Objetivo Especifico Relacionado -->
                        <div class="md:col-span-2">
                            <label class="block font-bold mb-2 dark:text-white">Objetivo Específico Relacionado</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto pr-1">
                                <div 
                                    v-for="obj in obtenerEspecificos()" 
                                    :key="obj.id_obj_proy"
                                    @click="modalActividad.id_obj_proy = obj.id_obj_proy"
                                    :class="[
                                            'p-3 rounded-lg border-2 cursor-pointer transition-all flex items-start gap-3',
                                            modalActividad.id_obj_proy === obj.id_obj_proy 
                                                ? 'bg-green-50 border-green-500 shadow-sm dark:bg-green-900/20' 
                                                : 'bg-white border-gray-200 hover:border-green-300 dark:bg-gray-800 dark:border-gray-700'
                                        ]"
                                    >
                                    <!-- Custom Radio Button -->
                                    <div :class="['w-5 h-5 mt-0.5 rounded-full border-2 flex-shrink-0 flex items-center justify-center', 
                                            modalActividad.id_obj_proy === obj.id_obj_proy ? 'border-green-500' : 'border-gray-300 dark:border-gray-500']">
                                        <div v-if="modalActividad.id_obj_proy === obj.id_obj_proy" class="w-2.5 h-2.5 rounded-full bg-green-500"></div>
                                    </div>
                                    <span :class="['text-sm font-medium', modalActividad.id_obj_proy === obj.id_obj_proy ? 'text-green-800 dark:text-green-400' : 'text-gray-700 dark:text-gray-300']">
                                        {{ obj.detalle_obj_proy }}
                                    </span>
                                </div>
                            </div>
                        </div>

                            <!-- Nombre de Actividad -->
                        <div class="md:col-span-2">
                            <label class="block font-bold mb-1">Nombre de la Actividad</label>
                            <textarea v-model="modalActividad.nom_actividad"
                                rows="1" 
                                @input="ajustarAlturaTextarea" 
                                type="text" class="w-full p-2 text-xs border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-hidden" 
                                placeholder="Ej: Talleres de Capacitación"></textarea>
                        </div>

                        <!-- Responsables -->
                        <div>
                            <label class="block font-bold mb-1">Responsables</label>
                            <input v-model="modalActividad.responsables" type="text" class="w-full border rounded-lg p-2 dark:bg-gray-800">
                        </div>

                        <!-- Año del Cronograma -->
                        <div>
                            <label class="block font-bold mb-1">Año asignado</label>
                            <select 
                                v-model="modalActividad.detalle_anio" 
                                @change="alCambiarAnioModal"
                                class="w-full border rounded-lg p-2 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                                <option v-for="itemAnio in aniosProyecto" :key="itemAnio.id" :value="itemAnio.id">
                                    {{ itemAnio.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Fechas y Horas -->
                        <div>
                            <label class="block font-bold mb-1 dark:text-white">Fecha Desde</label>
                            <input 
                                v-model="modalActividad.fecha_desde" 
                                type="date" 
                                :min="`${obtenerAnioNumerico(modalActividad.detalle_anio)}-01-01`"
                                :max="`${obtenerAnioNumerico(modalActividad.detalle_anio)}-12-31`"
                                class="w-full border rounded-lg p-2 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block font-bold mb-1 dark:text-white">Fecha Hasta</label>
                            <input 
                                v-model="modalActividad.fecha_hasta" 
                                type="date" 
                                :min="`${obtenerAnioNumerico(modalActividad.detalle_anio)}-01-01`"
                                :max="`${obtenerAnioNumerico(modalActividad.detalle_anio)}-12-31`"
                                class="w-full border rounded-lg p-2 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Total de Horas</label>
                            <input v-model.number="modalActividad.horas" type="number" min="1" max="240" class="w-full border rounded-lg p-2 dark:bg-gray-800">
                        </div>
                    </div>

                        <!-- SECCIÓN DE SUBACTIVIDADES -->
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-bold text-sm">Subactividades</h4>
                            <button @click="agregarItemActividad('invi_subactividad')" class="text-xs bg-blue-500 text-white px-2 py-1 rounded">+ Agregar Subactividad</button>
                        </div>
                        <div v-for="(sub, sIdx) in modalActividad.invi_subactividad" :key="sIdx" class="grid grid-cols-12 gap-2 mb-2 items-center">
                            <textarea v-model="sub.nom_sub_actv"
                                rows="1" 
                                @input="ajustarAlturaTextarea"
                                type="text" placeholder="Nombre" class="w-full p-2 text-xs border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-hidden"/>
                                
                            <input 
                                v-model="sub.fecha_desde" 
                                type="date" 
                                :min="`${obtenerAnioNumerico(modalActividad.detalle_anio)}-01-01`"
                                :max="`${obtenerAnioNumerico(modalActividad.detalle_anio)}-12-31`"
                                class="col-span-2 border rounded p-1 text-xs dark:bg-gray-800 dark:text-white dark:border-gray-600">
                            <input 
                                v-model="sub.fecha_hasta" 
                                type="date" 
                                :min="`${obtenerAnioNumerico(modalActividad.detalle_anio)}-01-01`"
                                :max="`${obtenerAnioNumerico(modalActividad.detalle_anio)}-12-31`"
                                class="col-span-2 border rounded p-1 text-xs dark:bg-gray-800 dark:text-white dark:border-gray-600">
                                
                            <input v-model.number="sub.horas" type="number" placeholder="Hrs" class="col-span-2 border rounded p-1 text-xs dark:bg-gray-800 dark:text-white dark:border-gray-600">
                            <button @click="eliminarItemActividad('invi_subactividad', sIdx)" class="col-span-1 text-red-500 hover:text-red-700 font-bold text-center transition">✕</button>
                        </div>
                    </div>

                        <!-- COMPONENTES DINÁMICOS (Indicadores, Productos, Medios, Supuestos) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4 text-xs">
                        <!-- Indicadores -->
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="font-bold">Indicadores</span>
                                <button @click="agregarItemActividad('invi_actindicadores')" class="text-blue-500">+ Agregar</button>
                            </div>
                            <div v-for="(item, k) in modalActividad.invi_actindicadores" :key="k" class="flex gap-1 mb-1">
                                <textarea 
                                    v-model="item.detalle_indicador" 
                                    rows="1" 
                                    @input="ajustarAlturaTextarea"
                                    type="text" class="w-full p-2 text-xs border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-hidden" />
                                <button @click="eliminarItemActividad('invi_actindicadores', k)" class="text-red-500">✕</button>
                            </div>
                        </div>

                            <!-- Productos Verificables -->
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="font-bold">Productos Verificables</span>
                                <button @click="agregarItemActividad('invi_actprod_verificables')" class="text-blue-500">+ Agregar</button>
                            </div>
                            <div v-for="(item, k) in modalActividad.invi_actprod_verificables" :key="k" class="flex gap-1 mb-1">
                                <textarea 
                                    v-model="item.detalle_prod_verif" 
                                    rows="1" 
                                    @input="ajustarAlturaTextarea"
                                    type="text" class="w-full p-2 text-xs border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-hidden" />
                                <button @click="eliminarItemActividad('invi_actprod_verificables', k)" class="text-red-500">✕</button>
                            </div>
                        </div>

                            <!-- Medios de Verificación -->
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="font-bold">Medios de Verificación</span>
                                <button @click="agregarItemActividad('invi_actmedios_verificacion')" class="text-blue-500">+ Agregar</button>
                            </div>
                            <div v-for="(item, k) in modalActividad.invi_actmedios_verificacion" :key="k" class="flex gap-1 mb-1">
                                <textarea
                                    v-model="item.detalle_medio_verifica"
                                    rows="1" 
                                    @input="ajustarAlturaTextarea" 
                                    type="text" class="w-full p-2 text-xs border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-hidden" />
                                <button @click="eliminarItemActividad('invi_actmedios_verificacion', k)" class="text-red-500">✕</button>
                            </div>
                        </div>

                            <!-- Supuestos -->
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="font-bold">Supuestos</span>
                                <button @click="agregarItemActividad('invi_actsupuestos')" class="text-blue-500">+ Agregar</button>
                            </div>
                            <div v-for="(item, k) in modalActividad.invi_actsupuestos" :key="k" class="flex gap-1 mb-1">
                                <textarea 
                                    v-model="item.detalle_supuestos" 
                                    rows="1" 
                                    @input="ajustarAlturaTextarea"
                                    type="text" class="w-full p-2 text-xs border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-hidden" />
                                <button @click="eliminarItemActividad('invi_actsupuestos', k)" class="text-red-500">✕</button>
                            </div>
                        </div>
                    </div>

                        <!-- Botones de Acción del Modal -->
                    <div class="flex justify-end gap-3 border-t pt-4">
                        <button @click="showModalActividad = false" class="px-4 py-2 border rounded-lg text-gray-600">Cancelar</button>
                        <button @click="guardarActividad" class="px-4 py-2 bg-green-600 text-white rounded-lg font-bold">Guardar Actividad</button>
                    </div>
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
const scrollContainer = ref(null);
const isDragging = ref(false);
const startX = ref(0);
const scrollLeft = ref(0);
// Creamos una función para que el bloque de abajo pueda cerrar el modal
const cerrarModalDesdeAfuera = () => {
    isProfileAddressModal.value = false
}
const startDrag = (e) => {
    isDragging.value = true;
    // Calcula la posición inicial del clic relativa al contenedor
    startX.value = e.pageX - scrollContainer.value.offsetLeft;
    // Guarda la posición actual del scroll
    scrollLeft.value = scrollContainer.value.scrollLeft;
};

const stopDrag = () => {
    isDragging.value = false;
};

const doDrag = (e) => {
    if (!isDragging.value) return;
    e.preventDefault(); // Evita que se seleccione el texto accidentalmente
    
    // Calcula cuánto se ha movido el mouse
    const x = e.pageX - scrollContainer.value.offsetLeft;
    const walk = (x - startX.value) * 1.5; // Multiplicador para la velocidad de arrastre
    
    // Aplica el nuevo scroll
    scrollContainer.value.scrollLeft = scrollLeft.value - walk;
};
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
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

export default {
    data() {
        return {
            idus: 0,
            baseUrl: "/inves",
            filteredarray: [],
            botonCargando: null,
            UsuarioInfo: {},
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
            listaCompromisos: [
                'Libros',
                'Capítulos de libros',
                'Artículos científicos',
                'Informe de Avance cada tres meses',
                'Otros'
            ],
            habilitarcompro: false,
            formInt: {
                id_deta_invi_proyect: null,
                horas: 0,
                id_funcion: null,
                idCarr: null,
                reemplazado: 0,
                anexo_integrante: null,
                anexo_integrante2: null,
                id_funcion_reemplazado: null,
                horas_reemplazado: 0,
                idCarr_reemplazado: null,
                compromisos: [],
                compromiso_otro: ''
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
            //pdf proyect
            archivopdfSeleccionado: null,
            archivopdfPreviewName: '',
            pdfuploading: false,
            continuarEnProyecto: true,
            showModalBaja: false,
            integranteBaja: null,
            archivoBaja: null,
            archivoBajaName: '',
            cargandoBaja: false,
            enviando: false,
            // Variables para el modal de edición de proyecto
            showEditModal: false,
            activeTab: 'generales',
            cargandoEdicion: false,
            guardando: false,
            yaTraducido: false,
            objetivosPeiDisponibles: [],
            politicasPlandeDisponibles: [],
            objetivosPoliticasDisponibles: [],
            objetivosODSDisponibles: [],
            facultadesCatalogo: [],
            carrerasCatalogo: [],
            dominiosCatalogo: [],
            convocatoriasCatalogo: [],
            dominioRecomendadoId: '',
            lineasCatalogo: [],
            sublineasCatalogo: [],
            unescoCatalogo: [],
            tip_proyectCatalogo: [],
            zonasCatalogo: [],
            detallesZonasCatalogo: [],
            provinciasCatalogo: [],
            cantonesCatalogo: [],
            parroquiasCatalogo: [],
            proyectoEditandoId: null,
            busquedaParroquia: '',
            showModalMarcoLogico: false,
            indiceEditandoML: null,
            searchEmpresaQuery: '',
            empresaBuscada: null,
            empresaNoEncontrada: false,
            buscandoEmpresa: false,
            empresasAgregadas: [],
            empresasAgregadas2: [],
            asignaturasDisponibles: [],
            searchAsignatura: '',
            editForm: {
                proyect_id: '',
                proyect_cod: '',
                proyect_nombre: '',
                proyect_titulo: '',
                proyect_nombre_en: '',
                proyect_titulo_en: '',
                proyect_multidis: 0,
                objetivos: [],
                politicas: [],
                ods: [],
                facultades: [],
                id_facultad_priori: '',
                carreras: [],
                id_carr_priori: '',
                dominios_humanisticos: [],
                id_convocatoria: '',
                sublineas_investigacion: [],
                unesco_areas: [],
                id_tip_invi_proy: '',
                proyect_cobertura: '',
                id_zona_plan: '',
                provincias: [], // Arreglo de id_provincia
                cantones: [],   // Arreglo de id_canton
                parroquias: [],  // Arreglo de idparroquia
                objetivos_marco_logico: [],
                proyect_antecedentes: '',
                proyect_justificacion: '',
                empresas: [],
                aportes_utlvt: [],
                aportes_inst: [],
                proyect_fecha_pres: '',
                fechainicio: '',
                fechafin: '',
                proyect_duracion_mes: '',
                proyect_estado: '',
                proyect_desc_situ_act: '',
                proyect_diag_probl: '',
                proyect_contribucion_soci: '',
                asignaturas: [],
                proyec_ident_poblaobj: '',
                proyect_num_direct_hombres: 0,
                proyect_num_direct_mujeres: 0,
                proyect_total_num_direct: 0,
                proyect_num_personas_div_fun: 0,
                proyect_total_num_indirect: 0,
                proyect_num_doce_part: 0,
                proyect_num_doce_h: 0,
                proyect_num_doce_m: 0,
                proyect_num_est_part: 0,
                proyect_num_est_h: 0,
                proyect_num_est_m: 0,
                proyect_fact_exito: '',
                proyect_rest_supu: '',
                actividades: [],
                proyect_bienes: '',
                proyect_servicios: '',
                proyect_bienes_servicios: '',
                adquisiciones: [],
                proyect_categorizacion: '',
                proyect_metodologia: '',
                financiamientos: [],
                proyect_viabilidad_tec: '',
                proyect_equip_tec: '',
                proyect_no_ejecuta: '',
                impactos: [],
                proyect_sostenibilidad_soc: '',
                proyect_transf_tecn: '',
                proyect_art_cientificos: '',
                proyect_prototipos: '',
                proyect_reg_propin: '',
                proyect_empr_spin: '',
                difusion: [],
                bibliografias: [],
                proyect_archivo: null,
                pdfurlarchivo: null,
            },
            anioSeleccionadoTab: 'Primer Año',
            showModalActividad: false,
            indiceActividadEditando: null,
            modalFormML: {
                id_obj_proy: null,
                tipo_obj_proy: '',
                detalle_obj_proy: '',
                indicadores: [],
                metas: [],
                supuestos: [],
                medios_verificacion: [],
                prod_verificables: []
            },
            showModalDuplicar: false,
            anioOrigenDuplicar: '',   // ej: '2025'
            anioDestinoDuplicar: '',  // ej: '2026'
            modalActividad: {
                id_actividades: null,
                id_obj_proy: '',
                nom_actividad: '',
                responsables: '',
                fecha_desde: '',
                fecha_hasta: '',
                horas: 0,
                detalle_anio: 'Primer Año',
                invi_subactividad: [],
                invi_actprod_verificables: [],
                invi_actmedios_verificacion: [],
                invi_actindicadores: [],
                invi_actsupuestos: []
            },
            isGeneratingPDF: false,
            showModalFinancia: false,
            listaRubrosDisponibles: [],
            formFinancia: this.resetFormFinancia(),
            isGeneratingPDFFinancia: false,
            showModalAreaTematica: false,
            showModalArea2Tematica: false,
            areaTematicaInput: '',
            areaTematica2Input: '',
            indexFinanciaEditando: null,
            listaImpactosDisponibles: [],
            showModalImpacto: false,
            formImpacto: {
                id_impactos: '',
                descripcion_general: ''
            },
            indexImpactoEditando: null,
            showDivModalDifusion: false,
            indexDifusionEditando: null,
            formDifusion: {
                id_det_difusion: null,
                id_difusion: null,
                nombre_actividad: '',
                costo: ''
            },
            mostrarMenu: false,
            menuX: 0,
            menuY: 0,
            campoObjetivoCita: '',
            posicionCursorCita: 0,
            showModalBib: false,
            formBib: {
                autor: '',
                anio: '',
                titulo: '',
                editorial_fuente: ''
            },
            selectedConvocatoria: '',
            listaConvocatorias: [],

        };
    },
    watch: {
        'editForm.politicas': {
            handler(nuevosIdsSeleccionados) {
                this.actualizarObjetivosPoliticas(nuevosIdsSeleccionados);
            },
            deep: true // deep: true asegura que detecte si se agregan/quitan elementos del array
        },
        'editForm.proyect_multidis'(nuevoValor) {
            if (nuevoValor === 0) {
                this.editForm.facultades = [];
                this.editForm.carreras = [];
            } else if (nuevoValor === 1) {
                if (this.editForm.id_facultad_priori && !this.editForm.facultades.includes(this.editForm.id_facultad_priori)) {
                    this.editForm.facultades.push(this.editForm.id_facultad_priori);
                }
                if (this.editForm.id_carr_priori && !this.editForm.carreras.includes(this.editForm.id_carr_priori)) {
                    this.editForm.carreras.push(this.editForm.id_carr_priori);
                }
            }
            this.obtenerCarrerasPorFacultades();
        },
        'editForm.id_facultad_priori'(nuevoId, viejoId) {
            // Evitamos borrar las carreras durante la carga inicial de abrirEdicion
            if (!this.cargandoEdicion && viejoId) {
                this.editForm.id_carr_priori = '';
                this.editForm.carreras = [];
            }
            this.obtenerCarrerasPorFacultades();
        },
        'editForm.facultades': {
            handler() {
                this.obtenerCarrerasPorFacultades();
            },
            deep: true
        },
        sublineasFiltradas: {
            handler(nuevasSublineas) {
                // Extrae solo los IDs de las sublíneas y los guarda en el form
                this.editForm.sublineas_investigacion = nuevasSublineas.map(sub => sub.id_sublin_investiga);
            },
            deep: true,
            immediate: true
        },
        activeTab(newTab) {
            // Cuando el usuario cambie a la pestaña de antecedentes, recalculamos la altura
            if (newTab === 'antecedentes_justifi') {
                this.$nextTick(() => {
                    this.recalcularAlturasCargadas();
                });
            }
            if (newTab === 'diagnostico_prob') {
                this.$nextTick(() => {
                    this.recalcularAlturasCargadas();
                });
            }
            if (newTab === 'articulaTextarea') {
                this.$nextTick(() => {
                    this.recalcularAlturasCargadas();
                });
            }
            if (newTab === 'identPoblaobjTextarea') {
                this.$nextTick(() => {
                    this.recalcularAlturasCargadas();
                });
            }
            if (newTab === 'facExitTextarea') {
                this.$nextTick(() => {
                    this.recalcularAlturasCargadas();
                });
            }
            if (newTab === 'restSupuTextarea') {
                this.$nextTick(() => {
                    this.recalcularAlturasCargadas();
                });
            }
            if (newTab === 'bienes_servi_proyec') {
                this.$nextTick(() => {
                    this.recalcularAlturasCargadas();
                });
            }
            if (newTab === 'metodologia') {
                this.$nextTick(() => {
                    this.recalcularAlturasCargadas();
                });
            }
            if (newTab === 'viabiliadad_sos') {
                this.$nextTick(() => {
                    this.recalcularAlturasCargadas();
                });
            }
            if (newTab === 'difusion') {
                this.$nextTick(() => {
                    this.recalcularAlturasCargadas();
                });
            }
            
        }
    },
    created() {
        // Ahora sí puedes usar this.filterAndFetch
        this.debouncedFilter = debounce(() => {
            this.filterAndFetch();
        }, 900);
    },
    async mounted() {
        this.UsuarioInfo = useUsuario();
        this.obtenerConvocatorias();
        this.GetData(1, this.searchQuery);
        document.addEventListener('click', this.cerrarMenuContextual);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.cerrarMenuContextual);
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
        lineasFiltradas() {
            if (!this.editForm.id_facultad_priori) return [];
            return this.lineasCatalogo.filter(lin => String(lin.idfacultad) === String(this.editForm.id_facultad_priori));
        },
        // Filtra las sublíneas basadas en la Carrera Prioritaria seleccionada
        sublineasFiltradas() {
            if (!this.editForm.id_carr_priori) return [];
            return this.sublineasCatalogo.filter(sub => String(sub.idCarr) === String(this.editForm.id_carr_priori));
        },
        unescoAreasPadre() {
            return this.unescoCatalogo.filter(item => item.tipo_area === 'Área de conocimiento');
        },
        // Muestra las subáreas cuyos padres (sau_pdid) estén seleccionados en el formulario
        unescoSubareasFiltradas() {
            return this.unescoCatalogo.filter(item => {
                return item.tipo_area === 'Subárea de conocimiento' && 
                       this.editForm.unesco_areas.includes(item.sau_pdid);
            });
        },
        // Muestra las áreas específicas cuyas subáreas de procedencia (sau_pdid) estén seleccionadas en el formulario
        unescoEspecificasFiltradas() {
            return this.unescoCatalogo.filter(item => {
                return item.tipo_area === 'Área específica de conocimiento' && 
                       this.editForm.unesco_areas.includes(item.sau_pdid);
            });
        },
        // 1. Filtrar Provincias según la Zona única
        provinciasDisponibles() {
            if (!this.editForm.id_zona_plan) return [];
            const provinciasIds = this.detallesZonasCatalogo
                .filter(d => d.id_zona_plan == this.editForm.id_zona_plan)
                .map(d => d.id_provincia);
            return this.provinciasCatalogo.filter(p => provinciasIds.includes(p.id_provincia));
        },
        // 2. Objetos de las provincias actualmente marcadas (útil para los botones)
        provinciasSeleccionadasObj() {
            return this.provinciasCatalogo.filter(p => this.editForm.provincias.includes(p.id_provincia));
        },
        // 3. Filtrar Cantones según las Provincias marcadas
        cantonesDisponibles() {
            if (this.editForm.provincias.length === 0) return [];
            const provinciasMarcadasObj = this.provinciasCatalogo.filter(p => this.editForm.provincias.includes(p.id_provincia));
            const codigosProvincias = provinciasMarcadasObj.map(p => p.codigo);
            // Aquí relacionamos el ID de la provincia seleccionada con 'codigoprovincia' del cantón
            // NOTA: Verifica en tu base de datos si Canton se enlaza con ID o con código. Asumo que es codigoprovincia = id_provincia según tu modelo
            return this.cantonesCatalogo.filter(c => codigosProvincias.includes(c.codigoprovincia));
        },
        // 4. Filtrar Parroquias resolviendo el BUG del código + Búsqueda de texto
        parroquiasFiltradas() {
            if (this.editForm.cantones.length === 0) return [];
            
            // a) Obtener los OBJETOS de los cantones seleccionados para extraer su "codigo" (no su id)
            const cantonesMarcadosObj = this.cantonesCatalogo.filter(c => this.editForm.cantones.includes(c.id_canton));
            const codigosCantones = cantonesMarcadosObj.map(c => c.codigo); // <-- ¡LA CLAVE AQUÍ!

            // b) Filtrar las parroquias cuyo 'codigocanton' coincida con los códigos extraídos
            let filtradas = this.parroquiasCatalogo.filter(p => codigosCantones.includes(p.codigocanton));

            // c) Aplicar búsqueda de texto si el usuario escribió algo
            if (this.busquedaParroquia.trim() !== '') {
                const search = this.busquedaParroquia.toLowerCase();
                filtradas = filtradas.filter(p => p.parroquia.toLowerCase().includes(search));
            }

            return filtradas;
        },
        palabrasAntecedentes() {
            return this.contarPalabras(this.editForm.proyect_antecedentes);
        },
        palabrasMetodo() {
            return this.contarPalabras(this.editForm.proyect_metodologia);
        },
        palabrasDescrip() {
            return this.contarPalabras(this.editForm.proyect_desc_situ_act);
        },
        palabrasJustificacion() {
            return this.contarPalabras(this.editForm.proyect_justificacion);
        },
        palabrasDiagnostico() {
            return this.contarPalabras(this.editForm.proyect_diag_probl);
        },
        colorContadorAntecedentes() {
            const total = this.palabrasAntecedentes;
            if (total === 0) return 'text-gray-500 dark:text-gray-400';
            if (total < 500 || total > 800) return 'text-red-500';
            return 'text-green-600 dark:text-green-400';
        },
        colorContadorMetodo() {
            const total = this.palabrasMetodo;
            if (total === 0) return 'text-gray-500 dark:text-gray-400';
            if (total < 600 || total > 800) return 'text-red-500';
            return 'text-green-600 dark:text-green-400';
        },
        colorContadorDescripcion() {
            const total = this.palabrasDescrip;
            if (total === 0) return 'text-gray-500 dark:text-gray-400';
            if (total < 550 || total > 650) return 'text-red-500';
            return 'text-green-600 dark:text-green-400';
        },
        colorContadorJustificacion() {
            const total = this.palabrasJustificacion;
            if (total === 0) return 'text-gray-500 dark:text-gray-400';
            if (total < 600 || total > 800) return 'text-red-500';
            return 'text-green-600 dark:text-green-400';
        },
        colorContadorDiagnostico() {
            const total = this.palabrasDiagnostico;
            if (total === 0) return 'text-gray-500 dark:text-gray-400';
            if (total < 550 || total > 650) return 'text-red-500';
            return 'text-green-600 dark:text-green-400';
        },
        cooperadoresFiltrados() {
            console.log(this.empresasAgregadas2);
            return this.empresasAgregadas2.filter(emp => emp && emp.ruc !== '0860000830001');
        },
        // Calcula dinámicamente la suma del presupuesto del proyecto en tiempo real
        totalProyecto() {
            let total = 0;
            this.editForm.aportes_utlvt.forEach(a => {
                total += this.limpiarYConvertirAFloat(a.valor);
            });
            this.editForm.aportes_inst.forEach(a => {
                total += this.limpiarYConvertirAFloat(a.valor);
            });
            return total;
        },
        limitesFechaFin() {
            if (!this.editForm.fechainicio) {
                return { min: '', max: '' };
            }
            // Creamos objetos Date basados en la fecha de inicio
            const minDate = new Date(this.editForm.fechainicio);
            const maxDate = new Date(this.editForm.fechainicio);
            
            // Sumamos 3 años para el mínimo y 5 años para el máximo
            minDate.setFullYear(minDate.getFullYear() + 3);
            maxDate.setFullYear(maxDate.getFullYear() + 5);

            return {
                min: minDate.toISOString().split('T')[0], // Formato YYYY-MM-DD
                max: maxDate.toISOString().split('T')[0]
            };
        },
        filteredAsignaturas() {
            if (!this.searchAsignatura) {
                return this.asignaturasDisponibles;
            }
            const searchTerm = this.searchAsignatura.toLowerCase();
            return this.asignaturasDisponibles.filter(asig => 
                asig.Nombre_Asignatura.toLowerCase().includes(searchTerm) || 
                asig.Codigo_Asignatura.toLowerCase().includes(searchTerm)
            );
        },
        aniosProyecto() {
            if (!this.editForm.fechainicio || !this.editForm.fechafin) {
                return [{ id: 'Primer Año', label: 'Primer Año', anio: new Date().getFullYear() }];
            }

            const anioInicio = new Date(this.editForm.fechainicio).getFullYear();
            const anioFin = new Date(this.editForm.fechafin).getFullYear();
            const anos = [];

            const nombresAnio = ['Primer Año', 'Segundo Año', 'Tercer Año', 'Cuarto Año', 'Quinto Año'];

            let index = 0;
            for (let y = anioInicio; y <= anioFin; y++) {
                const nombre = nombresAnio[index] || `Año ${index + 1}`;
                anos.push({
                    id: nombre,
                    label: `${nombre} (${y})`,
                    anio: y,
                    fechaInicioMin: `${y}-01-01`,
                    fechaFinMax: `${y}-12-31`
                });
                index++;
            }
            return anos;
        },
        actividadesDelAnio() {
            return this.editForm.actividades.filter(a => a.detalle_anio === this.anioSeleccionadoTab);
        },

        // Suma total de horas del año activo
        totalHorasAnioSeleccionado() {
            return this.actividadesDelAnio.reduce((sum, act) => sum + (parseFloat(act.horas) || 0), 0);
        },
        totalesFinanciamiento() {
            let totales = {
                utlvte_anio1: 0, utlvte_anio2: 0, utlvte_anio3: 0, utlvte_anio4: 0, utlvte_anio5: 0,
                otros_anio1: 0, otros_anio2: 0, otros_anio3: 0, otros_anio4: 0, otros_anio5: 0,
                granTotal: 0
            };

            this.editForm.financiamientos.forEach(item => {
                for (let i = 1; i <= 5; i++) {
                    totales[`utlvte_anio${i}`] += parseFloat(item[`utlvte_anio${i}`] || 0);
                    totales[`otros_anio${i}`] += parseFloat(item[`otros_anio${i}`] || 0);
                }
                totales.granTotal += parseFloat(item.total_efectivo || 0);
            });

            return totales;
        },
        rubrosDisponiblesFiltrados() {
            // 1. Extraemos los IDs de los rubros que ya están en el array del proyecto
            const idsSeleccionados = this.editForm.financiamientos.map(fin => fin.id_rubro);
            
            // 2. Retornamos la lista mapeando una propiedad "disabled" si el ID ya fue seleccionado
            return this.listaRubrosDisponibles.map(rubro => {
                return {
                    ...rubro,
                    disabled: idsSeleccionados.includes(rubro.id_rubro)
                };
            });
        },
        impactosAgrupados() {
            const grupos = {};
            this.editForm.impactos.forEach((imp, index) => {
                // Buscar el nombre del impacto en el catálogo
                const categoria = this.listaImpactosDisponibles.find(c => c.id_impactos === imp.id_impactos);
                const nombre = categoria ? categoria.nombre_impacto : 'Impacto Desconocido';
                
                if (!grupos[nombre]) {
                    grupos[nombre] = [];
                }
                
                // Guardamos la info junto con su índice original en editForm.impactos para poder eliminarlo
                grupos[nombre].push({
                    ...imp,
                    originalIndex: index
                });
            });
            return grupos;
        }
    },
    methods: {
        async obtenerConvocatorias() {
            try {
                const response = await API.get(`${this.baseUrl}/listar_convocatorias`);
                this.listaConvocatorias = response.data?.data || [];
            } catch (error) {
                console.warn("⚠️ Error al cargar convocatorias:", error);
            }
        },
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
        async uploadarpdfchivo(codPro, oldFilename = null) {
            if (!this.archivopdfSeleccionado) return null; // nada que subir
            try {
                this.uploading = true;
                const form = new FormData();
                form.append('file', this.archivopdfSeleccionado);
                form.append('codPro', codPro);
                if (oldFilename) {
                    form.append('old_filename', oldFilename); // Enviamos el nombre del archivo viejo
                }

                // Si tu backend exige otros campos (ej: tipo), añade aquí
                const resp = await API.post(`${this.baseUrl}/subir_pdf_proyecto`, form, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                console.log(resp);
                if (resp && resp.data && resp.data.filename) {
                    this.archivopdfSeleccionado = null;
                    this.archivopdfPreviewName = '';
                    this.$refs.filePDF.value = null;
                    return resp.data; // { filename, url }
                } else {
                    mostraralertas2('Error subiendo archivo', 'warning');
                    return null;
                }
            } catch (error) {
                mostraralertas2('Error subiendo archivo', 'warning');
                return null;
            } finally {
                this.uploading = false;
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
                this.habilitarcompro = true;
            } else if (nombre.includes('subdirector')) {
                this.formInt.horas = 6;
                this.habilitarcompro = true;
            } else if (nombre.includes('estudiante')){
                this.formInt.horas = 0;
                this.habilitarcompro = false;
            }else {
                this.formInt.horas = 4;
                this.habilitarcompro = true;
            }
        },
        calcularHorasReemplazo() {
            const funcionObj = this.funciones.find(f => f.id_funcion === this.formInt.id_funcion_reemplazado);
            if (!funcionObj) return;

            const nombre = funcionObj.nombre_funcion.toLowerCase();
            if (nombre.includes('director') && !nombre.includes('sub')) {
                this.formInt.horas_reemplazado = 8;
                this.habilitarcompro = true;
            } else if (nombre.includes('subdirector')) {
                this.formInt.horas_reemplazado = 6;
                this.habilitarcompro = true;
            } else if (nombre.includes('estudiante')) {
                this.formInt.horas_reemplazado = 0;
                this.habilitarcompro = false;
            }else {
                this.formInt.horas_reemplazado = 4;
                this.habilitarcompro = true;
            }
        },
        async abrirDetallesProyecto(id) {
            this.botonCargando = 'detalles_' + id;
            try {
                const [resProj, resCat] = await Promise.all([
                    API.get(`${this.baseUrl}/invi_proyectos/${id}`),
                    API.get(`${this.baseUrl}/catalogos-integrantes`)
                ]);
                this.proyectoSeleccionado = resProj.data;
                this.funciones = resCat.data.funciones;
                this.carreras = resCat.data.carreras;
                this.showModalDetalles = true;
                this.showEditModal = false;
            } catch (e) {
                console.error(e);
            }finally {
                this.botonCargando = null; // Detiene el spinner al terminar
            }
        },
        seleccionarIntegrante(int) {
            const compAsignados = (int.compromisos || []).map(c => c.detalle_compromiso);
            
            let compromisosVModel = [];
            let compromisoOtroVModel = '';

            // 2. Evaluamos cada compromiso para saber si es estándar o es "Otro"
            compAsignados.forEach(c => {
                if (this.listaCompromisos.includes(c)) {
                    compromisosVModel.push(c);
                } else {
                    // Si no está en la lista predefinida, significa que era "Otros"
                    compromisosVModel.push('Otros');
                    compromisoOtroVModel = c;
                }
            });
            this.integranteEdit = {
                id: int.id_deta_invi_proyect,
                id_deta_invi_proyect: int.id_deta_invi_proyect,
                nombre: (int.informacion_personal_d?.NombInfPer || int.informacionpersonal?.NombInfPer),
                apellido: (int.informacion_personal_d?.ApellInfPer || int.informacionpersonal?.ApellInfPer),
                apellidomaterno: (int.informacion_personal_d?.ApellMatInfPer || int.informacionpersonal?.ApellMatInfPer),
                funcion: int.funciones?.nombre_funcion || 'N/A',
                cedula: int.ciinfper_doc || int.ciinfper_est,
            };
            if(this.integranteEdit.funcion !== 'Estudiante del proyecto de investigación'){
                this.habilitarcompro = true;
            }else{
                this.habilitarcompro = false;
            }
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
                idCarr_reemplazado: int.idCarr,
                anexo_integrante2: int.anexo_integrante2,
                compromisos: compromisosVModel,
                compromiso_otro: compromisoOtroVModel
            };
            this.nuevoIntegranteData = null;
            this.cedulaBusqueda = '';
            //this.buscando = true;
        },
        async buscarNuevoIntegrante() {
            if (!this.cedulaBusqueda) return;

            // Validación local: No reemplazarse a sí mismo
            if (this.integranteEdit && this.cedulaBusqueda === this.integranteEdit.cedula) {
                this.buscando = false;
                mostraralertas2("No puedes reemplazar a un integrante por sí mismo.", "warning");
                return;
            }
            if(this.formInt.reemplazado == 1){
                this.formInt.compromisos = []; 
                this.formInt.compromiso_otro = ''; 
            
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
            this.formInt = { horas: 0, id_funcion: null, idCarr: null, reemplazado: 0,compromisos: [],        // <-- IMPORTANTE REINICIAR
                compromiso_otro: '' };
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
            if (this.formInt.compromisos.includes('Otros') && !this.formInt.compromiso_otro.trim()) {
                return mostraralertas2("Por favor, especifique el compromiso en el campo 'Otros'.", "warning");
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
                let compromisosParaBackend = [...this.formInt.compromisos];
                if (compromisosParaBackend.includes('Otros')) {
                    compromisosParaBackend = compromisosParaBackend.filter(c => c !== 'Otros');
                    if (this.formInt.compromiso_otro.trim()) {
                        compromisosParaBackend.push(this.formInt.compromiso_otro.trim());
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
                        anexo_integrante2: (this.formInt.reemplazado == 0 && anexoData) ? anexoData.filename : this.formInt.anexo_integrante2,
                        compromisos: compromisosParaBackend
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
        filtrarPorConvocatoria() {
            // Reinicia a la página 1 cuando se cambia la convocatoria
            this.GetData(1, this.searchQuery);
        },
        async GetData(page = 1, searchQuery = "") {
            this.cargando = true;

            try {
                const params = {
                    page: page,
                    search_query: searchQuery, // Parámetro para búsqueda
                    id_convocatoria: this.selectedConvocatoria
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
        handleFacultadPrioriChange() {
            if (this.editForm.proyect_multidis === 1 && this.editForm.id_facultad_priori) {
                // Si no existe dentro del array de facultades, la añadimos
                if (!this.editForm.facultades.includes(this.editForm.id_facultad_priori)) {
                    this.editForm.facultades.push(this.editForm.id_facultad_priori);
                }
            }
        },
        handleCarreraPrioriChange() {
            if (this.editForm.proyect_multidis === 1 && this.editForm.id_carr_priori) {
                if (!this.editForm.carreras.includes(this.editForm.id_carr_priori)) {
                    this.editForm.carreras.push(this.editForm.id_carr_priori);
                }
            }
        },
        async obtenerCarrerasPorFacultades() {
            let facultadesIds = [];

            if (this.editForm.proyect_multidis === 1) {
                facultadesIds = [...this.editForm.facultades];
            } else if (this.editForm.id_facultad_priori) {
                facultadesIds = [this.editForm.id_facultad_priori];
            }

            if (facultadesIds.length === 0) {
                this.carrerasCatalogo = [];
                return;
            }

            try {
                // Genera promesas dinámicas usando el endpoint existente
                const promesas = facultadesIds.map(id => API.get(`${this.baseUrl}/getCarrerasFacultad/${id}`));

                const respuestas = await Promise.all(promesas);

                let unificado = [];
                respuestas.forEach(res => {
                    if (res.data && res.data.data) {
                        unificado = [...unificado, ...res.data.data];
                    }
                });

                // Evitar posibles duplicados por idCarr
                this.carrerasCatalogo = unificado.filter((item, index, self) =>
                    index === self.findIndex((t) => t.id_carrera === item.id_carrera)
                );
            } catch (error) {
                console.error("Error cargando el catálogo de carreras:", error);
            }
        },
        actualizarObjetivosPoliticas(politicasSeleccionadasIds) {
            // Si el array está vacío o no existe, limpiamos los objetivos
            if (!politicasSeleccionadasIds || politicasSeleccionadasIds.length === 0) {
                this.objetivosPoliticasDisponibles = [];
                return;
            }

            // Usamos un Map para evitar duplicados en caso de que 
            // dos políticas apunten al mismo objetivo
            const objetivosUnicos = new Map();

            // Recorremos las políticas que cargamos desde el backend
            this.politicasPlandeDisponibles.forEach(politica => {
                // Si el ID de esta política está dentro de los seleccionados en el form...
                if (politicasSeleccionadasIds.includes(politica.id_pol_pladne)) {
                    // Y si la política trae su objeto de relación (objetivos_plandne)
                    if (politica.objetivos_plandne) {
                        const objetivo = politica.objetivos_plandne;
                        // Lo agregamos al Map usando su ID como llave
                        if (!objetivosUnicos.has(objetivo.id_obj_pol_pladne)) {
                            objetivosUnicos.set(objetivo.id_obj_pol_pladne, objetivo);
                        }
                    }
                }
            });

            // Finalmente, convertimos los valores únicos del Map a un Array
            this.objetivosPoliticasDisponibles = Array.from(objetivosUnicos.values());
        },
        ejecutarBusquedaEmpresa() {
            const query = this.searchEmpresaQuery.trim();
            
            if (query.length < 3) {
                // Reiniciar si se borra el texto o es muy corto
                this.empresaBuscada = null;
                this.empresaNoEncontrada = false;
                return;
            }

            this.buscandoEmpresa = true;
            this.empresaNoEncontrada = false;
            
            // Llamamos a la función debounced
            this.debouncedBuscarEmpresa(query);
        },
        debouncedBuscarEmpresa: debounce(async function(query) {
            try {
                // Modifica esta ruta para que coincida con la que creaste en web.php / api.php
                const response = await API.get(`${this.baseUrl}/buscar_empresa?q=${encodeURIComponent(query)}`);
                
                if (response.data && response.data.idempresa) {
                    this.empresaBuscada = response.data;
                    this.empresaNoEncontrada = false;
                } else {
                    this.empresaBuscada = null;
                    this.empresaNoEncontrada = true;
                }
            } catch (error) {
                console.error("Error buscando empresa:", error);
                this.empresaBuscada = null;
                this.empresaNoEncontrada = true; 
            } finally {
                this.buscandoEmpresa = false;
            }
        }, 600),
        agregarEmpresaSeleccionada() {
            if (!this.empresaBuscada) return;

            // Validar si ya existe para no duplicarla
            const existe = this.empresasAgregadas.some(emp => emp.idempresa === this.empresaBuscada.idempresa);
            
            if (existe) {
                mostraralertas2('Esta institución ya ha sido añadida al proyecto.', 'warning');
                return;
            }

            // Agregamos al arreglo de visualización
            this.empresasAgregadas.push(this.empresaBuscada);
            
            // Limpiamos el buscador
            this.searchEmpresaQuery = '';
            this.empresaBuscada = null;
            this.empresaNoEncontrada = false;
            
            mostraralertas2('Institución añadida correctamente.', 'success');
        },
        // 4. Quitar empresa de la tabla
        quitarEmpresa(index) {
            this.empresasAgregadas.splice(index, 1);
        },
        contarPalabras(texto) {
            if (!texto) return 0;
            // Quitamos espacios extra y saltos de línea para contar palabras reales
            return texto.trim().split(/\s+/).filter(word => word.length > 0).length;
        },
        recalcularAlturasCargadas() {
            // Añadimos un pequeño retraso mínimo por las animaciones de transición de Tailwind
            setTimeout(() => {
                const antTextarea = this.$refs.antecedentesTextarea;
                const jusTextarea = this.$refs.justificacionTextarea;
                const descriTextarea = this.$refs.descripcionTextarea;
                const diagnTextarea = this.$refs.diagnostico_problemaTextarea;
                const artiTextarea = this.$refs.articulaTextarea;
                const idenTextarea = this.$refs.identPoblaobjTextarea;
                const factTextarea = this.$refs.facExitTextarea;
                const restsuTextarea = this.$refs.restSupuTextarea;
                const bienTextarea = this.$refs.bienesTextarea;
                const servTextarea = this.$refs.serviTextarea;
                const servybiTextarea = this.$refs.servibienTextarea;
                const textareasBien = this.$refs.detallebTextarea;
                const servbTextarea = this.$refs.servibienTextarea;
                const metoTextarea = this.$refs.metodologiaTextarea;
                const viabilidadTextarea = this.$refs.viabTextarea;
                const equiptTextarea = this.$refs.quipteTextarea;
                const perderTextarea = this.$refs.perderproTextarea;
                const impacdesTextarea = this.$refs.impactodesTextarea;
                const sostTextarea = this.$refs.sost_socTextarea;
                const trasnfTextarea = this.$refs.trasnf_tTextarea;
                const artctTextarea = this.$refs.artciTextarea;
                const protTextarea = this.$refs.protoTextarea;
                const repTextarea = this.$refs.reproTextarea;
                const spinTextarea = this.$refs.spinofTextarea;

                if (antTextarea) {
                    antTextarea.style.height = 'auto';
                    antTextarea.style.height = antTextarea.scrollHeight + 'px';
                }
                if (jusTextarea) {
                    jusTextarea.style.height = 'auto';
                    jusTextarea.style.height = jusTextarea.scrollHeight + 'px';
                }
                if (descriTextarea) {
                    descriTextarea.style.height = 'auto';
                    descriTextarea.style.height = descriTextarea.scrollHeight + 'px';
                }
                if (diagnTextarea) {
                    diagnTextarea.style.height = 'auto';
                    diagnTextarea.style.height = diagnTextarea.scrollHeight + 'px';
                }
                if (artiTextarea) {
                    artiTextarea.style.height = 'auto';
                    artiTextarea.style.height = artiTextarea.scrollHeight + 'px';
                }
                if (idenTextarea) {
                    idenTextarea.style.height = 'auto';
                    idenTextarea.style.height = idenTextarea.scrollHeight + 'px';
                }
                if (factTextarea) {
                    factTextarea.style.height = 'auto';
                    factTextarea.style.height = factTextarea.scrollHeight + 'px';
                }
                if (restsuTextarea) {
                    restsuTextarea.style.height = 'auto';
                    restsuTextarea.style.height = restsuTextarea.scrollHeight + 'px';
                }
                if (servbTextarea) {
                    servbTextarea.style.height = 'auto';
                    servbTextarea.style.height = servbTextarea.scrollHeight + 'px';
                }
                if (bienTextarea) {
                    bienTextarea.style.height = 'auto';
                    bienTextarea.style.height = bienTextarea.scrollHeight + 'px';
                }
                if (servTextarea) {
                    servTextarea.style.height = 'auto';
                    servTextarea.style.height = servTextarea.scrollHeight + 'px';
                }
                if (servybiTextarea) {
                    servybiTextarea.style.height = 'auto';
                    servybiTextarea.style.height = servybiTextarea.scrollHeight + 'px';
                }
                if (metoTextarea) {
                    metoTextarea.style.height = 'auto';
                    metoTextarea.style.height = metoTextarea.scrollHeight + 'px';
                }
                if (viabilidadTextarea) {
                    viabilidadTextarea.style.height = 'auto';
                    viabilidadTextarea.style.height = viabilidadTextarea.scrollHeight + 'px';
                }
                if (equiptTextarea) {
                    equiptTextarea.style.height = 'auto';
                    equiptTextarea.style.height = equiptTextarea.scrollHeight + 'px';
                }
                if (perderTextarea) {
                    perderTextarea.style.height = 'auto';
                    perderTextarea.style.height = perderTextarea.scrollHeight + 'px';
                }
                if (impacdesTextarea) {
                    impacdesTextarea.style.height = 'auto';
                    impacdesTextarea.style.height = impacdesTextarea.scrollHeight + 'px';
                }
                if (sostTextarea) {
                    sostTextarea.style.height = 'auto';
                    sostTextarea.style.height = sostTextarea.scrollHeight + 'px';
                }
                if (trasnfTextarea) {
                    trasnfTextarea.style.height = 'auto';
                    trasnfTextarea.style.height = trasnfTextarea.scrollHeight + 'px';
                }
                if (artctTextarea) {
                    artctTextarea.style.height = 'auto';
                    artctTextarea.style.height = artctTextarea.scrollHeight + 'px';
                }
                if (protTextarea) {
                    protTextarea.style.height = 'auto';
                    protTextarea.style.height = protTextarea.scrollHeight + 'px';
                }
                if (repTextarea) {
                    repTextarea.style.height = 'auto';
                    repTextarea.style.height = repTextarea.scrollHeight + 'px';
                }
                if (spinTextarea) {
                    spinTextarea.style.height = 'auto';
                    spinTextarea.style.height = spinTextarea.scrollHeight + 'px';
                }
                if (textareasBien && textareasBien.length > 0) {
                    textareasBien.forEach(textarea => {
                        if (textarea) {
                            textarea.style.height = 'auto';
                            textarea.style.height = textarea.scrollHeight + 'px';
                        }
                    });
                }
                const textareasNac = this.$refs.detalleporcTextarea;
                if (textareasNac && textareasNac.length > 0) {
                    textareasNac.forEach(textarea => {
                        if (textarea) {
                            textarea.style.height = 'auto';
                            textarea.style.height = textarea.scrollHeight + 'px';
                        }
                    });
                }

                const textareasImport = this.$refs.detalleimportTextarea;
                if (textareasImport && textareasImport.length > 0) {
                    textareasImport.forEach(textarea => {
                        if (textarea) {
                            textarea.style.height = 'auto';
                            textarea.style.height = textarea.scrollHeight + 'px';
                        }
                    });
                }
                
            }, 50); // 50ms bastan para que el DOM se dibuje tras la transición v-else-if
        },
        calcularTotalBeneficiarios() {
            // Convertimos a entero asegurando que si está vacío se cuente como 0
            const hombres = parseInt(this.editForm.proyect_num_direct_hombres) || 0;
            const mujeres = parseInt(this.editForm.proyect_num_direct_mujeres) || 0;
            
            this.editForm.proyect_total_num_direct = hombres + mujeres;
        },
        ajustarAlturaTextarea(event) {
            const el = event.target;
            el.style.height = 'auto'; // Resetea la altura para recalcular correctamente
            el.style.height = el.scrollHeight + 'px';
        },
        actualizarPorcentajes(item){
            if (item.porcent_nacio > 100) item.porcent_nacio = 100;
            if (item.porcent_nacio < 0) item.porcent_nacio = 0;

            // 2. Calcular automáticamente el importado (100 - nacional)
            if (item.porcent_nacio !== null && item.porcent_nacio !== '') {
                // Redondeamos a 2 decimales para evitar bugs nativos de JS con los flotantes
                item.porcent_importado = parseFloat((100 - item.porcent_nacio).toFixed(2));
            } else {
                item.porcent_importado = 0; // Por defecto
            }

            // 3. Manejar el bloqueo de 'Detalle Importado'
            if (item.porcent_importado <= 0) {
                // Si el importado es 0 (o menos), se pone "N/A"
                item.detalle_insu_import = 'N/A';
            } else {
                // Si el importado es mayor a 0 y estaba en "N/A", lo limpiamos para que escriba
                if (item.detalle_insu_import === 'N/A') {
                    item.detalle_insu_import = '';
                }
            }
        },
        agregarAporteUtlvt() {
            this.editForm.aportes_utlvt.push({
                actividad: '',
                valor: ''
            });
        },
        agregarAporteInst(idempresa) {
            this.editForm.aportes_inst.push({
                idempresa: idempresa,
                actividad: '',
                valor: ''
            });
        },
        eliminarAporteUtlvt(index) {
            this.editForm.aportes_utlvt.splice(index, 1);
        },
        eliminarAporteInst(index) {
            this.editForm.aportes_inst.splice(index, 1);
        },
        obtenerCantidadAportesEmpresa(idempresa) {
            return this.editForm.aportes_inst.filter(a => a.idempresa === idempresa).length;
        },
        validarMonto(objeto, propiedad) {
            let val = objeto[propiedad];
            // Reemplaza cualquier carácter que no sea dígito, punto o coma
            val = val.replace(/[^0-9.,]/g, '');
            objeto[propiedad] = val;
        },
        limpiarYConvertirAFloat(valor) {
           if (valor === null || valor === undefined || valor === '') return 0;
            
            // Si ya es un número traído directamente de la API, lo usamos tal cual
            if (typeof valor === 'number') return valor;
            
            let limpio = String(valor).trim();
            
            // Si el texto contiene una coma (ej: 1.250,50 -> Ingresado por el usuario)
            if (limpio.includes(',')) {
                // Quitamos los puntos de miles y cambiamos la coma por punto decimal
                limpio = limpio.replace(/\./g, '').replace(',', '.');
            } else {
                // Si no tiene coma, asumimos que es formato estándar con decimal (ej: 1250.50)
                // Quitamos solo posibles comas de miles gringas por precaución
                limpio = limpio.replace(/,/g, '');
            }
            
            let num = parseFloat(limpio);
            return isNaN(num) ? 0 : num;
        },
        formatearMonedaVisual(valor) {
            return new Intl.NumberFormat('es-EC', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2
            }).format(valor);
        },
        validarFechas() {
            // 1. Validar que el inicio no sea menor a la presentación
            if (this.editForm.proyect_fecha_pres && this.editForm.fechainicio) {
                if (this.editForm.fechainicio < this.editForm.proyect_fecha_pres) {
                    mostraralertas2('La fecha de inicio no puede ser anterior a la presentación', 'warning');
                    this.editForm.fechainicio = this.editForm.proyect_fecha_pres;
                }
            }

            // 2. Calcular los meses basados estrictamente en AÑOS (Inclusivo)
            if (this.editForm.fechainicio && this.editForm.fechafin) {
                const f1 = new Date(this.editForm.fechainicio);
                const f2 = new Date(this.editForm.fechafin);

                // Validar que el año final no sea anterior al año inicial
                if (f2.getFullYear() < f1.getFullYear()) {
                    this.editForm.fechafin = '';
                    this.editForm.proyect_duracion_mes = 0;
                    return;
                }

                // Calculamos la diferencia de años y sumamos 1 para incluir el año de inicio
                // Ejemplo: 2027 - 2025 = 2. Sumamos 1 = 3 años.
                let aniosTotales = (f2.getFullYear() - f1.getFullYear()) + 1;
                
                // Convertimos los años a meses
                let mesesTotales = aniosTotales * 12;

                this.editForm.proyect_duracion_mes = mesesTotales;
            } else {
                this.editForm.proyect_duracion_mes = 0;
            }
        },
        cancelarNuevoPDF() {
            this.archivopdfSeleccionado = null;
            this.archivopdfPreviewName = '';
            if (this.$refs.filePDF) {
                this.$refs.filePDF.value = null;
            }
        },
        async abrirEdicion(id) {
            this.proyectoEditandoId = id;
            this.showEditModal = true;
            this.cargandoEdicion = true;
            this.activeTab = 'generales';
            this.yaTraducido = false;
            this.botonCargando = 'editar_' + id;

            try {
                // Asegúrate de crear esta ruta en tus routes/api.php de Laravel
                const response = await API.get(`${this.baseUrl}/getEdicionDatos/${id}`, {
                    params: {
                        id_departamento: this.UsuarioInfo.departamentoUsuario
                    }
                });
                const data = response.data;
                
                this.objetivosPeiDisponibles = data.objetivos_pei;
                
                this.politicasPlandeDisponibles = data.politicas_plandne || [];
                //Objetivos del Plan de Desarrollo para el Nuevo Ecuador 2024 • 2025:
                this.objetivosPoliticasDisponibles = data.objetivos_politicas_seleccionadas || [];
                
                this.objetivosODSDisponibles = data.ods || [];
                this.facultadesCatalogo = data.facultades_catalogo || [];
                this.dominiosCatalogo = data.dominios_catalogo || [];
                this.convocatoriasCatalogo = data.convocatorias_catalogo || [];
                this.lineasCatalogo = data.lineas_catalogo || [];
                this.sublineasCatalogo = data.sublineas_catalogo || [];
                this.unescoCatalogo = data.unesco_catalogo || [];
                this.tip_proyectCatalogo = data.tip_proyect_catalogo || [];
                this.zonasCatalogo = data.zonas_catalogo || [];
                this.detallesZonasCatalogo = data.detalles_zonas_catalogo || [];
                this.provinciasCatalogo = data.provincias_catalogo || [];
                this.cantonesCatalogo = data.cantones_catalogo || [];
                this.parroquiasCatalogo = data.parroquias_catalogo || [];
                this.listaRubrosDisponibles = data.rubros_catalogo || [];

                let mapeoObjetivos = data.proyecto.invi_obj_proyectos.map(obj => ({
                    id_obj_proy: obj.id_obj_proy,
                    tipo_obj_proy: obj.tipo_obj_proy,
                    detalle_obj_proy: obj.detalle_obj_proy,
                    // Aseguramos que siempre sean arrays (mapeando con las propiedades exactas de tu Base de Datos)
                    indicadores: obj.invi_indicadores || [],
                    metas: obj.invi_metas || [],
                    supuestos: obj.invi_supuestos || [],
                    medios_verificacion: obj.invi_medios_verificacion || [],
                    prod_verificables: obj.invi_prod_verificables || [],
                }));
                this.empresasAgregadas = data.empresas_seleccionadas || [];
                this.empresasAgregadas2 = data.empresas_seleccionadas2 || [];
                this.asignaturasDisponibles = data.asignaturas_disponibles || [];
                const calc = data.calculo_integrantes || {};
                const inte = data.integrantes_activos
                // Extraemos todas las actividades estructuradas
                let actividadesExtraidas = [];
                (data.proyecto.invi_obj_proyectos || []).forEach(obj => {
                    if (obj.invi_actividades && obj.invi_actividades.length > 0) {
                        obj.invi_actividades.forEach(act => {
                            actividadesExtraidas.push({
                                ...act,
                                invi_subactividad: act.invi_subactividad || [],
                                invi_actprod_verificables: act.invi_actprod_verificables || [],
                                invi_actmedios_verificacion: act.invi_actmedios_verificacion || [],
                                invi_actindicadores: act.invi_actindicadores || [],
                                invi_actsupuestos: act.invi_actsupuestos || []
                            });
                        });
                    }
                });
                let adquisicionesMapeadas = [];
                if (data.proyecto.invi_detalle_adqui && data.proyecto.invi_detalle_adqui.length > 0) {
                    adquisicionesMapeadas = data.proyecto.invi_detalle_adqui.map(detalle => {
                        return {
                            id_adquisicion: detalle.invi_adquisicion.id_adquisicion,
                            tipo_adqui: detalle.invi_adquisicion.tipo_adqui || '',
                            detalle: detalle.invi_adquisicion.detalle || '',
                            porcent_nacio: detalle.invi_adquisicion.porcent_nacio || 0,
                            detalle_iinsu_nac: detalle.invi_adquisicion.detalle_iinsu_nac || '',
                            porcent_importado: detalle.invi_adquisicion.porcent_importado || 0,
                            detalle_insu_import: detalle.invi_adquisicion.detalle_insu_import || ''
                        };
                    });
                }
                let financiamientosMapeados = [];
                if (data.proyecto.invi_detalle_financia && data.proyecto.invi_detalle_financia.length > 0) {
                    financiamientosMapeados = data.proyecto.invi_detalle_financia.map(det => {
                        return {
                            id_det_financia: det.id_det_financia,
                            id_rubro: det.id_rubro,
                            cantidad: det.cantidad || 0,
                            valor: det.valor || 0,
                            utlvte_anio1: det.utlvte_anio1 || 0,
                            utlvte_anio2: det.utlvte_anio2 || 0,
                            utlvte_anio3: det.utlvte_anio3 || 0,
                            utlvte_anio4: det.utlvte_anio4 || 0,
                            utlvte_anio5: det.utlvte_anio5 || 0,
                            otros_anio1:  det.otros_anio1 || 0,
                            otros_anio2:  det.otros_anio2 || 0,
                            otros_anio3:  det.otros_anio3 || 0,
                            otros_anio4:  det.otros_anio4 || 0,
                            otros_anio5:  det.otros_anio5 || 0,
                            total_efectivo: det.total_efectivo || 0
                        };
                    });
                }
                this.listaImpactosDisponibles = data.impactos_catalogo || [];
                let impactosMapeados = [];
                if (data.proyecto.invi_det_impactos_esperados && data.proyecto.invi_det_impactos_esperados.length > 0) {
                    impactosMapeados = data.proyecto.invi_det_impactos_esperados.map(det => {
                        return {
                            id_det_impactos_esp: det.id_det_impactos_esp,
                            id_impactos: det.id_impactos,
                            descripcion_general: det.descripcion_general || ''
                        };
                    });
                }
                let difusionMapeada = [];
                if (data.proyecto.invi_detalle_difusion && data.proyecto.invi_detalle_difusion.length > 0) {
                    difusionMapeada = data.proyecto.invi_detalle_difusion.map(det => {
                        return {
                            id_det_difusion: det.id_det_difusion,
                            id_difusion: det.id_difusion,
                            costo: det.costo,
                            nombre_actividad: det.invi_difusion ? det.invi_difusion.nombre_actividad : ''
                        };
                    });
                }
                const nombreArchivo = data.proyecto.proyect_archivo;
                console.log(data.proyecto)
                this.editForm = {
                    proyect_id: data.proyecto.proyect_id,
                    proyect_cod: data.proyecto.proyect_cod || '',
                    proyect_nombre: data.proyecto.proyect_nombre,
                    proyect_titulo: data.proyecto.proyect_titulo || '',
                    proyect_nombre_en: data.proyecto.proyect_nombre_en || '',
                    proyect_titulo_en: data.proyecto.proyect_titulo_en || '',
                    proyect_multidis: data.proyecto.proyect_multidis,
                    //Objetivos del Plan Estratégico Institucional
                    objetivos: data.seleccionados || [],
                    //Políticas del Plan de Desarrollo para el Nuevo Ecuador 2024 • 2025
                    politicas: data.politicas_seleccionadas || [],
                    //Agenda 2030 y los Objetivos de desarrollo sostenible una oportunidad para América Latina y el Caribe
                    ods: data.ods_seleccionadas || [],
                    //Nombre de Facultad/es: 
                    facultades: data.facultades_seleccionadas || [],
                    id_facultad_priori: data.id_facultad_priori || '',
                    //Carrera/s
                    carreras: data.carreras_seleccionadas || [],
                    id_carr_priori: data.id_carr_priori || '',
                    //Dominios académicos
                    dominios_humanisticos: data.dominios_seleccionados || [],
                    //No. Convocatoria
                    id_convocatoria: data.proyecto.id_convocatoria || '',
                    sublineas_investigacion: [],
                    unesco_areas: data.unesco_seleccionadas || [],
                    id_tip_invi_proy: data.proyecto.id_tip_invi_proy || '',
                    proyect_cobertura: data.proyecto.proyect_cobertura || '',
                    id_zona_plan: data.cobertura_guardada?.id_zona_plan || '',
                    provincias:   data.cobertura_guardada?.provincias || [],
                    cantones:     data.cobertura_guardada?.cantones || [],
                    parroquias:   data.cobertura_guardada?.parroquias || [],
                    objetivos_marco_logico: mapeoObjetivos,
                    proyect_antecedentes: data.proyecto.proyect_antecedentes || '',
                    proyect_justificacion: data.proyecto.proyect_justificacion || '',
                    empresas: [...this.empresasAgregadas],
                    aportes_utlvt: data.aportes_utlvt || [],
                    aportes_inst: data.aportes_inst || [],
                    proyect_fecha_pres: data.proyecto.proyect_fecha_pres || '',
                    fechainicio: data.proyecto.fechainicio || '',
                    fechafin: data.proyecto.fechafin || '',
                    proyect_duracion_mes: data.proyecto.proyect_duracion_mes || '',
                    proyect_estado: data.proyecto.proyect_estado || '',
                    proyect_desc_situ_act: data.proyecto.proyect_desc_situ_act || '',
                    proyect_diag_probl: data.proyecto.proyect_diag_probl || '',
                    proyect_contribucion_soci: data.proyecto.proyect_contribucion_soci || '',
                    asignaturas: data.asignaturas_seleccionadas || [],
                    proyec_ident_poblaobj: data.proyecto.proyec_ident_poblaobj || '',
                    proyect_num_direct_hombres: data.proyecto.proyect_num_direct_hombres || '',
                    proyect_num_direct_mujeres: data.proyecto.proyect_num_direct_mujeres || '',
                    proyect_total_num_direct: data.proyecto.proyect_total_num_direct || '',
                    proyect_num_personas_div_fun: data.proyecto.proyect_num_personas_div_fun || '',
                    proyect_total_num_indirect: data.proyecto.proyect_total_num_indirect || '',
                    proyect_num_doce_h: data.proyecto.proyect_num_doce_h ?? calc.docentes_h ?? 0,
                    proyect_num_doce_m: data.proyecto.proyect_num_doce_m ?? calc.docentes_m ?? 0,
                    proyect_num_doce_part: data.proyecto.proyect_num_doce_part ?? calc.docentes_total ?? 0,

                    // Estudiantes (se usan los valores guardados en BD o se calculan automáticamente si están nulos)
                    proyect_num_est_h: data.proyecto.proyect_num_est_h ?? calc.estudiantes_h ?? 0,
                    proyect_num_est_m: data.proyecto.proyect_num_est_m ?? calc.estudiantes_m ?? 0,
                    proyect_num_est_part: data.proyecto.proyect_num_est_part ?? calc.estudiantes_total ?? 0,
                    proyect_fact_exito: data.proyecto.proyect_fact_exito || '',
                    proyect_rest_supu: data.proyecto.proyect_rest_supu || '',
                    actividades: actividadesExtraidas,
                    proyect_bienes: data.proyecto.proyect_bienes || '',
                    proyect_servicios: data.proyecto.proyect_servicios || '',
                    proyect_bienes_servicios: data.proyecto.proyect_bienes_servicios || '',
                    adquisiciones: adquisicionesMapeadas,
                    proyect_categorizacion: data.proyecto.proyect_categorizacion || '',
                    proyect_metodologia: data.proyecto.proyect_metodologia || '',
                    financiamientos: financiamientosMapeados,
                    proyect_viabilidad_tec: data.proyecto.proyect_viabilidad_tec || '',
                    proyect_equip_tec: data.proyecto.proyect_equip_tec || '',
                    proyect_no_ejecuta: data.proyecto.proyect_no_ejecuta || '',
                    impactos: impactosMapeados,
                    proyect_sostenibilidad_soc: data.proyecto.proyect_sostenibilidad_soc || '',
                    proyect_transf_tecn: data.proyecto.proyect_transf_tecn || '',
                    proyect_art_cientificos: data.proyecto.proyect_art_cientificos || '',
                    proyect_prototipos: data.proyecto.proyect_prototipos || '',
                    proyect_reg_propin: data.proyecto.proyect_reg_propin || '',
                    proyect_empr_spin: data.proyecto.proyect_empr_spin || '',
                    difusion: difusionMapeada,
                    bibliografias: data.proyecto.invi_bibliografias ? [...data.proyecto.invi_bibliografias] : [],
                    proyect_archivo: nombreArchivo || '',
                    pdfurlarchivo: nombreArchivo
                        ? `http://192.168.1.112:8082/Documentos/INVESTIGACION/Evidencia_Proyect/${data.proyecto.proyect_cod}/${nombreArchivo}`
                        : null

                };
                if (this.aniosProyecto.length > 0) {
                    this.anioSeleccionadoTab = this.aniosProyecto[0].id;
                }
                this.calcularTotalDocentes();
                this.calcularTotalEstudiantes();
            } catch (error) {
                mostraralertas2('Error al cargar datos del proyecto', 'error');
                console.error(error);
                this.cerrarEdicion();
            } finally {
                this.cargandoEdicion = false;
                this.botonCargando = null;
            }
        },
        resetFormFinancia() {
            return {
                id_rubro: '', cantidad: 1, valor: 0,
                utlvte_anio1: 0, utlvte_anio2: 0, utlvte_anio3: 0, utlvte_anio4: 0, utlvte_anio5: 0,
                otros_anio1: 0, otros_anio2: 0, otros_anio3: 0, otros_anio4: 0, otros_anio5: 0,
                total_efectivo: 0
            };
        },
        abrirModalFinancia() {
            this.indexFinanciaEditando = null;
            this.formFinancia = this.resetFormFinancia();
            this.showModalFinancia = true;
        },
        cerrarModalFinancia() {
            this.showModalFinancia = false;
            this.indexFinanciaEditando = null;
        },
        calcularTotalModal() {
            let total = 0;
            for (let i = 1; i <= 5; i++) {
                total += parseFloat(this.formFinancia[`utlvte_anio${i}`] || 0);
                total += parseFloat(this.formFinancia[`otros_anio${i}`] || 0);
            }
            this.formFinancia.total_efectivo = total;
        },
        limpiarNumeros(event) {
            // Previene que escriban la letra 'e' u otros caracteres no válidos en inputs tipo number
            const val = parseFloat(event.target.value);
            if(isNaN(val) || val < 0) {
                event.target.value = 0;
            }
        },
        guardarFinanciamientoEnTabla() {
            if (this.indexFinanciaEditando !== null && this.indexFinanciaEditando >= 0) {
                // EDITAR: Reemplazamos los datos en la posición correspondiente
                this.editForm.financiamientos[this.indexFinanciaEditando] = { ...this.formFinancia };
            } else {
                // AGREGAR: Si el índice es null, agregamos un elemento nuevo
                this.editForm.financiamientos.push({ ...this.formFinancia });
            }

            this.cerrarModalFinancia();
        },
        editarFinanciamiento(item, index) {
            // Guardamos el índice actual por si necesitas actualizarlo directamente en el array
            this.indexFinanciaEditando = index;

            // Clonamos el objeto para evitar mutaciones no deseadas antes de guardar
            this.formFinancia = JSON.parse(JSON.stringify(item));

            // Abrimos el modal de financiamiento
            this.showModalFinancia = true;
        },
        eliminarFinanciamiento(index) {
            this.editForm.financiamientos.splice(index, 1);
        },
        getNombreRubro(id) {
            const rubro = this.listaRubrosDisponibles.find(r => r.id_rubro === id);
            return rubro ? rubro.nombre_rubro : 'Rubro Desconocido';
        },
        formatoDinero(valor) {
            return parseFloat(valor || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        calcularPorcentaje(valor) {
            const granTotal = this.totalesFinanciamiento.granTotal;
            if (granTotal === 0) return "0.00";
            return ((parseFloat(valor || 0) / granTotal) * 100).toFixed(2);
        },
        abrirModalImpacto() {
            this.indexImpactoEditando = null;
            this.formImpacto = { id_impactos: '', descripcion_general: '' };
            this.showModalImpacto = true;
        },
        cerrarModalImpacto() {
            this.showModalImpacto = false;
            this.indexImpactoEditando = null;
        },
        guardarImpactoEnTabla() {
            // Empujamos el nuevo impacto al array
            if(this.indexImpactoEditando !== null && this.indexImpactoEditando >= 0) {
                // EDITAR: Reemplazamos los datos en la posición correspondiente
                this.editForm.impactos[this.indexImpactoEditando] = { ...this.formImpacto };
            } else {
                // AGREGAR: Si el índice es null, agregamos un elemento nuevo
                this.editForm.impactos.push({ ...this.formImpacto });
            }
            this.cerrarModalImpacto();
        },
        editarImpacto(item, index) {
            this.indexImpactoEditando = index;
            this.formImpacto = JSON.parse(JSON.stringify(item));
            this.showModalImpacto = true;
        },
        eliminarImpacto(indexOriginal) {
            // Se elimina usando el índice que tenía en this.editForm.impactos
            this.editForm.impactos.splice(indexOriginal, 1);
        },
        abrirModalDifusion() {
            this.indexDifusionEditando = null;
            this.formDifusion = { id_det_difusion: null, id_difusion: null, nombre_actividad: '', costo: '' };
            this.showDivModalDifusion = true;
        },
        cerrarModalDifusion() {
            this.showDivModalDifusion = false;
            this.indexDifusionEditando = null;
        },
        validarNumeroCosto(event) {
            // Reemplaza cualquier caracter que no sea un dígito o un punto decimal
            let valor = event.target.value.replace(/[^0-9.]/g, '');
            // Evitar múltiples puntos decimales
            const partes = valor.split('.');
            if (partes.length > 2) {
                valor = partes[0] + '.' + partes.slice(1).join('');
            }
            this.formDifusion.costo = valor;
        },
        guardarDifusionEnTabla() {
            if (this.indexDifusionEditando !== null && this.indexDifusionEditando >= 0) {
                // Modo Edición
                this.editForm.difusion[this.indexDifusionEditando] = { ...this.formDifusion };
            } else {
                // Modo Creación
                this.editForm.difusion.push({ ...this.formDifusion });
            }
            this.cerrarModalDifusion();
        },
        editarDifusion(item, index) {
            this.indexDifusionEditando = index;
            // Clonamos el objeto para no editar en vivo la tabla
            this.formDifusion = JSON.parse(JSON.stringify(item));
            this.showDivModalDifusion = true;
        },
        eliminarDifusion(index) {
            this.editForm.difusion.splice(index, 1);
        },
        agregarAdquisicion() {
            this.editForm.adquisiciones.push({
                id_adquisicion: null, // Null porque es nuevo y no está en BD aún
                tipo_adqui: '',
                detalle: '',
                porcent_nacio: 0,
                detalle_iinsu_nac: '',
                porcent_importado: 0,
                detalle_insu_import: ''
            });
        },
        async abrirMenuContextual(event, campo) {
            // 1. Guardar datos de la selección
            this.campoObjetivoCita = campo;
            this.posicionCursorCita = event.target.selectionStart;

            // 2. Dimensiones estáticas del menú (w-48 = 192px aprox, alto 55px)
            const menuWidth = 195;
            const menuHeight = 55;

            // 3. Obtener el modal padre (el que tiene la clase transform)
            // event.target es el textarea donde dimos clic
            const modal = event.target.closest('.transform');

            if (modal) {
                // Obtenemos las coordenadas exactas del modal en la pantalla
                const modalRect = modal.getBoundingClientRect();

                // 4. RESTAMOS la posición del modal a la posición del clic
                // Así convertimos coordenadas de "pantalla" a coordenadas de "modal"
                let relativeX = event.clientX - modalRect.left;
                let relativeY = event.clientY - modalRect.top;

                // 5. Ajuste inteligente: si choca con el borde DERECHO del modal, abrir a la izquierda
                if (relativeX + menuWidth > modalRect.width) {
                    relativeX = relativeX - menuWidth;
                }

                // 6. Ajuste inteligente: si choca con el borde INFERIOR del modal, abrir hacia arriba
                if (relativeY + menuHeight > modalRect.height) {
                    relativeY = relativeY - menuHeight;
                }

                // Asignamos las coordenadas limpias
                this.menuX = relativeX;
                this.menuY = relativeY;
            } else {
                // Fallback de seguridad
                this.menuX = event.clientX;
                this.menuY = event.clientY;
            }

            // 7. Mostramos el menú (saldrá a la primera, sin necesidad de doble clic)
            this.mostrarMenu = true;
        },
        cerrarMenuContextual() {
            this.mostrarMenu = false;
        },
        prepararModalBibliografia() {
            this.cerrarMenuContextual();
            this.formBib = { autor: '', anio: '', titulo: '', editorial_fuente: '' };
            this.showModalBib = true;
        },
        guardarCitaEnTexto() {
            // 1. Evitar duplicados exactos en el array de bibliografías
            const existe = this.editForm.bibliografias.find(b => 
                b.autor.toLowerCase().trim() === this.formBib.autor.toLowerCase().trim() && 
                b.anio.trim() === this.formBib.anio.trim() &&
                b.titulo.toLowerCase().trim() === this.formBib.titulo.toLowerCase().trim()
            );

            if (!existe) {
                this.editForm.bibliografias.push({
                    ...this.formBib,
                    seccion_campo: this.campoObjetivoCita
                });
            }

            // 2. Insertar la cita en formato APA (Autor, Año) en el texto
            const citaAPA = ` (${this.formBib.autor}, ${this.formBib.anio})`;
            const textoActual = this.editForm[this.campoObjetivoCita] || '';
            
            this.editForm[this.campoObjetivoCita] = 
                textoActual.substring(0, this.posicionCursorCita) + 
                citaAPA + 
                textoActual.substring(this.posicionCursorCita);

            // 3. Cerrar modal y notificar
            this.showModalBib = false;
        },
        eliminarBibliografia(index) {
            this.editForm.bibliografias.splice(index, 1);
        },
        formatearNombreCampo(campo) {
            // Un pequeño helper para que se vea bonito en la pestaña Bibliografías
            const diccionario = {
                'proyect_antecedentes': 'Antecedentes',
                'proyect_justificacion': 'Justificación',
                'proyect_desc_situ_act': ' Descripción de la situación actual',
                'proyect_diag_probl': ' Diagnóstico de los problemas',
                'proyect_contribucion_soci': 'Contribución a la sociedad',
                'proyec_ident_poblaobj': 'Identificación y caracterización de la población objetiva beneficiarios',
                'proyect_fact_exito': ' Factores críticos de éxito',
                'proyect_rest_supu': 'Restricciones/Supuestos',
                'proyect_metodologia': 'Metodología',
                'proyect_viabilidad_tec': 'Viabilidad Técnica',
                'proyect_equip_tec': 'Equipamiento Tecnológico Disponible',
                'proyect_no_ejecuta': '¿Qué perdería la provincia si el proyecto no se ejecuta en este periodo?',
                
                // Añade otros campos si decides habilitar el clic derecho en ellos
            };
            return diccionario[campo] || campo;
        },
        eliminarAdquisicion(index) {
            this.editForm.adquisiciones.splice(index, 1);
        },
        calcularTotalDocentes() {
            const h = parseInt(this.editForm.proyect_num_doce_h) || 0;
            const m = parseInt(this.editForm.proyect_num_doce_m) || 0;
            this.editForm.proyect_num_doce_part = h + m;
        },
        calcularTotalEstudiantes() {
            const h = parseInt(this.editForm.proyect_num_est_h) || 0;
            const m = parseInt(this.editForm.proyect_num_est_m) || 0;
            this.editForm.proyect_num_est_part = h + m;
        },
        obtenerObjetivo(tipo) {
            return this.editForm.objetivos_marco_logico.find(o => o.tipo_obj_proy === tipo);
        },
        obtenerEspecificos() {
            return this.editForm.objetivos_marco_logico.filter(o => o.tipo_obj_proy === 'especifico');
        },
        eliminarObjetivo(objRef) {
            if(confirm("¿Estás seguro de eliminar este objetivo específico?")) {
                this.editForm.objetivos_marco_logico = this.editForm.objetivos_marco_logico.filter(o => o !== objRef);
            }
        },
        abrirModalMarcoLogico(tipo, index = null, objExistente = null) {
            this.indiceEditandoML = index;
            
            // Si vamos a editar
            if (index !== null) {
                let objOriginal = tipo === 'especifico' ? objExistente : this.obtenerObjetivo(tipo);
                // Clonamos profundamente para no afectar el array original hasta darle a "Guardar"
                this.modalFormML = JSON.parse(JSON.stringify(objOriginal));
            } else {
                // Si es nuevo
                this.modalFormML = {
                    id_obj_proy: null,
                    tipo_obj_proy: tipo,
                    detalle_obj_proy: '',
                    indicadores: [{ detalle_indicador: '' }],
                    metas: [{ detalle_metas: '' }],
                    supuestos: [{ detalle_supuestos: '' }],
                    medios_verificacion: [{ detalle_medio_verifica: '' }],
                    prod_verificables: tipo === 'especifico' ? [{ detalle_prod_verif: '' }] : []
                };
            }
            this.showModalMarcoLogico = true;
            this.$nextTick(() => {
                const textareas = document.querySelectorAll('#modalMarcoLogico textarea');
                textareas.forEach(el => {
                        el.style.height = 'auto';
                        el.style.height = el.scrollHeight + 'px';
                });
            });
        },
        abrirModalActividad(actividad = null, index = null) {
            this.indiceActividadEditando = index;

            if (actividad) {
                this.modalActividad = JSON.parse(JSON.stringify(actividad));
            } else {
                this.modalActividad = {
                    id_actividades: null,
                    id_obj_proy: this.obtenerEspecificos()[0]?.id_obj_proy || '',
                    nom_actividad: '',
                    responsables: '',
                    fecha_desde: '',
                    fecha_hasta: '',
                    horas: 0,
                    detalle_anio: this.anioSeleccionadoTab, // Por defecto toma el año seleccionado
                    invi_subactividad: [],
                    invi_actprod_verificables: [{ detalle_prod_verif: '' }],
                    invi_actmedios_verificacion: [{ detalle_medio_verifica: '' }],
                    invi_actindicadores: [{ detalle_indicador: '' }],
                    invi_actsupuestos: [{ detalle_supuestos: '' }]
                };
            }
            this.showModalActividad = true;
        },
        agregarItemActividad(campo) {
            const plantillas = {
                invi_subactividad: { nom_sub_actv: '', fecha_desde: '', fecha_hasta: '', horas: 0 },
                invi_actprod_verificables: { detalle_prod_verif: '' },
                invi_actmedios_verificacion: { detalle_medio_verifica: '' },
                invi_actindicadores: { detalle_indicador: '' },
                invi_actsupuestos: { detalle_supuestos: '' }
            };
            this.modalActividad[campo].push({ ...plantillas[campo] });
        },
        eliminarItemActividad(campo, index) {
            this.modalActividad[campo].splice(index, 1);
        },
        agregarAtributoML(campo) {
            const modelosVios = {
                indicadores: { detalle_indicador: '' },
                metas: { detalle_metas: '' },
                supuestos: { detalle_supuestos: '' },
                medios_verificacion: { detalle_medio_verifica: '' },
                prod_verificables: { detalle_prod_verif: '' }
            };
            this.modalFormML[campo].push({ ...modelosVios[campo] });
        },
        obtenerAnioNumerico(idAnio) {
            const item = this.aniosProyecto.find(a => a.id === idAnio);
            return item ? item.anio : new Date().getFullYear();
        },
        reemplazarAnioEnFecha(fechaStr, nuevoAnioNumerico) {
            if (!fechaStr) return '';
            const partes = fechaStr.split('-');
            // Si tiene formato X-MM-DD (donde X es el año previo o un texto corrupto)
            if (partes.length === 3) {
                const mes = partes[1].padStart(2, '0');
                const dia = partes[2].padStart(2, '0');
                return `${nuevoAnioNumerico}-${mes}-${dia}`;
            }
            return fechaStr;
        },
        alCambiarAnioModal() {
            const anioNum = this.obtenerAnioNumerico(this.modalActividad.detalle_anio);
            if (this.modalActividad.fecha_desde) {
                this.modalActividad.fecha_desde = this.reemplazarAnioEnFecha(this.modalActividad.fecha_desde, anioNum);
            }
            if (this.modalActividad.fecha_hasta) {
                this.modalActividad.fecha_hasta = this.reemplazarAnioEnFecha(this.modalActividad.fecha_hasta, anioNum);
            }
            if (this.modalActividad.invi_subactividad) {
                this.modalActividad.invi_subactividad.forEach(sub => {
                    if (sub.fecha_desde) sub.fecha_desde = this.reemplazarAnioEnFecha(sub.fecha_desde, anioNum);
                    if (sub.fecha_hasta) sub.fecha_hasta = this.reemplazarAnioEnFecha(sub.fecha_hasta, anioNum);
                });
            }
        },
        eliminarAtributoML(campo, index) {
            this.modalFormML[campo].splice(index, 1);
        },
        ejecutarDuplicacion() {
            // Validaciones básicas
            if (!this.anioOrigenDuplicar || !this.anioDestinoDuplicar) {
                mostraralertas2('Debe seleccionar el año origen y destino para duplicar.', 'error');
                return;
            }
            if (this.anioOrigenDuplicar === this.anioDestinoDuplicar) {
                mostraralertas2('El año origen y destino no pueden ser iguales.', 'error');
                return;
            }

            const objDestino = this.aniosProyecto.find(a => a.id === this.anioDestinoDuplicar);
            if (!objDestino) {
                mostraralertas2('El año destino no es válido.', 'error');
                return;
            }
            const anioNumericoDestino = objDestino.anio;
            
            // Obtener actividades del año origen
            const actividadesOrigen = this.editForm.actividades.filter(
                act => act.detalle_anio === this.anioOrigenDuplicar
            );

            if (actividadesOrigen.length === 0) {
                mostraralertas2('No hay actividades para duplicar.', 'error');
                return;
            }

            // Duplicar y procesar cada actividad
            const actividadesDuplicadas = actividadesOrigen.map(act => {
                const actCopia = JSON.parse(JSON.stringify(act));
                
                // --- SOLUCIÓN: Limpiar correctamente las claves primarias ---
                // La clave es id_actividades, no id. La forzamos a null para que Laravel cree una nueva.
                delete actCopia.id_actividades; 
                actCopia.id_actividades = null; 
                
                // 1. Conservar el texto en detalle_anio (ej: 'Segundo Año')
                actCopia.detalle_anio = this.anioDestinoDuplicar;

                // 2. Reemplazar solo el año numérico en las fechas (ej: '2026-03-03')
                if (actCopia.fecha_desde) {
                    actCopia.fecha_desde = this.reemplazarAnioEnFecha(actCopia.fecha_desde, anioNumericoDestino);
                }
                if (actCopia.fecha_hasta) {
                    actCopia.fecha_hasta = this.reemplazarAnioEnFecha(actCopia.fecha_hasta, anioNumericoDestino);
                }

                // 3. Modificar fechas dentro de las subactividades
                if (actCopia.invi_subactividad && actCopia.invi_subactividad.length > 0) {
                    actCopia.invi_subactividad = actCopia.invi_subactividad.map(sub => {
                        // Si las subactividades tienen un ID propio, también es buena práctica limpiarlo
                        delete sub.id; 
                        delete sub.id_subactividad; // Por si acaso se llama así
                        
                        if (sub.fecha_desde) {
                            sub.fecha_desde = this.reemplazarAnioEnFecha(sub.fecha_desde, anioNumericoDestino);
                        }
                        if (sub.fecha_hasta) {
                            sub.fecha_hasta = this.reemplazarAnioEnFecha(sub.fecha_hasta, anioNumericoDestino);
                        }
                        return sub;
                    });
                }

                return actCopia;
            });

            // Insertar al array principal de actividades
            this.editForm.actividades.push(...actividadesDuplicadas);

            // Opcional: Cambiar la pestaña activa para ver las nuevas actividades
            this.anioSeleccionadoTab = this.anioDestinoDuplicar;
            this.showModalDuplicar = false;
            
            // Limpiar selects del modal
            this.anioOrigenDuplicar = '';
            this.anioDestinoDuplicar = '';

            mostraralertas2(`Se duplicaron con éxito ${actividadesDuplicadas.length} actividades al año ${this.anioDestinoDuplicar}.`, "success");
        },
        guardarMarcoLogico() {
            // Validaciones básicas
            if (!this.modalFormML.detalle_obj_proy.trim()) {
                mostraralertas2("El detalle principal no puede estar vacío", "warning");
                return;
            }
            if (this.modalFormML.tipo_obj_proy === 'especifico') {
                let tieneProds = this.modalFormML.prod_verificables.some(p => p.detalle_prod_verif.trim() !== '');
                if (!tieneProds) {
                    mostraralertas2("Los objetivos específicos requieren al menos un Producto Verificable", "warning");
                    return;
                }
            }

            // Guardar en el editForm (el array que viaja a Laravel)
            if (this.indiceEditandoML !== null) {
                // Actualizar existente
                if (this.modalFormML.tipo_obj_proy === 'especifico') {
                    // Encontrar el índice global real en el array
                    let realIndex = this.editForm.objetivos_marco_logico.findIndex(o => o.id_obj_proy === this.modalFormML.id_obj_proy && o.detalle_obj_proy === this.modalFormML.detalle_obj_proy);
                    if(realIndex !== -1) {
                         this.editForm.objetivos_marco_logico[realIndex] = JSON.parse(JSON.stringify(this.modalFormML));
                    } else {
                        // Respaldo por si es nuevo y aún no tiene ID
                        let indexLocal = 0;
                        for(let i=0; i<this.editForm.objetivos_marco_logico.length; i++) {
                            if(this.editForm.objetivos_marco_logico[i].tipo_obj_proy === 'especifico') {
                                if(indexLocal === this.indiceEditandoML) {
                                    this.editForm.objetivos_marco_logico[i] = JSON.parse(JSON.stringify(this.modalFormML));
                                    break;
                                }
                                indexLocal++;
                            }
                        }
                    }
                } else {
                    let i = this.editForm.objetivos_marco_logico.findIndex(o => o.tipo_obj_proy === this.modalFormML.tipo_obj_proy);
                    this.editForm.objetivos_marco_logico[i] = JSON.parse(JSON.stringify(this.modalFormML));
                }
            } else {
                // Es nuevo
                this.editForm.objetivos_marco_logico.push(JSON.parse(JSON.stringify(this.modalFormML)));
            }
            
            this.showModalMarcoLogico = false;
        },
        guardarActividad() {
            const form = this.modalActividad;

            // 1. Validaciones básicas
            if (!form.id_obj_proy) {
                mostraralertas2("Debe seleccionar un objetivo específico.", "warning");
                return;
            }
            if (!form.nom_actividad.trim()) {
                mostraralertas2("Ingrese el nombre de la actividad.", "warning");
                return;
            }
            if (!form.fecha_desde || !form.fecha_hasta) {
                mostraralertas2("Seleccione el rango de fechas de la actividad.", "warning");
                return;
            }
            // Validación de coherencia en fechas de la actividad (Inicio <= Fin)
            if (form.fecha_desde > form.fecha_hasta) {
                mostraralertas2("La fecha de inicio no puede ser posterior a la fecha de fin.", "warning");
                return;
            }

            // 2. Validación: Evitar fechas repetidas/solapadas entre actividades del mismo año
            const conflictoFecha = this.editForm.actividades.find((act, idx) => {
                // Ignorar la misma actividad en caso de estar editando
                if (this.indiceActividadEditando !== null && idx === this.indiceActividadEditando) {
                    return false;
                }

                // Validar solo actividades correspondientes al mismo año
                if (act.detalle_anio !== form.detalle_anio) {
                    return false;
                }

                // Evaluar solapamiento de rangos de fechas
                return act.fecha_desde <= form.fecha_hasta && act.fecha_hasta >= form.fecha_desde;
            });

            if (conflictoFecha) {
                mostraralertas2(
                    `El rango de fechas se traslapa con la actividad "${conflictoFecha.nom_actividad}" (${conflictoFecha.fecha_desde} al ${conflictoFecha.fecha_hasta}).`,
                    "warning"
                );
                return;
            }

            // 3. Validación de Horas Máximas por Año (240 Horas)
            const horasIngresadas = parseFloat(form.horas) || 0;
            
            // SOLUCIÓN: Usamos reduce directamente sobre el arreglo original para que 'idx' coincida perfectamente.
            let totalActualAnio = this.editForm.actividades.reduce((sum, a, idx) => {
                // Ignorar si no es del año seleccionado
                if (a.detalle_anio !== form.detalle_anio) return sum;
                
                // Ignorar la misma actividad en caso de estar editando (usando el index global correcto)
                if (this.indiceActividadEditando !== null && idx === this.indiceActividadEditando) return sum;
                
                return sum + (parseFloat(a.horas) || 0);
            }, 0);

            if (totalActualAnio + horasIngresadas > 240) {
                mostraralertas2(`La suma total de horas para ${form.detalle_anio} excede el límite de 240 hrs. (Actualmente tienes ${totalActualAnio} hrs)`, "warning");
                return;
            }

            // 4. Validación de Subactividades (Rangos de Fecha y Horas)
            let sumaHorasSubactividades = 0;
            for (let sub of form.invi_subactividad) {
                if (sub.nom_sub_actv.trim() !== '') {
                    // Validar fechas
                    if (sub.fecha_desde < form.fecha_desde || sub.fecha_hasta > form.fecha_hasta) {
                        mostraralertas2(`La subactividad "${sub.nom_sub_actv}" debe estar dentro del rango de fechas de la actividad (${form.fecha_desde} al ${form.fecha_hasta}).`, "warning");
                        return;
                    }
                    sumaHorasSubactividades += parseFloat(sub.horas) || 0;
                }
            }

            if (sumaHorasSubactividades > horasIngresadas) {
                mostraralertas2(`La suma de horas de las subactividades (${sumaHorasSubactividades} hrs) no puede ser mayor a las horas de la actividad (${horasIngresadas} hrs).`, "warning");
                return;
            }

            // 5. Guardar o Actualizar en el array principal
            if (this.indiceActividadEditando !== null) {
                this.editForm.actividades[this.indiceActividadEditando] = JSON.parse(JSON.stringify(form));
            } else {
                this.editForm.actividades.push(JSON.parse(JSON.stringify(form)));
            }

            this.showModalActividad = false;
            mostraralertas2("Actividad agregada al cronograma correctamente.", "success");
        },
        eliminarActividad(index) {
            this.editForm.actividades.splice(index, 1);
            mostraralertas2("Actividad eliminada del cronograma.", "info");
        },
        toggleObjetivo(id_obj) {
            const index = this.editForm.objetivos.indexOf(id_obj);
            if (index > -1) {
                this.editForm.objetivos.splice(index, 1); // Deseleccionar
            } else {
                this.editForm.objetivos.push(id_obj); // Seleccionar
            }
        },
        togglePolitica(id_pol) {
            const index = this.editForm.politicas.indexOf(id_pol);
            if (index > -1) {
                this.editForm.politicas.splice(index, 1); // Deseleccionar
            } else {
                this.editForm.politicas.push(id_pol); // Seleccionar
            }
        },
        toggleODS(id_ods) {
            const index = this.editForm.ods.indexOf(id_ods);
            if (index > -1) {
                this.editForm.ods.splice(index, 1); // Deseleccionar
            } else {
                this.editForm.ods.push(id_ods); // Seleccionar
            }
        },
        toggleDominio(id) {
            const index = this.editForm.dominios_humanisticos.indexOf(id);
            if (index > -1) {
                this.editForm.dominios_humanisticos.splice(index, 1);
            } else {
                this.editForm.dominios_humanisticos.push(id);
            }
        },
        recomendarDominio() {
            if (this.dominiosCatalogo.length === 0) return;

            // Concatenamos el contexto lingüístico del proyecto actual
            const textoProyecto = `${this.editForm.proyect_titulo} ${this.editForm.proyect_nombre}`.toLowerCase();

            // Mapeo básico nativo de palabras clave por carreras seleccionadas para robustecer la búsqueda
            // (por ejemplo, si tiene carreras de salud o educación, asocia tokens humanísticos)
            let palabrasAdicionales = "";
            this.editForm.carreras.forEach(carrId => {
                const carreraObj = this.carrerasCatalogo.find(c => c.id_carrera === carrId);
                if (carreraObj) palabrasAdicionales += ` ${carreraObj.nombre_carrera.toLowerCase()}`;
            });

            const contextoCompleto = (textoProyecto + palabrasAdicionales);

            let mejorDominioId = null;
            let maxCoincidencias = -1;

            // Analizamos cada dominio del catálogo buscando la mayor densidad de coincidencia léxica
            this.dominiosCatalogo.forEach(dom => {
                const detalle = dom.detalle_dom_huma.toLowerCase();

                // Dividimos el nombre del dominio en palabras clave ignorando conectores cortos (< 4 letras)
                const palabrasClave = detalle.split(/\s+/).filter(palabra => palabra.length > 4);

                let coincidencias = 0;
                palabrasClave.forEach(palabra => {
                    // Quitamos sufijos comunes para una coincidencia flexible (por ejemplo: "educación" -> "educac")
                    const raiz = palabra.substring(0, palabra.length - 2);
                    if (contextoCompleto.includes(raiz)) {
                        coincidencias++;
                    }
                });

                if (coincidencias > maxCoincidencias && coincidencias > 0) {
                    maxCoincidencias = coincidencias;
                    mejorDominioId = dom.id_dom_huma;
                }
            });

            if (mejorDominioId) {
                this.dominioRecomendadoId = mejorDominioId;

                // Si el usuario no lo tiene seleccionado, lo agregamos automáticamente
                if (!this.editForm.dominios_humanisticos.includes(mejorDominioId)) {
                    this.editForm.dominios_humanisticos.push(mejorDominioId);
                }
                mostraralertas2('Se ha sugerido y marcado el dominio más compatible con el proyecto.', 'success');
            } else {
                // Fallback genérico si es muy abstracto: sugerir el primer dominio del catálogo
                this.dominioRecomendadoId = this.dominiosCatalogo[0].id_dom_huma;
                if (!this.editForm.dominios_humanisticos.includes(this.dominioRecomendadoId)) {
                    this.editForm.dominios_humanisticos.push(this.dominioRecomendadoId);
                }
                mostraralertas2('Dominio sugerido basándose en el alcance social estándar.', 'success');
            }
        },
        async traducirTextos() {
            if (!this.editForm.proyect_nombre && !this.editForm.proyect_titulo) {
                mostraralertas2('No hay textos para traducir', 'warning');
                return;
            }

            mostraralertas2('Traduciendo textos...', 'info');

            try {
                // Traducción del Nombre del Proyecto
                if (this.editForm.proyect_nombre) {
                    const urlNombre = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=es&tl=en&dt=t&q=${encodeURIComponent(this.editForm.proyect_nombre)}`;
                    const resNombre = await fetch(urlNombre);
                    const dataNombre = await resNombre.json();
                    // Google retorna fragmentos en un array anidado. Los unimos por seguridad.
                    this.editForm.proyect_nombre_en = dataNombre[0].map(item => item[0]).join('');
                }

                // Traducción del Título del Proyecto
                if (this.editForm.proyect_titulo) {
                    const urlTitulo = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=es&tl=en&dt=t&q=${encodeURIComponent(this.editForm.proyect_titulo)}`;
                    const resTitulo = await fetch(urlTitulo);
                    const dataTitulo = await resTitulo.json();
                    this.editForm.proyect_titulo_en = dataTitulo[0].map(item => item[0]).join('');
                }
                this.yaTraducido = true;
                mostraralertas2('Traducción completada con éxito', 'success');
            } catch (error) {
                console.error("Error en traducción:", error);
                mostraralertas2('Error al conectar con el servicio de traducción', 'error');
            }
        },
        alCambiarZona() {
            // Si cambian la zona, se debe resetear lo de abajo porque ya no pertenecen
            this.editForm.provincias = [];
            this.editForm.cantones = [];
            this.editForm.parroquias = [];
        },
        alCambiarProvincia() {
            // Limpiar cantones y parroquias que ya no pertenezcan a las provincias seleccionadas
            const cantonesValidos = this.cantonesDisponibles.map(c => c.id_canton);
            this.editForm.cantones = this.editForm.cantones.filter(id => cantonesValidos.includes(id));
            this.alCambiarCanton(); // Disparar revisión de parroquias
        },
        alCambiarCanton() {
            // Extraer códigos válidos para limpiar parroquias "huerfanas"
            const cantonesMarcadosObj = this.cantonesCatalogo.filter(c => this.editForm.cantones.includes(c.id_canton));
            const codigosValidos = cantonesMarcadosObj.map(c => c.codigo);
            const parroquiasValidas = this.parroquiasCatalogo
                .filter(p => codigosValidos.includes(p.codigocanton))
                .map(p => p.idparroquia);
            
            this.editForm.parroquias = this.editForm.parroquias.filter(id => parroquiasValidas.includes(id));
        },
        seleccionarTodasLasParroquiasDeProvincia(idProvincia) {
            // 1. Encontrar todos los cantones de esa provincia
            const cantonesDeLaProvincia = this.cantonesCatalogo.filter(c => c.codigoprovincia == idProvincia);
            
            // 2. Extraer sus IDs para marcarlos visualmente en la sección de Cantones
            const idsCantones = cantonesDeLaProvincia.map(c => c.id_canton);
            
            // Agregarlos sin duplicar al arreglo form
            idsCantones.forEach(id => {
                if (!this.editForm.cantones.includes(id)) this.editForm.cantones.push(id);
            });

            // 3. Extraer los CÓDIGOS de esos cantones para buscar sus parroquias
            const codigosCantones = cantonesDeLaProvincia.map(c => c.codigo);
            
            // 4. Encontrar todas las parroquias y sumarlas al arreglo de parroquias seleccionadas
            const parroquiasASeleccionar = this.parroquiasCatalogo.filter(p => codigosCantones.includes(p.codigocanton));
            
            parroquiasASeleccionar.forEach(p => {
                if (!this.editForm.parroquias.includes(p.idparroquia)) {
                    this.editForm.parroquias.push(p.idparroquia);
                }
            });
        },
        async guardarEdicion() {
            if (this.editForm.objetivos.length === 0) return;
            if (!this.editForm.id_facultad_priori) {
                mostraralertas2('Debe seleccionar una facultad prioritaria obligatoriamente.', 'error');
                return;
            }
            if (!this.editForm.id_carr_priori) {
                mostraralertas2('Debe seleccionar una carrera prioritaria obligatoriamente.', 'error');
                return;
            }
            if (this.editForm.dominios_humanisticos.length === 0) {
                mostraralertas2('Debe seleccionar al menos un dominio humanístico.', 'error');
                return;
            }
            if (!this.editForm.id_convocatoria) {
                mostraralertas2('Debe seleccionar una convocatoria obligatoriamente.', 'error');
                return;
            }
            if (!this.editForm.id_tip_invi_proy) {
                mostraralertas2('Debe seleccionar un tipo de proyecto de vinculación obligatoriamente.', 'error');
                return;
            }
            const cantAnt = this.palabrasAntecedentes;
            const cantJus = this.palabrasJustificacion;
            const cantdiag = this.palabrasDiagnostico;
            const cantDesc = this.palabrasDescrip;

            if (cantAnt < 500 || cantAnt > 800) {
                mostraralertas2(`Los antecedentes deben tener entre 500 y 800 palabras. Actualmente tienes ${cantAnt}.`, 'error');
                return;
            }
            if (cantJus < 600 || cantJus > 800) {
                mostraralertas2(`La justificación debe tener entre 600 y 800 palabras. Actualmente tienes ${cantJus}.`, 'error');
                return;
            }
            if (cantdiag < 550 || cantdiag > 650) {
                mostraralertas2(`El diagnóstico debe tener entre 550 y 650 palabras. Actualmente tienes ${cantdiag}.`, 'error');
                return;
            }
            if (cantDesc < 550 || cantDesc > 650) {
                mostraralertas2(`La descripción debe tener entre 550 y 650 palabras. Actualmente tienes ${cantDesc}.`, 'error');
                return;
            }

            if (!this.editForm.proyect_fecha_pres || !this.editForm.fechainicio || !this.editForm.fechafin || !this.editForm.proyect_estado) {
                mostraralertas2('Debe completar todos los campos del Plazo de Ejecución.', 'error');
                return;
            }
            
            // 36 meses = 3 años | 60 meses = 5 años
            if (this.editForm.proyect_duracion_mes < 36 || this.editForm.proyect_duracion_mes > 60) {
                mostraralertas2('La duración del proyecto debe ser obligatoriamente entre 3 y 5 años (36 a 60 meses).', 'error');
                return;
            }

            this.guardando = true;
            try {
                const payload = {
                    ...this.editForm,
                    empresas: this.empresasAgregadas.map(emp => emp.idempresa)
                };
                let uploadResp = null;
                if (this.archivopdfSeleccionado){
                    if(this.editForm.proyect_archivo){

                        const archivoViejo = this.editForm.proyect_archivo;
                        uploadResp = await this.uploadarpdfchivo(this.editForm.proyect_cod, archivoViejo);
                        console.log(uploadResp);
                        if (uploadResp && uploadResp.filename) {
                                //Asignar el nombre del archivo a la variable certificado_matricula
                            payload.proyect_archivo = uploadResp.filename;
                        }
                    }else{
                        uploadResp = await this.uploadarpdfchivo(this.editForm.proyect_cod);
                        console.log(uploadResp);
                        if (uploadResp && uploadResp.filename) {
                                //Asignar el nombre del archivo a la variable certificado_matricula
                            payload.proyect_archivo = uploadResp.filename;
                        }
                    }
                }
                await API.put(`${this.baseUrl}/invi_proyectos/${this.proyectoEditandoId}`, payload);

                mostraralertas2('Proyecto actualizado correctamente', 'success');
                this.cerrarEdicion();
                this.actualizar(); // Refresca tu tabla principal
            } catch (error) {
                mostraralertas2('Error al guardar los cambios', 'error');
            } finally {
                this.guardando = false;
            }
        },
        cerrarEdicion() {
            this.showEditModal = false;
            this.proyectoEditandoId = null;
            this.yaTraducido = false;
            this.unescoCatalogo = [];
            this.tip_proyectCatalogo = [];
            this.searchAsignatura = '';
            this.asignaturasDisponibles = [];
            this.editForm = {
                proyect_id: '',
                proyect_cod: '',
                proyect_nombre: '',
                proyect_titulo: '',
                proyect_nombre_en: '',
                proyect_titulo_en: '',
                proyect_multidis: 0,
                objetivos: [],
                politicas: [],
                ods: [],
                facultades: [],
                id_facultad_priori: '',
                carreras: [],
                id_carr_priori: '',
                dominios_humanisticos: [],
                id_convocatoria: '',
                sublineas_investigacion: [],
                unesco_areas: [],
                id_tip_invi_proy: '',
                proyect_cobertura: '',
                id_zona_plan: '',
                provincias: [], // Arreglo de id_provincia
                cantones: [],   // Arreglo de id_canton
                parroquias: [],  // Arreglo de idparroquia
                objetivos_marco_logico: [],
                proyect_antecedentes: '',
                proyect_justificacion: '',
                empresas: [],
                aportes_utlvt: [],
                aportes_inst: [],
                proyect_fecha_pres: '',
                fechainicio: '',
                fechafin: '',
                proyect_duracion_mes: '',
                proyect_estado: '',
                proyect_desc_situ_act: '',
                proyect_diag_probl: '',
                proyect_contribucion_soci: '',
                asignaturas: [],
                proyec_ident_poblaobj: '',
                proyect_num_direct_hombres: 0,
                proyect_num_direct_mujeres: 0,
                proyect_total_num_direct: 0,
                proyect_num_personas_div_fun: 0,
                proyect_total_num_indirect: 0,
                proyect_num_doce_part: 0,
                proyect_num_doce_h: 0,
                proyect_num_doce_m: 0,
                proyect_num_est_part: 0,
                proyect_num_est_h: 0,
                proyect_num_est_m: 0,
                proyect_fact_exito: '',
                proyect_rest_supu: '',
                actividades: [],
                proyect_bienes: '',
                proyect_servicios: '',
                proyect_bienes_servicios: '',
                adquisiciones: [],
                proyect_metodologia: '',
                proyect_viabilidad_tec: '',
                proyect_equip_tec: '',
                proyect_no_ejecuta: '',
                impactos: [],
                proyect_sostenibilidad_soc: '',
                proyect_transf_tecn: '',
                proyect_art_cientificos: '',
                proyect_prototipos: '',
                proyect_reg_propin: '',
                proyect_empr_spin: '',
            };
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
        async ObtenerCarr(id){
            const response = await API.get(`${this.baseUrl}/obtnercarreraindv/${id}`);
            return response.data.nombre_carrera;
        },
        async ObteneProDoc(id){
            const response = await API.get(`${this.baseUrl}/getDocentesIndProyectosVinculacion/${id}`);
            return response;
        },
        async ObteneProEst(id){
            const response = await API.get(`${this.baseUrl}/getEstudiantesIndProyectosVinculacion/${id}`);
            return response;
        },
        async ObteneProDir(id){
            const response = await API.get(`${this.baseUrl}/getDirectoresIndProyectosVinculacion/${id}`);
            return response;
        },
        async ObteneProSubDir(id){
            const response = await API.get(`${this.baseUrl}/getSubDirectoresIndProyectosVinculacion/${id}`);
            return response;
        },
        async ObteneRespVin(id){
            const response = await API.get(`${this.baseUrl}/getResponsablesIndInfo/${id}`);
            return response;
        },
        async ObteneDirVin(id){
            const response = await API.get(`${this.baseUrl}/getdirectvin`);
            return response;
        },
        async abrirModalAreaTematica(id) {
            try{
                if(this.editForm.proyect_id){

                    if (!this.editForm.financiamientos || this.editForm.financiamientos.length === 0) {
                        mostraralertas2("No hay rubros de financiamiento para generar el Anexo 3.", "warning");
                        return;
                    }
                    this.areaTematicaInput = ''; // Limpiar el input al abrir
                    this.showModalAreaTematica = true;
                }else{
                    this.botonCargando = 'anexo3_' + id;
                    const response = await API.get(`${this.baseUrl}/getEdicionDatos/${id}`);
                    const data = response.data;
                    this.empresasAgregadas = data.empresas_seleccionadas || [];
                    this.empresasAgregadas2 = data.empresas_seleccionadas2 || [];
                    this.listaRubrosDisponibles = data.rubros_catalogo || [];
                    let mapeoObjetivos = data.proyecto.invi_obj_proyectos.map(obj => ({
                        id_obj_proy: obj.id_obj_proy,
                        tipo_obj_proy: obj.tipo_obj_proy,
                        detalle_obj_proy: obj.detalle_obj_proy,
                        // Aseguramos que siempre sean arrays (mapeando con las propiedades exactas de tu Base de Datos)
                        indicadores: obj.invi_indicadores || [],
                        metas: obj.invi_metas || [],
                        supuestos: obj.invi_supuestos || [],
                        medios_verificacion: obj.invi_medios_verificacion || [],
                        prod_verificables: obj.invi_prod_verificables || [],
                    }));
                    const calc = data.calculo_integrantes || {};
                    let actividadesExtraidas = [];
                    (data.proyecto.invi_obj_proyectos || []).forEach(obj => {
                        if (obj.invi_actividades && obj.invi_actividades.length > 0) {
                            obj.invi_actividades.forEach(act => {
                                actividadesExtraidas.push({
                                    ...act,
                                    invi_subactividad: act.invi_subactividad || [],
                                    invi_actprod_verificables: act.invi_actprod_verificables || [],
                                    invi_actmedios_verificacion: act.invi_actmedios_verificacion || [],
                                    invi_actindicadores: act.invi_actindicadores || [],
                                    invi_actsupuestos: act.invi_actsupuestos || []
                                });
                            });
                        }
                    });
                    let adquisicionesMapeadas = [];
                    if (data.proyecto.invi_detalle_adqui && data.proyecto.invi_detalle_adqui.length > 0) {
                        adquisicionesMapeadas = data.proyecto.invi_detalle_adqui.map(detalle => {
                            return {
                                id_adquisicion: detalle.invi_adquisicion.id_adquisicion,
                                tipo_adqui: detalle.invi_adquisicion.tipo_adqui || '',
                                detalle: detalle.invi_adquisicion.detalle || '',
                                porcent_nacio: detalle.invi_adquisicion.porcent_nacio || 0,
                                detalle_iinsu_nac: detalle.invi_adquisicion.detalle_iinsu_nac || '',
                                porcent_importado: detalle.invi_adquisicion.porcent_importado || 0,
                                detalle_insu_import: detalle.invi_adquisicion.detalle_insu_import || ''
                            };
                        });
                    }
                    let financiamientosMapeados = [];
                    if (data.proyecto.invi_detalle_financia && data.proyecto.invi_detalle_financia.length > 0) {
                        financiamientosMapeados = data.proyecto.invi_detalle_financia.map(det => {
                            return {
                                id_det_financia: det.id_det_financia,
                                id_rubro: det.id_rubro,
                                cantidad: det.cantidad || 0,
                                valor: det.valor || 0,
                                utlvte_anio1: det.utlvte_anio1 || 0,
                                utlvte_anio2: det.utlvte_anio2 || 0,
                                utlvte_anio3: det.utlvte_anio3 || 0,
                                utlvte_anio4: det.utlvte_anio4 || 0,
                                utlvte_anio5: det.utlvte_anio5 || 0,
                                otros_anio1: det.otros_anio1 || 0,
                                otros_anio2: det.otros_anio2 || 0,
                                otros_anio3: det.otros_anio3 || 0,
                                otros_anio4: det.otros_anio4 || 0,
                                otros_anio5: det.otros_anio5 || 0,
                                total_efectivo: det.total_efectivo || 0
                            };
                        });
                    }
                    this.listaImpactosDisponibles = data.impactos_catalogo || [];
                    let impactosMapeados = [];
                    if (data.proyecto.invi_det_impactos_esperados && data.proyecto.invi_det_impactos_esperados.length > 0) {
                        impactosMapeados = data.proyecto.invi_det_impactos_esperados.map(det => {
                            return {
                                id_det_impactos_esp: det.id_det_impactos_esp,
                                id_impactos: det.id_impactos,
                                descripcion_general: det.descripcion_general || ''
                            };
                        });
                    }
                    let difusionMapeada = [];
                    if (data.proyecto.invi_detalle_difusion && data.proyecto.invi_detalle_difusion.length > 0) {
                        difusionMapeada = data.proyecto.invi_detalle_difusion.map(det => {
                            return {
                                id_det_difusion: det.id_det_difusion,
                                id_difusion: det.id_difusion,
                                costo: det.costo,
                                nombre_actividad: det.invi_difusion ? det.invi_difusion.nombre_actividad : ''
                            };
                        });
                    }
                    this.editForm = {
                        proyect_id: data.proyecto.proyect_id,
                        proyect_nombre: data.proyecto.proyect_nombre || '',
                        proyect_titulo: data.proyecto.proyect_titulo || '',
                        proyect_nombre_en: data.proyecto.proyect_nombre_en || '',
                        proyect_titulo_en: data.proyecto.proyect_titulo_en || '',
                        proyect_multidis: data.proyecto.proyect_multidis,
                        //Objetivos del Plan Estratégico Institucional
                        objetivos: data.seleccionados || [],
                        //Políticas del Plan de Desarrollo para el Nuevo Ecuador 2024 • 2025
                        politicas: data.politicas_seleccionadas || [],
                        //Agenda 2030 y los Objetivos de desarrollo sostenible una oportunidad para América Latina y el Caribe
                        ods: data.ods_seleccionadas || [],
                        //Nombre de Facultad/es: 
                        facultades: data.facultades_seleccionadas || [],
                        id_facultad_priori: data.id_facultad_priori || '',
                        //Carrera/s
                        carreras: data.carreras_seleccionadas || [],
                        id_carr_priori: data.id_carr_priori || '',
                        //Dominios académicos
                        dominios_humanisticos: data.dominios_seleccionados || [],
                        //No. Convocatoria
                        id_convocatoria: data.proyecto.id_convocatoria || '',
                        sublineas_investigacion: [],
                        unesco_areas: data.unesco_seleccionadas || [],
                        id_tip_invi_proy: data.proyecto.id_tip_invi_proy || '',
                        proyect_cobertura: data.proyecto.proyect_cobertura || '',
                        id_zona_plan: data.cobertura_guardada?.id_zona_plan || '',
                        provincias: data.cobertura_guardada?.provincias || [],
                        cantones: data.cobertura_guardada?.cantones || [],
                        parroquias: data.cobertura_guardada?.parroquias || [],
                        objetivos_marco_logico: mapeoObjetivos,
                        proyect_antecedentes: data.proyecto.proyect_antecedentes || '',
                        proyect_justificacion: data.proyecto.proyect_justificacion || '',
                        empresas: [...this.empresasAgregadas],
                        aportes_utlvt: data.aportes_utlvt || [],
                        aportes_inst: data.aportes_inst || [],
                        proyect_fecha_pres: data.proyecto.proyect_fecha_pres || '',
                        fechainicio: data.proyecto.fechainicio || '',
                        fechafin: data.proyecto.fechafin || '',
                        proyect_duracion_mes: data.proyecto.proyect_duracion_mes || '',
                        proyect_estado: data.proyecto.proyect_estado || '',
                        proyect_desc_situ_act: data.proyecto.proyect_desc_situ_act || '',
                        proyect_diag_probl: data.proyecto.proyect_diag_probl || '',
                        proyect_contribucion_soci: data.proyecto.proyect_contribucion_soci || '',
                        asignaturas: data.asignaturas_seleccionadas || [],
                        proyec_ident_poblaobj: data.proyecto.proyec_ident_poblaobj || '',
                        proyect_num_direct_hombres: data.proyecto.proyect_num_direct_hombres || '',
                        proyect_num_direct_mujeres: data.proyecto.proyect_num_direct_mujeres || '',
                        proyect_total_num_direct: data.proyecto.proyect_total_num_direct || '',
                        proyect_num_personas_div_fun: data.proyecto.proyect_num_personas_div_fun || '',
                        proyect_total_num_indirect: data.proyecto.proyect_total_num_indirect || '',
                        proyect_num_doce_h: data.proyecto.proyect_num_doce_h ?? calc.docentes_h ?? 0,
                        proyect_num_doce_m: data.proyecto.proyect_num_doce_m ?? calc.docentes_m ?? 0,
                        proyect_num_doce_part: data.proyecto.proyect_num_doce_part ?? calc.docentes_total ?? 0,

                        // Estudiantes (se usan los valores guardados en BD o se calculan automáticamente si están nulos)
                        proyect_num_est_h: data.proyecto.proyect_num_est_h ?? calc.estudiantes_h ?? 0,
                        proyect_num_est_m: data.proyecto.proyect_num_est_m ?? calc.estudiantes_m ?? 0,
                        proyect_num_est_part: data.proyecto.proyect_num_est_part ?? calc.estudiantes_total ?? 0,
                        proyect_fact_exito: data.proyecto.proyect_fact_exito || '',
                        proyect_rest_supu: data.proyecto.proyect_rest_supu || '',
                        actividades: actividadesExtraidas,
                        proyect_bienes: data.proyecto.proyect_bienes || '',
                        proyect_servicios: data.proyecto.proyect_servicios || '',
                        proyect_bienes_servicios: data.proyecto.proyect_bienes_servicios || '',
                        adquisiciones: adquisicionesMapeadas,
                        proyect_categorizacion: data.proyecto.proyect_categorizacion || '',
                        proyect_metodologia: data.proyecto.proyect_metodologia || '',
                        financiamientos: financiamientosMapeados,
                        proyect_viabilidad_tec: data.proyecto.proyect_viabilidad_tec || '',
                        proyect_equip_tec: data.proyecto.proyect_equip_tec || '',
                        proyect_no_ejecuta: data.proyecto.proyect_no_ejecuta || '',
                        impactos: impactosMapeados,
                        proyect_sostenibilidad_soc: data.proyecto.proyect_sostenibilidad_soc || '',
                        proyect_transf_tecn: data.proyecto.proyect_transf_tecn || '',
                        proyect_art_cientificos: data.proyecto.proyect_art_cientificos || '',
                        proyect_prototipos: data.proyecto.proyect_prototipos || '',
                        proyect_reg_propin: data.proyecto.proyect_reg_propin || '',
                        proyect_empr_spin: data.proyecto.proyect_empr_spin || '',
                        difusion: difusionMapeada,
                        bibliografias: data.proyecto.invi_bibliografias ? [...data.proyecto.invi_bibliografias] : [],
                    };
                    if (!this.editForm.financiamientos || this.editForm.financiamientos.length === 0) {
                        mostraralertas2("No hay rubros de financiamiento para generar el Anexo 3.", "warning");
                        return;
                    }
                    this.areaTematica2Input = ''; // Limpiar el input al abrir
                    this.showModalArea2Tematica = true;
                }

            }catch(error){
                console.error('Error al abrir el modal de Área Temática:', error);
            }finally {
                this.botonCargando = null; // Detiene el spinner al terminar
            }
            
        },

        cerrarModalAreaTematica() {
            this.showModalAreaTematica = false;
            this.showModalArea2Tematica = false;
            this.areaTematicaInput = '';
            this.areaTematica2Input = '';
            this.botonCargando = null;
        },

    },
};
</script>
<style scoped>
/* Estilos para navegadores basados en WebKit (Chrome, Safari, Edge) */
.custom-scrollbar::-webkit-scrollbar {
    height: 6px; /* Altura más delgada y elegante */
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent; /* Fondo invisible */
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1; /* Un gris sutil (tailwind slate-300) */
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8; /* Más oscuro al pasar el mouse */
}

/* Soporte para Firefox */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #ccf7d0 transparent;
}
</style>

