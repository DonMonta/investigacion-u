<template>
  <div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
    <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <!-- Search Form -->
        <form class="flex-grow">
          <div class="relative">
            <button class="absolute -translate-y-1/2 left-4 top-1/2">
              <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z"
                  fill="" />
              </svg>
            </button>
            <!-- @input llama al debouncedFilter, que inicia la nueva consulta al backend -->
            <input type="text" placeholder="Ingresa el nombre de la zona de planificación a buscar..."
              v-model="searchQuery" @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
          </div>
        </form>
      </div>

      <div class="flex items-center gap-3">


        <button @click="isProfileAddressModal = true"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
          Añadir Zona de Planificación
        </button>
      </div>
    </div>
    <div class="max-w-full overflow-x-auto custom-scrollbar">
      <table class="min-w-full">
        <thead>
          <tr class="border-t border-gray-100 dark:border-gray-800">
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">ID</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Zona de Planificación</p>
            </th>
            <th class="py-5 px-4 text-center">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Provincias Asignadas</p>
            </th>
            <th class="py-5 px-4 text-right">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Acciones</p>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr class="border-t border-gray-100 dark:border-gray-800" v-if="cargando">
            <td class="px-5 py-6" colspan="5">
              <div class="flex justify-center items-center gap-2">
                <span class="animate-spin h-5 w-5 border-2 border-brand-500 border-t-transparent rounded-full"></span>
                <h3 class="text-gray-500 font-medium">Cargando datos...</h3>
              </div>
            </td>
          </tr>
          <tr v-else v-for="post in filteredarray" :key="post.id_zona_plan"
            class="border-t border-gray-100 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:bg-white/[0.02] transition-colors">

            <td class="py-5 px-4 whitespace-nowrap">
              <p class="text-gray-600 text-sm font-medium dark:text-gray-400">{{ post.id_zona_plan }}</p>
            </td>

            <td class="py-5 px-4 whitespace-nowrap">
              <div>
                <p class="font-bold text-gray-800 text-base dark:text-white/90">
                  {{ post.nombre_zona }}
                </p>
              </div>
            </td>
            <td class="py-5 px-4 text-center whitespace-nowrap">
              <span
                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                {{ post.detalle_zona_planificacion_count ?? 0 }} {{ (post.detalle_zona_planificacion_count === 1) ?
                  'provincia' : 'provincias' }}
              </span>
            </td>

            <!-- Acciones de Edición y Eliminación -->
            <td class="py-3 text-right whitespace-nowrap">
              <div class="flex justify-end gap-2">
                <button @click="abrirModalProvincias(post)" title="Gestionar Provincias"
                  class="p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z" />
                    <circle cx="12" cy="10" r="3" />
                  </svg>
                </button>
                <button @click="abrirModalEdicion(post)"
                  class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </button>
                <button @click="eliminar(post.id_zona_plan, post.nombre_zona)"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>

              </div>
            </td>

          </tr>
        </tbody>
      </table>
    </div>
    <br /><br />
    <!-- Botones de Paginación -->
    <div class="d-flex justify-content-center mb-4">
      <button @click="previousPage" :disabled="currentPage === 1 || buscando" class="btn btn-primary text-white">
        <i class="fas fa-angle-left"></i></button>&nbsp; <span class="text-dark">Página {{ currentPage }} de {{ lastPage
        }}</span>&nbsp;
      <button @click="nextPage" :disabled="currentPage === lastPage || buscando" class="btn btn-primary text-white">
        <i class="fas fa-angle-right"></i>
      </button>
    </div>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <div class="d-flex justify-content-center mb-4" v-if="!cargando">
      &nbsp;&nbsp;&nbsp;
      <button class="btn btn-primary text-white" @click="actualizar">
        Actualizar
      </button>
    </div>
    <div v-if="isProvinciaModalOpen" class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 z-[99999]">
      <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
        
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
          <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Asignar Provincias</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Zona Seleccionada: <span class="font-semibold text-brand-600 dark:text-brand-400">{{ zonaSeleccionada?.nombre_zona }}</span>
            </p>
          </div>
          <button @click="isProvinciaModalOpen = false" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </button>
        </div>

        <div class="p-6 overflow-y-auto custom-scrollbar flex-grow">
          <p class="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 p-3 rounded-lg mb-4 font-medium">
             Las provincias seleccionadas en otras zonas de planificación aparecerán deshabilitadas y no podrán marcarse.
          </p>

          <div v-if="cargandoProvincias" class="flex flex-col justify-center items-center py-12 gap-2">
            <span class="animate-spin h-6 w-6 border-2 border-brand-500 border-t-transparent rounded-full"></span>
            <span class="text-sm text-gray-500">Cargando catálogo provincial...</span>
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label v-for="prov in provinciasList" :key="prov.id_provincia" 
              :class="[
                'flex items-start gap-3 p-3 rounded-xl border text-sm transition-all cursor-pointer select-none',
                provinciasSeleccionadasIds.includes(prov.id_provincia) ? 'bg-brand-50/50 border-brand-300 dark:bg-brand-500/10 dark:border-brand-500/30' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700',
                esProvinciaDeOtraZona(prov) ? 'opacity-40 bg-gray-50 dark:bg-gray-800/40 cursor-not-allowed border-gray-100 dark:border-gray-800' : 'hover:border-gray-300 dark:hover:border-gray-600'
              ]">
              <input type="checkbox" :value="prov.id_provincia" v-model="provinciasSeleccionadasIds" :disabled="esProvinciaDeOtraZona(prov)"
                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500/30 disabled:opacity-50" />
              
              <div class="flex flex-col">
                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ prov.detalle }}</span>
                <span class="text-[11px] text-gray-400 dark:text-gray-500">Código: {{ prov.codigo }} • Región: {{ prov.region }}</span>
                <span v-if="esProvinciaDeOtraZona(prov)" class="text-[10px] text-amber-700 dark:text-amber-400 font-medium mt-1">
                  Ocupada por otra zona
                </span>
              </div>
            </label>
          </div>
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3 rounded-b-2xl">
          <button @click="isProvinciaModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">
            Cancelar
          </button>
          <button @click="guardarProvinciasAsignadas" :disabled="guardandoProvincias" class="px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 disabled:opacity-50 shadow-sm flex items-center gap-2">
            <span v-if="guardandoProvincias" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
            Guardar Relaciones
          </button>
        </div>

      </div>
    </div>
    <!-- Modal de Registro-->
    <Modal v-if="isProfileAddressModal" @close="isProfileAddressModal = false">
      <template #body>
        <div
          class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <!-- close btn -->
          <button @click="isProfileAddressModal = false"
            class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                fill="" />
            </svg>
          </button>
          <div class="px-2 pr-14">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Agregar Zona de Planificación
            </h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Llene todos los campos para agregar una nueva Zona de Planificación.
            </p>
          </div>
          <form class="flex flex-col">
            <div class="px-2 overflow-y-auto custom-scrollbar">
              <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Nombre de la Zona de Planificación
                  </label>
                  <input type="text" v-model="objetoguardar.nombre_zona" placeholder="Ej: Zona de planificación 1"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                  <p class="mt-1.5 text-xs text-gray-500">Use un nombre descriptivo</p>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3 mt-6 lg:justify-end">
              <button @click="isProfileAddressModal = false" type="button"
                class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">
                Cerrar
              </button>
              <button v-if="formIsValid" @click="registrar" type="button"
                class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-lg transition-all">
                Guardar Zona de Planificación
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>
    <!-- Modal de Edición-->
    <Modal v-if="isEditModalOpen" @close="isEditModalOpen = false">
      <template #body>
        <div
          class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <!-- close btn -->
          <button @click="isEditModalOpen = false"
            class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                fill="" />
            </svg>
          </button>
          <div class="px-2 pr-14">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Editar Zona de Planificación
            </h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Los datos mostrados son los actuales de la Zona de Planificación. Realice los cambios necesarios y guarde.
            </p>
          </div>
          <form class="flex flex-col">
            <div class="px-2 overflow-y-auto custom-scrollbar">
              <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Nombre de la Zona de Planificación
                  </label>
                  <input type="text" v-model="objetoeditar.nombre_zona" placeholder="Ej: Zona de planificación 1"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                  <p class="mt-1.5 text-xs text-gray-500">Use un nombre descriptivo</p>
                </div>
              </div>

            </div>

            <div class="flex items-center gap-3 mt-6 lg:justify-end">
              <button @click="isEditModalOpen = false" type="button"
                class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">
                Cerrar
              </button>
              <button v-if="formIsValidEdit" @click="Update" type="button"
                class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-lg transition-all">
                Guardar cambios
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Modal from '@/components/Modal/Modal.vue'

const isProfileAddressModal = ref(false)
const isEditModalOpen = ref(false)
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
import { mostraralertas2, enviarsolig, eliminacion, confimarhabi, elimnarpermanente } from '@/assets/js/function/funciones';

export default {
  data() {
    return {
      idus: 0,
      baseUrl: "/inves",

      usersarray: [],
      objetoguardar: {
        nombre_zona: "",
      },
      objetoeditar: {
        id_zona_plan: 0,
        nombre_zona: "",
      },
      filteredarray: [],
      searchQuery: "",
      isProfileAddressModal: false,
      cargando: false,
      currentPage: 1,
      lastPage: 1,
      buscando: false, // Mantenido, pero no se usa en la lógica de paginación actual
      debouncedFilter: null,
      isProvinciaModalOpen: false,
      zonaSeleccionada: null,
      provinciasList: [],
      provinciasSeleccionadasIds: [],
      cargandoProvincias: false,
      guardandoProvincias: false,
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

    formIsValid() {

      return (
        this.objetoguardar.nombre_zona.trim() !== ''
      );
    },
    formIsValidEdit() {
      return (
        this.objetoeditar.nombre_zona.trim() !== ''
      );
    },


  },
  methods: {
    abrirModalEdicion(user) {
      // Clonamos el objeto para no modificar la tabla directamente antes de guardar
      this.objetoeditar = {
        id_zona_plan: user.id_zona_plan,
        nombre_zona: user.nombre_zona,
      };
      this.$.setupState.isEditModalOpen = true;
    },
    async abrirModalProvincias(zona) {
      this.zonaSeleccionada = zona;
      this.provinciasSeleccionadasIds = [];
      this.isProvinciaModalOpen = true;
      this.cargandoProvincias = true;

      try {
        await this.getProvincias();

        // 2. Traer las provincias que ya están asociadas a ESTA zona específica para pre-marcarlas
        const respAsignadas = await API.get(`${this.baseUrl}/getProvinciasPorZona/${zona.id_zona_plan}`);
        const asignadas = respAsignadas.data?.data || [];
        this.provinciasSeleccionadasIds = asignadas.map(p => p.id_provincia);

      } catch (error) {
        console.error("Error al estructurar provincias:", error);
        mostraralertas2("Error al sincronizar las provincias", "error");
      } finally {
        this.cargandoProvincias = false;
      }
    },
    esProvinciaDeOtraZona(provincia) {
      if (!provincia.id_zona_plan) return false;
      // Si tiene una zona asignada y no coincide con la actual, está ocupada
      return provincia.id_zona_plan !== this.zonaSeleccionada?.id_zona_plan;
    },

    // GUARDAR EN DETALLE_ZONA_PLANIFICACION
    async guardarProvinciasAsignadas() {
      this.guardandoProvincias = true;
      try {
        const payload = {
          id_zona_plan: this.zonaSeleccionada.id_zona_plan,
          provincias: this.provinciasSeleccionadasIds // Arreglo de IDs de provincias seleccionadas
        };

        // Enviamos al backend para procesar el lote (sincronización)
        const exito = await enviarsolig('POST', payload, `${this.baseUrl}/guardarProvincias`, 'Provincias actualizadas con éxito');
        
        if (exito) {
          this.isProvinciaModalOpen = false;
          this.actualizar(); // Refresca conteo en la tabla principal
        }
      } catch (error) {
        console.error("Error al guardar detalle de provincias:", error);
      } finally {
        this.guardandoProvincias = false;
      }
    },
    async getProvincias() {
      try {
        const response = await API.get(`${this.baseUrl}/getProvincias`);
        this.provinciasList = response.data.data || [];
      } catch (error) {
        console.error("Error al obtener provincias:", error);
      }
    },
    async GetData(page = 1, searchQuery = "") {
      this.cargando = true;

      try {
        const params = {
          page: page,
          search_query: searchQuery // Parámetro para búsqueda
        };
        const response = await API.get(`${this.baseUrl}/zona_planificacion`, { params });

        const data = response.data?.data || [];
        this.filteredarray = data;
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

    async registrar() {

      try {
        const params = {
          nombre_zona: this.objetoguardar.nombre_zona
        };
        const exito = await enviarsolig('POST', params, `${this.baseUrl}/zona_planificacion`, 'ZONA DE PLANIFICACIÓN registrada con éxito');
        if (exito) {
          this.$.setupState.isProfileAddressModal = false;

          this.limpiarFormulario();
          this.actualizar();
        } else {
          mostraralertas2("No se pudo registrar la ZONA DE PLANIFICACIÓN", "error");
        }
      } catch (error) {
        console.error("❌ Error al registrar ZONA DE PLANIFICACIÓN:", error.response?.data || error);
      }
    },
    async Update() {
      try {
        const params = {
          nombre_zona: this.objetoeditar.nombre_zona
        };
        const exito = await enviarsolig('PUT', params, `${this.baseUrl}/zona_planificacion/${this.objetoeditar.id_zona_plan}`, 'ZONA DE PLANIFICACIÓN actualizada con éxito');
        if (exito) {
          this.$.setupState.isEditModalOpen = false;

          this.limpiarFormulario();
          this.actualizar();
        } else {
          this.$.setupState.isEditModalOpen = false;
          mostraralertas2("No se pudo editar la ZONA DE PLANIFICACIÓN", "error");
        }
      } catch (error) {
        console.error("❌ Error al registrar zonas de planificación:", error.response?.data || error);
      }
    },
    limpiarFormulario() {
      this.objetoguardar = {
        nombre_zona: ""
      };
      this.objetoeditar = {
        id_zona_plan: 0,
        nombre_zona: ""
      };
    },
    eliminar(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/zona_planificacion/`,
          id,
          'Eliminar registro',
          '¿Realmente desea eliminar la ZONA DE PLANIFICACIÓN  ' + nombre + '?',
          this.actualizar   // 👈 callback para refrescar la tabla al confirmar
        );
      } catch (error) {
        console.error("Error al eliminar la ZONA DE PLANIFICACIÓN:", error);
        this.cargando = false;
      }
    },

  },
};
</script>
