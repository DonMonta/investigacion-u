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
            <input type="text" placeholder="Ingresa el nombre de la Linea de Investigación a buscar..."
              v-model="searchQuery" @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
          </div>
        </form>
      </div>

      <div class="flex items-center gap-3">


        <button @click="isProfileAddressModal = true"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
          Añadir Linea de Invesigación
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
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Linea de Investigación</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Facultades/Sub-lineas</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Estado</p>
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
          <tr v-else v-for="post in filteredarray" :key="post.id_lin_investiga"
            class="border-t border-gray-100 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:bg-white/[0.02] transition-colors">

            <td class="py-5 px-4 whitespace-nowrap">
              <p class="text-gray-600 text-sm font-medium dark:text-gray-400">{{ post.id_lin_investiga }}</p>
            </td>

            <td class="py-5 px-4 whitespace-normal break-words min-w-[250px] max-w-[450px]">
              <div>
                <p class="font-bold text-gray-800 text-base dark:text-white/90 leading-relaxed">
                  {{ post.nombre_lin }}
                </p>
              </div>
            </td>
            <td class="py-5 px-4">
              <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full w-fit">
                  {{ post.facultades_count }} Facultades
                </span>
                <span class="text-xs font-medium text-cyan-600 bg-cyan-50 px-2 py-0.5 rounded-full w-fit">
                  {{ post.sub_linea_investigacion_count }} Sub-lineas
                </span>
              </div>
            </td>
            <td class="py-5 px-4 whitespace-nowrap">
              <span :class="{
                'rounded-lg px-3 py-1 text-xs font-bold uppercase tracking-wider': true,
                'bg-green-100 text-green-700 dark:bg-success-500/15 dark:text-success-500': post.estado_lin_investiga === 1,
                'bg-orange-100 text-orange-700 dark:bg-warning-500/15 dark:text-orange-400': post.estado_lin_investiga === 0
              }">
                {{ post.estado_lin_investiga === 1 ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="py-3 text-right whitespace-nowrap">
              <div class="flex justify-end gap-2">
                <button @click="abrirModalEdicion(post)" :disabled="botonCargando === 'editar_' + post.id_lin_investiga"
                  class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                  <svg v-if="botonCargando === 'editar_' + post.id_lin_investiga" class="animate-spin h-5 w-5 text-amber-600"
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
                <button v-if="post.estado_lin_investiga === 1 && post.facultades_count > 0"
                  @click="abrirModalsublineas(post)" :disabled="botonCargando === 'sublineas_' + post.id_lin_investiga"
                  class="p-2 text-cyan-600 hover:bg-cyan-50 rounded-lg transition-colors" title="Gestionar Sub-lineas">
                  <svg v-if="botonCargando === 'sublineas_' + post.id_lin_investiga" class="animate-spin h-5 w-5 text-cyan-600"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8l4 4-4 4M8 12h7" />
                  </svg>
                </button>

                <button @click="eliminar(post.id_lin_investiga, post.nombre_lin)" v-if="post.estado_lin_investiga === 1"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>
                <button @click="habilitar(post.id_lin_investiga, post.nombre_lin)"
                  v-if="post.estado_lin_investiga === 0"
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
    <div v-if="isSublineasModalOpen"
      class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 z-[99999]">
      <div
        class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">

        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-start">
          <div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Gestionar Sub-líneas</h3>
            <div
              class="bg-brand-50 rounded-lg p-4 mt-4 border border-brand-100 dark:bg-brand-500/10 dark:border-brand-500/20">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>Línea de Investigación:</strong> {{ lineaSeleccionada?.nombre_lin }}
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                <strong>Facultad:</strong>
                {{ getFacultadInfo(lineaSeleccionada?.idfacultad).siglas }} - {{
                  getFacultadInfo(lineaSeleccionada?.idfacultad).facultad }}
              </p>
            </div>
          </div>
          <button @click="isSublineasModalOpen = false"
            class="p-2 rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition-colors focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div
          class="m-6 mb-2 p-4 rounded-xl bg-blue-50 border border-blue-100 dark:bg-blue-500/10 dark:border-blue-500/20">
          <div class="flex gap-3">
            <svg class="text-blue-600 dark:text-blue-400 shrink-0" width="20" height="20" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="16" x2="12" y2="12" />
              <line x1="12" y1="8" x2="12.01" y2="8" />
            </svg>
            <p class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
              <strong>Nota importante:</strong> Las sub-líneas que añada aquí deben ser únicamente las que se encuentran
              directamente relacionadas con la línea de investigación seleccionada.
            </p>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 pt-2 grid grid-cols-1 md:grid-cols-12 gap-8">

          <div class="md:col-span-4 border-r border-gray-100 dark:border-gray-800 pr-8">
            <div class="space-y-4">

              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Carrera</label>
                <div class="relative group">
                  <div
                    class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50/50 dark:bg-gray-800/50 custom-scrollbar">
                    <div v-for="carr in carrerasList" :key="carr.idCarr"
                      @click="!isCarreraBloqueada(carr.idCarr) && (objetoSublinea.idCarr = carr.idCarr)" :class="[
                        'p-3 border-b border-gray-100 dark:border-gray-800 last:border-0 transition-all',
                        isCarreraBloqueada(carr.idCarr)
                          ? 'opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-800/80'
                          : 'cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20',
                        objetoSublinea.idCarr === carr.idCarr && !isCarreraBloqueada(carr.idCarr) ? 'border-l-4 border-l-blue-600 bg-blue-50/50 dark:bg-blue-900/10' : ''
                      ]">
                      <div class="flex items-center justify-between mb-1">
                        <div>
                          <span class="text-xs font-bold uppercase"
                            :class="isCarreraBloqueada(carr.idCarr) ? 'text-gray-500' : 'text-blue-500'">
                            {{ carr.NombCarr }}
                          </span>
                          <span v-if="isCarreraBloqueada(carr.idCarr)"
                            class="inline-block ml-2 text-[9px] font-semibold px-1.5 py-0.5 bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded">
                            Asignada
                          </span>
                        </div>
                        <span v-if="objetoSublinea.idCarr === carr.idCarr && !isCarreraBloqueada(carr.idCarr)"
                          class="text-blue-600">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="3">
                            <path d="M20 6L9 17l-5-5" />
                          </svg>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <p v-if="!objetoSublinea.idCarr" class="mt-2 text-[10px] text-gray-500 font-medium italic">
                  * Debe seleccionar una carrera disponible
                </p>
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nombre de la
                  Sub-línea</label>
                <textarea v-model="objetoSublinea.nombre_sublin" rows="4" placeholder="Ej: Inteligencia Artificial"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-700 outline-none text-sm dark:text-white" />
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Objeto de
                  Estudio</label>
                <textarea v-model="objetoSublinea.objeto_estudio_sublin" rows="6"
                  placeholder="Descripción del objeto de estudio..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-700 outline-none text-sm dark:text-white"></textarea>
              </div>

              <div class="flex gap-2 mt-2">
                <button v-if="isEditingSublinea" @click="cancelarEdicionSublinea" type="button"
                  class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                  Cancelar
                </button>
                <button @click="guardarSublinea" :disabled="!formSublineaValido" type="button"
                  class="flex-1 rounded-lg bg-brand-500 px-3 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                  {{ isEditingSublinea ? 'Actualizar' : 'Guardar' }}
                </button>
              </div>
            </div>
          </div>

          <div class="md:col-span-8 flex flex-col min-h-0">
            <div class="overflow-y-auto border rounded-xl dark:border-gray-800 custom-scrollbar"
              style="max-height: 500px;">
              <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50 sticky top-0">
                  <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Sub-línea /
                      Objeto</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Carrera</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                  <tr v-if="sublineasList.length === 0">
                    <td colspan="3" class="py-8 text-center text-sm text-gray-500">No hay sub-líneas registradas.</td>
                  </tr>
                  <tr v-else v-for="sub in sublineasList" :key="sub.id_sublin_investiga"
                    class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                    <td class="py-3 px-4">
                      <p class="font-bold text-sm text-gray-800 dark:text-white/90">{{ sub.nombre_sublin }}</p>
                      <p class="text-xs text-gray-500 mt-1 line-clamp-2" :title="sub.objeto_estudio_sublin">{{
                        sub.objeto_estudio_sublin }}</p>
                    </td>
                    <td class="py-3 px-4">
                      <span
                        class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-md dark:bg-blue-500/10 dark:text-blue-400">
                        {{ sub.carreras?.NombCarr || 'Carrera no encontrada' }}
                      </span>
                    </td>
                    <td class="py-3 px-4 text-right">
                      <div class="flex justify-end gap-1">
                        <button @click="prepararEdicionSublinea(sub)"
                          class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </button>
                        <button @click="eliminarSublinea(sub.id_sublin_investiga, sub.nombre_sublin)"
                          class="p-1.5 text-red-600 hover:bg-red-50 rounded-md transition-colors">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
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
              Agregar Linea de Investigación
            </h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Llene todos los campos para agregar un nueva Linea de Investigación.
            </p>
          </div>
          <form class="flex flex-col">
            <div class="px-2 overflow-y-auto custom-scrollbar max-h-[60vh]">
              <div class="mt-5">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Nombre de la Linea de Investigación
                  </label>
                  <textarea v-model="objetoguardar.nombre_lin" rows="4"
                    placeholder="Ej: Línea de Investigación en Energías Renovables"
                    class="w-full px-4 py-2 border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 outline-none" />

                </div>

              </div>
              <div class="col-span-1 lg:col-span-2 mt-2">
                <label class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-400">
                  Seleccione la Facultad
                </label>

                <div
                  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-60 overflow-y-auto custom-scrollbar p-1">
                  <div v-for="fac in facultades" :key="fac.idfacultad"
                    @click="objetoguardar.idfacultad = fac.idfacultad" :class="[
                      'relative cursor-pointer rounded-xl border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md',
                      objetoguardar.idfacultad === fac.idfacultad
                        ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500 dark:border-brand-500 dark:bg-brand-500/10'
                        : 'border-gray-200 bg-white hover:border-brand-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600'
                    ]">
                    <div class="flex items-start justify-between mb-1">
                      <span :class="[
                        'font-bold text-sm tracking-wide',
                        objetoguardar.idfacultad === fac.idfacultad ? 'text-brand-700 dark:text-brand-400' : 'text-gray-800 dark:text-white/90'
                      ]">
                        {{ fac.siglas || 'N/A' }}
                      </span>

                      <div v-if="objetoguardar.idfacultad === fac.idfacultad"
                        class="flex-shrink-0 bg-brand-500 rounded-full p-0.5 text-white shadow-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                    </div>

                    <p class="text-xs mt-2 line-clamp-2" :class="[
                      objetoguardar.idfacultad === fac.idfacultad ? 'text-brand-600/80 dark:text-brand-400/80' : 'text-gray-500 dark:text-gray-400'
                    ]" :title="fac.facultad">
                      {{ fac.facultad }}
                    </p>
                  </div>
                </div>

                <p v-if="objetoguardar.idfacultad === ''"
                  class="mt-2 text-xs text-red-500 font-medium italic animate-pulse">
                  * Debe seleccionar una facultad obligatoriamente
                </p>
              </div>
              <div class="mt-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estado de la Linea de
                  Investigación</label>

                <select v-model="objetoguardar.estado_lin_investiga"
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
              Editar Linea de Investigación
            </h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Los datos mostrados son los actuales de la Línea de Investigación. Realice los cambios necesarios y
              guarde.
            </p>
          </div>
          <form class="flex flex-col">
            <div class="px-2 overflow-y-auto custom-scrollbar max-h-[60vh]">
              <div class="mt-5">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Nombre de la Línea de Investigación
                  </label>
                  <textarea v-model="objetoeditar.nombre_lin" rows="4"
                    placeholder="Ej: Línea de Investigación en Energías Renovables"
                    class="w-full px-4 py-2 border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 outline-none" />

                </div>

              </div>
              <div class="col-span-1 lg:col-span-2 mt-2">
                <label class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-400">
                  Seleccione la Facultad
                </label>

                <div
                  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-60 overflow-y-auto custom-scrollbar p-1">
                  <div v-for="fac in facultades" :key="fac.idfacultad" @click="objetoeditar.idfacultad = fac.idfacultad"
                    :class="[
                      'relative cursor-pointer rounded-xl border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md',
                      objetoeditar.idfacultad === fac.idfacultad
                        ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500 dark:border-brand-500 dark:bg-brand-500/10'
                        : 'border-gray-200 bg-white hover:border-brand-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600'
                    ]">
                    <div class="flex items-start justify-between mb-1">
                      <span :class="[
                        'font-bold text-sm tracking-wide',
                        objetoeditar.idfacultad === fac.idfacultad ? 'text-brand-700 dark:text-brand-400' : 'text-gray-800 dark:text-white/90'
                      ]">
                        {{ fac.siglas || 'N/A' }}
                      </span>

                      <div v-if="objetoeditar.idfacultad === fac.idfacultad"
                        class="flex-shrink-0 bg-brand-500 rounded-full p-0.5 text-white shadow-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                    </div>

                    <p class="text-xs mt-2 line-clamp-2" :class="[
                      objetoeditar.idfacultad === fac.idfacultad ? 'text-brand-600/80 dark:text-brand-400/80' : 'text-gray-500 dark:text-gray-400'
                    ]" :title="fac.facultad">
                      {{ fac.facultad }}
                    </p>
                  </div>
                </div>

                <p v-if="objetoeditar.idfacultad === ''"
                  class="mt-2 text-xs text-red-500 font-medium italic animate-pulse">
                  * Debe seleccionar una facultad obligatoriamente
                </p>
              </div>

              <div class="mt-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estado de la Linea de
                  Investigación</label>
                <select v-model="objetoeditar.estado_lin_investiga"
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
import Modal from '@/components/Modal/Modal.vue'
import { mostraralertas2, enviarsolig, eliminacion, confimarhabi, elimnarpermanente } from '@/assets/js/function/funciones';

export default {
  data() {
    return {
      idus: 0,
      baseUrl: "/inves",
      botonCargando: null,
      usersarray: [],
      objetoguardar: {
        idfacultad: "",
        nombre_lin: "",
        estado_lin_investiga: 0,
      },
      uploading: false,
      objetoeditar: {
        id_lin_investiga: 0,
        idfacultad: "",
        nombre_lin: "",
        estado_lin_investiga: 0
      },
      filteredarray: [],
      searchQuery: "",
      selectedStatus: "", // Nuevo: estado seleccionado
      isProfileAddressModal: false,
      cargando: false,
      currentPage: 1,
      lastPage: 1,
      buscando: false, // Mantenido, pero no se usa en la lógica de paginación actual
      debouncedFilter: null,
      facultades: [],
      isSublineasModalOpen: false,
      lineaSeleccionada: null,
      carrerasList: [],
      sublineasList: [],
      isEditingSublinea: false,
      objetoSublinea: {
        id_sublin_investiga: null,
        idCarr: "",
        nombre_sublin: "",
        objeto_estudio_sublin: ""
      },
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
    await this.getFacultades();

  },
  computed: {

    formIsValid() {

      return (
        this.objetoguardar.nombre_lin.trim() !== '' &&// <--- Validación aquí
        this.objetoguardar.estado_lin_investiga !== null // Si es obligatorio
      );
    },
    formIsValidEdit() {
      return (
        this.objetoeditar.nombre_lin.trim() !== '' &&
        this.objetoeditar.estado_lin_investiga !== null
      );
    },
    formSublineaValido() {
      return (
        this.objetoSublinea.idCarr !== "" &&
        this.objetoSublinea.nombre_sublin.trim() !== '' &&
        this.objetoSublinea.objeto_estudio_sublin.trim() !== ''
      );
    },


  },
  methods: {
    async getFacultades() {
      try {
        const resp = await API.get(`${this.baseUrl}/getFacultades`);
        this.facultades = resp.data.data || [];
      } catch (error) {
        console.error("Error al obtener facultades:", error);
      }
    },
    isCarreraBloqueada(idCarr) {
      return this.sublineasList.some(sub => {
        // Verifica si el id de la carrera coincide (dependiendo de cómo te lo devuelva el backend)
        const coincideCarrera = sub.idCarr === idCarr || (sub.carreras && sub.carreras.idCarr === idCarr);
        
        // Si estamos editando y la carrera es la misma de la sublínea que estoy editando, NO la bloqueamos.
        const noEsLaSublineaActual = sub.id_sublin_investiga !== this.objetoSublinea.id_sublin_investiga;

        return coincideCarrera && noEsLaSublineaActual;
      });
    },
    getFacultadInfo(idfacultad) {
      if (!idfacultad) return { siglas: '', facultad: '' };
      const fac = this.facultades.find(f => f.idfacultad === idfacultad);
      return fac ? fac : { siglas: 'N/A', facultad: 'Facultad no encontrada' };
    },
    abrirModalEdicion(user) {
      // Clonamos el objeto para no modificar la tabla directamente antes de guardar
      this.botonCargando = 'editar_' + user.id_lin_investiga;
      try{
        this.objetoeditar = {
          id_lin_investiga: user.id_lin_investiga,
          idfacultad: user.idfacultad,
          nombre_lin: user.nombre_lin,
          estado_lin_investiga: user.estado_lin_investiga
        };
        this.$.setupState.isEditModalOpen = true;
      }catch(e){
        console.log(e)
      }finally{
        this.botonCargando = null;
      }
      
    },
    async abrirModalsublineas(post) {
      this.botonCargando = 'sublineas_' + post.id_lin_investiga;
      try{
        this.lineaSeleccionada = post;
        this.cancelarEdicionSublinea(); // Limpia formulario
        this.isSublineasModalOpen = true;
        this.sublineasList = []; // Limpia sub-líneas anteriores
        this.carrerasList = []; // Limpia carreras anteriores
        await this.cargarCarrerasDeFacultad(post.idfacultad);
        await this.cargarSublineas(post.id_lin_investiga);
      }catch(e){
        console.log(e)
      }finally{
        this.botonCargando = null;
      }
      
    },
    async GetData(page = 1, searchQuery = "") {
      this.cargando = true;

      try {
        const params = {
          page: page,
          search_query: searchQuery // Parámetro para búsqueda
        };
        const response = await API.get(`${this.baseUrl}/invi_linea_investiga`, { params });

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
    async cargarCarrerasDeFacultad(idfacultad) {
      try {
        const resp = await API.get(`${this.baseUrl}/getCarreras/${idfacultad}`);
        this.carrerasList = resp.data.data || [];
      } catch (error) {
        console.error("Error obteniendo carreras:", error);
      }
    },

    async cargarSublineas(id_lin_investiga) {
      try {
        const resp = await API.get(`${this.baseUrl}/getSublineas/${id_lin_investiga}`);
        this.sublineasList = resp.data.data || [];
      } catch (error) {
        console.error("Error obteniendo sub-lineas:", error);
      }
    },

    prepararEdicionSublinea(sublinea) {
      this.isEditingSublinea = true;
      this.objetoSublinea = {
        id_sublin_investiga: sublinea.id_sublin_investiga,
        idCarr: sublinea.idCarr,
        nombre_sublin: sublinea.nombre_sublin,
        objeto_estudio_sublin: sublinea.objeto_estudio_sublin
      };
    },

    cancelarEdicionSublinea() {
      this.isEditingSublinea = false;
      this.objetoSublinea = {
        id_sublin_investiga: null,
        idCarr: "",
        nombre_sublin: "",
        objeto_estudio_sublin: ""
      };
    },

    async guardarSublinea() {
      try {
        const params = {
          id_lin_investiga: this.lineaSeleccionada.id_lin_investiga,
          idCarr: this.objetoSublinea.idCarr,
          nombre_sublin: this.objetoSublinea.nombre_sublin,
          objeto_estudio_sublin: this.objetoSublinea.objeto_estudio_sublin
        };

        let exito;
        if (this.isEditingSublinea) {
          // Editar
          exito = await enviarsolig('PUT', params, `${this.baseUrl}/invi_sub_linea_inves/${this.objetoSublinea.id_sublin_investiga}`, 'Sub-línea actualizada exitosamente');
        } else {
          // Crear
          exito = await enviarsolig('POST', params, `${this.baseUrl}/invi_sub_linea_inves`, 'Sub-línea registrada exitosamente');
        }

        if (exito) {
          this.cancelarEdicionSublinea();
          await this.cargarSublineas(this.lineaSeleccionada.id_lin_investiga);
          this.actualizar(); // Refresca la tabla principal por si cambia el contador
        } else {
          mostraralertas2("Error al procesar la Sub-línea", "error");
        }
      } catch (error) {
        console.error("Error al guardar sub-linea:", error);
      }
    },

    eliminarSublinea(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/invi_sub_linea_inves/`,
          id,
          'Eliminar Sub-línea',
          `¿Realmente desea eliminar la sub-línea ${nombre}?`,
          () => {
            // Callback tras confirmación y éxito
            this.cargarSublineas(this.lineaSeleccionada.id_lin_investiga);
            this.actualizar();
          }
        );
      } catch (error) {
        console.error("Error al eliminar sub-linea:", error);
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
          nombre_lin: this.objetoguardar.nombre_lin,
          idfacultad: this.objetoguardar.idfacultad,
          estado_lin_investiga: this.objetoguardar.estado_lin_investiga
        };
        const exito = await enviarsolig('POST', params, `${this.baseUrl}/invi_linea_investiga`, 'Línea de investigación registrada con éxito');
        if (exito) {
          this.$.setupState.isProfileAddressModal = false;

          this.limpiarFormulario();
          this.actualizar();
        } else {
          mostraralertas2("No se pudo registrar la Línea de Investigación", "error");
        }
      } catch (error) {
        console.error("❌ Error al registrar Línea de Investigación:", error.response?.data || error);
      } finally {
        this.uploading = false; // Desactiva el loader al terminar
      }
    },
    async Update() {
      if (this.uploading) return;
      try {
        this.uploading = true;
        const params = {
          nombre_lin: this.objetoeditar.nombre_lin,
          idfacultad: this.objetoeditar.idfacultad,
          estado_lin_investiga: this.objetoeditar.estado_lin_investiga
        };
        const exito = await enviarsolig('PUT', params, `${this.baseUrl}/invi_linea_investiga/${this.objetoeditar.id_lin_investiga}`, 'Línea de investigación actualizada con éxito');
        if (exito) {
          this.$.setupState.isEditModalOpen = false;

          this.limpiarFormulario();
          this.actualizar();
        } else {
          this.$.setupState.isEditModalOpen = false;
          mostraralertas2("No se pudo editar la Línea de Investigación", "error");
        }
      } catch (error) {
        console.error("❌ Error al actualizar la Línea de Investigación:", error.response?.data || error);
      } finally {
        this.uploading = false; // Desactiva el loader al terminar
      }
    },
    limpiarFormulario() {
      this.objetoguardar = {
        nombre_lin: "",
        idfacultad: "",
        estado_lin_investiga: 0
      };
      this.objetoeditar = {
        id_lin_investiga: 0,
        nombre_lin: "",
        idfacultad: "",
        estado_lin_investiga: 0
      };
    },
    eliminar(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/inhabilitar_linea_investiga/`,
          id,
          'Deshabilitar registro',
          '¿Realmente desea deshabilitar la Línea de Investigación  ' + nombre + '?',
          this.actualizar   // 👈 callback para refrescar la tabla al confirmar
        );
      } catch (error) {
        console.error("Error al eliminar la Línea de Investigación:", error);
        this.cargando = false;
      }
    },
    async habilitar(id, nombre) {
      try {
        // No hace falta poner this.cargando = true aquí si confimarhabi maneja la alerta,
        // pero si lo haces, asegúrate de cerrarlo.
        await confimarhabi(
          `${this.baseUrl}/habilitar_linea_investiga/`,
          id,
          'Habilitar registro',
          `¿Desea habilitar la Línea de Investigación "${nombre}"?`,
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
