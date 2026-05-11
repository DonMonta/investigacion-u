<template>
  <div class="rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="px-5 pt-5 bg-white shadow-default rounded-2xl pb-11 dark:bg-gray-900 sm:px-6 sm:pt-6">
      <div class="flex justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Meta Mensual</h3>
          <p class="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
            Progreso basado en el objetivo de ${{ stats.target.toLocaleString() }}
          </p>
        </div>
        <DropdownMenu :menu-items="menuItems" />
      </div>

      <div class="relative max-h-[195px]">
        <div id="chartTwo" class="h-full">
          <div class="radial-bar-chart">
            <VueApexCharts v-if="!loading" type="radialBar" height="330" :options="chartOptions"
              :series="[stats.percentage]" />
          </div>
        </div>
        <span
          class="absolute left-1/2 top-[85%] -translate-x-1/2 -translate-y-[85%] rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
          Activo
        </span>
      </div>
      <p class="mx-auto mt-1.5 w-full max-w-[380px] text-center text-sm text-gray-500 sm:text-base">
        Has ganado <span class="font-bold text-gray-800 dark:text-white">${{ formatCurrency(stats.revenue_today)
        }}</span> hoy.
        {{ stats.revenue_month >= stats.target ? '¡Meta alcanzada!' : '¡Sigue así!' }}
      </p>
    </div>

    <div class="flex items-center justify-center gap-5 px-6 py-3.5 sm:gap-8 sm:py-5">
      <div>
        <p class="mb-1 text-center text-gray-500 text-theme-xs dark:text-gray-400 sm:text-sm">Objetivo</p>
        <p class="text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">${{ stats.target / 1000 }}K</p>
      </div>
      <div class="w-px bg-gray-200 h-7 dark:bg-gray-800"></div>
      <div>
        <p class="mb-1 text-center text-gray-500 text-theme-xs dark:text-gray-400 sm:text-sm">Ingreso Mes</p>
        <p class="text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">${{ (stats.revenue_month /
          1000).toFixed(1) }}K</p>
      </div>
      <div class="w-px bg-gray-200 h-7 dark:bg-gray-800"></div>
      <div>
        <p class="mb-1 text-center text-gray-500 text-theme-xs dark:text-gray-400 sm:text-sm">Hoy</p>
        <p class="text-base font-semibold text-green-600 sm:text-lg">
          ${{ formatCurrency(stats.revenue_today) }}
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import API from "@/assets/js/services/axios";
import VueApexCharts from 'vue3-apexcharts';
import DropdownMenu from '../common/DropdownMenu.vue';

export default {
  name: 'MonthlyTargetChart',
  components: {
    VueApexCharts,
    DropdownMenu
  },
  data() {
    return {
      loading: true,
      baseUrl: "/restrik",
      pollingInterval: null,
      stats: {
        target: 0,
        revenue_month: 0,
        revenue_today: 0,
        percentage: 0
      },
      menuItems: [
        { label: 'Actualizar', onClick: () => this.fetchTargetData() }
      ],
      chartOptions: {
        colors: ['#F5B30F'],
        chart: {
          fontFamily: 'Outfit, sans-serif',
          sparkline: { enabled: true },
        },
        plotOptions: {
          radialBar: {
            startAngle: -90,
            endAngle: 90,
            hollow: { size: '80%' },
            track: {
              background: '#E4E7EC',
              strokeWidth: '100%',
              margin: 5,
            },
            dataLabels: {
              name: { show: false },
              value: {
                fontSize: '36px',
                fontWeight: '600',
                offsetY: 60,
                color: '#1D2939',
                formatter: (val) => val.toFixed(1) + '%'
              },
            },
          },
        },
        fill: { type: 'solid' },
        stroke: { lineCap: 'round' }
      }
    };
  },
  unmounted() {
    if (this.pollingInterval) clearInterval(this.pollingInterval);
  },
  methods: {
    formatCurrency(value) {
      return value.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
    async actualizarSilenciosamente() {
      try {
        const response = await API.get(`${this.baseUrl}/dashboard/monthly-target`);
        this.stats = response.data;
      } catch (error) {
        console.warn("Error en actualización silenciosa", error);
      }
    },
    async fetchTargetData() {
      this.loading = true;
      try {
        const response = await API.get(`${this.baseUrl}/dashboard/monthly-target`);
        this.stats = response.data;
      } catch (error) {
        console.error("Error cargando meta mensual:", error);
      } finally {
        this.loading = false;
      }
    }
  },
  mounted() {
    this.pollingInterval = setInterval(() => {
      this.actualizarSilenciosamente();
    }, 10000);
    this.fetchTargetData();
  }
};
</script>

<style scoped>
.radial-bar-chart {
  width: 100%;
  max-width: 330px;
  margin: 0 auto;
}
</style>