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
            <input type="text" placeholder="Ingresa el nombre del PLANDNE a buscar..." v-model="searchQuery"
              @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
          </div>
        </form>
      </div>

      <div class="flex items-center gap-3">


        <button @click="isProfileAddressModal = true"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
          Añadir PEI
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
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">PLANDNE</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Objetivos/Políticas</p>
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
          <tr v-else v-for="post in filteredarray" :key="post.id_pladne"
            class="border-t border-gray-100 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:bg-white/[0.02] transition-colors">

            <td class="py-5 px-4 whitespace-nowrap">
              <p class="text-gray-600 text-sm font-medium dark:text-gray-400">{{ post.id_pladne }}</p>
            </td>

            <td class="py-5 px-4 whitespace-nowrap">
              <div>
                <p class="font-bold text-gray-800 text-base dark:text-white/90">
                  {{ post.nombre_plandne }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5 italic">Periodo: {{ post.anio_plandne }}</p>
              </div>
            </td>
            <td class="py-5 px-4">
              <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full w-fit">
                  {{ post.objetivos_plandne_count }} Objetivos
                </span>
                <span class="text-xs font-medium text-cyan-600 bg-cyan-50 px-2 py-0.5 rounded-full w-fit">
                  {{ post.politicas_plandne_count }} Políticas
                </span>
              </div>
            </td>
            <td class="py-5 px-4 whitespace-nowrap">
              <span :class="{
                'rounded-lg px-3 py-1 text-xs font-bold uppercase tracking-wider': true,
                'bg-green-100 text-green-700 dark:bg-success-500/15 dark:text-success-500': post.estado_plandne === 1,
                'bg-orange-100 text-orange-700 dark:bg-warning-500/15 dark:text-orange-400': post.estado_plandne === 0
              }">
                {{ post.estado_plandne === 1 ? 'Activo' : 'Inactivo' }}
              </span>
            </td>

            <td class="py-5 px-4">
              <div v-if="post.link_plandne">
                <a :href="post.link_plandne" target="_blank"
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
                <button @click="abrirModalEdicion(post)"
                  class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </button>
                <button v-if="post.estado_plandne === 1 && post.objetivos_plandne_count > 0"
                  @click="abrirModalPoliticas(post)"
                  class="p-2 text-cyan-600 hover:bg-cyan-50 rounded-lg transition-colors" title="Gestionar Objetivos">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8l4 4-4 4M8 12h7" />
                  </svg>
                </button>
                <button v-if="post.estado_plandne === 1" @click="abrirModalObjPol(post)"
                  class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                  title="Gestionar Objetivos">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v18m9-9H3" />
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                  </svg>
                </button>
                <button @click="eliminar(post.id_plandne, post.nombre_plandne)" v-if="post.estado_plandne === 1"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>
                <button @click="habilitar(post.id_plandne, post.nombre_plandne)" v-if="post.estado_plandne === 0"
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
    <div v-if="isPoliticasModalOpen"
      class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 z-99999">
      <div
        class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">

        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
          <div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Objetivos Estratégicos</h3>
            <p class="text-sm text-success-600 font-medium">{{ selectedPLANDNE?.nombre_plandne }}</p>
          </div>
          <button @click="isPoliticasModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
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
              <strong>Nota importante:</strong> Las políticas que añada aquí deben ser únicamente las que se encuentran
              registradas en el documento oficial del <strong>PLANDNE</strong> seleccionado.
            </p>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-12 gap-8">

          <div class="md:col-span-4 border-r border-gray-100 dark:border-gray-800 pr-8">
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Objetivo Estratégico</label>

                <div class="relative group">
                  <div
                    class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50/50 dark:bg-gray-800/50 custom-scrollbar">
                    <div v-for="sub in ListaObjetivosPol" :key="sub.id_obj_pol_pladne"
                      @click="PoliticasForm.id_obj_pol_pladne = sub.id_obj_pol_pladne" :class="[
                        'p-3 cursor-pointer border-b border-gray-100 dark:border-gray-800 last:border-0 transition-all hover:bg-blue-50 dark:hover:bg-blue-900/20',
                        PoliticasForm.id_obj_pol_pladne === sub.id_obj_pol_pladne ? 'bg-blue-100 dark:bg-blue-900/40 border-l-4 border-l-blue-600' : ''
                      ]">
                      <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold text-purple-600 uppercase">{{ sub.cod_obj_pol }}</span>
                        <span v-if="PoliticasForm.id_obj_pol_pladne === sub.id_obj_pol_pladne" class="text-blue-600">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="3">
                            <path d="M20 6L9 17l-5-5" />
                          </svg>
                        </span>
                      </div>
                      <p class="text-xs text-gray-600 dark:text-gray-400 leading-tight italic">
                        {{ sub.detalle_obj_pol }}
                      </p>
                    </div>
                  </div>
                </div>

                <p v-if="!PoliticasForm.id_obj_pol_pladne" class="mt-2 text-[10px] text-amber-600 font-medium italic">
                  * Debe seleccionar un objetivo de la lista superior
                </p>
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Código</label>
                <div class="flex">
                  <span
                    class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-200 rounded-l-lg text-gray-500">POLÍTICA</span>
                  <input type="text" v-model="codNumero"
                    class="w-full px-4 py-2 border border-gray-200 rounded-r-lg dark:bg-gray-800 dark:border-gray-700 outline-none"
                    placeholder="1.1 o 2-A">
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Detalle de la Política</label>
                <textarea v-model="PoliticasForm.detalle_pol" rows="4"
                  class="w-full px-4 py-2 border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 outline-none"
                  placeholder="Escriba la política..."></textarea>
              </div>

              <button @click="guardarPoliticas" class="w-full btn-primary text-white font-bold py-2 rounded-lg">
                {{ isEditingPoliticas ? 'Actualizar Política' : 'Guardar Política' }}
              </button>
              <button v-if="isEditingPoliticas" @click="cancelarEdicionPoliticas"
                class="w-full text-danger-500 text-sm">Cancelar</button>
            </div>
          </div>

          <div class="md:col-span-8 flex flex-col min-h-0">
            <div class="overflow-y-auto border rounded-xl dark:border-gray-800 custom-scrollbar" style="max-height: 400px;">
              <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500">COD</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500">Objetivo / Detalle</th>
                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500">Acciones</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                  <tr v-for="obj in listaPoliticas" :key="obj.id_pol_pladne">
                    <td class="px-4 py-3 text-sm font-bold text-cyan-600">{{ obj.cod_pol }}</td>
                    <td class="px-4 py-3">
                      <p class="text-xs font-semibold text-purple-600 mb-1">{{ obj.objetivos_plandne?.cod_obj_pol }}
                      </p>
                      <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ obj.detalle_pol }}</p>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                      <button @click="prepararEdicionPol(obj)"
                        class="text-blue-600 hover:underline text-xs">Editar</button>
                      <button @click="eliminarPol(obj.id_pol_pladne)"
                        class="text-red-600 hover:underline text-xs">Eliminar</button>
                    </td>
                  </tr>
                  <tr v-if="listaPoliticas.length === 0">
                    <td colspan="2" class="px-4 py-8 text-center text-gray-400 text-sm italic">No hay políticas
                      registradas</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="isObjetivosPolModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 z-99999">

      <div
        class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">

        <div
          class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-white/[0.02]">
          <div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Objetivos del</h3>
            <p class="text-sm text-success-600 font-medium">{{ selectedPLANDNE?.nombre_plandne }}</p>
          </div>

          <button @click="isObjetivosPolModalOpen = false"
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
              registrados en el documento oficial del <strong>PLANDNE</strong> seleccionado.
            </p>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-12 gap-8">

          <div class="md:col-span-4 border-r border-gray-100 dark:border-gray-800 pr-0 md:pr-8">

            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 uppercase tracking-wider">
              {{ isEditingObjetivosPol ? 'Editar Objetivo' : 'Nuevo Objetivo' }}
            </h4>
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Código</label>
                <div class="flex">
                  <span
                    class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-200 rounded-l-lg text-gray-500">OBJETIVO</span>
                  <input type="number" v-model="codNumero"
                    class="w-full px-4 py-2 border border-gray-200 rounded-r-lg dark:bg-gray-800 dark:border-gray-700 outline-none"
                    placeholder="1">
                </div>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Detalle del Objetivo</label>
                <textarea v-model="ObjetivosPolForm.detalle_obj_pol" rows="4"
                  class="w-full px-4 py-2 border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 outline-none"
                  placeholder="Escriba el objetivo del PLANDNE..."></textarea>
              </div>
              <div class="flex gap-2">
                <button @click="guardarObjetivosPol"
                  class="flex-1 btn-primary text-white font-bold py-2 rounded-lg transition-colors">
                  {{ isEditingObjetivosPol ? 'Actualizar' : 'Guardar' }}
                </button>
                <button v-if="isEditingObjetivosPol" @click="cancelarEdicionObjetivosPol"
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
                  <tr v-for="sub in ListaObjetivosPol" :key="sub.id_obj_pol_pladne"
                    class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-3">
                      <p class="text-xs font-semibold text-purple-600 mb-1">{{ sub.cod_obj_pol }}</p>
                      <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ sub.detalle_obj_pol }}</p>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                      <button @click="prepararEdicionObjetivosPol(sub)"
                        class="text-blue-600 hover:text-blue-800 font-medium text-xs transition-colors">Editar</button>
                      <button @click="eliminarObjetivosPol(sub.id_obj_pol_pladne)"
                        class="text-red-600 hover:text-red-800 font-medium text-xs transition-colors">Eliminar</button>
                    </td>
                  </tr>

                  <tr v-if="ListaObjetivosPol.length === 0">
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
              Agregar PLANDNE
            </h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Llene todos los campos para agregar un nuevo PLANDNE.
            </p>
          </div>
          <form class="flex flex-col">
            <div class="px-2 overflow-y-auto custom-scrollbar">
              <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Nombre del PLANDNE
                  </label>
                  <input type="text" v-model="objetoguardar.nombre_plandne"
                    placeholder="Ej: Plan de Desarrollo para el Nuevo Ecuador 2026"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                  <p class="mt-1.5 text-xs text-gray-500">Use un nombre descriptivo y el año actual.</p>
                </div>

                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Periodo de vigencia
                  </label>
                  <input type="text" v-model="objetoguardar.anio_plandne" placeholder="Ej: 2024-2028"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                  <p class="mt-1.5 text-xs text-gray-500">Rango de años del plan (Ej: 2024-2030).</p>
                  <p v-if="objetoguardar.anio_plandne && !/^\d{4}-\d{4}$/.test(objetoguardar.anio_plandne)"
                    class="mt-1 text-xs text-red-500 font-medium">
                    ⚠️ Formato inválido. Use el formato AAAA-AAAA (ej: 2024-2030).
                  </p>
                </div>
              </div>
              <div class="mt-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                  Link del Plan de Desarrollo
                </label>
                <input type="text" v-model="objetoguardar.link_plandne" placeholder="Ej: https://www.google.com"
                  class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                <p v-if="objetoguardar.link_plandne && !/^https?:\/\/.+/.test(objetoguardar.link_plandne)"
                  class="mt-1 text-xs text-red-500 font-medium">
                  ⚠️ Formato inválido. Use el formato https://www.google.com.
                </p>
              </div>
              <div class="mt-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estado del
                  PLANDNE</label>
                <select v-model="objetoguardar.estado_plandne"
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
              <button v-if="formIsValid" @click="registrar" type="button"
                class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-lg transition-all">
                Guardar PLANDNE
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
              Editar PLANDNE
            </h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Los datos mostrados son los actuales del PLANDNE. Realice los cambios necesarios y guarde.
            </p>
          </div>
          <form class="flex flex-col">
            <div class="px-2 overflow-y-auto custom-scrollbar">
              <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Nombre del PLANDNE
                  </label>
                  <input type="text" v-model="objetoeditar.nombre_plandne"
                    placeholder="Ej: Plan de Desarrollo para el Nuevo Ecuador 2026"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                  <p class="mt-1.5 text-xs text-gray-500">Use un nombre descriptivo y el año actual.</p>
                </div>

                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Periodo de vigencia
                  </label>
                  <input type="text" v-model="objetoeditar.anio_plandne" placeholder="Ej: 2024-2028"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                  <p class="mt-1.5 text-xs text-gray-500">Rango de años del plan (Ej: 2024-2030).</p>
                  <p v-if="objetoeditar.anio_plandne && !/^\d{4}-\d{4}$/.test(objetoeditar.anio_plandne)"
                    class="mt-1 text-xs text-red-500 font-medium">
                    ⚠️ Formato inválido. Use el formato AAAA-AAAA (ej: 2024-2030).
                  </p>
                </div>
              </div>
              <div class="mt-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                  Link del Plan de Desarrollo
                </label>
                <input type="text" v-model="objetoeditar.link_plandne" placeholder="Ej: https://www.google.com"
                  class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                <p v-if="objetoeditar.link_plandne && !/^https?:\/\/.+/.test(objetoeditar.link_plandne)"
                  class="mt-1 text-xs text-red-500 font-medium">
                  ⚠️ Formato inválido. Use el formato https://www.google.com.
                </p>
              </div>

              <div class="mt-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estado del
                  PLANDNE</label>
                <select v-model="objetoeditar.estado_plandne"
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
      baseUrl: "/vin",

      usersarray: [],
      objetoguardar: {
        nombre_plandne: "",
        anio_plandne: "",
        estado_plandne: 0,
        link_plandne: ""
      },
      objetoeditar: {
        id_pladne: 0,
        nombre_plandne: "",
        anio_plandne: "",
        estado_plandne: 0,
        link_plandne: ""
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
      isObjetivosPolModalOpen: false,
      selectedPLANDNE: null,
      ListaObjetivosPol: [],
      isEditingObjetivosPol: false,
      ObjetivosPolForm: {
        id_obj_pol_pladne: null,
        id_pladne: null,
        cod_obj_pol: '',
        detalle_obj_pol: ''
      },
      isPoliticasModalOpen: false,
      listaPoliticas: [],
      codNumero: '', // Solo el número (ej: 1)
      isEditingPoliticas: false,
      PoliticasForm: {
        id_pol_pladne: null,
        id_obj_pol_pladne: '',
        cod_pol: '', // Se armará como OE + codNumero
        detalle_pol: ''
      }
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
      const regexAnio = /^\d{4}-\d{4}$/;
      const regexLink = /^https?:\/\/.+/; // Validación básica para URLs

      return (
        this.objetoguardar.nombre_plandne.trim() !== '' &&
        regexAnio.test(this.objetoguardar.anio_plandne) && // <--- Validación aquí
        this.objetoguardar.estado_plandne !== null &&
        regexLink.test(this.objetoguardar.link_plandne) // Si es obligatorio
      );
    },
    formIsValidEdit() {
      const regexAnio = /^\d{4}-\d{4}$/;
      const regexLink = /^https?:\/\/.+/; // Validación básica para URLs
      return (
        this.objetoeditar.nombre_plandne.trim() !== '' &&
        regexAnio.test(this.objetoeditar.anio_plandne) && // <--- Validación aquí
        this.objetoeditar.estado_plandne !== null &&
        regexLink.test(this.objetoeditar.link_plandne)
      );
    },


  },
  methods: {
    async abrirModalPoliticas(pei) {
      this.selectedPLANDNE = pei;
      this.cancelarEdicionPoliticas();

      // 1. Cargar subsistemas del PEI seleccionado para el Select
      const respSub = await API.get(`${this.baseUrl}/obj_pol_plandne/${pei.id_pladne}`);
      this.ListaObjetivosPol = respSub.data.data || [];

      // 2. Cargar objetivos (Tu backend debería filtrar objetivos por PEI a través de los subsistemas)
      await this.getPoliticas();
      this.isPoliticasModalOpen = true;
    },

    async guardarPoliticas() {
      // Validaciones
      if (!this.PoliticasForm.id_obj_pol_pladne || !this.codNumero || !this.PoliticasForm.detalle_pol) {
        mostraralertas2("Todos los campos son obligatorios", "warning");
        return;
      }

      // Armar el código final: OE + numero
      this.PoliticasForm.cod_pol = 'POLÍTICA ' + this.codNumero;

      const metodo = this.isEditingPoliticas ? 'PUT' : 'POST';
      const url = this.isEditingPoliticas
        ? `${this.baseUrl}/politicas_plandne/${this.PoliticasForm.id_pol_pladne}`
        : `${this.baseUrl}/politicas_plandne`;

      // 2. Llamada al servidor
      const exito = await enviarsolig(metodo, this.PoliticasForm, url, 'Objetivo guardado con éxito');

      // 3. Solo si fue exitoso (status 200), limpiamos y refrescamos
      if (exito) {
        this.cancelarEdicionPoliticas();
        this.getPoliticas();
      }
    },

    prepararEdicionPol(obj) {
      this.isEditingPoliticas = true;
      this.PoliticasForm.id_pol_pladne = obj.id_pol_pladne;
      this.PoliticasForm.id_obj_pol_pladne = obj.id_obj_pol_pladne;
      this.PoliticasForm.detalle_pol = obj.detalle_pol;
      // Extraer solo el número del código (quita las letras OE)
      this.codNumero = obj.cod_pol.replace('POLÍTICAS', '');
    },

    cancelarEdicionPoliticas() {
      this.isEditingPoliticas = false;
      this.codNumero = '';
      this.PoliticasForm = { id_pol_pladne: null, id_obj_pol_pladne: '', cod_pol: '', detalle_pol: '' };
    },

    async eliminarPol(id) {
      const res = await elimnarpermanente(`${this.baseUrl}/politicas_plandne/`, id, '¿Eliminar?', 'Esta acción no se puede deshacer');
      if (res && res.status === 200) {
        this.listaPoliticas = this.listaPoliticas.filter(o => o.id_pol_pladne !== id);
      }
    },
    async abrirModalObjPol(obj) {
      this.selectedPLANDNE = obj;
      this.ObjetivosPolForm.id_pladne = obj.id_pladne;
      this.cancelarEdicionObjetivosPol(); // Limpia el form
      await this.getObjPol();
      this.isObjetivosPolModalOpen = true;
    },
    async getPoliticas() {
      try {
        this.cargandoObjetivos = true; // Opcional: para un spinner interno

        // Enviamos el ID del PEI para que el backend sepa qué objetivos buscar
        // a través de sus subsistemas relacionados.
        const resp = await API.get(`${this.baseUrl}/politicas_por_plandne/${this.selectedPLANDNE.id_pladne}`);
        if (resp && resp.data) {
          this.listaPoliticas = resp.data;
        }
      } catch (error) {
        console.error("Error al obtener politicas:", error);
        mostraralertas2("No se pudieron cargar las politicas", "error");
      } finally {
        this.cargandoObjetivos = false;
      }
    },

    async getObjPol() {
      try {
        const resp = await API.get(`${this.baseUrl}/obj_pol_plandne/${this.selectedPLANDNE.id_pladne}`);
        this.ListaObjetivosPol = resp.data.data || [];
      } catch (error) {
        console.error("Error al obtener objetivos políticos:", error);
      }
    },

    async guardarObjetivosPol() {
      if (!this.codNumero || !this.ObjetivosPolForm.detalle_obj_pol) {
        mostraralertas2("Todos lo campos son obligatorios", "warning");
        return;
      }

      try {
        this.ObjetivosPolForm.cod_obj_pol = 'OBJETIVO ' + this.codNumero;
        const metodo = this.isEditingObjetivosPol ? 'PUT' : 'POST';
        const url = this.isEditingObjetivosPol
          ? `${this.baseUrl}/obj_pol_plandne/${this.ObjetivosPolForm.id_obj_pol_pladne}`
          : `${this.baseUrl}/obj_pol_plandne`;

        const exito = await enviarsolig(metodo, this.ObjetivosPolForm, url, 'Operación exitosa');
        if (exito) {
          this.cancelarEdicionObjetivosPol();
          this.getObjPol();
        }
      } catch (error) {
        console.error("Error al guardar subsistema:", error);
      }
    },

    prepararEdicionObjetivosPol(obj) {
      this.isEditingObjetivosPol = true;
      this.ObjetivosPolForm.id_obj_pol_pladne = obj.id_obj_pol_pladne;
      this.ObjetivosPolForm.detalle_obj_pol = obj.detalle_obj_pol;
      this.codNumero = obj.cod_obj_pol.replace('OBJETIVO', '');
    },

    cancelarEdicionObjetivosPol() {
      this.isEditingObjetivosPol = false;
      this.ObjetivosPolForm.id_obj_pol_pladne = null;
      this.ObjetivosPolForm.cod_obj_pol = '';
      this.codNumero = '';
      this.ObjetivosPolForm.detalle_obj_pol = '';
    },

    async eliminarObjetivosPol(id) {
      const response = await elimnarpermanente(
        `${this.baseUrl}/obj_pol_plandne/`,
        id,
        'Eliminar Objetivo',
        '¿Realmente desea eliminar el objetivo?'
      );
      if (response && response.status === 200) {
        this.ListaObjetivosPol = this.ListaObjetivosPol.filter(sub => sub.id_obj_pol_pladne !== id);
      }
    },
    abrirModalEdicion(user) {
      // Clonamos el objeto para no modificar la tabla directamente antes de guardar
      this.objetoeditar = {
        id_pladne: user.id_pladne,
        nombre_plandne: user.nombre_plandne,
        anio_plandne: user.anio_plandne,
        estado_plandne: user.estado_plandne,
        link_plandne: user.link_plandne
      };
      this.$.setupState.isEditModalOpen = true;
    },
    async GetData(page = 1, searchQuery = "") {
      this.cargando = true;

      try {
        const params = {
          page: page,
          search_query: searchQuery // Parámetro para búsqueda
        };
        const response = await API.get(`${this.baseUrl}/plandne`, { params });

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
          nombre_plandne: this.objetoguardar.nombre_plandne,
          anio_plandne: this.objetoguardar.anio_plandne,
          estado_plandne: this.objetoguardar.estado_plandne,
          link_plandne: this.objetoguardar.link_plandne
        };
        const exito = await enviarsolig('POST', params, `${this.baseUrl}/plandne`, 'PLANDNE registrado con éxito');
        if (exito) {
          this.$.setupState.isProfileAddressModal = false;

          this.limpiarFormulario();
          this.actualizar();
        } else {
          mostraralertas2("No se pudo registrar el PLANDNE", "error");
        }
      } catch (error) {
        console.error("❌ Error al registrar PLANDNE:", error.response?.data || error);
      }
    },
    async Update() {
      try {
        const params = {
          nombre_plandne: this.objetoeditar.nombre_plandne,
          anio_plandne: this.objetoeditar.anio_plandne,
          estado_plandne: this.objetoeditar.estado_plandne,
          link_plandne: this.objetoeditar.link_plandne
        };
        const exito = await enviarsolig('PUT', params, `${this.baseUrl}/plandne/${this.objetoeditar.id_pladne}`, 'PLANDNE actualizado con éxito');
        if (exito) {
          this.$.setupState.isEditModalOpen = false;

          this.limpiarFormulario();
          this.actualizar();
        } else {
          this.$.setupState.isEditModalOpen = false;
          mostraralertas2("No se pudo editar el PLANDNE", "error");
        }
      } catch (error) {
        console.error("❌ Error al registrar plandne:", error.response?.data || error);
      }
    },
    limpiarFormulario() {
      this.objetoguardar = {
        nombre_plandne: "",
        anio_plandne: "",
        estado_plandne: 0,
        link_plandne: ""
      };
      this.objetoeditar = {
        id_plandne: 0,
        nombre_plandne: "",
        anio_plandne: "",
        estado_plandne: 0,
        link_plandne: ""
      };
    },
    eliminar(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/inhabilitar_plandne/`,
          id,
          'Deshabilitar registro',
          '¿Realmente desea deshabilitar el PLANDNE  ' + nombre + '?',
          this.actualizar   // 👈 callback para refrescar la tabla al confirmar
        );
      } catch (error) {
        console.error("Error al eliminar el PLANDNE:", error);
        this.cargando = false;
      }
    },
    async habilitar(id, nombre) {
      try {
        // No hace falta poner this.cargando = true aquí si confimarhabi maneja la alerta,
        // pero si lo haces, asegúrate de cerrarlo.
        await confimarhabi(
          `${this.baseUrl}/habilitar_plandne/`,
          id,
          'Habilitar registro',
          `¿Desea habilitar el PLANDNE "${nombre}"?`,
          this.actualizar
        );
      } catch (error) {
        // Este catch capturaría errores si confimarhabi hace un 'throw'
        console.error("Error en el componente habilitar:", error);
      }
    },

  },
};
</script>
