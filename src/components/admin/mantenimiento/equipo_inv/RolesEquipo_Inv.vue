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
            <input type="text" placeholder="Ingresa el nombre del rol a buscar..." v-model="searchQuery"
              @input="debouncedFilter"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
          </div>
        </form>
      </div>

      <div class="flex items-center gap-3">


        <button @click="isProfileAddressModal = true"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
          Añadir Rol
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
              <p class="font-semibold text-gray-500 text-sm dark:text-gray-400">Rol y Descripción</p>
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

          <tr v-else v-for="post in filteredarray" :key="post.id_equipo_roles"
            class="border-t border-gray-100 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:bg-white/[0.02] transition-colors">

            <td class="py-5 px-4 whitespace-nowrap align-top">
              <p class="text-gray-600 text-sm font-medium dark:text-gray-400">{{ post.id_equipo_roles }}</p>
            </td>

            <td class="py-5 px-4 align-top min-w-[300px] max-w-[450px] whitespace-normal">
              <div>
                <p class="font-bold text-gray-800 text-base dark:text-white/90">
                  {{ post.nombre_rol }}
                </p>

                <p class="text-sm text-gray-500 mt-1.5 dark:text-gray-400 leading-relaxed text-pretty">
                  {{ post.detalle_rol && post.detalle_rol.length > 90
                    ? post.detalle_rol.substring(0, 90) + '...'
                    : post.detalle_rol }}

                  <button v-if="post.detalle_rol && post.detalle_rol.length > 90" @click="abrirModalDetalle(post)"
                    class="ml-1 inline-flex items-center font-semibold text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                    Ver más
                  </button>
                </p>
              </div>
            </td>

            <td class="py-5 px-4 whitespace-nowrap align-top">
              <span :class="{
                'rounded-lg px-3 py-1 text-xs font-bold uppercase tracking-wider': true,
                'bg-green-100 text-green-700 dark:bg-success-500/15 dark:text-success-500': post.estado_rol === 1,
                'bg-orange-100 text-orange-700 dark:bg-warning-500/15 dark:text-orange-400': post.estado_rol === 0
              }">
                {{ post.estado_rol === 1 ? 'Activo' : 'Inactivo' }}
              </span>
            </td>

            <td class="py-4 text-right whitespace-nowrap align-top">
              <div class="flex justify-end gap-2">
                <button @click="abrirModalEdicion(post)"
                  class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </button>
                <button @click="eliminar(post.id_equipo_roles, post.nombre_rol)" v-if="post.estado_rol === 1"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>
                <button @click="habilitar(post.id_equipo_roles, post.nombre_rol)" v-if="post.estado_rol === 0"
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
    <Modal v-if="isProfileAddressModal" @close="isProfileAddressModal = false">
      <template #body>
        <div
          class="no-scrollbar relative w-full max-w-[700px] max-h-[90vh] overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl dark:bg-gray-900 sm:p-10">

          <button @click="isProfileAddressModal = false"
            class="absolute right-4 top-4 sm:right-6 sm:top-6 z-50 flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-all hover:bg-gray-100 hover:text-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300">
            <svg class="fill-current" width="20" height="20" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                fill="" />
            </svg>
          </button>

          <div class="mb-8 pr-12 mt-2">
            <h4 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
              Agregar Rol al equipo
            </h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Llene todos los campos para registrar una nueva posición en el equipo de Investigación.
            </p>
          </div>

          <form class="flex flex-col gap-6">

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                Nombre del Rol
              </label>
              <input type="text" v-model="objetoguardar.nombre_rol"
                placeholder="Ej: Director(a) de Investigación"
                class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20" />
              <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                * Estos roles deben estar contemplados en el reglamento vigente.
              </p>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                Detalle del Rol
              </label>
              <textarea v-model="objetoguardar.detalle_rol" rows="6"
                placeholder="Describa el propósito y alcance de esta posición..."
                class="w-full resize-y appearance-none rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20"></textarea>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                Funciones del Rol
              </label>
              <textarea v-model="objetoguardar.funciones_rol" rows="6"
                placeholder="Enumere las responsabilidades principales..."
                class="w-full resize-y appearance-none rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  Estado del Rol
                </label>
                <select v-model="objetoguardar.estado_rol"
                  class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20">
                  <option value="1">Activo</option>
                  <option value="0">Inactivo</option>
                </select>
              </div>
            </div>

            <div class="mt-2 flex flex-col-reverse items-center justify-end gap-3 sm:flex-row">
              <button @click="isProfileAddressModal = false" type="button"
                class="w-full rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-800 sm:w-auto">
                Cancelar
              </button>
              <button v-if="formIsValid" @click="registrar" type="button"
                class="w-full rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-500/30 sm:w-auto">
                Guardar Rol
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>
    <Modal v-if="isEditModalOpen" @close="isEditModalOpen = false">
      <template #body>
        <div
          class="no-scrollbar relative w-full max-w-[700px] max-h-[90vh] overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl dark:bg-gray-900 sm:p-10">

          <button @click="isEditModalOpen = false"
            class="absolute right-4 top-4 sm:right-6 sm:top-6 z-50 flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-all hover:bg-gray-100 hover:text-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300">
            <svg class="fill-current" width="20" height="20" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                fill="" />
            </svg>
          </button>

          <div class="mb-8 pr-12 mt-2">
            <h4 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
              Editar Rol
            </h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Modifique los datos actuales del rol dentro del equipo de Investigación.
            </p>
          </div>

          <form class="flex flex-col gap-6">

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                Nombre del Rol
              </label>
              <input type="text" v-model="objetoeditar.nombre_rol"
                placeholder="Ej: Director(a) de Investigación"
                class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20" />
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                Detalle del Rol
              </label>
              <textarea v-model="objetoeditar.detalle_rol" rows="4"
                placeholder="Describa el propósito y alcance de esta posición..."
                class="w-full resize-y appearance-none rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20"></textarea>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                Funciones del Rol
              </label>
              <textarea v-model="objetoeditar.funciones_rol" rows="4"
                placeholder="Enumere las responsabilidades principales..."
                class="w-full resize-y appearance-none rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  Estado del Rol
                </label>
                <select v-model="objetoeditar.estado_rol"
                  class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20">
                  <option value="1">Activo</option>
                  <option value="0">Inactivo</option>
                </select>
              </div>
            </div>

            <div class="mt-2 flex flex-col-reverse items-center justify-end gap-3 sm:flex-row">
              <button @click="isEditModalOpen = false" type="button"
                class="w-full rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-800 sm:w-auto">
                Cancelar
              </button>
              <button v-if="formIsValidEdit" @click="Update" type="button"
                class="w-full rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-500/30 sm:w-auto">
                Guardar cambios
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>
    <div v-if="isDetalleModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 z-99999">

      <div
        class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-900 sm:p-8">

        <button @click="isDetalleModalOpen = false"
          class="absolute right-4 top-4 rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition-colors">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div class="mt-2">
          <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white pr-8">
            Detalle del Rol
          </h3>
          <h4 class="mb-3 font-semibold text-brand-600 dark:text-brand-400">
            {{ postSeleccionado.nombre_rol }}
          </h4>

          <div class="max-h-[60vh] overflow-y-auto custom-scrollbar pr-2">
            <p class="text-base leading-relaxed text-gray-700 text-justify dark:text-gray-300">
              {{ postSeleccionado.detalle_rol }}
            </p>
          </div>
        </div>

        <div class="mt-8 flex justify-end">
          <button @click="isDetalleModalOpen = false" type="button"
            class="w-full rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 sm:w-auto">
            Cerrar
          </button>
        </div>
      </div>
    </div>
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
        nombre_rol: "",
        detalle_rol: "",
        funciones_rol: "",
        tipo_rol: "INVESTIGACIÓN",
        estado_rol: 1
      },
      objetoeditar: {
        id_equipo_roles: 0,
        nombre_rol: "",
        detalle_rol: "",
        funciones_rol: "",
        tipo_rol: "INVESTIGACIÓN",
        estado_rol: 0
      },
      filteredarray: [],
      searchQuery: "",
      isProfileAddressModal: false,
      cargando: false,
      currentPage: 1,
      lastPage: 1,
      buscando: false, // Mantenido, pero no se usa en la lógica de paginación actual
      debouncedFilter: null,
      isDetalleModalOpen: false,
      postSeleccionado: {}
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
        this.objetoguardar.nombre_rol.trim() !== '' &&
        this.objetoguardar.detalle_rol.trim() !== '' &&
        this.objetoguardar.funciones_rol.trim() !== '' &&
        this.objetoguardar.estado_rol !== null
      );
    },
    formIsValidEdit() {
      return (
        this.objetoeditar.nombre_rol.trim() !== '' &&
        this.objetoeditar.detalle_rol.trim() !== '' &&
        this.objetoeditar.funciones_rol.trim() !== '' &&
        this.objetoeditar.estado_rol !== null
      );
    },


  },
  methods: {
    abrirModalDetalle(post) {
      this.postSeleccionado = post;
      this.isDetalleModalOpen = true;
    },
    abrirModalEdicion(user) {
      // Clonamos el objeto para no modificar la tabla directamente antes de guardar
      this.objetoeditar = {
        id_equipo_roles: user.id_equipo_roles,
        nombre_rol: user.nombre_rol,
        detalle_rol: user.detalle_rol,
        funciones_rol: user.funciones_rol,
        tipo_rol: user.tipo_rol,
        estado_rol: user.estado_rol
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
        const response = await API.get(`${this.baseUrl}/invi_equipo_roles`, { params });

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
          nombre_rol: this.objetoguardar.nombre_rol,
          detalle_rol: this.objetoguardar.detalle_rol,
          funciones_rol: this.objetoguardar.funciones_rol,
          tipo_rol: this.objetoguardar.tipo_rol,
          estado_rol: this.objetoguardar.estado_rol
        };

        const exito = await enviarsolig('POST', params, `${this.baseUrl}/invi_equipo_roles`, 'Rol al equipo de Investigación registrado con éxito');
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
      }
    },
    async Update() {
      try {
        const params = {
          nombre_rol: this.objetoeditar.nombre_rol,
          detalle_rol: this.objetoeditar.detalle_rol,
          funciones_rol: this.objetoeditar.funciones_rol,
          tipo_rol: this.objetoeditar.tipo_rol,
          estado_rol: this.objetoeditar.estado_rol
        };

        const exito = await enviarsolig('PUT', params, `${this.baseUrl}/invi_equipo_roles/${this.objetoeditar.id_equipo_roles}`, 'Rol al equipo de Investigación actualizado con éxito');

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
      }
    },
    limpiarFormulario() {
      this.objetoguardar = {
        nombre_rol: "",
        detalle_rol: "",
        funciones_rol: "",
        tipo_rol: "INVESTIGACIÓN",
        estado_rol: 0
      };
      this.objetoeditar = {
        id_equipo_roles: 0,
        nombre_rol: "",
        detalle_rol: "",
        funciones_rol: "",
        tipo_rol: "INVESTIGACIÓN",
        estado_rol: 0
      };
    },
    eliminar(id, nombre) {
      try {
        eliminacion(
          `${this.baseUrl}/inhabilitar_equipo_roles/`,
          id,
          'Deshabilitar registro',
          '¿Realmente desea deshabilitar el rol al equipo de Investigación  ' + nombre + '?',
          this.actualizar   // 👈 callback para refrescar la tabla al confirmar
        );
      } catch (error) {
        console.error("Error al Deshabilitar el rol al equipo de Investigación:", error);
        this.cargando = false;
      }
    },
    async habilitar(id, nombre) {
      try {
        // No hace falta poner this.cargando = true aquí si confimarhabi maneja la alerta,
        // pero si lo haces, asegúrate de cerrarlo.
        await confimarhabi(
          `${this.baseUrl}/habilitar_equipo_roles/`,
          id,
          'Habilitar registro',
          `¿Desea habilitar el rol al equipo de Investigación "${nombre}"?`,
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
