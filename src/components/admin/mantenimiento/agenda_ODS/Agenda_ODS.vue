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
            <input type="text" placeholder="Ingresa el nombre de la agenda ODS a buscar..." v-model="searchQuery"
              @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
          </div>
        </form>
      </div>

      <div class="flex items-center gap-3">


        <button @click="isProfileAddressModal = true"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
          Añadir Agenda ODS
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
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Agenda ODS</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">ODS</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Estado</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Archivo</p>
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
          <tr v-else v-for="post in filteredarray" :key="post.id_ag_ods"
            class="border-t border-gray-100 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:bg-white/[0.02] transition-colors">

            <td class="py-5 px-4 whitespace-nowrap">
              <p class="text-gray-600 text-sm font-medium dark:text-gray-400">{{ post.id_ag_ods }}</p>
            </td>

            <td class="py-5 px-4 whitespace-nowrap">
              <div>
                <p class="font-bold text-gray-800 text-base dark:text-white/90">
                  {{ post.nombre_ag_ods }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5 italic">Periodo: {{ post.anio_ag_ods }}</p>
              </div>
            </td>
            <td class="py-5 px-4">
              <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full w-fit">
                  {{ post.ods_count }} ODS
                </span>
              </div>
            </td>
            <td class="py-5 px-4 whitespace-nowrap">
              <div class="flex flex-col gap-3">
                <!-- Estado General -->
                <div>
                  <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">General</p>
                  <span :class="{
                    'rounded-lg px-2 py-1 text-xs font-bold uppercase tracking-wider': true,
                    'bg-green-100 text-green-700 dark:bg-success-500/15 dark:text-success-500': post.estado_ag_ods === 1,
                    'bg-orange-100 text-orange-700 dark:bg-warning-500/15 dark:text-orange-400': post.estado_ag_ods === 0
                  }">
                    {{ post.estado_ag_ods === 1 ? 'Activo' : 'Inactivo' }}
                  </span>
                </div>

                <!-- Estado del Departamento (Solo si se filtró por departamento) -->
                <div v-if="post.estado_departamento !== undefined">
                  <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 max-w-[150px] truncate" :title="post.nombre_departamento">
                    En: {{ post.nombre_departamento }}
                  </p>
                  <span :class="{
                    'rounded-lg px-2 py-1 text-xs font-bold uppercase tracking-wider': true,
                    'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400': post.estado_departamento === 1,
                    'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400': post.estado_departamento === 0
                  }">
                    {{ post.estado_departamento === 1 ? 'Habilitado' : 'Inhabilitado' }}
                  </span>
                </div>
              </div>
            </td>

            <td class="py-5 px-4">
              <div v-if="post.archivo_ag_ods">
                <a :href="post.ad_archivo_url" target="_blank"
                  class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors dark:bg-red-500/10 dark:text-red-400">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                    <polyline points="10 9 9 9 8 9" />
                  </svg>
                  <span>PDF</span>
                </a>
              </div>
              <span v-else class="text-xs text-gray-400 italic">Sin documento</span>
            </td>

            <!-- Acciones de Edición y Eliminación -->
            <td class="py-3 text-right whitespace-nowrap">
              <div class="flex justify-end gap-2">
                <button @click="abrirModalEdicion(post)" :disabled="botonCargando === 'editar_' + post.id_ag_ods"
                  class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                  <svg v-if="botonCargando === 'editar_' + post.id_ag_ods" class="animate-spin h-5 w-5 text-amber-600"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </button>
                <button v-if="post.estado_departamento === 1" @click="abriModalODS(post)" :disabled="botonCargando === 'objetivos_' + post.id_ag_ods"
                 class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                  title="Gestionar ODS">
                  <svg v-if="botonCargando === 'objetivos_' + post.id_ag_ods" class="animate-spin h-5 w-5 text-purple-600"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v18m9-9H3" />
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                  </svg>
                </button>
                <button @click="eliminar(post.id_ag_ods, post.nombre_ag_ods)" v-if="(post.estado_departamento !== undefined ? post.estado_departamento : post.estado_pei) === 1"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>
                <button @click="habilitar(post.id_ag_ods, post.nombre_ag_ods)" v-if="(post.estado_departamento !== undefined ? post.estado_departamento : post.estado_pei) === 0"
                  class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors dark:text-gray-400 dark:hover:bg-white/10"
                  title="Refrescar lista">
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
    <div v-if="isODSModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 z-99999">

      <div
        class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">

        <div
          class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-white/[0.02]">
          <div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Objetivos de Desarrollo Sostenible de la</h3>
            <p class="text-sm text-success-600 font-medium">{{ selectedAgendaODS?.nombre_ag_ods }}</p>
          </div>

          <button @click="isODSModalOpen = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-100 dark:bg-blue-500/10 dark:border-blue-500/20">
          <div class="flex gap-3">
            <svg class="text-blue-600 dark:text-blue-400 shrink-0" width="20" height="20" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="16" x2="12" y2="12" />
              <line x1="12" y1="8" x2="12.01" y2="8" />
            </svg>
            <p class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
              <strong>Nota importante:</strong> Los objetivos que añada aquí deben ser únicamente los que se encuentran
              registrados en el documento oficial del <strong>Agenda ODS</strong> seleccionado.
            </p>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-12 gap-8">

          <div class="md:col-span-4 border-r border-gray-100 dark:border-gray-800 pr-0 md:pr-8">

            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 uppercase tracking-wider">
              {{ isEditingODS ? 'Editar Objetivo' : 'Nuevo Objetivo' }}
            </h4>
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Código</label>
                <div class="flex">
                  <span
                    class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-200 rounded-l-lg text-gray-500">ODS</span>
                  <input type="number" v-model="codNumero"
                    class="w-full px-4 py-2 border border-gray-200 rounded-r-lg dark:bg-gray-800 dark:border-gray-700 outline-none"
                    placeholder="1">
                </div>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Detalle del Objetivo</label>
                <textarea v-model="ObjetivosODSForm.detalle_ods" rows="4"
                  class="w-full px-4 py-2 border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 outline-none"
                  placeholder="Escriba el objetivo de la Agenda ODS..."></textarea>
              </div>
              <div class="flex gap-2">
                <button @click="guardarODS"
                  class="flex-1 btn-primary text-white font-bold py-2 rounded-lg transition-colors">
                  {{ isEditingODS ? 'Actualizar' : 'Guardar' }}
                </button>
                <button v-if="isEditingODS" @click="cancelarEdicionODS"
                  class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg">
                  ✕
                </button>
              </div>
            </div>
          </div>
          <div class="md:col-span-8 flex flex-col min-h-0">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 uppercase tracking-wider">
              Registrados
            </h4>
            <div class="overflow-y-auto border rounded-xl dark:border-gray-800 custom-scrollbar"
              style="max-height: 400px;">
              <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50 sticky top-0 z-10">
                  <tr>
                    <th
                      class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 dark:bg-gray-800">
                      Código / Detalle
                    </th>
                    <th
                      class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 dark:bg-gray-800">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-transparent">
                  <tr v-for="sub in ListaODS" :key="sub.id_ods"
                    class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-3">
                      <p class="text-xs font-semibold text-purple-600 mb-1">{{ sub.cod_ods }}</p>
                      <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ sub.detalle_ods }}</p>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                      <button @click="prepararEdicionODS(sub)"
                        class="text-blue-600 hover:text-blue-800 font-medium text-xs transition-colors">Editar</button>
                      <button @click="eliminarODS(sub.id_ods)"
                        class="text-red-600 hover:text-red-800 font-medium text-xs transition-colors">Eliminar</button>
                    </td>
                  </tr>

                  <tr v-if="ListaODS.length === 0">
                    <td colspan="2" class="px-4 py-12 text-center text-gray-400 text-sm italic">
                      No hay objetivos registrados
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
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
              Agregar Agenda ODS
            </h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Llene todos los campos para agregar una nueva Agenda ODS.
            </p>
          </div>
          <form class="flex flex-col">
            <div class="px-2 overflow-y-auto custom-scrollbar max-h-[60vh]">
              <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Nombre de la Agenda ODS
                  </label>
                  <input type="text" v-model="objetoguardar.nombre_ag_ods"
                    placeholder="Ej: Agenda 2030 y los Objetivos de desarrollo sostenible una oportunidad para América Latina y el Caribe"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                  <p class="mt-1.5 text-xs text-gray-500">Use un nombre descriptivo y el año actual.</p>
                </div>

                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Periodo de vigencia
                  </label>
                  <input type="text" v-model="objetoguardar.anio_ag_ods" placeholder="Ej: 2024-2028"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                  <p class="mt-1.5 text-xs text-gray-500">Rango de años de la agenda (Ej: 2024-2030).</p>
                  <p v-if="objetoguardar.anio_ag_ods && !/^\d{4}-\d{4}$/.test(objetoguardar.anio_ag_ods)"
                    class="mt-1 text-xs text-red-500 font-medium">
                    ⚠️ Formato inválido. Use el formato AAAA-AAAA (ej: 2024-2030).
                  </p>
                </div>
              </div>
              <div class="mt-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                  Documento Agenda ODS (PDF)
                </label>

                <div @click="$refs.filePDF.click()"
                  class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition-all"
                  :class="archivoPreviewName ? 'border-brand-500 bg-brand-50/20' : 'border-gray-300 hover:border-brand-400 bg-gray-50 dark:bg-gray-800/50'">
                  <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg v-if="!archivoPreviewName" class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <svg v-else class="w-8 h-8 mb-3 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                      <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                    </svg>

                    <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">
                      <span class="font-semibold" v-if="!archivoPreviewName">Haga clic para cargar</span>
                      <span class="font-semibold text-brand-600" v-else>{{ archivoPreviewName }}</span>
                    </p>
                    <p class="text-xs text-gray-400" v-if="!archivoPreviewName">PDF (Máx. 10MB)</p>
                  </div>

                  <input type="file" ref="filePDF" class="hidden" accept="application/pdf"
                    @change="handleFileChange" />
                </div>
              </div>
              <div class="mt-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estado de la
                  Agenda ODS</label>
                <select v-model="objetoguardar.estado_ag_ods"
                  class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:text-white/90">
                  <option value="1">Activo</option>
                  <option value="0">Inactivo</option>
                </select>
              </div>
            </div>

            <div class="flex items-center gap-3 mt-6 lg:justify-end">
              <button @click="isProfileAddressModal = false" type="button"
                class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">
                Cerrar
              </button>
              <button v-if="formIsValid" @click="registrar" :disabled="uploading" type="button"
                class="flex items-center gap-2 w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">

                <!-- Span para el estado de carga (Spinner + Texto) -->
                <span v-if="uploading" class="inline-flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <span>Guardando...</span>
                </span>

                <!-- Span estado normal -->
                <span v-else>
                  Guardar
                </span>
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
              Editar Agenda ODS
            </h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Los datos mostrados son los actuales de la Agenda ODS. Realice los cambios necesarios y guarde.
            </p>
          </div>
          <form class="flex flex-col">
            <div class="px-2 overflow-y-auto custom-scrollbar max-h-[60vh]">
              <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Nombre de la Agenda ODS
                  </label>
                  <input type="text" v-model="objetoeditar.nombre_ag_ods"
                    placeholder="Ej: Agenda 2030 y los Objetivos de desarrollo sostenible una oportunidad para América Latina y el Caribe"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                  <p class="mt-1.5 text-xs text-gray-500">Use un nombre descriptivo y el año actual.</p>
                </div>

                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Periodo de vigencia
                  </label>
                  <input type="text" v-model="objetoeditar.anio_ag_ods" placeholder="Ej: 2024-2028"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                  <p class="mt-1.5 text-xs text-gray-500">Rango de años de la agenda (Ej: 2024-2030).</p>
                  <p v-if="objetoeditar.anio_ag_ods && !/^\d{4}-\d{4}$/.test(objetoeditar.anio_ag_ods)"
                    class="mt-1 text-xs text-red-500 font-medium">
                    ⚠️ Formato inválido. Use el formato AAAA-AAAA (ej: 2024-2030).
                  </p>
                </div>
              </div>
              <div v-if="objetoeditar.ad_archivo_url && !archivoSeleccionado" class="mt-4 col-span-1 lg:col-span-2">
                <label class="block text-xs font-bold mb-2 text-gray-700 dark:text-gray-300">Documento Agenda ODS
                  Actual:</label>
                <div
                  class="w-full h-[400px] border border-gray-300 dark:border-gray-700 rounded-xl overflow-hidden bg-gray-50">
                  <iframe :src="objetoeditar.ad_archivo_url" class="w-full h-full border-0"></iframe>
                </div>
                <div class="mt-2 text-right">
                  <a :href="objetoeditar.ad_archivo_url" target="_blank"
                    class="text-sm text-brand-600 hover:underline">Abrir en nueva pestaña</a>
                </div>
              </div>

              <!-- ZONA PARA SUBIR/REEMPLAZAR PDF -->
              <div class="mt-5 col-span-1 lg:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                  {{ objetoeditar.ad_archivo_url ? 'Subir un nuevo documento para reemplazar el actual (PDF)' :
                  'Documento Agenda ODS (PDF)' }}
                </label>

                <div @click="$refs.filePDF.click()"
                  class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition-all"
                  :class="archivoPreviewName ? 'border-brand-500 bg-brand-50/20' : 'border-gray-300 hover:border-brand-400 bg-gray-50 dark:bg-gray-800/50'">
                  <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg v-if="!archivoPreviewName" class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <svg v-else class="w-8 h-8 mb-3 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                      <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                    </svg>

                    <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">
                      <span class="font-semibold" v-if="!archivoPreviewName">
                        {{ objetoeditar.ad_archivo_url ? 'Haga clic para reemplazar' : 'Haga clic para cargar' }}
                      </span>
                      <span class="font-semibold text-brand-600" v-else>{{ archivoPreviewName }}</span>
                    </p>
                    <p class="text-xs text-gray-400" v-if="!archivoPreviewName">PDF (Máx. 10MB)</p>
                  </div>

                  <!-- NOTA: Cambié ref="filePDF" por ref="filePDF" para ser coherentes -->
                  <input type="file" ref="filePDF" class="hidden" accept="application/pdf" @change="handleFileChange" />
                </div>

                <!-- Botón para cancelar el archivo recién seleccionado -->
                <div class="mt-2 text-right" v-if="archivoPreviewName && objetoeditar.ad_archivo_url">
                  <button @click.prevent="cancelarNuevoPDF" class="text-sm text-red-500 hover:underline">
                    Cancelar nuevo archivo y mantener el actual
                  </button>
                </div>
              </div>

              <div class="mt-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estado de la
                  Agenda ODS</label>
                <select v-model="objetoeditar.estado_ag_ods"
                  class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:text-white/90">
                  <option value="1">Activo</option>
                  <option value="0">Inactivo</option>
                </select>
              </div>

            </div>

            <div class="flex items-center gap-3 mt-6 lg:justify-end">
              <button @click="isEditModalOpen = false" type="button"
                class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">
                Cerrar
              </button>
              <button v-if="formIsValidEdit" @click="Update" :disabled="uploading" type="button"
                class="flex items-center gap-2 w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">

                <!-- Span para el estado de carga (Spinner + Texto) -->
                <span v-if="uploading" class="inline-flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <span>Actualizando...</span>
                </span>

                <!-- Span estado normal -->
                <span v-else>
                  Guardar cambios
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
import { useUsuario } from "@/composables/useUsuario";
import Modal from '@/components/Modal/Modal.vue'
import { mostraralertas2, enviarsolig, eliminacion, confimarhabi, elimnarpermanente } from '@/assets/js/function/funciones';

export default {
  data() {
    return {
      idus: 0,
      baseUrl: "/inves",
      UsuarioInfo: {},
      objetoguardar: {
        nombre_ag_ods: "",
        anio_ag_ods: "",
        estado_ag_ods: 0,
        archivo_ag_ods: ""
      },
      botonCargando: null,
      objetoeditar: {
        id_ag_ods: 0,
        nombre_ag_ods: "",
        anio_ag_ods: "",
        estado_ag_ods: 0,
        archivo_ag_ods: "",       // El nombre del archivo en la BD
        ad_archivo_url: null
      },
      filteredarray: [],
      searchQuery: "",
      selectedStatus: "", // Nuevo: estado seleccionado
      isFilterDropdownOpen: false, // Nuevo: control del menu
      isProfileAddressModal: false,
      cargando: false,
      currentPage: 1,
      lastPage: 1,
      buscando: false, // Mantenido, pero no se usa en la lógica de paginación actual
      debouncedFilter: null,
      archivoSeleccionado: null,
      archivoPreviewName: '',
      uploading: false,
      isODSModalOpen: false,
      selectedAgendaODS: null,
      ListaODS: [],
      isEditingODS: false,
      ObjetivosODSForm: {
        id_ods: null,
        id_ag_ods: null,
        cod_ods: '',
        detalle_ods: ''
      },
      codNumero: '', // Solo el número (ej: 1)
    };
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

    formIsValid() {
      const regexAnio = /^\d{4}-\d{4}$/;
      const regexLink = /^https?:\/\/.+/; // Validación básica para URLs

      return (
        this.objetoguardar.nombre_ag_ods.trim() !== '' &&
        regexAnio.test(this.objetoguardar.anio_ag_ods) && // <--- Validación aquí
        this.objetoguardar.estado_ag_ods !== null  // Si es obligatorio
      );
    },
    formIsValidEdit() {
      const regexAnio = /^\d{4}-\d{4}$/;
      const regexLink = /^https?:\/\/.+/; // Validación básica para URLs
      return (
        this.objetoeditar.nombre_ag_ods.trim() !== '' &&
        regexAnio.test(this.objetoeditar.anio_ag_ods) && // <--- Validación aquí
        this.objetoeditar.estado_ag_ods !== null 
      );
    },


  },
  methods: {
    async abriModalODS(obj) {
      this.botonCargando = 'objetivos_' + obj.id_ag_ods;
      try{
        this.selectedAgendaODS = obj;
        this.ObjetivosODSForm.id_ag_ods = obj.id_ag_ods;
        this.cancelarEdicionODS(); // Limpia el form
        await this.getODS();
        this.isODSModalOpen = true;
      }catch(e){
        console.error(e)
      }finally{
        this.botonCargando = null;
      }
      
    },

    async getODS() {
      try {
        const resp = await API.get(`${this.baseUrl}/ods/${this.selectedAgendaODS.id_ag_ods}`);
        this.ListaODS = resp.data.data || [];
      } catch (error) {
        console.error("Error al obtener ODS:", error);
      }
    },

    async guardarODS() {
      if (!this.codNumero || !this.ObjetivosODSForm.detalle_ods) {
        mostraralertas2("Todos lo campos son obligatorios", "warning");
        return;
      }

      try {
        this.ObjetivosODSForm.cod_ods = 'ODS ' + this.codNumero;
        const metodo = this.isEditingODS ? 'PUT' : 'POST';
        const url = this.isEditingODS
          ? `${this.baseUrl}/ods/${this.ObjetivosODSForm.id_ods}`
          : `${this.baseUrl}/ods`;

        const exito = await enviarsolig(metodo, this.ObjetivosODSForm, url, 'Operación exitosa');
        if (exito) {
          this.cancelarEdicionODS();
          this.getODS();
        }
      } catch (error) {
        console.error("Error al guardar subsistema:", error);
      }
    },

    prepararEdicionODS(obj) {
      this.isEditingODS = true;
      this.ObjetivosODSForm.id_ods = obj.id_ods;
      this.ObjetivosODSForm.detalle_ods = obj.detalle_ods;
      this.codNumero = obj.cod_ods.replace('ODS', '');
    },

    cancelarEdicionODS() {
      this.isEditingODS = false;
      this.ObjetivosODSForm.id_ods = null;
      this.ObjetivosODSForm.cod_ods = '';
      this.codNumero = '';
      this.ObjetivosODSForm.detalle_ods = '';
    },

    async eliminarODS(id) {
      const response = await elimnarpermanente(
        `${this.baseUrl}/ods/`,
        id,
        'Eliminar ODS',
        '¿Realmente desea eliminar el ODS?'
      );
      if (response && response.status === 200) {
        this.ListaODS = this.ListaODS.filter(sub => sub.id_ods !== id);
      }
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
        this.$refs.filePDF.value = null;
        //Devolver sin hacer nada más
        return;
      }
      //Validar que el tamaño del archivo no exceda el límite de 10 MB, si no se cumple se muestra una alerta y se limpia el archivo seleccionado
      const maxMB = 20;
      //Si el tamaño del archivo es mayor que el límite de 10 MB, se muestra una alerta y se limpia el archivo seleccionado
      if (file.size > maxMB * 1024 * 1024) {
        //Mostrar una alerta de advertencia si el tamaño del archivo es mayor que el límite de 10 MB, se usa la función mostraralertas2 para mostrar un mensaje de advertencia
        mostraralertas2(`Archivo muy grande. Máx ${maxMB} MB`, 'warning');
        //Limpiar el archivo seleccionado
        this.$refs.filePDF.value = null;
        //Devolver sin hacer nada más
        return;
      }
      //Asignar el archivo seleccionado a la variable archivoSeleccionado
      this.archivoSeleccionado = file;
      //Asignar el nombre del archivo seleccionado a la variable archivoPreviewName
      this.archivoPreviewName = file.name;
    },
    abrirModalEdicion(user) {
      // Clonamos el objeto para no modificar la tabla directamente antes de guardar
      this.botonCargando = 'editar_' + user.id_ag_ods;
      try{
        this.objetoeditar = {
          id_ag_ods: user.id_ag_ods,
          nombre_ag_ods: user.nombre_ag_ods,
          anio_ag_ods: user.anio_ag_ods,
          estado_ag_ods: user.estado_ag_ods,
          archivo_pei: user.archivo_pei,       // Nombre del archivo base
          ad_archivo_url: user.ad_archivo_url
        };
        this.$.setupState.isEditModalOpen = true;
      }catch(e){
        console.error(e)
      }finally{
        this.botonCargando = null;
      }
      
    },
    cancelarNuevoPDF() {
      this.archivoSeleccionado = null;
      this.archivoPreviewName = '';
      if (this.$refs.filePDF) {
        this.$refs.filePDF.value = null;
      }
    },
    async uploadArchivo(anio_ag_ods, oldFilename = null, oldAnio = null) {
      if (!this.archivoSeleccionado) return null; // nada que subir

      try {
        this.uploading = true;
        const form = new FormData();
        form.append('file', this.archivoSeleccionado);
        form.append('anio_ag_ods', anio_ag_ods); // El año actual (nueva carpeta)

        if (oldFilename) {
          form.append('old_filename', oldFilename);
          form.append('old_anio', oldAnio || anio_ag_ods); // Carpeta donde buscar el viejo
        }

        // Si tu backend exige otros campos (ej: tipo), añade aquí
        const resp = await API.post(`${this.baseUrl}/subir_archivo_agenda_ods`, form, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        if (resp && resp.data && resp.data.filename) {
          this.archivoSeleccionado = null;
          this.archivoPreviewName = '';
          this.$refs.filePDF.value = null;
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
    async GetData(page = 1, searchQuery = "") {
      this.cargando = true;

      try {
        const params = {
          page: page,
          search_query: searchQuery,
          id_departamento: this.UsuarioInfo.departamentoUsuario
        };
        const response = await API.get(`${this.baseUrl}/agenda_ods`, { params });
        const data = response.data?.data || [];
        const rootURL = (API.defaults.baseURL || '').replace(/\/api\/?$/, '');
        this.filteredarray = data.map(item => {
          // Limpiamos el año igual que en el backend para la URL
          const folderName = item.anio_ag_ods.replace(/[/\\ ]/g, '_');
          return {
            ...item,
            ad_archivo_url: item.archivo_ag_ods
              ? `${rootURL}/Documentos/AGENDA_ODS/${folderName}/${item.archivo_ag_ods}`
              : null
          };
        });
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
      if (this.uploading) return;
      try {
        this.uploading = true;
        const params = {
          nombre_ag_ods: this.objetoguardar.nombre_ag_ods,
          anio_ag_ods: this.objetoguardar.anio_ag_ods,
          estado_ag_ods: this.objetoguardar.estado_ag_ods,
          id_departamento: this.UsuarioInfo.departamentoUsuario
        };
        const res = await API.post(`${this.baseUrl}/agenda_ods`, params);
        if (res.data && res.data.data && this.archivoSeleccionado) {
          const agendaId = res.data.data.id_ag_ods;
          const uploadResp = await this.uploadArchivo(this.objetoguardar.anio_ag_ods, null, agendaId);
          if (uploadResp && uploadResp.filename) {
            await API.put(`${this.baseUrl}/agenda_ods/${agendaId}`, {
              ...params,
              archivo_ag_ods: uploadResp.filename
            });
          }  
        }
        mostraralertas2(res.data.mensaje || 'Agenda ODS registrado con éxito', 'success');
        this.$.setupState.isProfileAddressModal = false;
        this.limpiarFormulario();
        this.actualizar();
      } catch (error) {
        console.error("❌ Error al registrar ODS:", error.response?.data || error);
      }finally {
        this.uploading = false; // Desactiva el loader al terminar
      }
    },
    async Update() {
      if (this.uploading) return;
      try {
        this.uploading = true;
        const params = {
          nombre_ag_ods: this.objetoeditar.nombre_ag_ods,
          anio_ag_ods: this.objetoeditar.anio_ag_ods,
          estado_ag_ods: this.objetoeditar.estado_ag_ods,
          id_departamento: this.UsuarioInfo.departamentoUsuario
        };
        const response = await API.put(`${this.baseUrl}/agenda_ods/${this.objetoeditar.id_ag_ods}`, params);
        if (response.data && this.archivoSeleccionado) {
          const original = this.filteredarray.find(i => i.id_ag_ods === this.objetoeditar.id_ag_ods);
          const anioAnterior = original ? original.anio_ag_ods : this.objetoeditar.anio_ag_ods;
          const nombreArchivoAnterior = original ? original.archivo_ag_ods : null;
          const uploadResp = await this.uploadArchivo(
            this.objetoeditar.anio_ag_ods,
            nombreArchivoAnterior,
            anioAnterior
            );
          if (uploadResp && uploadResp.filename) {
            await API.put(`${this.baseUrl}/agenda_ods/${this.objetoeditar.id_ag_ods}`, {
              ...params,
              archivo_ag_ods: uploadResp.filename
            });
          } 
        }   
        mostraralertas2(response.data.mensaje || 'Agenda ODS actualizado con éxito', 'success');
        this.$.setupState.isEditModalOpen = false;
        this.limpiarFormulario();
        this.actualizar();
      } catch (error) {
        console.error("❌ Error al registrar ODS:", error.response?.data || error);
      } finally {
        this.uploading = false; // Desactiva el loader al terminar
      }
    },
    limpiarFormulario() {
      this.objetoguardar = {
        nombre_ag_ods: "",
        anio_ag_ods: "",
        estado_ag_ods: 0,
        link_ag_ods: ""
      };
      this.objetoeditar = {
        id_ag_ods: 0,
        nombre_ag_ods: "",
        anio_ag_ods: "",
        estado_ag_ods: 0,
        link_ag_ods: ""
      };
    },
    eliminar(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/inhabilitar_agenda_ods/`,
          id,
          'Deshabilitar registro',
          '¿Realmente desea deshabilitar la Agenda ODS  ' + nombre + '?',
          this.actualizar,this.UsuarioInfo.departamentoUsuario    // 👈 callback para refrescar la tabla al confirmar
        );
      } catch (error) {
        console.error("Error al eliminar la Agenda ODS:", error);
        this.cargando = false;
      }
    },
    async habilitar(id, nombre) {
      try {
        // No hace falta poner this.cargando = true aquí si confimarhabi maneja la alerta,
        // pero si lo haces, asegúrate de cerrarlo.
        await confimarhabi(
          `${this.baseUrl}/habilitar_agenda_ods/`,
          id,
          'Habilitar registro',
          `¿Desea habilitar la Agenda ODS "${nombre}"?`,
          this.actualizar,this.UsuarioInfo.departamentoUsuario 
        );
      } catch (error) {
        // Este catch capturaría errores si confimarhabi hace un 'throw'
        console.error("Error en el componente habilitar:", error);
      }
    },

  },
};
</script>
