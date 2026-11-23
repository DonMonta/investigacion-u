<template>
  <div class="container px-4 mx-auto mt-6 max-w-6xl">
    <!-- SPINNER DE CARGA -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center h-64 gap-4">
      <svg class="w-12 h-12 text-blue-200 animate-spin fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill" />
      </svg>
      <span class="text-sm font-medium text-gray-500 animate-pulse">Cargando información...</span>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div v-else class="animate-fade-in">
      <!-- HEADER / BANNER -->
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6 overflow-hidden hover:shadow-md transition-shadow duration-300">
        <!-- Banner Grid Pattern -->
        <div class="h-32 bg-gradient-to-r from-blue-50 to-indigo-50" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;"></div>
        
        <div class="px-6 pb-6 flex flex-col md:flex-row md:items-end justify-between -mt-12 relative z-10">
          <div class="flex flex-col md:flex-row gap-5 items-center md:items-end">
            <!-- Profile Image -->
            <div class="w-28 h-28 rounded-full border-4 border-white shadow-lg bg-white overflow-hidden shrink-0 group">
              <img :src="getPhotoUrl(PersonalInfo.ciinfper)" alt="user" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-300" />
            </div>
            
            <div class="mb-2 text-center md:text-left">
              <h2 class="text-2xl font-bold text-gray-800">
                {{ PersonalInfo.NombUsu }}
              </h2>
              <div class="flex flex-wrap gap-4 text-sm text-gray-600 mt-2 justify-center md:justify-start font-medium">
                <span class="flex items-center gap-1.5 bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                  {{ infoDepartamento?.equipo_roles?.nombre_rol || 'Investigador' }}
                </span>
                <span class="flex items-center gap-1.5 bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                  Departamento de Investigación
                </span>
              </div>
            </div>
          </div>
          
          <div class="mt-4 md:mt-0 flex justify-center pb-2">
            <span class="bg-green-100 text-green-700 border border-green-200 text-sm font-semibold px-4 py-2 rounded-full flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
              Conectado
            </span>
          </div>
        </div>
      </div>

      <!-- TABS SIMULADOS -->
      <div class="flex gap-2 mb-6 border-b border-gray-200 pb-1">
        <button class="text-blue-600 border-b-2 border-blue-600 px-4 py-2 text-sm font-semibold transition-colors">Perfil & Actividad</button>
      </div>

      <!-- DOS COLUMNAS -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- COLUMNA IZQUIERDA (Perfil y Equipo) -->
        <div class="space-y-6">
          <!-- Tarjeta ABOUT -->
          <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <h3 class="text-xs font-bold text-gray-400 mb-5 tracking-widest uppercase">Acerca de</h3>
            <ul class="space-y-4 text-sm">
              <li class="flex text-gray-700 items-center"><span class="font-semibold w-24 flex items-center gap-2 text-gray-500"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Nombre:</span> {{ PersonalInfo.NombUsu }}</li>
              <li class="flex text-gray-700 items-center"><span class="font-semibold w-24 flex items-center gap-2 text-gray-500"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Estado:</span> Activo</li>
              <li class="flex text-gray-700 items-center"><span class="font-semibold w-24 flex items-center gap-2 text-gray-500"><svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg> Rol:</span> {{ UsuarioInfo.rolUsuario }}</li>
            </ul>

            <h3 class="text-xs font-bold text-gray-400 mt-8 mb-5 tracking-widest uppercase">Contactos</h3>
            <ul class="space-y-4 text-sm">
              <li class="flex text-gray-700 items-center"><span class="font-semibold w-24 flex items-center gap-2 text-gray-500"><svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> Email:</span> {{ UsuarioInfo.emailUsuario }}</li>
            </ul>
          </div>

          <!-- Tarjeta MI EQUIPO -->
          <div v-if="equipoCargo.length > 0" class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <h3 class="text-xs font-bold text-gray-400 mb-4 tracking-widest uppercase">Mi Equipo</h3>
            <ul class="space-y-2">
              <li v-for="miembro in equipoCargo" :key="miembro.ciinfper" 
                  @click="cargarBitacoraMiembro(miembro)"
                  class="flex items-center justify-between p-2.5 rounded-lg cursor-pointer transition-all duration-200 border border-transparent"
                  :class="usuarioSeleccionado === miembro.ciinfper 
                    ? 'bg-blue-50 border-blue-200 shadow-sm' 
                    : 'hover:bg-gray-50 hover:border-gray-200'">
                <div class="flex items-center gap-3">
                  <img :src="getPhotoUrl(miembro.ciinfper)" class="w-10 h-10 rounded-full border-2 border-white shadow-sm object-cover" />
                  <div>
                    <p class="text-sm font-semibold text-gray-800 leading-tight">{{ miembro.nombres }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ miembro.rol }}</p>
                  </div>
                </div>
                <svg v-if="usuarioSeleccionado === miembro.ciinfper" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </li>
              
              <!-- Botón Volver -->
              <li v-if="usuarioSeleccionado !== PersonalInfo.ciinfper" 
                  @click="cargarBitacoraMiembro({ciinfper: PersonalInfo.ciinfper, nombres: 'Mi Bitácora'})"
                  class="flex items-center justify-center gap-2 p-3 mt-4 hover:bg-blue-50 rounded-lg cursor-pointer text-sm text-blue-600 font-semibold border border-blue-100 transition-colors bg-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver a mi bitácora
              </li>
            </ul>
          </div>
        </div>

        <!-- COLUMNA DERECHA (Activity Timeline) -->
        <div class="lg:col-span-2">
          <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 relative min-h-[400px]">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 pb-4 border-b border-gray-100 gap-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 rounded-lg">
                  <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">
                  Línea de Actividad
                  <span v-if="usuarioSeleccionado !== PersonalInfo.ciinfper" class="text-sm font-medium text-gray-500 ml-2 bg-gray-100 px-2 py-1 rounded">({{ nombreSeleccionado }})</span>
                </h3>
              </div>
              <span class="text-xs font-semibold text-gray-400 bg-gray-50 px-3 py-1 rounded-full border border-gray-200">{{ bitacoraFiltrada.length }} registros</span>
            </div>

            <!-- FILTROS DE FECHA -->
            <div class="flex flex-wrap items-center gap-4 mb-6 bg-gray-50 p-3 rounded-lg border border-gray-200">
              <div class="flex items-center gap-2">
                <label for="fechaInicio" class="text-xs font-semibold text-gray-600">Desde:</label>
                <input type="date" id="fechaInicio" v-model="filtro.fechaInicio" 
                       class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1.5 px-2 outline-none border">
              </div>
              <div class="flex items-center gap-2">
                <label for="fechaFin" class="text-xs font-semibold text-gray-600">Hasta:</label>
                <input type="date" id="fechaFin" v-model="filtro.fechaFin" 
                       class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1.5 px-2 outline-none border">
              </div>
              <button v-if="filtro.fechaInicio || filtro.fechaFin" @click="limpiarFiltros" 
                      class="text-xs text-red-600 hover:text-red-800 font-semibold px-3 py-1.5 bg-red-50 hover:bg-red-100 rounded-md transition-colors border border-red-100 ml-auto md:ml-0">
                Limpiar filtros
              </button>
            </div>

            <!-- Loader interno de timeline -->
            <div v-if="isTimelineLoading" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center rounded-xl z-10 transition-opacity">
              <svg class="w-10 h-10 text-blue-200 animate-spin fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" /></svg>
            </div>

            <!-- CONTENEDOR CON SCROLL -->
            <div class="max-h-[500px] overflow-y-auto pr-4 custom-scrollbar">
              <ul class="border-l-2 border-indigo-100 ml-4 space-y-6 pb-6 pt-2">
                <li v-for="(item, index) in bitacoraFiltrada" :key="index" class="relative pl-8 group">
                  <!-- Punto de color en la línea de tiempo -->
                  <span class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full bg-indigo-500 border-4 border-white shadow-sm ring-2 ring-indigo-50 group-hover:scale-125 transition-transform duration-200"></span>
                  
                  <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm group-hover:shadow-md group-hover:border-indigo-100 transition-all duration-200 -mt-1.5">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-2 gap-2">
                      <h4 class="text-sm font-bold text-gray-800">{{ item.bt_accion }}</h4>
                      <span class="text-xs font-semibold text-gray-500 bg-gray-50 px-2.5 py-1 rounded-md whitespace-nowrap flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ formatearFecha(item.bt_fechahora) }}
                      </span>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-3 leading-relaxed">{{ item.bt_observacion }}</p>
                    
                    <div class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded">
                      <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                      IP: {{ item.bt_ippc }}
                    </div>
                  </div>
                </li>
                
                <li v-if="bitacoraFiltrada.length === 0" class="pl-8 pt-4">
                  <div class="bg-gray-50 border border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <p class="text-sm text-gray-500 font-medium">No hay registros de actividad para este rango de fechas.</p>
                  </div>
                </li>
              </ul>
            </div>
            
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import API from "@/assets/js/services/axios";
import { getMe } from '@/store/auth';
import { useUsuario } from "@/composables/useUsuario";

export default {
  data() {
    return {
      isLoading: true,
      isTimelineLoading: false,
      PersonalInfo: {},
      UsuarioInfo: {},
      
      infoDepartamento: null,
      equipoCargo: [],
      bitacora: [],
      baseUrl: "/inves",
      usuarioSeleccionado: null,
      nombreSeleccionado: '',

      // Filtros de fecha
      filtro: {
        fechaInicio: '',
        fechaFin: ''
      }
    };
  },
  computed: {
    // Computada que devuelve la bitácora filtrada por el rango de fechas
    bitacoraFiltrada() {
      if (!this.filtro.fechaInicio && !this.filtro.fechaFin) {
        return this.bitacora;
      }

      return this.bitacora.filter(item => {
        const itemDate = new Date(item.bt_fechahora).getTime();
        let cumpleFiltro = true;

        if (this.filtro.fechaInicio) {
          // Convertimos la fecha inicio a milisegundos a las 00:00:00
          const start = new Date(this.filtro.fechaInicio + 'T00:00:00').getTime();
          if (itemDate < start) cumpleFiltro = false;
        }

        if (this.filtro.fechaFin) {
          // Convertimos la fecha fin a milisegundos a las 23:59:59 para incluir todo el día
          const end = new Date(this.filtro.fechaFin + 'T23:59:59').getTime();
          if (itemDate > end) cumpleFiltro = false;
        }

        return cumpleFiltro;
      });
    }
  },
  async mounted() {
    try {
      this.isLoading = true;
      const usuario = await getMe();
      this.PersonalInfo = usuario;
      this.UsuarioInfo = useUsuario();
      
      // Por defecto, carga la bitácora del usuario logueado
      this.usuarioSeleccionado = this.PersonalInfo.ciinfper;
      await this.cargarDatosBitacora(this.PersonalInfo.ciinfper);

    } catch (error) {
      console.error("Error al cargar datos:", error);
    } finally {
      this.isLoading = false;
    }
  },
  methods: {
    getPhotoUrl(ci) {
      if(!ci) return '';
      return `${API.defaults.baseURL}/inves/getFotoDocente/${ci}`;
    },
    
    async cargarDatosBitacora(identificador) {
      this.isTimelineLoading = true;
      try {
        const res = await API.get(`${this.baseUrl}/bitacora-investigacion/${identificador}`);
        this.bitacora = res.data.bitacora || [];
        
        // Asignamos la información del departamento y el equipo solo la primera vez 
        if (identificador === this.PersonalInfo.ciinfper) {
           this.infoDepartamento = res.data.departamento_investigacion || null;
           this.equipoCargo = res.data.equipo_cargo || [];
        }
      } catch (error) {
        console.error("Error al obtener la bitácora", error);
      } finally {
        this.isTimelineLoading = false;
      }
    },
    
    cargarBitacoraMiembro(miembro) {
      this.usuarioSeleccionado = miembro.ciinfper;
      this.nombreSeleccionado = miembro.nombres;
      this.limpiarFiltros(); // Reseteamos el filtro al cambiar de usuario
      this.cargarDatosBitacora(miembro.ciinfper);
    },

    limpiarFiltros() {
      this.filtro.fechaInicio = '';
      this.filtro.fechaFin = '';
    },

    formatearFecha(fechaStr) {
      if (!fechaStr) return '';
      const date = new Date(fechaStr);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.round(diffMs / 60000);
      
      if (diffMins < 60 && diffMins > 0) return `Hace ${diffMins} min`;
      if (diffMins < 1440 && diffMins > 0) return `Hace ${Math.floor(diffMins / 60)} horas`;
      if (diffMins < 2880 && diffMins > 0) return `Ayer`;
      return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute:'2-digit' });
    }
  },
}
</script>

<style scoped>
/* Transición suave para aparición de contenido */
.animate-fade-in {
  animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Estilos personalizados para el Scrollbar de la línea de tiempo */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f8fafc; /* gray-50 */
  border-radius: 8px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1; /* slate-300 */
  border-radius: 8px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; /* slate-400 */
}
</style>