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
            <input type="text" placeholder="Ingresa el nombre número de la convocatoria a buscar..."
              v-model="searchQuery" @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
          </div>
        </form>
      </div>

      <div class="flex items-center gap-3">


        <div class="flex items-center gap-3">
          <button @click="abrirModalRegistro"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            Añadir Convocatoria
          </button>
        </div>
      </div>
    </div>
    <div
      class="max-w-full overflow-x-auto rounded-xl border border-gray-100 shadow-sm custom-scrollbar dark:border-gray-800">
      <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
        <thead class="bg-gray-50/70 dark:bg-gray-900/50">
          <tr>
            <th class="py-4 px-4 text-left w-16">
              <p class="font-bold text-gray-500 text-xs tracking-wider uppercase dark:text-gray-400">ID</p>
            </th>
            <th class="py-4 px-4 text-left">
              <p class="font-bold text-gray-500 text-xs tracking-wider uppercase dark:text-gray-400">Detalles de la
                Convocatoria</p>
            </th>
            <th class="py-4 px-4 text-left w-48">
              <p class="font-bold text-gray-500 text-xs tracking-wider uppercase dark:text-gray-400">Vigencia (Inicio /
                Fin)</p>
            </th>
            <th class="py-4 px-4 text-left w-32">
              <p class="font-bold text-gray-500 text-xs tracking-wider uppercase dark:text-gray-400">Estado</p>
            </th>
            <th class="py-4 px-4 text-left w-32">
              <p class="font-bold text-gray-500 text-xs tracking-wider uppercase dark:text-gray-400">Editar Proyect.</p>
            </th>

            <th class="py-4 px-4 text-left w-32">
              <p class="font-bold text-gray-500 text-xs tracking-wider uppercase dark:text-gray-400">Documento</p>
            </th>
            <th class="py-4 px-4 text-right w-36">
              <p class="font-bold text-gray-500 text-xs tracking-wider uppercase dark:text-gray-400">Acciones</p>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white dark:bg-gray-950 dark:divide-gray-800">

          <tr v-if="cargando">
            <td class="px-5 py-12" colspan="6">
              <div class="flex flex-col justify-center items-center gap-3">
                <span class="animate-spin h-8 w-8 border-4 border-brand-500 border-t-transparent rounded-full"></span>
                <h3 class="text-gray-500 font-medium text-sm dark:text-gray-400">Cargando registros...</h3>
              </div>
            </td>
          </tr>

          <tr v-else v-for="post in filteredarray" :key="post.id_convocatoria"
            class="hover:bg-gray-50/60 dark:hover:bg-white/[0.01] transition-colors group">

            <td class="py-4 px-4 whitespace-nowrap align-top">
              <span class="text-gray-400 text-sm font-semibold dark:text-gray-600">#{{ post.id_convocatoria }}</span>
            </td>

            <td class="py-4 px-4 align-top">
              <div class="max-w-xl flex flex-col gap-2">

                <div>
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400 border border-brand-100 dark:border-brand-500/20">
                    Convocatoria: {{ post.num_convocatoria }}
                  </span>
                </div>

                <h4
                  class="text-sm font-semibold text-gray-900 leading-snug dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                  {{ post.titulo_convocatoria }}
                </h4>

                <div
                  class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5 mt-1 pt-2 border-t border-gray-100 dark:border-gray-900 text-xs text-gray-500 dark:text-gray-400">

                  <div class="flex items-center gap-1.5">
                    <span class="font-medium text-gray-400">Elaborado por:</span>

                    <span v-if="post.cargandoDocente" class="flex items-center gap-1 text-gray-400 italic">
                      <span
                        class="animate-spin h-3 w-3 border-2 border-brand-500 border-t-transparent rounded-full"></span>
                      Buscando...
                    </span>

                    <span v-else-if="post.docente"
                      class="font-semibold text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded"
                      :title="`Cédula: ${post.elaboracion}`">
                      {{ post.docente.nombre_formateado }}
                    </span>

                    <span v-else-if="post.errorDocente" class="text-red-500 font-medium" :title="post.errorDocente">
                      {{ post.elaboracion }} <span class="text-[10px] text-red-400">(No verificado)</span>
                    </span>

                    <span v-else class="italic font-medium text-gray-700 dark:text-gray-300">
                      {{ post.elaboracion }}
                    </span>
                  </div>

                  <div class="flex items-center gap-1.5">
                    <span class="font-medium text-gray-400">Resolución:</span>
                    <span
                      class="font-mono text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 px-1.5 py-0.5 rounded border border-gray-100 dark:border-gray-800">
                      {{ post.num_resolucion || 'N/A' }}
                    </span>
                  </div>

                </div>
              </div>
            </td>

            <td class="py-4 px-4 align-top whitespace-nowrap">
              <div class="flex flex-col gap-1.5">
                <div
                  class="flex items-center gap-1.5 text-xs font-medium text-purple-700 bg-purple-50 dark:bg-purple-500/10 dark:text-purple-400 px-2 py-1 rounded-lg w-fit">
                  <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                  <span>Inicia: {{ post.fecha_inicio }}</span>
                </div>
                <div
                  class="flex items-center gap-1.5 text-xs font-medium text-cyan-700 bg-cyan-50 dark:bg-cyan-500/10 dark:text-cyan-400 px-2 py-1 rounded-lg w-fit">
                  <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                  <span>Finaliza: {{ post.fecha_fin }}</span>
                </div>
              </div>
            </td>

            <td class="py-4 px-4 align-top whitespace-nowrap">
              <span :class="{
                'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide uppercase': true,
                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400': post.estado === 1,
                'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400': post.estado === 0
              }">
                <span class="w-1.5 h-1.5 rounded-full"
                  :class="post.estado === 1 ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                {{ post.estado === 1 ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="py-4 px-4 align-top whitespace-nowrap">
              <span :class="{
                'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide uppercase': true,
                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400': post.habilitar_edicion === 1,
                'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400': post.habilitar_edicion === 0
              }">
                <span class="w-1.5 h-1.5 rounded-full"
                  :class="post.habilitar_edicion === 1 ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                {{ post.habilitar_edicion === 1 ? 'SI' : 'NO' }}
              </span>
            </td>

            <td class="py-4 px-4 align-top whitespace-nowrap">
              <div v-if="post.archivo">
                <a :href="getPdfUrl(post.num_convocatoria,post.archivo)"
                  target="_blank"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 transition-all border border-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                  </svg>
                  <span>Ver PDF</span>
                </a>
              </div>
              <span v-else class="text-xs text-gray-400 italic">Sin documento</span>
            </td>

            <td class="py-4 px-4 align-top text-right whitespace-nowrap">
              <div class="flex justify-end gap-1">
                <button @click="abrirModalEdicion(post)" :disabled="botonCargando === 'editar_' + post.id_convocatoria"
                  class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                  <svg v-if="botonCargando === 'editar_' + post.id_convocatoria"
                    class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
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

                <button @click="eliminar(post.id_convocatoria, post.num_convocatoria)" v-if="post.estado === 1"
                  class="p-1.5 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                  title="Deshabilitar">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>

                <button @click="habilitar(post.id_convocatoria, post.num_convocatoria)" v-if="post.estado === 0"
                  class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-500/10 rounded-lg transition-colors"
                  title="Habilitar">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
              {{ isEditMode ? 'Editar Convocatoria' : 'Agregar Convocatoria' }}
            </h4>
            <p class="text-sm text-gray-500">Complete los datos de la convocatoria.</p>
          </div>

          <form class="flex flex-col gap-5 px-2" @submit.prevent>
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
              <div class="lg:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Título</label>
                <textarea v-model="formulario.titulo_convocatoria" rows="3"
                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-500 resize-none"
                  placeholder="Ingrese el título completo de la convocatoria"></textarea>
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Número de Convocatoria</label>
                <input v-model="formulario.num_convocatoria" type="text"
                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-500"
                  placeholder="Ej: UTELVT-CONV-PROY-2024" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Fecha Inicio</label>
                <input v-model="formulario.fecha_inicio" type="date"
                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-500" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Fecha Fin</label>
                <input v-model="formulario.fecha_fin" type="date"
                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-500" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Número de Resolución</label>
                <input v-model="formulario.num_resolucion" type="text"
                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-500"
                  placeholder="Ej: UTLVTE-060-2024" />
              </div>

              <div class="flex flex-col justify-center">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Estado de la Convocatoria</label>
                <label class="relative inline-flex items-center cursor-pointer mt-2">
                  <input type="checkbox" v-model="estadoBool" class="sr-only peer">
                  <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500">
                  </div>
                  <span class="ml-3 text-sm font-medium text-gray-700">
                    {{ estadoBool ? 'Activo (Visible)' : 'Inactivo (Cerrada)' }}
                  </span>
                </label>
              </div>
              <div class="flex flex-col justify-center">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Habilitar Edición de Proyectos</label>
                <label class="relative inline-flex items-center cursor-pointer mt-2">
                  <input type="checkbox" v-model="edicionBool" class="sr-only peer">
                  <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500">
                  </div>
                  <span class="ml-3 text-sm font-medium text-gray-700">
                    {{ edicionBool ? 'SI (Visible)' : 'NO (Cerrada)' }}
                  </span>
                </label>
              </div>  


            </div>

            <hr class="my-2 border-gray-100">

            <div v-for="campo in camposResponsables" :key="campo.id"
              class="p-5 bg-gray-50/60 rounded-2xl border border-gray-100 dark:bg-gray-900/40 dark:border-gray-800 transition-all">

              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2">
                  <div class="w-1.5 h-4 bg-brand-500 rounded-full"></div>
                  <label class="text-sm font-bold text-gray-800 dark:text-gray-200 capitalize tracking-wide">
                    {{ campo.label }}
                  </label>
                </div>

                <div class="flex p-1 bg-gray-200/70 rounded-xl dark:bg-gray-800/80 w-fit self-end sm:self-auto">
                  <button type="button" @click="campo.tipo = 'persona'"
                    :class="{ 'bg-white shadow-sm text-brand-600 font-bold dark:bg-gray-700 dark:text-brand-400': campo.tipo === 'persona', 'text-gray-500 dark:text-gray-400': campo.tipo !== 'persona' }"
                    class="px-4 py-1.5 text-xs rounded-lg transition-all duration-200">
                    Persona
                  </button>
                  <button type="button" @click="cambiarTipoDepartamento(campo)"
                    :class="{ 'bg-white shadow-sm text-brand-600 font-bold dark:bg-gray-700 dark:text-brand-400': campo.tipo === 'departamento', 'text-gray-500 dark:text-gray-400': campo.tipo !== 'departamento' }"
                    class="px-4 py-1.5 text-xs rounded-lg transition-all duration-200">
                    Departamento
                  </button>
                </div>
              </div>

              <div v-if="campo.tipo === 'persona'" class="flex flex-col gap-3.5">

                <div class="relative flex items-center group">
                  <div
                    class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 dark:text-gray-500">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2.5">
                      <circle cx="11" cy="11" r="8"></circle>
                      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                  </div>
                  <input v-model="campo.cedula" @input="validarNumeros(campo)" type="text" maxlength="10"
                    placeholder="Ingrese los 10 dígitos de la cédula..."
                    class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-28 py-3 text-sm outline-none transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-950 dark:text-white dark:focus:border-brand-500" />

                  <button @click="buscarDocente(campo)" type="button"
                    class="absolute right-1.5 top-1.5 bottom-1.5 bg-gray-900 text-white px-5 rounded-lg text-xs font-semibold hover:bg-gray-800 transition-all active:scale-95 dark:bg-brand-500 dark:hover:bg-brand-600 shadow-sm">
                    Buscar
                  </button>
                </div>

                <div v-if="campo.error"
                  class="flex items-center gap-1.5 text-xs text-red-500 font-medium px-1 animate-fadeIn">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                  </svg>
                  <span>{{ campo.error }}</span>
                </div>

                <div v-if="campo.docente"
                  class="flex items-center gap-4 bg-white p-4 rounded-xl border border-gray-100 shadow-sm dark:bg-gray-950 dark:border-gray-800/60 animate-fadeIn">

                  <div
                    class="w-14 h-14 rounded-full bg-gray-100 flex-shrink-0 border-2 border-gray-200 dark:border-gray-800 dark:bg-gray-900 overflow-hidden flex items-center justify-center shadow-inner relative group/avatar">
                    <img :src="getPhotoUrl(campo.docente.CIInfPer)" @error="handleImgError"
                      class="w-full h-full object-cover" alt="Foto de perfil" />
                  </div>

                  <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                    <h5 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                      {{ campo.docente.nombre_formateado || (campo.docente.NombInfPer + ' ' + campo.docente.ApellInfPer)
                      }}
                    </h5>

                    <div class="flex flex-wrap items-center gap-2 mt-1">
                      <span
                        class="inline-flex items-center text-[11px] font-mono font-medium text-gray-500 bg-gray-100 dark:bg-gray-900 dark:text-gray-400 px-2 py-0.5 rounded">
                        C.I: {{ campo.docente.CIInfPer }}
                      </span>
                      <span v-if="campo.docente.GeneroPer"
                        class="inline-flex items-center text-[11px] font-medium text-brand-600 bg-brand-50/60 dark:bg-brand-500/10 dark:text-brand-400 px-2 py-0.5 rounded">
                        {{ campo.docente.GeneroPer }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="animate-fadeIn">
                <!-- Modo Selección (Lista desplegable) -->
                <div v-if="campo.modoDepartamento !== 'manual'" class="relative">
                  <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                    Seleccione el departamento de la lista
                  </label>
                  
                  <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 dark:text-gray-500">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                      </svg>
                    </div>
                    
                    <select v-model="campo.texto" @change="verificarOpcionOtro(campo)"
                      class="w-full appearance-none rounded-xl border border-gray-300 bg-white pl-10 pr-10 py-3 text-sm outline-none transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-950 dark:text-white dark:focus:border-brand-500 cursor-pointer">
                      <option value="" disabled selected>-- Seleccione un departamento --</option>
                      <!-- Iteramos los departamentos traídos de la API -->
                      <option v-for="dep in departamentosApi" :key="dep.id_departamento" :value="dep.nombre_departamento || dep.nombre">
                        {{ dep.nombre_departamento || dep.nombre }}
                      </option>
                      <!-- Opción comodín para escribir manualmente -->
                      <option value="OTRO_MANUAL" class="font-bold text-brand-600 bg-brand-50">Otro (Escribir manualmente)</option>
                    </select>

                    <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-400">
                      <svg v-if="cargandoDepartamentos" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                      <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                  </div>
                </div>

                <!-- Modo Escritura Manual -->
                <div v-else class="relative animate-fadeIn">
                  <div class="flex justify-between items-end mb-2">
                    <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      Escriba el nombre manualmente
                    </label>
                    <button type="button" @click="volverAListaDepartamentos(campo)" 
                      class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400">
                      Volver a la lista
                    </button>
                  </div>
                  
                  <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 dark:text-gray-500">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                      </svg>
                    </div>
                    <input v-model="campo.texto" type="text"
                      :placeholder="`Escriba el departamento encargado de la ${campo.label}...`"
                      class="w-full rounded-xl border border-brand-300 bg-brand-50/30 pl-10 pr-4 py-3 text-sm outline-none transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 dark:border-brand-700/50 dark:bg-brand-900/10 dark:text-white dark:focus:border-brand-500" />
                  </div>
                </div>
              </div>
            </div>

            <div v-if="formulario.ad_archivo_url && !archivoSeleccionado" class="mt-4 col-span-1 lg:col-span-2">
              <label class="block text-xs font-bold mb-2 text-gray-700 dark:text-gray-300">Documento
                Actual:</label>
              <div
                class="w-full h-[400px] border border-gray-300 dark:border-gray-700 rounded-xl overflow-hidden bg-gray-50">
                <iframe :src="formulario.ad_archivo_url" class="w-full h-full border-0"></iframe>
              </div>
              <div class="mt-2 text-right">
                <a :href="formulario.ad_archivo_url" target="_blank"
                  class="text-sm text-brand-600 hover:underline">Abrir en nueva pestaña</a>
              </div>
            </div>
            <div class="mt-2">
              <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ formulario.ad_archivo_url ? 'Subir un nuevo documento para reemplazar el actual (PDF)' :
                    'Documento Convocatoria (PDF)' }}</label>
              <div @click="$refs.fileFoto.click()"
                class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition-all"
                :class="archivoPreviewName ? 'border-brand-500 bg-brand-50/20' : 'border-gray-300 hover:border-brand-400 bg-gray-50'">
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
                  <p class="mb-1 text-sm text-gray-500">
                    <span class="font-semibold" v-if="!archivoPreviewName">Haga clic para cargar</span>
                    <span class="font-semibold text-brand-600" v-else>{{ archivoPreviewName }}</span>
                  </p>
                  <p class="text-xs text-gray-400" v-if="!archivoPreviewName">PDF (Máx. 10MB)</p>
                </div>
                <input type="file" ref="fileFoto" class="hidden" accept="application/pdf" @change="handleFileChange" />
              </div>
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
                  {{ isEditMode ? 'Guardar Cambios' : 'Registrar Convocatoria' }}
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
import { mostraralertas2, enviarsolig, eliminacion, confimarhabi, elimnarpermanente } from '@/assets/js/function/funciones';

export default {
  data() {
    return {
      idus: 0,
      baseUrl: "/inves",
      botonCargando: null,
      formulario: {
        id_convocatoria: null,
        num_convocatoria: "",
        titulo_convocatoria: "",
        fecha_inicio: "",
        fecha_fin: "",
        num_resolucion: "",
        estado: 1, // Por defecto activo
        archivo: null,
        habilitar_edicion: 0, // Por defecto no habilitado
        tipo_convocatoria: "INVESTIGACIÓN",
        ad_archivo_url: null
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
      archivoSeleccionado: null,
      archivoPreviewName: '',
      uploading: false,
      estadoBool: true,
      edicionBool: false,
      // Configuraciones dinámicas para Elaboración, Revisión y Aprobación
      camposResponsables: {
        elaboracion: { id: 'elaboracion', label: 'elaboración', tipo: 'persona', cedula: '', texto: '', docente: null, error: '' },
        revision: { id: 'revision', label: 'revisión', tipo: 'persona', cedula: '', texto: '', docente: null, error: '' },
        aprobacion: { id: 'aprobacion', label: 'aprobación', tipo: 'persona', cedula: '', texto: '', docente: null, error: '' }
      },
      departamentosApi: [],
      cargandoDepartamentos: false,

    };
  },
  watch: {
    estadoBool(val) {
      this.formulario.estado = val ? 1 : 0;
    },
    edicionBool(val) {
      this.formulario.habilitar_edicion = val ? 1 : 0;
    }
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


  },
  methods: {
    async fetchDepartamentos() {
      // Evitar llamadas repetitivas si ya están cargados
      if (this.departamentosApi.length > 0) return; 

      try {
        this.cargandoDepartamentos = true;
        const res = await API.get(`${this.baseUrl}/listar_departamentos?all=true`);
        console.log(res);
        // Asegurarse de acceder a la data correcta según tu API response
        this.departamentosApi = res.data.data; 
      } catch (error) {
        console.error("Error al cargar departamentos:", error);
        mostraralertas2("No se pudo cargar la lista de departamentos", "warning");
      } finally {
        this.cargandoDepartamentos = false;
      }
    },
    cambiarTipoDepartamento(campo) {
      campo.tipo = 'departamento';
      // Si no tiene el atributo modoDepartamento, se lo agregamos por defecto en 'select'
      if (!campo.modoDepartamento) {
        campo.modoDepartamento = 'select';
      }
      // Llamamos a la API (solo hará petición la primera vez)
      this.fetchDepartamentos();
    },
    verificarOpcionOtro(campo) {
      if (campo.texto === 'OTRO_MANUAL') {
        campo.modoDepartamento = 'manual';
        campo.texto = ''; // Limpiamos el texto para que pueda escribir desde cero
      }
    },
    volverAListaDepartamentos(campo) {
      campo.modoDepartamento = 'select';
      campo.texto = ''; // Limpiamos para que vuelva a decir "-- Seleccione --"
    },
    validarNumeros(campo) {
      campo.cedula = campo.cedula.replace(/\D/g, '');
    },
    getPdfUrl(num_convocatoria, pdfName) {
      if (!pdfName || !num_convocatoria) return null;

      // Si ya es una URL completa
      if (pdfName.startsWith('http://') || pdfName.startsWith('https://')) {
        return pdfName;
      }

      // Remueve '/api' o '/api/' al final de baseURL para ir a la raíz de public
      const rootURL = (API.defaults.baseURL || '').replace(/\/api\/?$/, '');
      return `${rootURL}/Documentos/INVESTIGACIÓN/Convocatorias/${num_convocatoria}/${pdfName}`;
    },
    async buscarDocente(campo) {
      if (campo.cedula.length !== 10) {
        campo.error = 'La cédula debe tener 10 dígitos.';
        return;
      }
      campo.error = '';
      try {
        // Ajusta la URL según el endpoint real que programaste
        const resp = await API.get(`${this.baseUrl}/getalldoce/${campo.cedula}`);
        if (resp.data && resp.data.data) {
          campo.docente = resp.data.data;
        } else {
          campo.error = 'Docente no encontrado.';
          campo.docente = null;
        }
      } catch (e) {
        campo.error = 'Error en el servidor al buscar.';
        campo.docente = null;
      }
    },
    abrirModalRegistro() {
      this.isEditMode = false;
      this.limpiarFormulario();
      this.$.setupState.isModalOpen = true;
    },
    abrirModalEdicion(user) {
      this.isEditMode = true;
      this.limpiarFormulario();
      this.botonCargando = 'editar_' + user.id_convocatoria;
      try {

        this.formulario = {
          id_convocatoria: user.id_convocatoria,
          num_convocatoria: user.num_convocatoria,
          titulo_convocatoria: user.titulo_convocatoria,
          fecha_inicio: user.fecha_inicio,
          fecha_fin: user.fecha_fin,
          num_resolucion: user.num_resolucion,
          estado: user.estado,
          archivo: user.archivo,
          habilitar_edicion: user.habilitar_edicion,
          ad_archivo_url: user.ad_archivo_url

        };
        this.estadoBool = user.estado === 1;
        this.edicionBool = user.habilitar_edicion === 1;
        // Función helper para procesar cómo viene el dato (Cédula 10 dígitos o Texto normal)
        const parseCampo = (clave, valor) => {
          if (!valor) return;
          const esCedula = /^\d{10}$/.test(valor.trim());
          if (esCedula) {
            this.camposResponsables[clave].tipo = 'persona';
            this.camposResponsables[clave].cedula = valor;
            this.buscarDocente(this.camposResponsables[clave]); // Para cargar la data visual
          } else {
            this.camposResponsables[clave].tipo = 'departamento';
            this.camposResponsables[clave].texto = valor;
          }
        };

        parseCampo('elaboracion', user.elaboracion);
        parseCampo('revision', user.revision);
        parseCampo('aprobacion', user.aprobacion);

        this.$.setupState.isModalOpen = true;
      } catch (e) {
        console.error("Error al abrir modal de edición:", e);
      } finally {
        this.botonCargando = null;
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
    cerrarModal() {
      this.$.setupState.isModalOpen = false;
      this.limpiarFormulario();
    },
    getPhotoUrl(ci) {
      // Si no hay CI, retornamos una imagen vacía o un placeholder
      if (!ci) return '';

      const baseURL2 = API.defaults.baseURL;
      // Usamos el timestamp para evitar problemas de caché al cambiar de integrante
      return `${baseURL2}/inves/getFotoDocente/${ci}?t=${new Date().getTime()}`;
    },
    handleImgError(e) {
      e.target.src = '/favicon.ico';
    },
    async uploadArchivo(num_convocatoria, oldFilename = null) {
      if (!this.archivoSeleccionado) return null; // nada que subir
      try {
        this.uploading = true;
        const form = new FormData();
        form.append('file', this.archivoSeleccionado);
        form.append('num_convocatoria', num_convocatoria);
        if (oldFilename) {
          form.append('old_filename', oldFilename); // Enviamos el nombre del archivo viejo
        }

        // Si tu backend exige otros campos (ej: tipo), añade aquí
        const resp = await API.post(`${this.baseUrl}/subir_archivo_convocatoria`, form, {
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
    async GetData(page = 1, searchQuery = "") {
      this.cargando = true;

      try {
        const params = {
          page: page,
          search_query: searchQuery
        };
        const response = await API.get(`${this.baseUrl}/invi_convocatoria`, { params });

        const data = response.data?.data || [];
        const rootURL = (API.defaults.baseURL || '').replace(/\/api\/?$/, '');
        // Mapeamos los datos iniciales agregando propiedades reactivas para el docente
        this.filteredarray = data.map(item => ({
          ...item,
          ad_archivo_url: item.archivo
            ? `${rootURL}/Documentos/INVESTIGACIÓN/Convocatorias/${item.num_convocatoria}/${item.archivo}`
            : null,
          docente: null,
          cargandoDocente: false,
          errorDocente: ''
        }));

        // Ejecutamos la búsqueda de docentes solo para los registros que tengan una cédula válida
        this.filteredarray.forEach(post => {
          // Validamos si "elaboracion" contiene exactamente 10 números
          const esCedula = /^\d{10}$/.test(post.elaboracion);

          if (esCedula) {
            this.cargarDocenteParaPost(post);
          }
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
    async cargarDocenteParaPost(post) {
      post.cargandoDocente = true;
      try {
        const resp = await API.get(`${this.baseUrl}/getalldoce/${post.elaboracion}`);
        if (resp.data && resp.data.data) {
          // Guardamos la información del docente dentro del mismo objeto de la convocatoria
          post.docente = resp.data.data;
        } else {
          post.errorDocente = 'Docente no encontrado.';
        }
      } catch (e) {
        post.errorDocente = 'Error al buscar docente.';
      } finally {
        post.cargandoDocente = false;
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
      const procesarCampo = (campo) => campo.tipo === 'persona' ? campo.cedula : campo.texto;
      const params = {
        ...this.formulario,
        elaboracion: procesarCampo(this.camposResponsables.elaboracion),
        revision: procesarCampo(this.camposResponsables.revision),
        aprobacion: procesarCampo(this.camposResponsables.aprobacion),
      };

      try {
        this.botonCargando = 'guardando';
        if (this.archivoSeleccionado) {
          const uploadResp = await this.uploadArchivo(this.formulario.num_convocatoria);
          if (uploadResp && uploadResp.filename) params.archivo = uploadResp.filename;
        }

        let exito = false;
        if (this.isEditMode) {
          exito = await enviarsolig('PUT', params, `${this.baseUrl}/invi_convocatoria/${this.formulario.id_convocatoria}`, 'Convocatoria actualizada');
        } else {
          exito = await enviarsolig('POST', params, `${this.baseUrl}/invi_convocatoria`, 'Convocatoria registrada');
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
      this.formulario = { id_convocatoria: null, num_convocatoria: "", titulo_convocatoria: "", fecha_inicio: "", fecha_fin: "", num_resolucion: "", estado: 1, habilitar_edicion: 0, tipo_convocatoria: "INVESTIGACIÓN" };
      this.estadoBool = true;
      this.edicionBool = false;
      this.archivoSeleccionado = null;
      this.archivoPreviewName = '';

      // Reiniciar configuraciones dinámicas
      Object.keys(this.camposResponsables).forEach(key => {
        this.camposResponsables[key] = { ...this.camposResponsables[key], tipo: 'persona', cedula: '', texto: '', docente: null, error: '' };
      });
    },
    eliminar(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/inhabilitar_convocatoria/`,
          id,
          'Deshabilitar registro',
          '¿Realmente desea deshabilitar la convocatoria  ' + nombre + '?',
          this.actualizar   // 👈 callback para refrescar la tabla al confirmar
        );
      } catch (error) {
        console.error("Error al eliminar la convocatoria:", error);
        this.cargando = false;
      }
    },
    async habilitar(id, nombre) {
      try {
        await confimarhabi(
          `${this.baseUrl}/habilitar_convocatoria/`,
          id,
          'Habilitar registro',
          `¿Desea habilitar la convocatoria "${nombre}"?`,
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
