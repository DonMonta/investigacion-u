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
            <input type="text" placeholder="Ingresa el número de cédula a buscar..." v-model="searchQuery"
              @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
          </div>
        </form>
      </div>

      <div class="flex items-center gap-3">


        <button @click="isProfileAddressModal = true"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
          Añadir Responsable de Investigación
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
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Personal</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm">Facultad</p>
            </th>
            <th class="py-5 px-4 text-left">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Estado</p>
            </th>
            <th class="py-5 px-4 text-center">
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Evidencia</p>
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

          <tr v-else v-for="post in filteredarray" :key="post.id_responsable"
            class="border-t border-gray-100 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:bg-white/[0.02] transition-colors">

            <td class="py-5 px-4 whitespace-nowrap align-top">
              <p class="text-gray-600 text-sm font-medium dark:text-gray-400">{{ post.id_responsable }}</p>
            </td>
            <td class="py-5 px-4 align-middle whitespace-nowrap">
              <div class="flex items-center gap-3">
                <img :src="getPhotoUrl(post.ciinfper_doc)" alt="Foto"
                  class="w-10 h-10 rounded-full object-cover border border-gray-200"
                  @error="(e) => e.target.src = '/default-avatar.png'" />
                <div>
                  <p class="font-bold text-gray-800 text-sm dark:text-white/90">
                    {{ post.NombInfPer }} {{ post.ApellInfPer }} {{ post.ApellMatInfPer }}
                  </p>
                  <p class="text-xs text-gray-500">{{ post.ciinfper_doc }}</p>
                </div>
              </div>
            </td>

            <td class="py-5 px-4 align-middle">
              <p class="text-gray-800 text-sm font-semibold" v-if="post.siglas == 'SC'">SEDE CONCORDIA</p>
              <p class="text-gray-800 text-sm font-semibold" v-else>{{ post.siglas }}</p>
            </td>

            <td class="py-5 px-4 whitespace-nowrap align-top">
              <span :class="{
                'rounded-lg px-3 py-1 text-xs font-bold uppercase tracking-wider': true,
                'bg-green-100 text-green-700 dark:bg-success-500/15 dark:text-success-500': post.estado_responsable === 1,
                'bg-orange-100 text-orange-700 dark:bg-warning-500/15 dark:text-orange-400': post.estado_responsable === 0
              }">
                {{ post.estado_responsable === 1 ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="p-3 text-center">
              <div v-if="post.evidencia_arch" class="flex justify-center">
                <a :href="`http://192.168.1.112:8087/Documentos/INVESTIGACION/AnexoResponsable/${post.ciinfper_doc}/${post.evidencia_arch}`"
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
            </td>

            <td class="py-4 text-right whitespace-nowrap align-top">
              <div class="flex justify-end gap-2">
                <button @click="abrirModalEdicion(post)"
                  :disabled="botonCargando === 'editar_' + post.id_responsable"
                  class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                  <svg v-if="botonCargando === 'editar_' + post.id_responsable" class="animate-spin h-5 w-5 text-blue-600"
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
                <button @click="eliminar(post.id_responsable, post.ciinfper_doc)" v-if="post.estado_responsable === 1"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>
                <button @click="habilitar(post.id_responsable, post.ciinfper_doc)" v-if="post.estado_responsable === 0"
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
    <!-- Modal de Registro-->
    <Modal v-if="isProfileAddressModal || isEditModalOpen" @close="cerrarModalGeneral">
      <template #body>
        <div
          class="no-scrollbar relative w-full max-w-[800px] max-h-[90vh] overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl dark:bg-gray-900 sm:p-10">
          <button @click="cerrarModalGeneral"
            class="absolute right-4 top-4 z-50 flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
            <svg class="fill-current" width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                fill="" />
            </svg>
          </button>

          <div class="mb-6 mt-2">
            <h4 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
              {{ isEditModalOpen ? 'Editar' : 'Agregar' }} Responsable de Investigación
            </h4>
            <p class="text-sm text-gray-500">Asigne o modifique el responsable de Investigación.</p>
          </div>

          <form class="flex flex-col gap-6" @submit.prevent>
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Ingrese la Cédula (10 dígitos)</label>
              <div class="flex gap-2">
                <input type="text" v-model="cedulaFormulario" @input="validarInputCedula"
                  @keyup.enter="buscarDocenteAPI" placeholder="Ej: 0800001234" maxlength="10"
                  class="h-11 w-full flex-1 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10" />
                <button type="button" @click="buscarDocenteAPI"
                  :disabled="cedulaFormulario.length !== 10 || buscandoDocenteModal"
                  class="h-11 px-6 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700 disabled:opacity-50 flex items-center justify-center min-w-[100px]">
                  <span v-if="buscandoDocenteModal"
                    class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                  <span v-else>Buscar</span>
                </button>
              </div>
            </div>

            <div v-if="docenteEncontrado"
              class="flex items-center gap-4 p-4 rounded-xl border border-blue-100 bg-blue-50/50 dark:bg-blue-900/10 dark:border-blue-800">
              <img :src="getPhotoUrl(docenteEncontrado.CIInfPer)" alt="Foto Docente"
                class="w-16 h-16 rounded-full border-2 border-white shadow-sm object-cover"
                @error="(e) => e.target.src = '/default-avatar.png'" />
              <div>
                <p class="text-xs text-brand-600 font-bold uppercase tracking-wide">Docente Seleccionado</p>
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ docenteEncontrado.NombInfPer }} {{
                  docenteEncontrado.ApellInfPer }} {{ docenteEncontrado.ApellMatInfPer }}</h4>
                <p class="text-sm text-gray-500">C.I: {{ docenteEncontrado.CIInfPer }}</p>
              </div>
            </div>

            <div v-if="docenteEncontrado">
              <label class="mb-3 block text-sm font-medium text-gray-700">Seleccione una facultad</label>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label v-for="facu in facultadesDisponibles" :key="facu.idfacultad" :class="['relative flex p-4 border rounded-xl cursor-pointer transition-all duration-200 shadow-sm',
                  (facu.is_assigned && facu.asignado_a !== docenteEncontrado.CIInfPer) ? 'opacity-50 cursor-not-allowed bg-gray-50 border-gray-200' : 'hover:border-brand-400 hover:bg-brand-50/30',
                  formularioFacultadesID === facu.idfacultad ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-gray-200 bg-white'
                ]">
                  <input type="radio" :value="facu.idfacultad" v-model="formularioFacultadesID"
                    :disabled="facu.is_assigned && facu.asignado_a !== docenteEncontrado.CIInfPer" class="hidden" />

                  <div class="flex-1">
                    <div class="flex justify-between items-start">
                      <h5 class="font-semibold text-gray-900 text-sm" v-if="facu.siglas == 'SC'">SEDE CONCORDIA</h5>
                      <h5 class="font-semibold text-gray-900 text-sm" v-else>{{ facu.siglas }}</h5>
                      <span v-if="facu.is_assigned && facu.asignado_a !== docenteEncontrado.CIInfPer"
                        class="text-[10px] font-bold bg-red-100 text-red-600 px-2 py-0.5 rounded uppercase">Ocupado</span>
                      <svg v-else-if="formularioFacultadesID === facu.idfacultad" class="w-5 h-5 text-brand-600"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                          clip-rule="evenodd"></path>
                      </svg>
                    </div>
                  </div>
                </label>
              </div>
              <div v-if="isEditModalOpen && !archivoSeleccionado" class="mt-4 col-span-1 lg:col-span-2">
                <label class="block text-xs font-bold mb-2 text-gray-700 dark:text-gray-300">Documento de respaldo
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
              <label class="block text-[10px] font-bold mb-1">{{ isEditModalOpen ? 'Subir un nuevo documento para reemplazar el actual (PDF)' :
                    'Documento de respaldo (PDF)' }}</label>
              <div @click="$refs.fileFoto.click()"
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
                    <span class="font-semibold" v-if="!archivoPreviewName">Haga clic para
                      cargar</span>
                    <span class="font-semibold text-brand-600" v-else>{{ archivoPreviewName
                    }}</span>
                  </p>
                  <p class="text-xs text-gray-400" v-if="!archivoPreviewName">PDF (Máx. 10MB)</p>
                </div>

                <input type="file" ref="fileFoto" class="hidden" accept="application/pdf" @change="handleFileChange" />
              </div>

            </div>

            <div class="mt-4 flex flex-col-reverse items-center justify-end gap-3 sm:flex-row">
              <button @click="cerrarModalGeneral" type="button"
                class="w-full rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 sm:w-auto">Cancelar</button>
              <button v-if="docenteEncontrado && formularioFacultadesID"
                @click="isEditModalOpen ? Update() : registrar()" type="button" :disabled="uploading"
                class="w-full rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-brand-700 sm:w-auto">
                <span v-if="uploading" class="inline-flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <span>{{ isEditModalOpen ? 'Guardando....' : 'Actualizando...' }}</span>
                </span>

                <!-- Span estado normal -->
                <span v-else>
                  {{ isEditModalOpen ? 'Guardar Cambios' : 'Asignar Rol' }}
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

      usersarray: [],
      objetoguardar: {
        ciinfper_doc: "",
        idfacultad: "",
        estado_responsable: 1
      },
      objetoeditar: {
        id_responsable: 0,
        ciinfper_doc: "",
        idfacultad: "",
        estado_responsable: 0,
        ad_archivo_url: null
      },
      filteredarray: [],
      searchQuery: "",
      isProfileAddressModal: false,
      cargando: false,
      currentPage: 1,
      lastPage: 1,
      buscando: false, // Mantenido, pero no se usa en la lógica de paginación actual
      debouncedFilter: null,
      // Variables Formulario/Modal
      cedulaFormulario: "",
      buscandoDocenteModal: false,
      docenteEncontrado: null,
      facultadesDisponibles: [],
      formularioFacultadesID: null,
      idEquipoDepartEdit: 0,
      botonCargando: null,
      // Modal Nativo de Detalles
      showDetallesModal: false,
      archivoSeleccionado: null,
      archivoPreviewName: '',
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
    this.GetData(1, this.searchQuery);
    this.cargarFacultades();

  },
  computed: {

    formIsValid() {

      return (
        this.objetoguardar.ciinfper_doc.trim() !== '' &&
        this.objetoguardar.idfacultad.trim() !== '' &&
        this.objetoguardar.estado_responsable !== null
      );
    },
    formIsValidEdit() {
      return (
        this.objetoeditar.ciinfper_doc.trim() !== '' &&
        this.objetoeditar.idfacultad.trim() !== '' &&
        this.objetoeditar.estado_responsable !== null
      );
    },


  },
  methods: {
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
        const resp = await API.post(`${this.baseUrl}/subir_archivo_responsable`, form, {
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
    formatFunciones(texto) {
      if (!texto) return [];
      // Dividimos por salto de línea y limpiamos espacios o guiones iniciales
      return texto.split('\n').map(line => line.replace(/^[-*•]\s*/, '').trim()).filter(line => line.length > 0);
    },
    validarInputCedula(e) {
      // Solo permite números y máximo 10
      this.cedulaFormulario = e.target.value.replace(/\D/g, '').slice(0, 10);
    },
    async abrirModalEdicion(user) {
      this.botonCargando = 'editar_' + user.id_responsable;
      try{
        this.limpiarFormulario();
        this.idEquipoDepartEdit = user.id_responsable; // Asignamos el ID
        this.cedulaFormulario = user.ciinfper_doc;
        this.objetoeditar = {
          evidencia_arch: user.evidencia_arch,
          ad_archivo_url: user.ad_archivo_url
        };

        // IMPORTANTE: Primero cargamos los roles con el ID a excluir
        await this.cargarFacultades();

        // Luego buscamos al docente
        await this.buscarDocenteAPI();

        // Finalmente asignamos el rol
        this.formularioFacultadesID = user.idfacultad;
        this.$.setupState.isEditModalOpen = true;
      }catch(e){
        console.log(e)
      }finally{
        this.botonCargando = null;
      }
     
    },
    cerrarModalGeneral() {
      this.$.setupState.isProfileAddressModal = false;
      this.$.setupState.isEditModalOpen = false;
      this.limpiarFormulario();
    },
    limpiarFormulario() {
      this.cedulaFormulario = "";
      this.docenteEncontrado = null;
      this.formularioFacultadesID = null;
      this.idEquipoDepartEdit = 0;
      this.archivoSeleccionado = null;
      this.archivoPreviewName = '';
      this.archivoActualNombre = '';
    },
    getPhotoUrl(ci) {
      // Si no hay CI, retornamos una imagen vacía o un placeholder
      if (!ci) return '';

      const baseURL2 = API.defaults.baseURL;
      // Usamos el timestamp para evitar problemas de caché al cambiar de integrante
      return `${baseURL2}/inves/getFotoDocente/${ci}?t=${new Date().getTime()}`;
    },
    async buscarDocenteAPI() {
      if (this.cedulaFormulario.length !== 10) return;
      this.buscandoDocenteModal = true;
      this.docenteEncontrado = null;
      this.formularioFacultadesID = null;

      try {
        const response = await API.get(`${this.baseUrl}/getDocente/${this.cedulaFormulario}`);
        this.docenteEncontrado = response.data.data;
        await this.cargarFacultades(); // Refrescamos estados de roles (ocupados/libres)
      } catch (error) {
        mostraralertas2("Docente no encontrado o inactivo.", "error");
      } finally {
        this.buscandoDocenteModal = false;
      }
    },
    async cargarFacultades() {
      try {
        const params = { id_excluir: this.idEquipoDepartEdit };
        const res = await API.get(`${this.baseUrl}/getFacultadesInvestigacion`, { params });
        this.facultadesDisponibles = res.data.data;
      } catch (e) { console.error("Error cargando roles", e); }
    },
    async GetData(page = 1, searchQuery = "") {
      this.cargando = true;

      try {
        const params = {
          page: page,
          search_query: searchQuery // Parámetro para búsqueda
        };
        const response = await API.get(`${this.baseUrl}/invi_responsable`, { params });

        const data = response.data?.data || [];
        const rootURL = (API.defaults.baseURL || '').replace(/\/api\/?$/, '');
        this.filteredarray = data.map(item => {
          const cedula = item.ciinfper_doc.replace(/[/\\ ]/g, '_');
          return {
            ...item,
            ad_archivo_url: item.evidencia_arch
              ? `${rootURL}/Documentos/INVESTIGACION/AnexoResponsable/${cedula}/${item.evidencia_arch}`
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
        // 1. Inicializar la variable del anexo como vacía por defecto
        let nombreArchivoSubido = null;

        // 2. Si hay un archivo en cola, se sube primero
        if (this.archivoSeleccionado) {
          const resultadoSubida = await this.uploadarchivo(this.cedulaFormulario);

          // Si falló el upload (mostró alerta internamente), cortamos el flujo para corregir
          if (!resultadoSubida || !resultadoSubida.filename) {
            return;
          }

          nombreArchivoSubido = resultadoSubida.filename;
        }
        const params = {
          ciinfper_doc: this.cedulaFormulario,
          tipo_responsable: 'INVESTIGACIÓN',
          estado_responsable: 1,
          idfacultad: this.formularioFacultadesID,
          evidencia_arch: nombreArchivoSubido
        };
        const exito = await enviarsolig('POST', params, `${this.baseUrl}/invi_responsable`, 'Asignación registrada éxito');
        if (exito) {
          this.$.setupState.isProfileAddressModal = false;
          this.limpiarFormulario();
          this.actualizar();
        }
      } catch (error) {
        // Aquí capturamos el error 422 o cualquier otro del servidor
        const mensajeError = error.response?.data?.mensaje || "No se pudo registrar el Rol al equipo de Investigación";
        mostraralertas2(mensajeError, "error");
        console.error("❌ Error al registrar Rol:", error.response?.data || error);
      } finally {
        this.uploading = false; // Desactiva el loader al terminar
      }
    },
    async Update() {
      if (this.uploading) return;
      try {
        this.uploading = true;
        // 1. Mantener por defecto el archivo que ya tenía asignado el registro en edición
        let nombreArchivoSubido = this.archivoActualNombre;

        // 2. Si el usuario seleccionó un archivo nuevo para reemplazar el anterior
        if (this.archivoSeleccionado) {
          // Pasamos la cédula y el nombre viejo para que Laravel limpie el disco público automáticamente
          const resultadoSubida = await this.uploadarchivo(this.cedulaFormulario, this.archivoActualNombre);

          if (!resultadoSubida || !resultadoSubida.filename) {
            return;
          }

          nombreArchivoSubido = resultadoSubida.filename;
        }
        const params = {
          ciinfper_doc: this.cedulaFormulario,
          tipo_responsable: 'INVESTIGACIÓN',
          estado_responsable: 1,
          idfacultad: this.formularioFacultadesID,
          evidencia_arch: nombreArchivoSubido
        };
        const exito = await enviarsolig('PUT', params, `${this.baseUrl}/invi_responsable/${this.idEquipoDepartEdit}`, 'Asignación actualizada con éxito');
        if (exito) {
          this.$.setupState.isEditModalOpen = false;
          this.limpiarFormulario();
          this.actualizar();
        }
      } catch (error) {
        // Captura el mensaje enviado desde Laravel (422 o 500)
        const mensajeError = error.response?.data?.mensaje || "No se pudo editar el Rol al equipo de Investigación";
        mostraralertas2(mensajeError, "error");
        console.error("❌ Error al editar Rol:", error.response?.data || error);
      } finally {
        this.uploading = false; // Desactiva el loader al terminar
      }
    },

    eliminar(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/inhabilitar_responsable/`,
          id,
          'Deshabilitar registro',
          '¿Realmente desea deshabilitar al responsable de Investigación  ' + nombre + '?',
          this.actualizar   // 👈 callback para refrescar la tabla al confirmar
        );
      } catch (error) {
        console.error("Error al Deshabilitar al responsable de Investigación:", error);
        this.cargando = false;
      }
    },
    async habilitar(id, nombre) {
      try {
        // No hace falta poner this.cargando = true aquí si confimarhabi maneja la alerta,
        // pero si lo haces, asegúrate de cerrarlo.
        await confimarhabi(
          `${this.baseUrl}/habilitar_responsable/`,
          id,
          'Habilitar registro',
          `¿Desea habilitar al responsable de Investigación "${nombre}"?`,
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
