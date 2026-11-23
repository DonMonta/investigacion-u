<template>
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
    <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <form class="flex-grow">
          <div class="relative">
            <button class="absolute -translate-y-1/2 left-4 top-1/2">
              <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" fill="" />
              </svg>
            </button>
            <input type="text" placeholder="Buscar por acrónimo..." v-model="searchQuery" @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 xl:w-[430px]" />
          </div>
        </form>
      </div>

      <div class="flex items-center gap-3">
        <button @click="abrirModalCrear" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 dark:border-gray-700">
          Añadir Grupo de Investigación
        </button>
      </div>
    </div>

    <!-- Tabla Principal -->
    <div class="max-w-full overflow-x-auto custom-scrollbar">
      <table class="min-w-full">
        <thead>
          <tr class="border-t border-gray-100 dark:border-gray-800">
            <th class="py-5 px-4 text-left"><p class="font-semibold text-gray-500 text-sm">ID</p></th>
            <th class="py-5 px-4 text-left"><p class="font-semibold text-gray-500 text-sm">Grupo (Acrónimo)</p></th>
            <th class="py-5 px-4 text-left"><p class="font-semibold text-gray-500 text-sm">Proyecto Vinculado</p></th>
            <th class="py-5 px-4 text-center"><p class="font-semibold text-gray-500 text-sm">Integrantes</p></th>
            <th class="py-5 px-4 text-left"><p class="font-semibold text-gray-500 text-sm">Estado</p></th>
            <th class="py-5 px-4 text-right"><p class="font-semibold text-gray-500 text-sm">Acciones</p></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="cargando">
            <td class="px-5 py-6" colspan="6">
              <div class="flex justify-center items-center gap-2">
                <span class="animate-spin h-5 w-5 border-2 border-brand-500 border-t-transparent rounded-full"></span>
                <h3 class="text-gray-500 font-medium">Cargando datos...</h3>
              </div>
            </td>
          </tr>
          <tr v-else v-for="post in filteredarray" :key="post.id_grupo" class="border-t border-gray-100 hover:bg-gray-50/50 dark:border-gray-800 transition-colors">
            <td class="py-5 px-4 whitespace-nowrap"><p class="text-gray-600 text-sm font-medium">{{ post.id_grupo }}</p></td>
            <td class="py-5 px-4 align-top">
              <div>
                <p class="font-bold text-gray-800 text-base dark:text-white/90">{{ post.nombre_grupo }}</p>
                <p class="text-sm text-brand-500 mt-1 font-medium">{{ post.acronimo }}</p>
              </div>
            </td>
            <td class="py-5 px-4 max-w-[250px] whitespace-normal">
              <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2" :title="post.invi_proyectos?.proyect_nombre">
                {{ post.invi_proyectos?.proyect_nombre || 'Sin Proyecto Asignado' }}
              </p>
            </td>
            <td class="py-5 px-4 text-center">
              <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full dark:bg-blue-900/30 dark:text-blue-400">
                {{ post.integrantes ? post.integrantes.length : 0 }}
              </span>
            </td>
            <td class="py-5 px-4 whitespace-nowrap">
              <span :class="{'rounded-lg px-2 py-1 text-xs font-bold uppercase': true, 'bg-green-100 text-green-700': post.estado === 1, 'bg-orange-100 text-orange-700': post.estado === 0}">
                {{ post.estado === 1 ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="py-3 text-right whitespace-nowrap">
              <div class="flex justify-end gap-2">
                <button @click="abrirModalDetalle(post)" v-if="post.integrantes && post.integrantes.length > 0" title="Ver Detalles" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
                <button @click="abrirModalEdicion(post)" title="Editar" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button @click="eliminar(post.id_grupo, post.acronimo)" v-if="post.estado === 1 && post.integrantes.length > 0" title="Deshabilitar" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>
                <button @click="habilitar(post.id_grupo, post.acronimo)" v-if="post.estado === 0" title="Habilitar"
                  class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors dark:text-gray-400 dark:hover:bg-white/10">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 4v6h-6"></path>
                    <path d="M1 20v-6h6"></path>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                  </svg>
                </button>
                
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="flex justify-center mt-6 mb-4 items-center gap-4" v-if="!cargando">
      <button @click="previousPage" :disabled="currentPage === 1" class="px-3 py-1 bg-gray-100 rounded-lg disabled:opacity-50 text-gray-700">Anterior</button>
      <span class="text-sm font-medium text-gray-600">Página {{ currentPage }} de {{ lastPage }}</span>
      <button @click="nextPage" :disabled="currentPage === lastPage" class="px-3 py-1 bg-gray-100 rounded-lg disabled:opacity-50 text-gray-700">Siguiente</button>
    </div>

    <!-- MODAL: Crear/Editar Grupo -->
    <Modal v-if="isFormModalOpen" @close="cerrarModalFormulario">
      <template #body>
        <div class="no-scrollbar relative w-full max-w-[800px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 shadow-2xl">
          <button @click="cerrarModalFormulario" class="absolute right-5 top-5 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200">✕</button>
          
          <div class="mb-6">
            <h4 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
              {{ modoEdicion ? 'Editar Grupo de Investigación' : 'Agregar Grupo de Investigación' }}
            </h4>
            <p class="text-sm text-gray-500 mt-1">Llene los datos del grupo y asigne el proyecto y sus integrantes.</p>
          </div>

          <form class="flex flex-col gap-5">
            <!-- Datos Básicos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Nombre del Grupo *</label>
                <input type="text" v-model="formulario.nombre_grupo" placeholder="Ej: Grupo de Innovación Tecnológica"
                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Acrónimo *</label>
                <input type="text" v-model="formulario.acronimo" placeholder="Ej: GIT"
                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500" />
              </div>
            </div>

            <!-- Selección de Proyecto -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Proyecto de Investigación *</label>
              <select v-model="formulario.proyect_id" @change="buscarIntegrantesProyecto"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                <option value="">Seleccione un proyecto...</option>
                <option v-for="proyecto in listaProyectos" :key="proyecto.proyect_id" :value="proyecto.proyect_id">
                  {{ proyecto.proyect_nombre }}
                </option>
              </select>
            </div>

            <!-- Asignación de Integrantes -->
            <div v-if="formulario.proyect_id" class="border rounded-xl p-4 bg-gray-50 dark:bg-gray-800/50">
              <label class="block text-sm font-semibold text-gray-800 mb-3">Asignar Integrantes y Coordinador</label>
              
              <div v-if="cargandoIntegrantes" class="flex items-center gap-2 text-sm text-gray-500">
                <span class="animate-spin h-4 w-4 border-2 border-brand-500 border-t-transparent rounded-full"></span> Buscando integrantes...
              </div>
              <div v-else-if="integrantesProyecto.length === 0" class="text-sm text-orange-600 bg-orange-50 p-3 rounded-lg">
                Este proyecto no tiene integrantes registrados.
              </div>
              <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto custom-scrollbar pr-2">
                <!-- Listar integrantes -->
                <div v-for="inte in integrantesProyecto" :key="inte.id_deta_invi_proyect" 
                    class="flex items-start gap-3 p-3 bg-white border rounded-lg shadow-sm transition-all"
                    :class="{'opacity-60 cursor-not-allowed bg-gray-100': inte.id_grupo && inte.id_grupo !== formulario.id_grupo, 'border-brand-300 bg-brand-50/20': formulario.integrantes_seleccionados.includes(inte.id_deta_invi_proyect)}">
                  
                  <input type="checkbox" :id="'inte_'+inte.id_deta_invi_proyect" 
                        :value="inte.id_deta_invi_proyect" 
                        v-model="formulario.integrantes_seleccionados"
                        @change="validarCoordinador(inte.id_deta_invi_proyect)"
                        :disabled="inte.id_grupo && inte.id_grupo !== formulario.id_grupo"
                        class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                  
                  <div class="flex flex-col flex-1">
                    <label :for="'inte_'+inte.id_deta_invi_proyect" class="cursor-pointer">
                      <span class="text-sm font-medium text-gray-800 block">
                        {{ inte.informacion_personal_d?.NombInfPer || inte.informacion_personal?.NombInfPer || 'Nombre' }} 
                        {{ inte.informacion_personal_d?.ApellInfPer || inte.informacion_personal?.ApellInfPer || 'Apellido' }}
                      </span>
                      <span class="text-xs text-gray-500 mt-0.5">Rol: {{ inte.funciones?.nombre_funcion || 'Integrante' }}</span>
                      
                      <span v-if="inte.id_grupo && inte.id_grupo !== formulario.id_grupo" class="text-xs text-red-500 font-semibold mt-1 block">
                        Ya pertenece a otro grupo
                      </span>
                    </label>
                    
                    <!-- Opción para marcar como coordinador (solo si está seleccionado el checkbox) -->
                    <div v-if="formulario.integrantes_seleccionados.includes(inte.id_deta_invi_proyect)" class="mt-2 pt-2 border-t border-gray-100">
                      <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-brand-700">
                        <input type="radio" v-model="formulario.coordinador_id" :value="inte.id_deta_invi_proyect" name="coordinador"
                              class="h-3.5 w-3.5 text-brand-600 focus:ring-brand-500 border-gray-300">
                        Designar como Coordinador
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center gap-3 mt-4 justify-end border-t pt-4">
              <button @click="cerrarModalFormulario" type="button" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
              <button @click="guardarDatos" :disabled="uploading || !formIsValid" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 disabled:opacity-50">
                <span v-if="uploading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                {{ uploading ? 'Guardando...' : (modoEdicion ? 'Actualizar Cambios' : 'Guardar Grupo') }}
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>

    <!-- MODAL: Detalle del Grupo -->
    <Modal v-if="isDetalleModalOpen" @close="isDetalleModalOpen = false">
      <template #body>
        <div class="no-scrollbar relative w-full max-w-[650px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 shadow-2xl">
          <button @click="isDetalleModalOpen = false" class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200">✕</button>
          
          <div class="mb-5 border-b pb-4">
            <h3 class="text-2xl font-bold text-gray-800">{{ postSeleccionado.nombre_grupo }}</h3>
            <span class="inline-block mt-2 bg-brand-100 text-brand-700 text-xs font-bold px-3 py-1 rounded-full">{{ postSeleccionado.acronimo }}</span>
          </div>

          <div class="space-y-4">
            <div>
              <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Proyecto Vinculado</p>
              <p class="text-base text-gray-800 font-medium mt-1">{{ postSeleccionado.invi_proyectos?.proyect_nombre || 'No asignado' }}</p>
            </div>

            <div>
              <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Integrantes del Grupo ({{ postSeleccionado.integrantes?.length || 0 }})</p>
              <ul v-if="postSeleccionado.integrantes?.length" class="divide-y divide-gray-100 border rounded-lg bg-gray-50/50">
                <li v-for="miembro in postSeleccionado.integrantes" :key="miembro.id_deta_invi_proyect" class="px-4 py-3 flex flex-col sm:flex-row justify-between sm:items-center gap-2 text-sm text-gray-700">
                  
                  <!-- Mostrar Datos Personales -->
                  <div class="flex flex-col">
                    <span class="font-bold text-gray-800">
                      <!-- Verifica si es docente (informacion_personal_d) o estudiante (informacion_personal) -->
                      {{ miembro.informacion_personal_d?.NombInfPer || miembro.informacion_personal?.NombInfPer || 'Nombre' }} 
                      {{ miembro.informacion_personal_d?.ApellInfPer || miembro.informacion_personal?.ApellInfPer || 'No registrado' }}
                    </span>
                    <span class="text-xs text-gray-500 mt-0.5">
                      C.I: {{ miembro.ciinfper_doc || miembro.ciinfper_est }} | 
                      Rol: <span class="font-medium">{{ miembro.funciones?.nombre_funcion || 'Sin rol' }}</span>
                    </span>
                  </div>
                </li>
              </ul>
              <p v-else class="text-sm text-gray-500 italic">No hay integrantes asignados a este grupo.</p>
            </div>
            
            <div class="flex gap-4 mt-2">
               <div>
                  <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Estado</p>
                  <p class="text-sm font-medium mt-1" :class="postSeleccionado.estado === 1 ? 'text-green-600' : 'text-red-600'">
                    {{ postSeleccionado.estado === 1 ? 'Activo' : 'Inactivo' }}
                  </p>
               </div>
            </div>
          </div>
          
          <div class="mt-6 text-right">
            <button @click="isDetalleModalOpen = false" class="px-5 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition">Cerrar Detalle</button>
          </div>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script>
import API from "@/assets/js/services/axios";
import debounce from 'lodash.debounce';
import Modal from '@/components/Modal/Modal.vue';
import { mostraralertas2, enviarsolig, eliminacion, confimarhabi  } from '@/assets/js/function/funciones';

export default {
  components: { Modal },
  data() {
    return {
      baseUrl: "/inves",
      filteredarray: [],
      searchQuery: "",
      cargando: false,
      currentPage: 1,
      lastPage: 1,
      debouncedFilter: null,
      
      // Control de Modales
      isFormModalOpen: false,
      isDetalleModalOpen: false,
      modoEdicion: false,
      uploading: false,
      
      // Selectores de Proyecto e Integrantes
      listaProyectos: [],
      integrantesProyecto: [],
      cargandoIntegrantes: false,
      
      // Formularios
      formulario: {
        id_grupo: null,
        nombre_grupo: "",
        acronimo: "",
        proyect_id: "",
        estado: 1,
        integrantes_seleccionados: [],
        coordinador_id: null
      },
      postSeleccionado: {}
    };
  },
  created() {
    this.debouncedFilter = debounce(() => {
      this.filterAndFetch();
    }, 800);
  },
  async mounted() {
    this.GetData(1, this.searchQuery);
    this.obtenerProyectos();
  },
  computed: {
    formIsValid() {
      return (
        this.formulario.nombre_grupo.trim() !== '' &&
        this.formulario.acronimo.trim() !== '' &&
        this.formulario.proyect_id !== ''
      );
    }
  },
  methods: {
    validarCoordinador(id) {
      // Si deseleccionamos a un integrante que era coordinador, limpiamos el coordinador
      if (!this.formulario.integrantes_seleccionados.includes(id) && this.formulario.coordinador_id === id) {
        this.formulario.coordinador_id = null;
      }
    },
    // --- OBTENCIÓN DE DATOS PRINCIPALES ---
    async GetData(page = 1, searchQuery = "") {
      this.cargando = true;
      try {
        const response = await API.get(`${this.baseUrl}/invi_grupos_investigacion`, { 
          params: { page, search_query: searchQuery } 
        });
        const data = response.data?.data || [];
        this.filteredarray = data;
        const pagination = response.data?.pagination || {};
        this.currentPage = pagination.current_page || 1;
        this.lastPage = pagination.last_page || 1;
      } catch (error) {
        this.filteredarray = [];
      } finally {
        this.cargando = false;
      }
    },
    
    // --- OBTENCIÓN DE PROYECTOS Y SUS INTEGRANTES ---
    async obtenerProyectos() {
      try {
        // Endpoint ficticio, asume que existe para listar los proyectos en un selector.
        const res = await API.get(`${this.baseUrl}/invi_proyectos?all=true`);
        this.listaProyectos = res.data?.data || [];
      } catch (e) {
        console.warn("No se pudieron cargar los proyectos");
      }
    },
    async buscarIntegrantesProyecto() {
      if(!this.formulario.proyect_id) {
        this.integrantesProyecto = [];
        return;
      }
      this.cargandoIntegrantes = true;
      try {
        // Asumiendo que el backend puede devolver los integrantes filtrados por ID de proyecto
        const res = await API.get(`${this.baseUrl}/invi_detalle_integrante`, {
           params: { proyect_id: this.formulario.proyect_id, all: true }
        });
        this.integrantesProyecto = res.data?.data || [];
      } catch (error) {
        console.error("Error al buscar integrantes", error);
      } finally {
        this.cargandoIntegrantes = false;
      }
    },

    // --- ACCIONES DE MODALES ---
    abrirModalCrear() {
      this.modoEdicion = false;
      this.limpiarFormulario();
      this.isFormModalOpen = true;
    },
    async abrirModalEdicion(grupo) {
      this.modoEdicion = true;
    
      // Buscar si hay un coordinador actual
      let coordinadorActual = null;
      if (grupo.integrantes) {
        const coord = grupo.integrantes.find(i => i.es_coordinador === 1);
        if (coord) coordinadorActual = coord.id_deta_invi_proyect;
      }

      this.formulario = {
        id_grupo: grupo.id_grupo,
        nombre_grupo: grupo.nombre_grupo,
        acronimo: grupo.acronimo,
        proyect_id: grupo.proyect_id || "",
        estado: grupo.estado,
        integrantes_seleccionados: grupo.integrantes ? grupo.integrantes.map(i => i.id_deta_invi_proyect) : [],
        coordinador_id: coordinadorActual
      };
      
      this.isFormModalOpen = true;
      if (this.formulario.proyect_id) {
        await this.buscarIntegrantesProyecto();
      }
    },
    abrirModalDetalle(post) {
      this.postSeleccionado = post;
      this.isDetalleModalOpen = true;
    },
    cerrarModalFormulario() {
      this.isFormModalOpen = false;
      this.limpiarFormulario();
    },
    limpiarFormulario() {
      this.formulario = {
        id_grupo: null, nombre_grupo: "", acronimo: "", proyect_id: "", estado: 1, integrantes_seleccionados: [], coordinador_id: null
      };
      this.integrantesProyecto = [];
    },

    // --- GUARDADO / ACTUALIZACIÓN ---
    async guardarDatos() {
      if (this.uploading) return;

      // Validar que se haya elegido un coordinador si se seleccionaron integrantes
      if (this.formulario.integrantes_seleccionados.length > 0 && !this.formulario.coordinador_id) {
        mostraralertas2("Debe designar un coordinador entre los integrantes seleccionados.", "warning");
        return;
      }

      this.uploading = true;
      try {
        // =========================================================
        // PASO 1: Crear o Actualizar el Grupo de Investigación
        // =========================================================
        const urlGrupo = this.modoEdicion 
          ? `${this.baseUrl}/invi_grupos_investigacion/${this.formulario.id_grupo}` 
          : `${this.baseUrl}/invi_grupos_investigacion`;
        const methodGrupo = this.modoEdicion ? 'put' : 'post';

        const paramsGrupo = {
          nombre_grupo: this.formulario.nombre_grupo,
          acronimo: this.formulario.acronimo,
          proyect_id: this.formulario.proyect_id,
          estado: this.formulario.estado
        };

        const resGrupo = await API[methodGrupo](urlGrupo, paramsGrupo);
        
        // Obtenemos el ID del grupo (ya sea el existente en edición o el recién creado en store)
        const idGrupoGuardado = this.modoEdicion 
          ? this.formulario.id_grupo 
          : resGrupo.data?.data?.id_grupo;

        if (!idGrupoGuardado) {
          throw new Error("No se pudo obtener el ID del grupo de investigación.");
        }

        // =========================================================
        // PASO 2: Asignar Integrantes y Coordinador al Grupo
        // =========================================================
        
          const paramsAsignacion = {
            proyect_id: this.formulario.proyect_id,
            integrantes_ids: this.formulario.integrantes_seleccionados,
            coordinador_id: this.formulario.coordinador_id,
            grupo_datos: {
              id_grupo: idGrupoGuardado,
              es_nuevo: false, // Se envía false porque ya se creó/actualizó en el Paso 1
              nombre_grupo: this.formulario.nombre_grupo,
              acronimo: this.formulario.acronimo
            }
          };

          await API.post(`${this.baseUrl}/integrantes/asignar-grupo`, paramsAsignacion);
        

        // =========================================================
        // PASO 3: Notificar éxito y refrescar interfaz
        // =========================================================
        const msg = this.modoEdicion 
          ? 'Grupo actualizado y asignaciones guardadas con éxito' 
          : 'Grupo registrado y asignaciones guardadas con éxito';
        
        mostraralertas2(msg, "success");
        this.cerrarModalFormulario();
        this.actualizar();

      } catch (error) {
        console.error("❌ Error en el proceso de guardado:", error.response?.data || error);
        mostraralertas2(
          error.response?.data?.message || error.response?.data?.error || "Ocurrió un error al procesar la solicitud", 
          "error"
        );
      } finally {
        this.uploading = false;
      }
    },

    // --- PAGINACIÓN Y ELIMINACIÓN ---
    filterAndFetch() {
      this.currentPage = 1;
      this.GetData(this.currentPage, this.searchQuery);
    },
    nextPage() {
      if (this.currentPage < this.lastPage && !this.cargando) this.GetData(this.currentPage + 1, this.searchQuery);
    },
    previousPage() {
      if (this.currentPage > 1 && !this.cargando) this.GetData(this.currentPage - 1, this.searchQuery);
    },
    actualizar() {
      this.GetData(this.currentPage, this.searchQuery);
    },
    eliminar(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/inhabilitar_grupo_investigacion/`,
          id,
          'Deshabilitar registro',
          '¿Realmente desea deshabilitar el grupo de investigación  ' + nombre + '?',
          this.actualizar  //  callback para refrescar la tabla al confirmar
        );
      } catch (error) {
        console.error("Error al eliminar el grupo de investigación:", error);
        this.cargando = false;
      }
    },
    async habilitar(id, nombre) {
      try {
        // No hace falta poner this.cargando = true aquí si confimarhabi maneja la alerta,
        // pero si lo haces, asegúrate de cerrarlo.
        await confimarhabi(
          `${this.baseUrl}/habilitar_grupo_investigacion/`,
          id,
          'Habilitar registro',
          `¿Desea habilitar el grupo de investigación "${nombre}"?`,
          this.actualizar
        );
      } catch (error) {
        // Este catch capturaría errores si confimarhabi hace un 'throw'
        console.error("Error en el componente habilitar:", error);
      }
    }
  }
};
</script>