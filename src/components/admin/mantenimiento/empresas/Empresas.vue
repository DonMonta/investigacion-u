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
            <input type="text" placeholder="Ingresa el ruc de la empresa a buscar..." v-model="searchQuery"
              @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
          </div>
        </form>
      </div>

      <div class="flex items-center gap-3">


        <button @click="isProfileAddressModal = true"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
          Añadir Empresa
        </button>
      </div>
    </div>
    <div class="max-w-full overflow-x-auto custom-scrollbar">
      <table class="min-w-full">
        <thead>
          <tr class="border-t border-gray-100 dark:border-gray-800">
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Ruc</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Empresa</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Pais/ciudad</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Estado</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Archivo</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">CRE/ACT</p>
            </th>

            <th class="py-5 px-4 text-right">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Acciones</p>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr class="border-t border-gray-100 dark:border-gray-800" v-if="cargando">
            <td class="px-5 py-6" colspan="7">
              <div class="flex justify-center items-center gap-2">
                <span class="animate-spin h-5 w-5 border-2 border-brand-500 border-t-transparent rounded-full"></span>
                <h3 class="text-gray-500 font-medium">Cargando datos...</h3>
              </div>
            </td>
          </tr>
          <tr v-else v-for="post in filteredarray" :key="post.idempresa"
            class="border-t border-gray-100 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:bg-white/[0.02] transition-colors">
            <td class="py-5 px-4 whitespace-nowrap">
              <p class="text-gray-600 text-sm font-medium dark:text-gray-400" v-if="post.ruc ">{{ post.ruc }}</p>
              <p class="text-gray-600 text-sm font-medium dark:text-gray-400" v-else>Sin Ruc</p>
            </td>
            <td class="py-5 px-4 whitespace-normal min-w-[220px] max-w-[320px] break-words">
              <div class="flex items-center gap-2">
                <img v-if="post.imagen" :src="getPhotoUrl(post.idempresa)"
                  class="w-8 h-8 rounded-full object-cover border border-gray-200 shrink-0" alt="Logo" />
                <div>
                  <p class="font-bold text-gray-800 text-sm dark:text-white/90">
                    {{ post.empresacorta }}
                  </p>
                  <p class="text-xs text-gray-400 line-clamp-2" :title="post.empresa">{{ post.empresa }}</p>

                  <span v-if="verificarIncompleto(post)"
                    class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                    ⚠️ Actualizar Datos Faltantes
                  </span>
                </div>
              </div>
            </td>
            <td class="py-5 px-4 whitespace-nowrap">
              <p class="text-gray-600 text-sm font-medium dark:text-gray-400">{{ post.pais }} / {{ post.lugar }}</p>
            </td>
            <td class="p-3 text-center">
              <span :class="Number(post.estado_empr) === 0 ? 'text-red-500 bg-red-50' : 'text-green-600 bg-green-50'"
                class="px-2 py-1 rounded-full text-[10px] font-bold uppercase">
                {{ Number(post.estado_empr) === 0 ? 'Inac' : 'Act' }}
              </span>
            </td>
            <td class="p-3">
              <div class="flex flex-col items-start gap-1">
                <div v-if="post.archivo" class="flex justify-center">
                  <a :href="`http://vinculacion.test/Documentos/Vinculación/Archivos_Empresas/${post.ruc}/${post.archivo}`"
                    target="_blank"
                    class="group relative flex items-center justify-center p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm"
                    title="Ver documento PDF">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      <path d="M9 15h6M9 11h6" />
                    </svg>
                  </a>
                </div>
                <span v-else class="text-[10px] text-gray-300 italic">Sin anexo</span>

                <span v-if="esFechaPasada(post.fechafin)"
                  class="inline-flex items-center gap-0.5 text-[10px] text-red-600 font-semibold bg-red-50 px-1.5 py-0.5 rounded border border-red-100">
                  🛑 Actualizar Convenio
                </span>
                <span v-else-if="post.fechafin" class="text-[10px] text-gray-500">
                  Fin: {{ post.fechafin.split(' ')[0] }}
                </span>
              </div>
            </td>
            <td class="p-3 whitespace-nowrap">
              <div class="flex flex-col gap-1">
                <div class="flex items-center gap-1 text-[10px] text-gray-500">
                  <span class="font-bold text-blue-500">CRE:</span>
                  {{ formatDate(post.created_at) }}
                </div>
                <div class="flex items-center gap-1 text-[10px] text-gray-400">
                  <span class="font-bold text-orange-400">ACT:</span>
                  {{ formatDate(post.updated_at) }}
                </div>
              </div>
            </td>
            <td class="py-3 text-right whitespace-nowrap">
              <div class="flex justify-end gap-2">
                <button @click="abrirModalEdicion(post)"
                  class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </button>
                <button @click="eliminar(post.idempresa, post.empresacorta)"
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
    <Modal v-if="isProfileAddressModal" @close="isProfileAddressModal = false">
      <template #body>
        <div
          class="no-scrollbar relative w-full max-w-[850px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11 max-h-[90vh]">
          <button @click="isProfileAddressModal = false"
            class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z" />
            </svg>
          </button>

          <div class="px-2 pr-14">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Agregar Empresa</h4>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Llene los campos estructurados para registrar la
              organización.</p>
          </div>

          <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6 px-2 gap-4">
            <button v-for="t in tabs" :key="t.id" @click="activeTab = t.id" type="button"
              :class="activeTab === t.id ? 'border-brand-500 text-brand-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700'"
              class="py-2.5 px-1 border-b-2 text-sm transition-all whitespace-nowrap">
              {{ t.name }}
            </button>
          </div>

          <form class="flex flex-col" @submit.prevent>
            <div class="px-2 max-h-[50vh] overflow-y-auto custom-scrollbar">

              <div v-show="activeTab === 'general'" class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">RUC de la Empresa
                    *</label>
                  <input type="text" v-model="objetoguardar.ruc"
                    @input="objetoguardar.ruc = objetoguardar.ruc.replace(/[^0-9]/g, '').substring(0, 13)"
                    placeholder="13 dígitos exactos" class="form-style-input" />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Nombre Corto
                    *</label>
                  <input type="text" v-model="objetoguardar.empresacorta" placeholder="Ej: LOGISPETROL"
                    class="form-style-input" />
                </div>
                <div class="lg:col-span-2">
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Razón Social /
                    Proceso Completo *</label>
                  <textarea rows="2" v-model="objetoguardar.empresa" class="form-style-input text-xs"></textarea>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Tipo de Servicio
                    *</label>
                  <select v-model="objetoguardar.tipo" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.tipos" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Actividad Principal
                    *</label>
                  <select v-model="objetoguardar.actividad" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.actividades" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Tipo Institución
                    *</label>
                  <select v-model="objetoguardar.tipoinstitucion" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.tiposInstitucion" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Estado Empresa
                    *</label>
                  <select v-model="objetoguardar.estado_empr" class="form-style-input">
                    <option :value="1">Activo</option>
                    <option :value="0">Inactiva</option>
                  </select>
                </div>
              </div>

              <div v-show="activeTab === 'contacto'" class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">País *</label>
                  <select v-model="objetoguardar.pais" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.paises" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Ciudad/Lugar
                    *</label>
                  <input type="text" v-model="objetoguardar.lugar" class="form-style-input" />
                </div>
                <div class="lg:col-span-2">
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Dirección
                    Domiciliaria *</label>
                  <input type="text" v-model="objetoguardar.direccion" class="form-style-input" />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Teléfono *</label>
                  <input type="text" v-model="objetoguardar.telefono"
                    @input="objetoguardar.telefono = objetoguardar.telefono.replace(/[^0-9]/g, '').substring(0, 10)"
                    placeholder="Máximo 10 números" class="form-style-input" />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Correo Electrónico
                    *</label>
                  <input type="email" v-model="objetoguardar.email" placeholder="correo@empresa.com"
                    class="form-style-input" />
                </div>
                <div class="lg:col-span-2">
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Sitio Web
                    (URL)</label>
                  <input type="url" v-model="objetoguardar.url" placeholder="https://www.empresa.com"
                    class="form-style-input" />
                </div>
              </div>

              <div v-show="activeTab === 'legal'" class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Título
                    Representante *</label>
                  <select v-model="objetoguardar.titulo" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.titulos" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Nombre del
                    Representante *</label>
                  <input type="text" v-model="objetoguardar.representante" class="form-style-input" />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Cargo del
                    Representante *</label>
                  <select v-model="objetoguardar.cargo" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.cargos" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Fecha de
                    Finalización de Relación *</label>
                  <input type="date" v-model="objetoguardar.fechafin" class="form-style-input" />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Cargar Imagen/Logo
                    Empresa</label>
                  <input type="file" @change="procesarImagen($event, 'guardar')" accept="image/*"
                    class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100" />
                  <div v-if="objetoguardar.imagen" class="mt-2">
                    <img :src="objetoguardar.imagen" class="h-14 w-14 object-cover rounded-xl border animate-fade-in" />
                  </div>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Cargar Anexo PDF
                    (Convenio)</label>
                  <input type="file" @change="procesarArchivoPdf($event, 'guardar')" accept=".pdf"
                    class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100" />
                </div>
                <div class="lg:col-span-2">
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Misión *</label>
                  <textarea rows="2" v-model="objetoguardar.mision" class="form-style-input text-xs"></textarea>
                </div>
                <div class="lg:col-span-2">
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Visión *</label>
                  <textarea rows="2" v-model="objetoguardar.vision" class="form-style-input text-xs"></textarea>
                </div>
              </div>

            </div>

            <div class="flex items-center gap-3 mt-6 lg:justify-end border-t pt-4">
              <button @click="isProfileAddressModal = false" type="button" :disabled="guardandoDatos"
                class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">
                Cerrar
              </button>

              <button v-if="activeTab === 'general'" @click="activeTab = 'contacto'" type="button"
                class="flex w-full justify-center rounded-lg bg-gray-100 dark:bg-gray-800 dark:text-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 sm:w-auto transition-all">
                Siguiente
              </button>

              <button v-if="activeTab === 'contacto'" @click="activeTab = 'legal'" type="button"
                class="flex w-full justify-center rounded-lg bg-gray-100 dark:bg-gray-800 dark:text-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 sm:w-auto transition-all">
                Siguiente
              </button>

              <template v-if="activeTab === 'legal'">
                <button v-if="formIsValid" @click="registrar" :disabled="guardandoDatos" type="button"
                  class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-lg transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                  <span v-if="guardandoDatos"
                    class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                  {{ guardandoDatos ? 'Guardando...' : 'Guardar Empresa' }}
                </button>
                <span v-else
                  class="text-xs text-amber-600 bg-amber-50 px-3 py-2 rounded-lg font-medium border border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900">
                  Faltan datos obligatorios (*) en el formulario o el RUC no tiene 13 dígitos exactos.
                </span>
              </template>
            </div>
          </form>
        </div>
      </template>
    </Modal>

    <Modal v-if="isEditModalOpen" @close="isEditModalOpen = false">
      <template #body>
        <div
          class="no-scrollbar relative w-full max-w-[850px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11 max-h-[90vh]">
          <button @click="isEditModalOpen = false"
            class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z" />
            </svg>
          </button>

          <div class="px-2 pr-14">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Editar Empresa</h4>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Modifique los campos correspondientes a la
              organización seleccionada.</p>
          </div>

          <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6 px-2 gap-4">
            <button v-for="t in tabs" :key="t.id" @click="activeEditTab = t.id" type="button"
              :class="activeEditTab === t.id ? 'border-brand-500 text-brand-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700'"
              class="py-2.5 px-1 border-b-2 text-sm transition-all whitespace-nowrap">
              {{ t.name }}
            </button>
          </div>

          <form class="flex flex-col" @submit.prevent>
            <div class="px-2 max-h-[50vh] overflow-y-auto custom-scrollbar">

              <div v-show="activeEditTab === 'general'" class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">RUC de la Empresa
                    *</label>
                  <input type="text" v-model="objetoeditar.ruc"
                    @input="objetoeditar.ruc = objetoeditar.ruc.replace(/[^0-9]/g, '').substring(0, 13)"
                    placeholder="13 dígitos exactos" class="form-style-input" />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Nombre Corto
                    *</label>
                  <input type="text" v-model="objetoeditar.empresacorta" class="form-style-input" />
                </div>
                <div class="lg:col-span-2">
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Razón Social /
                    Proceso Completo *</label>
                  <textarea rows="2" v-model="objetoeditar.empresa" class="form-style-input text-xs"></textarea>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Tipo de Servicio
                    *</label>
                  <select v-model="objetoeditar.tipo" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.tipos" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Actividad
                    Principal *</label>
                  <select v-model="objetoeditar.actividad" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.actividades" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Tipo Institución
                    *</label>
                  <select v-model="objetoeditar.tipoinstitucion" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.tiposInstitucion" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Estado Empresa
                    *</label>
                  <select v-model="objetoeditar.estado_empr" class="form-style-input">
                    <option :value="1">Activo</option>
                    <option :value="0">Inactiva</option>
                  </select>
                </div>
              </div>

              <div v-show="activeEditTab === 'contacto'" class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">País *</label>
                  <select v-model="objetoeditar.pais" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.paises" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Ciudad/Lugar
                    *</label>
                  <input type="text" v-model="objetoeditar.lugar" class="form-style-input" />
                </div>
                <div class="lg:col-span-2">
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Dirección
                    Domiciliaria *</label>
                  <input type="text" v-model="objetoeditar.direccion" class="form-style-input" />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Teléfono
                    *</label>
                  <input type="text" v-model="objetoeditar.telefono"
                    @input="objetoeditar.telefono = objetoeditar.telefono.replace(/[^0-9]/g, '').substring(0, 10)"
                    placeholder="Máximo 10 números" class="form-style-input" />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Correo
                    Electrónico *</label>
                  <input type="email" v-model="objetoeditar.email" class="form-style-input" />
                </div>
                <div class="lg:col-span-2">
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Sitio Web
                    (URL)</label>
                  <input type="url" v-model="objetoeditar.url" class="form-style-input" />
                </div>
              </div>

              <div v-show="activeEditTab === 'legal'" class="grid grid-cols-1 gap-x-6 gap-y-4 lg:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Título
                    Representante *</label>
                  <select v-model="objetoeditar.titulo" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.titulos" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Nombre del
                    Representante *</label>
                  <input type="text" v-model="objetoeditar.representante" class="form-style-input" />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Cargo del
                    Representante *</label>
                  <select v-model="objetoeditar.cargo" class="form-style-input">
                    <option value="">Seleccione...</option>
                    <option v-for="opt in listasCombos.cargos" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Fecha de
                    Finalización *</label>
                  <input type="date" v-model="objetoeditar.fechafin" class="form-style-input" />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Cambiar
                    Imagen/Logo</label>
                  <input type="file" @change="procesarImagen($event, 'editar')" accept="image/*"
                    class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100" />
                  <div class="mt-2" v-if="objetoeditar.imagen || objetoeditar.imagenPreview">
                    <img :src="objetoeditar.imagenPreview || getPhotoUrl(objetoeditar.idempresa)"
                      class="h-14 w-14 object-cover rounded-xl border animate-fade-in" />
                  </div>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Reemplazar Anexo
                    PDF</label>
                  <input type="file" @change="procesarArchivoPdf($event, 'editar')" accept=".pdf"
                    class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100" />
                </div>
                <div class="lg:col-span-2">
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Misión *</label>
                  <textarea rows="2" v-model="objetoeditar.mision" class="form-style-input text-xs"></textarea>
                </div>
                <div class="lg:col-span-2">
                  <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-400">Visión *</label>
                  <textarea rows="2" v-model="objetoeditar.vision" class="form-style-input text-xs"></textarea>
                </div>
              </div>

            </div>

            <div class="flex items-center gap-3 mt-6 lg:justify-end border-t pt-4">
              <button @click="isEditModalOpen = false" type="button" :disabled="guardandoDatos"
                class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">
                Cerrar
              </button>

              <button v-if="activeEditTab === 'general'" @click="activeEditTab = 'contacto'" type="button"
                class="flex w-full justify-center rounded-lg bg-gray-100 dark:bg-gray-800 dark:text-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 sm:w-auto transition-all">
                Siguiente
              </button>

              <button v-if="activeEditTab === 'contacto'" @click="activeEditTab = 'legal'" type="button"
                class="flex w-full justify-center rounded-lg bg-gray-100 dark:bg-gray-800 dark:text-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 sm:w-auto transition-all">
                Siguiente
              </button>

              <template v-if="activeEditTab === 'legal'">
                <button v-if="formIsValidEdit" @click="Update" :disabled="guardandoDatos" type="button"
                  class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto shadow-lg transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                  <span v-if="guardandoDatos"
                    class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                  {{ guardandoDatos ? 'Actualizando...' : 'Guardar cambios' }}
                </button>
                <span v-else
                  class="text-xs text-amber-600 bg-amber-50 px-3 py-2 rounded-lg font-medium border border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900">
                  Revise que todos los campos requeridos (*) de todas las pestañas estén llenos y el RUC sea válido.
                </span>
              </template>
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
      guardandoDatos: false,
      usersarray: [],
      // Control de Tabs internas
      activeTab: 'general',
      activeEditTab: 'general',
      tabs: [
        { id: 'general', name: '1. Datos Generales' },
        { id: 'contacto', name: '2. Ubicación y Contacto' },
        { id: 'legal', name: '3. Representación y Archivo' }
      ],

      // Catálogos para Comboboxes
      listasCombos: {
        titulos: ['Dr.', 'Dra.', 'Ing.', 'Abg.', 'Lcdo.', 'Lcda.', 'Mgs.', 'Ph.D.',
          'Sr.', 'Sra.', 'Srta.', 'Mx. (neutral)', 'Médico', 'Arq.', 'Eco.', 'Cont.', 'Prof.', 'Otro'
        ],
        tipos: ['Pre-Profesionales', 'Servicio Comunitario', 'Vinculación', 'Organización sin Fines de Lucro', 'Laborales'],
        cargos: ['Director/a', 'Gerente General', 'Rector/a', 'Representante Legal', 'Administrador/a'],
        actividades: ['Venta', 'Turismo', 'Servicios Turísticos', 'Servicios Sociales', 'Servicios Petroleros',
          'Servicios Pecuarios', 'Servicios Empresariales', 'Servicios', 'Servicio Público', 'Servicio Eléctrico',
          'Servicio Comunitario', 'Servicio Ciudadano/a', 'Salud Pública', 'Salud', 'Reciclaje', 'Prácticas Preprofesionales',
          'Prácticas de Servicio Comunitario (Vinculación)', 'Protección de los derechos de los niños y adolescentes',
          'Protección de los derechos ciudadanos', 'Producción y comercialización', 'Producción Pecuaria',
          'Producción Agropecuaria', 'Producción', 'Municipio Atacames', 'Medio Ambiente', 'Investigación',
          'Institución Educativa', 'Hotelería', 'Gestión Educativa', 'Empresa Transporte', 'Empresa Pública',
          'Empresa Exportadora', 'Empresa de Tecnología e Innovación', 'Empresa Comercial', 'Educativa', 'Educación',
          'Educación Superior', 'Coordinación, Gestión e Investigación', 'Cooperación', 'Comunicación', 'Comercialización'
        ],
        tiposInstitucion: ['Institución Pública', 'Institución Privada', 'Institución Mixta', 'Institución Educativa',
          'Institución de Educación Superior', 'Institución Técnica y Tecnológica', 'Institución de Investigación',
          'Institución de Salud', 'Institución Social', 'Institución Cultural', 'Institución Financiera', 'Institución Deportiva',
          'Institución Ambiental', 'Gobierno Autónomo Descentralizado (GAD)', 'Ministerio', 'Empresa Pública', 'Empresa Privada',
          'Organización No Gubernamental (ONG)', 'Organismo Internacional', 'Fundación', 'Cooperativa', 'Asociación',
          'Cámara de Comercio o Producción', 'Colegio Profesional', 'Centro de Investigación', 'Centro Comunitario',
          'Hospital o Centro de Salud', 'Escuela o Colegio', 'Universidad o Politécnica'],

        paises: ['ECUADOR', 'COLOMBIA', 'PERÚ', 'CHILE', 'ARGENTINA', 'ESPAÑA', 'ESTADOS UNIDOS',
        ]
      },
      objetoguardar: {
        ruc: "", //ruc de la empresa
        empresa: "",
        empresacorta: "",//nombre corto de la empresa. Ej. LOGISPETROL
        lugar: "",//lugar de la empresa. Ej. ESMERALDAS
        direccion: "",//direccion de la empresa. Ej. AV. DE LAS AMERICAS, S/N
        telefono: "",//telefono de la empresa. Ej. 0123456789
        email: "",//email de la empresa. Ej. info@logispetrol.com
        url: "",//url de la empresa. Ej. www.logispetrol.com
        tipo: "",//tipo de servio que ofrece la empresa. Ej. Pre-Profesionales, Servicio Comunitario, Vinculación, etc.
        titulo: "",//titulo del representante legal de la empresa. Ej. Dr, Ing. Abg., etc.
        representante: "",//nombre del representante legal de la empresa. Ej. Juan Pérez
        cargo: "",//cargo del representante legal de la empresa. Ej. Director
        actividad: "",//actividad de la empresa. Ej. Académica, Administración Pública, Prácticas de servicio comunitario, Prácticas de vinculación, etc.
        fechafin: "",//fecha de finalización de la relación con la empresa. Ej. 2024-12-31 00:00:00
        tipoinstitucion: "",//tipo de institución de la empresa. Ej. Institución Pública, Empresa Pública, etc.
        pais: "",//pais de la empresa. Ej. ECUADOR
        imagen: "",//imagen de la empresa.
        estado_empr: 0,//estado de la empresa. 0: Inactiva, 1: Activo
        vision: "",//visión de la empresa. Ej. Ser una empresa líder en el sector de servicios petroleros, reconocida por su compromiso con
        mision: "",//misión de la empresa. Ej. Promover la adopción de prácticas de servicio comunitario en la región de la provincia de Esmeraldas
        archivoPdf: null, // Archivo PDF adjunto
        imagenPreview: null, // Preview de la imagen adjunta
        archivoImagenReal: null, // Archivo adjunto original
      },
      objetoeditar: {
        idempresa: 0,
        ruc: "", //ruc de la empresa
        empresa: "",
        empresacorta: "",//nombre corto de la empresa. Ej. LOGISPETROL
        lugar: "",//lugar de la empresa. Ej. ESMERALDAS
        direccion: "",//direccion de la empresa. Ej. AV. DE LAS AMERICAS, S/N
        telefono: "",//telefono de la empresa. Ej. 0123456789
        email: "",//email de la empresa. Ej. info@logispetrol.com
        url: "",//url de la empresa. Ej. www.logispetrol.com
        tipo: "",//tipo de servio que ofrece la empresa. Ej. Pre-Profesionales, Servicio Comunitario, Vinculación, etc.
        titulo: "",//titulo del representante legal de la empresa. Ej. Dr, Ing. Abg., etc.
        representante: "",//nombre del representante legal de la empresa. Ej. Juan Pérez
        cargo: "",//cargo del representante legal de la empresa. Ej. Director
        actividad: "",//actividad de la empresa. Ej. Académica, Administración Pública, Prácticas de servicio comunitario, Prácticas de vinculación, etc.
        fechafin: "",//fecha de finalización de la relación con la empresa. Ej. 2024-12-31 00:00:00
        tipoinstitucion: "",//tipo de institución de la empresa. Ej. Institución Pública, Empresa Pública, etc.
        pais: "",//pais de la empresa. Ej. ECUADOR
        imagen: "",//imagen de la empresa.
        estado_empr: 0,//estado de la empresa. 0: Inactiva, 1: Activo
        vision: "",//visión de la empresa. Ej. Ser una empresa líder en el sector de servicios petroleros, reconocida por su compromiso con
        mision: "",//misión de la empresa. Ej. Promover la adopción de prácticas de servicio comunitario en la región de la provincia de Esmeraldas
        archivoPdf: null, // Archivo PDF adjunto
        imagenPreview: null, // Preview de la imagen adjunta
        archivoImagenReal: null, // Archivo adjunto original
      },
      filteredarray: [],
      searchQuery: "",
      isProfileAddressModal: false,
      cargando: false,
      currentPage: 1,
      lastPage: 1,
      buscando: false, // Mantenido, pero no se usa en la lógica de paginación actual
      debouncedFilter: null,
      uploading: false,

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
      // 1. Extraemos y parseamos los strings de forma segura
      const ruc = this.objetoguardar.ruc ? String(this.objetoguardar.ruc).trim() : '';
      const email = this.objetoguardar.email ? String(this.objetoguardar.email).trim() : '';
      const url = this.objetoguardar.url ? String(this.objetoguardar.url).trim() : '';

      const rucRegex = /^\d{13}$/; // Validar un RUC de 13 dígitos
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const urlRegex = /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/;

      // 2. Validaciones RegEx obligatorias
      if (!rucRegex.test(ruc)) return false;
      if (!emailRegex.test(email)) return false;

      // La URL es opcional, pero si la ponen, debe ser válida
      if (url !== '' && !urlRegex.test(url)) return false;

      // 3. Verificación Segura de vacíos
      return (
        ruc !== '' &&
        String(this.objetoguardar.empresa || '').trim() !== '' &&
        String(this.objetoguardar.empresacorta || '').trim() !== '' &&
        String(this.objetoguardar.lugar || '').trim() !== '' &&
        String(this.objetoguardar.direccion || '').trim() !== '' &&
        String(this.objetoguardar.telefono || '').trim() !== '' &&
        String(this.objetoguardar.tipo || '').trim() !== '' &&
        String(this.objetoguardar.titulo || '').trim() !== '' &&
        String(this.objetoguardar.representante || '').trim() !== '' &&
        String(this.objetoguardar.cargo || '').trim() !== '' &&
        String(this.objetoguardar.actividad || '').trim() !== '' &&
        String(this.objetoguardar.fechafin || '').trim() !== '' &&
        String(this.objetoguardar.tipoinstitucion || '').trim() !== '' &&
        String(this.objetoguardar.pais || '').trim() !== '' &&
        String(this.objetoguardar.vision || '').trim() !== '' &&
        String(this.objetoguardar.mision || '').trim() !== '' &&
        this.objetoguardar.estado_empr !== null &&
        this.objetoguardar.estado_empr !== undefined
      );
    },

    formIsValidEdit() {
      // 1. Extraemos y parseamos los strings de forma segura
      const ruc = this.objetoeditar.ruc ? String(this.objetoeditar.ruc).trim() : '';
      const email = this.objetoeditar.email ? String(this.objetoeditar.email).trim() : '';
      const url = this.objetoeditar.url ? String(this.objetoeditar.url).trim() : '';

      const rucRegex = /^\d{13}$/;
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const urlRegex = /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/;

      // 2. Validaciones RegEx obligatorias
      if (!rucRegex.test(ruc)) return false;
      if (!emailRegex.test(email)) return false;

      if (url !== '' && !urlRegex.test(url)) return false;

      // 3. Verificación Segura de vacíos
      return (
        ruc !== '' &&
        String(this.objetoeditar.empresa || '').trim() !== '' &&
        String(this.objetoeditar.empresacorta || '').trim() !== '' &&
        String(this.objetoeditar.lugar || '').trim() !== '' &&
        String(this.objetoeditar.direccion || '').trim() !== '' &&
        String(this.objetoeditar.telefono || '').trim() !== '' &&
        String(this.objetoeditar.tipo || '').trim() !== '' &&
        String(this.objetoeditar.titulo || '').trim() !== '' &&
        String(this.objetoeditar.representante || '').trim() !== '' &&
        String(this.objetoeditar.cargo || '').trim() !== '' &&
        String(this.objetoeditar.actividad || '').trim() !== '' &&
        String(this.objetoeditar.fechafin || '').trim() !== '' &&
        String(this.objetoeditar.tipoinstitucion || '').trim() !== '' &&
        String(this.objetoeditar.pais || '').trim() !== '' &&
        String(this.objetoeditar.mision || '').trim() !== '' &&
        String(this.objetoeditar.vision || '').trim() !== '' &&
        this.objetoeditar.estado_empr !== null &&
        this.objetoeditar.estado_empr !== undefined
      );
    },
  },
  methods: {
    getPhotoUrl(idempresa) {
      if (!idempresa) return '';
      const baseURL2 = API.defaults.baseURL;
      // Agregamos un timestamp para limpiar la caché del navegador cuando actualices el logo
      return `${baseURL2}/vin/getFotoEmpresa/${idempresa}?t=${new Date().getTime()}`;
    },
    esFechaPasada(fechaFinStr) {
      if (!fechaFinStr) return false;
      const fechaFin = new Date(fechaFinStr);
      const hoy = new Date();
      return fechaFin < hoy;
    },
    verificarIncompleto(empresa) {
      return (
        !empresa.direccion ||
        !empresa.telefono ||
        !empresa.email ||
        !empresa.mision ||
        !empresa.vision ||
        !empresa.representante
      );
    },
    procesarImagen(event, modo) {
      const file = event.target.files[0];
      if (!file) return;

      // Validar que sea imagen (Misma validación de cargarfoto)
      const allowedTypes = ["image/jpeg", "image/jpg", "image/png"];
      if (!allowedTypes.includes(file.type)) {
        mostraralertas('Solo se permiten imágenes en formato JPG, JPEG o PNG.', 'warning', '');

        event.target.value = ""; // Limpia el input que disparó el evento de forma dinámica

        // Limpia todas las propiedades relacionadas según el modo para no dejar datos inválidos
        if (modo === 'guardar') {
          this.objetoguardar.imagen = '';
          this.objetoguardar.imagenPreview = null;
          this.objetoguardar.archivoImagenReal = null;
        } else {
          this.objetoeditar.imagen = '';
          this.objetoeditar.imagenPreview = null;
          this.objetoeditar.archivoImagenReal = null;
        }
        return;
      }

      const img = new Image();
      img.src = URL.createObjectURL(file);
      img.onload = () => {
        // Crear un canvas de 320x240
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        canvas.width = 320;
        canvas.height = 240;

        ctx.drawImage(img, 0, 0, 320, 240);

        const dataUrl = canvas.toDataURL("image/jpeg", 0.9);
        const base64Limpio = dataUrl.replace(/^data:image\/jpeg;base64,/, "");

        if (modo === 'guardar') {
          this.objetoguardar.imagen = base64Limpio;
          this.objetoguardar.imagenPreview = dataUrl; // Se usa para la vista previa en el formulario (<img :src="...">)
          this.objetoguardar.archivoImagenReal = file;  // Guarda el archivo original por si lo necesitas
        } else {
          this.objetoeditar.imagen = base64Limpio;
          this.objetoeditar.imagenPreview = dataUrl;   // Se usa para la vista previa en el formulario
          this.objetoeditar.archivoImagenReal = file;   // Guarda el archivo original por si lo necesitas
        }
      };
    },
    procesarArchivoPdf(event, modo) {
      const file = event.target.files[0];
      if (!file) return;
      // Validar que el archivo seleccionado sea un archivo PDF
      if (file.type !== 'application/pdf') {
        mostraralertas2('Solo se permiten archivos PDF', 'warning');
        event.target.value = null; // Limpia el input de forma dinámica
        // Limpia la propiedad en el objeto correspondiente para no dejar datos inválidos
        if (modo === 'guardar') this.objetoguardar.archivoPdf = null;
        else this.objetoeditar.archivoPdf = null;
        return;
      }
      // Validar que el tamaño del archivo no exceda el límite de 10 MB
      const maxMB = 10;
      if (file.size > maxMB * 1024 * 1024) {
        mostraralertas2(`Archivo muy grande. Máx ${maxMB} MB`, 'warning');
        event.target.value = null; // Limpia el input de forma dinámica
        // Limpia la propiedad en el objeto correspondiente
        if (modo === 'guardar') this.objetoguardar.archivoPdf = null;
        else this.objetoeditar.archivoPdf = null;

        return;
      }
      // Si pasa todas las validaciones, se asigna el archivo
      if (modo === 'guardar') {
        this.objetoguardar.archivoPdf = file;
      } else {
        this.objetoeditar.archivoPdf = file;
      }
    },
    async uploadarchivo(ruc, file, oldFilename = null) {
      if (!file) return null; // Nada que subir

      try {
        this.uploading = true;
        const form = new FormData();

        form.append('file', file);
        form.append('ruc', ruc); // Cambiado a 'ruc' según tu controlador

        if (oldFilename) {
          form.append('old_filename', oldFilename);
        }

        // Asegúrate de que la ruta coincida con la de tu web.php o api.php
        const resp = await API.post(`${this.baseUrl}/subir_archivo`, form, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (resp && resp.data && resp.data.status) {
          // Retorna el filename generado por el backend
          return resp.data;
        } else {
          mostraralertas2('Error procesando el archivo en el servidor', 'error');
          return null;
        }
      } catch (error) {
        console.error("Error en uploadarchivo:", error);
        mostraralertas2('Error de red al intentar subir el archivo', 'error');
        return null;
      } finally {
        this.uploading = false;
      }
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
    abrirModalEdicion(user) {
      // Clonamos el objeto para no modificar la tabla directamente antes de guardar
      console.log("Abriendo modal de edición para empresa:", user);
      const fechaLimpia = user.fechafin ? String(user.fechafin).substring(0, 10) : "";
      this.objetoeditar = {
        idempresa: user.idempresa,
        ruc: user.ruc,
        empresa: user.empresa,
        empresacorta: user.empresacorta,
        lugar: user.lugar,
        direccion: user.direccion,
        telefono: user.telefono,
        email: user.email,
        url: user.url,
        tipo: user.tipo,
        titulo: user.titulo,
        representante: user.representante,
        cargo: user.cargo,
        actividad: user.actividad,
        fechafin: fechaLimpia,
        tipoinstitucion: user.tipoinstitucion,
        pais: user.pais,
        imagen: user.imagen,
        estado_empr: user.estado_empr,
        vision: user.vision,
        mision: user.mision,
        imagenPreview: null,
      };
      this.activeEditTab = 'general';
      this.objetoeditar.archivoPdf = null;
      this.$.setupState.isEditModalOpen = true;
    },
    async GetData(page = 1, searchQuery = "") {
      this.cargando = true;

      try {
        const params = {
          page: page,
          search_query: searchQuery // Parámetro para búsqueda
        };
        const response = await API.get(`${this.baseUrl}/empresa`, { params });

        const data = response.data?.data || [];
        this.filteredarray = data;
        console.log("⚡️ Datos obtenidos:", data);
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
      this.guardandoDatos = true;
      try {
        if (this.objetoguardar.archivoPdf) {
          // Llamamos a uploadarchivo pasándole el RUC y el archivo crudo
          const uploadResp = await this.uploadarchivo(this.objetoguardar.ruc, this.objetoguardar.archivoPdf);

          if (uploadResp && uploadResp.filename) {
            // Si tiene éxito, anexamos el nombre al objeto que va a la base de datos
            this.objetoguardar.archivo = uploadResp.filename;
          } else {
            // Si la subida falla, detenemos el proceso de registro
            mostraralertas2("Se canceló el registro porque el archivo no pudo subirse.", "warning");
            return;
          }
        }
        const exito = await enviarsolig('POST', this.objetoguardar, `${this.baseUrl}/empresa`, 'Empresa registrada con éxito');
        if (exito) {
          this.$.setupState.isProfileAddressModal = false;

          this.limpiarFormulario();
          this.actualizar();
        } else {
          mostraralertas2("No se pudo registrar la Empresa", "error");
        }
      } catch (error) {
        console.error("❌ Error al registrar Empresa:", error.response?.data || error);
      } finally {
        this.guardandoDatos = false; // Detiene el Spinner
      }
    },
    async Update() {
      this.guardandoDatos = true;
      try {
        if (this.objetoeditar.archivoPdf) {
          const uploadResp = await this.uploadarchivo(
            this.objetoeditar.ruc,
            this.objetoeditar.archivoPdf,
            this.objetoeditar.archivo // Pasamos el nombre del archivo actual como old_filename
          );

          if (uploadResp && uploadResp.filename) {
            // Actualizamos la propiedad con el nombre del nuevo archivo
            this.objetoeditar.archivo = uploadResp.filename;
          } else {
            mostraralertas2("Se canceló la actualización porque el archivo no pudo subirse.", "warning");
            return;
          }
        }
        const exito = await enviarsolig('PUT', this.objetoeditar, `${this.baseUrl}/empresa/${this.objetoeditar.idempresa}`, 'Empresa actualizada con éxito');
        if (exito) {
          this.$.setupState.isEditModalOpen = false;
          this.limpiarFormulario();
          this.actualizar();
        } else {
          this.$.setupState.isEditModalOpen = false;
          mostraralertas2("No se pudo editar la Empresa", "error");
        }
      } catch (error) {
        console.error("❌ Error al actualizar la Empresa:", error.response?.data || error);
      } finally {
        this.guardandoDatos = false; // Detiene el Spinner
      }
    },
    limpiarFormulario() {
      this.objetoguardar = {
        ruc: "", empresa: "", empresacorta: "", lugar: "", direccion: "", telefono: "",
        email: "", url: "", tipo: "", titulo: "", representante: "", cargo: "",
        actividad: "", fechafin: "", tipoinstitucion: "", pais: "", imagen: "",
        estado_empr: 1, vision: "", mision: "", archivoPdf: null
      };
      this.objetoeditar = {
        idempresa: 0, ruc: "", empresa: "", empresacorta: "", lugar: "", direccion: "",
        telefono: "", email: "", url: "", tipo: "", titulo: "", representante: "",
        cargo: "", actividad: "", fechafin: "", tipoinstitucion: "", pais: "", imagen: "",
        estado_empr: 1, vision: "", mision: "", archivoPdf: null
      };
    },
    async habilitar(id, nombre) {
      try {
        // No hace falta poner this.cargando = true aquí si confimarhabi maneja la alerta,
        // pero si lo haces, asegúrate de cerrarlo.
        await confimarhabi(
          `${this.baseUrl}/habilitar_empresa/`,
          id,
          'Habilitar registro',
          `¿Desea habilitar la Empresa "${nombre}"?`,
          this.actualizar
        );
      } catch (error) {
        // Este catch capturaría errores si confimarhabi hace un 'throw'
        console.error("Error en el componente habilitar:", error);
      }
    },
    eliminar(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/inhabilitar_empresa/`,
          id,
          'Deshabilitar registro',
          '¿Realmente desea deshabilitar la Empresa  ' + nombre + '?',
          this.actualizar   // 👈 callback para refrescar la tabla al confirmar
        );
      } catch (error) {
        console.error("Error al deshabilitar la Empresa:", error);
        this.cargando = false;
      }
    },

  },
};
</script>
<style scoped>
/* Clases utilitarias agregadas para uniformizar los inputs y selects sin romper estilos globales */
.form-style-input {
  width: 100%;
  height: 2.75rem;
  border-radius: 0.5rem;
  border-width: 1px;
  border-color: #d1d5db;
  background-color: transparent;
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  font-size: 0.875rem;
  color: #1f2937;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  transition: all 0.2s;
}

.form-style-input:focus {
  border-color: #a5b4fc;
  outline: none;
  --tw-ring-bleed: 0;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

textarea.form-style-input {
  height: auto;
}

.dark .form-style-input {
  background-color: #111827;
  border-color: #374151;
  color: rgba(255, 255, 255, 0.9);
}
</style>
