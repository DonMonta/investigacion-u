<template>
  <div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
    <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-100 dark:bg-amber-500/10 dark:border-amber-500/20">
      <div class="flex gap-3">
        <svg class="text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" width="20" height="20" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
          <h4 class="text-sm font-bold text-amber-800 dark:text-amber-400">Modo de consulta (Solo Visualización)</h4>
          <p class="text-xs text-amber-700 dark:text-amber-300/80 mt-0.5 leading-relaxed">
            Por el momento, las opciones de actualización y eliminación de las Áreas de la UNESCO no se encuentran
            disponibles. Solo se permite la visualización del catálogo oficial.
          </p>
        </div>
      </div>
    </div>
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
            <input type="text" placeholder="Ingresa el nombre del área de conocimiento a buscar..."
              v-model="searchQuery" @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
          </div>
        </form>
      </div>
    </div>
    <div class="max-w-full overflow-x-auto custom-scrollbar">
      <table class="min-w-full align-middle">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-800/40">
            <th
              class="py-3.5 px-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-28">
              Código
            </th>
            <th
              class="py-3.5 px-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-52">
              Nivel UNESCO
            </th>
            <th
              class="py-3.5 px-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
              Descripción / Detalle del Área
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/70">
          <tr v-if="cargando">
            <td class="px-5 py-10" colspan="3">
              <div class="flex justify-center items-center gap-2">
                <span class="animate-spin h-5 w-5 border-2 border-brand-500 border-t-transparent rounded-full"></span>
                <h3 class="text-gray-500 font-medium dark:text-gray-400">Cargando catálogo...</h3>
              </div>
            </td>
          </tr>

          <tr v-else v-for="post in filteredarray" :key="post.sau_id" :class="[
            'transition-colors',
            post.tipo_area === 'Área de conocimiento'
              ? 'bg-slate-50/80 font-semibold dark:bg-white/[0.02] hover:bg-slate-100/70 dark:hover:bg-white/[0.04]'
              : 'bg-white dark:bg-transparent hover:bg-gray-50/50 dark:hover:bg-white/[0.01]'
          ]">

            <td class="py-3.5 px-4 whitespace-nowrap">
              <span :class="[
                'inline-flex items-center justify-center font-mono text-xs font-bold px-2.5 py-1 rounded-md border',
                post.tipo_area === 'Área de conocimiento' ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20' : '',
                post.tipo_area === 'Subárea de conocimiento' ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20' : '',
                post.tipo_area === 'Área específica de conocimiento' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : ''
              ]">
                {{ post.sau_id }}
              </span>
            </td>

            <td class="py-3.5 px-4 whitespace-nowrap">
              <span :class="[
                'text-xs font-medium tracking-wide',
                post.tipo_area === 'Área de conocimiento' ? 'text-blue-600 dark:text-blue-400 font-bold' : '',
                post.tipo_area === 'Subárea de conocimiento' ? 'text-purple-600 dark:text-purple-400' : '',
                post.tipo_area === 'Área específica de conocimiento' ? 'text-gray-400 dark:text-gray-500 text-[11px]' : ''
              ]">
                {{ post.tipo_area }}
              </span>
            </td>

            <td class="py-3.5 px-4">
              <p :class="[
                'leading-relaxed transition-all',
                post.tipo_area === 'Área de conocimiento' ? 'font-extrabold text-gray-950 text-base dark:text-white' : '',
                post.tipo_area === 'Subárea de conocimiento' ? 'font-semibold text-sm text-gray-800 dark:text-gray-200' : '',
                post.tipo_area === 'Área específica de conocimiento' ? 'text-sm text-gray-500 dark:text-gray-400 pl-3 border-l border-gray-200 dark:border-gray-700' : ''
              ]">
                {{ post.sau_descripcion }}
              </p>
            </td>
          </tr>

          <tr v-if="filteredarray.length === 0 && !cargando">
            <td class="py-10 text-center text-sm text-gray-500 dark:text-gray-400" colspan="3">
              No se encontraron registros coincidentes en el catálogo de la UNESCO.
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
  </div>
</template>

<script>
import API from "@/assets/js/services/axios";
import { useRoute } from "vue-router";
import debounce from 'lodash.debounce';
import { mostraralertas2, enviarsolig, eliminacion, confimarhabi, elimnarpermanente } from '@/assets/js/function/funciones';

export default {
  data() {
    return {
      idus: 0,
      baseUrl: "/inves",
      filteredarray: [],
      searchQuery: "",
      cargando: false,
      currentPage: 1,
      lastPage: 1,
      buscando: false, // Mantenido, pero no se usa en la lógica de paginación actual
      debouncedFilter: null,
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
  methods: {
    async GetData(page = 1, searchQuery = "") {
      this.cargando = true;

      try {
        const params = {
          page: page,
          search_query: searchQuery // Parámetro para búsqueda
        };
        const response = await API.get(`${this.baseUrl}/getSubareas`, { params });

        const data = response.data?.data || [];
        this.filteredarray = data;
        this.currentPage = response.data?.current_page || 1;
        this.lastPage = response.data?.last_page || 1;
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
