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
            <input type="text" placeholder="Ingresa el nombre del detalle de departamento a buscar..."
              v-model="searchQuery" @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
          </div>
        </form>
      </div>

      <div class="flex items-center gap-3">


        <div class="flex items-center gap-3">
          <button @click="abrirModalRegistro"
            :disabled="filteredarray.length > 0"
            :title="filteredarray.length > 0 ? 'El departamento ya tiene detalles registrados' : 'Añadir nuevo detalle'"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-gray-700">
            Añadir detalle de departamento
          </button>
        </div>
      </div>
    </div>
    <div
      class="max-w-full overflow-x-auto rounded-xl border border-gray-100 shadow-sm custom-scrollbar dark:border-gray-800 mt-4">
      <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
        <thead class="bg-gray-50/70 dark:bg-gray-900/50">
          <tr>
            <th class="py-4 px-4 text-left w-16">
              <p class="font-bold text-gray-500 text-xs tracking-wider uppercase dark:text-gray-400">ID</p>
            </th>
            <th class="py-4 px-4 text-left">
              <p class="font-bold text-gray-500 text-xs tracking-wider uppercase dark:text-gray-400">Detalles del
                Departamento</p>
            </th>
            <th class="py-4 px-4 text-right w-36">
              <p class="font-bold text-gray-500 text-xs tracking-wider uppercase dark:text-gray-400">Acciones</p>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white dark:bg-gray-950 dark:divide-gray-800">

          <tr v-if="cargando">
            <td class="px-5 py-12" colspan="3">
              <div class="flex flex-col justify-center items-center gap-3">
                <span class="animate-spin h-8 w-8 border-4 border-brand-500 border-t-transparent rounded-full"></span>
                <h3 class="text-gray-500 font-medium text-sm dark:text-gray-400">Cargando registros...</h3>
              </div>
            </td>
          </tr>

          <tr v-else v-for="post in filteredarray" :key="post.id_detalle"
            class="hover:bg-gray-50/60 dark:hover:bg-white/[0.01] transition-colors group">

            <td class="py-4 px-4 whitespace-nowrap align-top">
              <span class="text-gray-400 text-sm font-semibold dark:text-gray-600">#{{ post.id_detalle }}</span>
            </td>

            <td class="py-4 px-4 align-top">
              <div class="max-w-3xl flex flex-col gap-4">

                <!-- Bloque Sobre Mí -->
                <div v-if="post.sobre_mi">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400 border border-brand-100 dark:border-brand-500/20 mb-1.5">
                    Sobre Mí
                  </span>
                  <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    {{ post.sobre_mi }}
                  </p>
                </div>

                <!-- Bloque Misión -->
                <div v-if="post.mision">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 border border-blue-100 dark:border-blue-500/20 mb-1.5">
                    Misión
                  </span>
                  <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    {{ post.mision }}
                  </p>
                </div>

                <!-- Bloque Visión -->
                <div v-if="post.vision">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 border border-purple-100 dark:border-purple-500/20 mb-1.5">
                    Visión
                  </span>
                  <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    {{ post.vision }}
                  </p>
                </div>

              </div>
            </td>

            <td class="py-4 px-4 align-top text-right whitespace-nowrap">
              <div class="flex justify-end gap-1">
                <button @click="abrirModalEdicion(post)" :disabled="botonCargando === 'editar_' + post.id_detalle"
                  class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                  <svg v-if="botonCargando === 'editar_' + post.id_detalle" class="animate-spin h-5 w-5 text-blue-600"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </button>

                <button @click="eliminar(post.id_detalle, 'los detalles del departamento')"
                  class="p-1.5 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                  title="Eliminar">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
    <!-- Modal de Registro-->
    <Modal v-if="isModalOpen" @close="cerrarModal">
      <template #body>
        <div
          class="no-scrollbar relative w-[calc(100%-2rem)] max-w-[800px] max-h-[90vh] mx-auto overflow-y-auto rounded-3xl bg-white p-4 shadow-2xl dark:bg-gray-900 lg:p-8">
          <button @click="cerrarModal"
            class="transition-color absolute right-5 top-5 z-50 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                fill="" />
            </svg>
          </button>

          <div class="px-2 mb-6">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800">
              {{ isEditMode ? 'Editar Detalle' : 'Agregar Detalle' }}
            </h4>
            <p class="text-sm text-gray-500">Complete los datos del Detalle del departamento.</p>
          </div>

          <form class="flex flex-col gap-5 px-2" @submit.prevent>
            <!-- Sobre Mí -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Sobre Mí <span class="text-gray-400 font-normal">(Opcional)</span></label>
              <textarea v-model="formulario.sobre_mi" rows="3" maxlength="2000"
                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-500 transition-colors"
                placeholder="Breve descripción del departamento..."></textarea>
            </div>

            <!-- Misión -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Misión</label>
              <textarea v-model="formulario.mision" rows="3" maxlength="2000"
                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-500 transition-colors"
                placeholder="Escriba la misión del departamento..."></textarea>
            </div>

            <!-- Visión -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Visión</label>
              <textarea v-model="formulario.vision" rows="3" maxlength="2000"
                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-500 transition-colors"
                placeholder="Escriba la visión del departamento..."></textarea>
            </div>
            <div class="flex items-center gap-3 mt-6 justify-end">
              <button @click="cerrarModal" :disabled="botonCargando === 'guardando'" type="button" 
                class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
              <button @click="registrar" type="button"
                class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 shadow-lg transition-all">
                
                <span v-if="uploading" class="inline-flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <span>{{ isEditMode ? 'Actualizando....' : 'Registrando....' }}</span>
                </span>

                <!-- Span estado normal -->
                <span v-else>
                  {{ isEditMode ? 'Guardar Cambios' : 'Registrar' }}
                </span>
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

// Exposición básica por si el layout principal lo requiere
const isModalOpen = ref(false)
const cerrarModalDesdeAfuera = () => { isModalOpen.value = false }
defineExpose({ isModalOpen, cerrarModalDesdeAfuera })
</script>
<script>
import API from "@/assets/js/services/axios";
import { useRoute } from "vue-router";
import debounce from 'lodash.debounce';
import Modal from '@/components/Modal/Modal.vue'
import { useUsuario } from "@/composables/useUsuario";
import { mostraralertas2, enviarsolig, eliminacion, confimarhabi, elimnarpermanente } from '@/assets/js/function/funciones';

export default {
  data() {
    return {
      idus: 0,
      baseUrl: "/inves",
      botonCargando: null,
      UsuarioInfo: {},
      formulario: {
        id_detalle: null,
        sobre_mi: "",
        mision: "",
        vision: "",
        id_departamento: null,
      },
      filteredarray: [],
      searchQuery: "",
      isModalOpen: false,
      isEditMode: false,
      cargando: false,
      currentPage: 1,
      lastPage: 1,
      buscando: false, // Mantenido, pero no se usa en la lógica de paginación actual
      debouncedFilter: null,
    };
  },
  watch: {
  },
  created() {
    // Ahora sí puedes usar this.filterAndFetch
    this.debouncedFilter = debounce(() => {
      this.filterAndFetch();
    }, 900);
  },
  async mounted() {
    this.UsuarioInfo = useUsuario();
    this.GetData(1, this.searchQuery);

  },
  computed: {


  },
  methods: {
    abrirModalRegistro() {
      this.isEditMode = false;
      this.limpiarFormulario();
      this.$.setupState.isModalOpen = true;
    },
    abrirModalEdicion(user) {
      this.isEditMode = true;
      this.limpiarFormulario();
      this.botonCargando = 'editar_' + user.id_detalle;
      try {

        this.formulario = {
          id_detalle: user.id_detalle,
          sobre_mi: user.sobre_mi,
          mision: user.mision,
          vision: user.vision,
          id_departamento: user.id_departamento

        };
        
        this.$.setupState.isModalOpen = true;
      } catch (e) {
        console.error("Error al abrir modal de edición:", e);
      } finally {
        this.botonCargando = null;
      }
    },
    cerrarModal() {
      this.$.setupState.isModalOpen = false;
      this.limpiarFormulario();
    },
    handleImgError(e) {
      e.target.src = '/favicon.ico';
    },
    
    async GetData(page = 1, searchQuery = "") {
      this.cargando = true;

      try {
        const params = {
          page: page,
          search_query: searchQuery,
          id_departamento: this.UsuarioInfo.departamentoUsuario
        };
        const response = await API.get(`${this.baseUrl}/detalle_departamentos`, { params });
        console.log(response)
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
      const params = {
        ...this.formulario,
      };
      try {
        this.botonCargando = 'guardando';
        let exito = false;
        if (this.isEditMode) {
          exito = await enviarsolig('PUT', params, `${this.baseUrl}/detalle_departamentos/${this.formulario.id_detalle}`, 'Actualizado');
        } else {
          exito = await enviarsolig('POST', params, `${this.baseUrl}/detalle_departamentos`, 'Registrado');
        }

        if (exito) {
          this.cerrarModal();
          this.GetData(this.currentPage, this.searchQuery);
        } else {
          mostraralertas2("Error al procesar la solicitud", "error");
        }

        
      } catch (error) {
        console.error("Error:", error);
      } finally {
        this.botonCargando = null;
      }
    },

    limpiarFormulario() {
      this.formulario = { id_detalle: null, sobre_mi: "", mision: "", vision: "",id_departamento: this.UsuarioInfo.departamentoUsuario};
      
    },
    eliminar(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/elimi_detalle_departamentos/`,
          id,
          'Deshabilitar registro',
          '¿Realmente desea eliminar el detalle del departamento  ' + nombre + '?',
          this.actualizar   // 👈 callback para refrescar la tabla al confirmar
        );
      } catch (error) {
        console.error("Error al eliminar el detalle del departamento:", error);
        this.cargando = false;
      }
    },
    

  },
};
</script>
