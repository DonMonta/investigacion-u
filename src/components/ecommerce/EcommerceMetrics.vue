<template>
  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 w-full">
    
    <div v-for="card in statCards" :key="card.title" 
      class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-gray-800 dark:bg-white/[0.03]">
      
      <div :class="`absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 transition-transform duration-500 group-hover:scale-150 ${card.iconBg}`"></div>

      <div class="relative flex items-center justify-between">
        <div :class="`flex h-14 w-14 items-center justify-center rounded-2xl shadow-inner transition-colors duration-300 ${card.bgColor} ${card.iconColor}`">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide">
            <path :d="card.svgPath" />
            <circle v-if="card.svgCircle" :cx="card.svgCircle.cx" :cy="card.svgCircle.cy" :r="card.svgCircle.r" />
          </svg>
        </div>

        <div class="flex flex-col items-end">
          <span class="flex h-3 w-3">
            <span :class="`absolute inline-flex h-3 w-3 animate-ping rounded-full opacity-75 ${card.dotColor}`"></span>
            <span :class="`relative inline-flex h-3 w-3 rounded-full ${card.dotColor}`"></span>
          </span>
        </div>
      </div>

      <div class="relative mt-6 flex items-baseline justify-between">
        <div>
          <p class="text-sm font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">
            {{ card.title }}
          </p>
          <h4 class="mt-2 text-3xl font-black text-gray-900 dark:text-white">
            <span v-if="loading" class="inline-block h-8 w-12 animate-pulse rounded bg-gray-200 dark:bg-gray-700"></span>
            <span v-else>{{ card.value }}</span>
          </h4>
        </div>
      </div>

      <div class="mt-4 h-1 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
        <div :class="`h-full transition-all duration-1000 ${card.barColor}`" :style="{ width: loading ? '0%' : '100%' }"></div>
      </div>
    </div>

  </div>
</template>

<script>
import API from "@/assets/js/services/axios";

export default {
  name: 'DashboardStats',
  data() {
    return {
      stats: {
        total_proyectos: 0,
        total_directores: 0,
        total_subdirectores: 0,
        total_docentes: 0
      },
      baseUrl: "/inves",
      loading: true
    };
  },
  computed: {
    statCards() {
      return [
        {
          title: 'Proyectos',
          value: this.stats.total_proyectos,
          bgColor: 'bg-blue-100 dark:bg-blue-500/20',
          iconBg: 'bg-blue-400',
          iconColor: 'text-blue-600 dark:text-blue-400',
          dotColor: 'bg-blue-500',
          barColor: 'bg-blue-500',
          svgPath: 'M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2zm18 0h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z' // Icono: Book/Project
        },
        {
          title: 'Directores',
          value: this.stats.total_directores,
          bgColor: 'bg-emerald-100 dark:bg-emerald-500/20',
          iconBg: 'bg-emerald-400',
          iconColor: 'text-emerald-600 dark:text-emerald-400',
          dotColor: 'bg-emerald-500',
          barColor: 'bg-emerald-500',
          svgPath: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2',
          svgCircle: { cx: 9, cy: 7, r: 4 } // Icono: User
        },
        {
          title: 'Subdirectores',
          value: this.stats.total_subdirectores,
          bgColor: 'bg-orange-100 dark:bg-orange-500/20',
          iconBg: 'bg-orange-400',
          iconColor: 'text-orange-600 dark:text-orange-400',
          dotColor: 'bg-orange-500',
          barColor: 'bg-orange-500',
          svgPath: 'M17 21v-2a4 4 0 0 0-3-3.87M9 21v-2a4 4 0 0 1 3-3.87',
          svgCircle: { cx: 12, cy: 7, r: 4 } // Icono: Users
        },
        {
          title: 'Docentes',
          value: this.stats.total_docentes,
          bgColor: 'bg-purple-100 dark:bg-purple-500/20',
          iconBg: 'bg-purple-400',
          iconColor: 'text-purple-600 dark:text-purple-400',
          dotColor: 'bg-purple-500',
          barColor: 'bg-purple-500',
          svgPath: 'M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-0-5H20' // Icono: Teacher/Education
        }
      ];
    }
  },
  methods: {
    async fetchStats() {
      try {
        const response = await API.get(`${this.baseUrl}/dashboard/stats`);
        if (response.data.status) {
          this.stats = response.data.stats;
        }
      } catch (error) {
        console.error("Error stats:", error);
      } finally {
        this.loading = false;
      }
    }
  },
  mounted() {
    this.fetchStats();
  }
};
</script>