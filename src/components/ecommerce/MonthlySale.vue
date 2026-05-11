<template>
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ventas Mensuales</h3>
        <p class="text-sm text-gray-500">Año seleccionado: {{ selectedYear }}</p>
      </div>

      <div class="relative h-fit">
        <DropdownMenu :menu-items="yearMenuItems">
          <template #icon>
            <div class="flex items-center gap-2 cursor-pointer bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
              <span class="text-sm font-medium text-gray-700 dark:text-white">{{ selectedYear }}</span>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
          </template>
        </DropdownMenu>
      </div>
    </div>

    <div class="max-w-full overflow-x-auto custom-scrollbar">
      <div id="chartOne" class="-ml-5 min-w-[650px] xl:min-w-full pl-2">
        <VueApexCharts 
          v-if="!loading"
          type="bar" 
          height="180" 
          :options="chartOptions" 
          :series="series" 
        />
        <div v-else class="h-[180px] flex items-center justify-center">
          <span class="text-gray-400 animate-pulse">Cargando estadísticas...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from "@/assets/js/services/axios";
import VueApexCharts from 'vue3-apexcharts';
import DropdownMenu from '../common/DropdownMenu.vue';

export default {
  name: 'MonthlySalesChart',
  components: {
    VueApexCharts,
    DropdownMenu
  },
  data() {
    return {
      loading: true,
      baseUrl: "/restrik",
      pollingInterval: null,
      selectedYear: new Date().getFullYear(),
      availableYears: [],
      series: [
        {
          name: 'Ventas',
          data: []
        }
      ],
      chartOptions: {
        colors: ['#F5B30F'],
        chart: {
          fontFamily: 'Outfit, sans-serif',
          type: 'bar',
          toolbar: { show: false },
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '35%',
            borderRadius: 5,
            borderRadiusApplication: 'end',
          },
        },
        dataLabels: { enabled: false },
        xaxis: {
          categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
          axisBorder: { show: false },
          axisTicks: { show: false },
        },
        yaxis: {
          labels: {
            formatter: (val) => `$${val.toFixed(0)}`
          }
        },
        tooltip: {
          y: {
            formatter: (val) => `$${val.toFixed(2)}`
          }
        }
      }
    };
  },
  unmounted() {
    if (this.pollingInterval) clearInterval(this.pollingInterval);
  },
  computed: {
    // Transformamos la lista de años en el formato que espera tu DropdownMenu
    yearMenuItems() {
      return this.availableYears.map(year => ({
        label: year.toString(),
        onClick: () => this.fetchSalesData(year)
      }));
    }
  },
  methods: {
    async actualizarSilenciosamente() {
      try {
        const response = await API.get(`${this.baseUrl}/dashboard/monthly-sales?year=${this.selectedYear}`);
        this.series[0].data = response.data.sales_data;
        this.availableYears = response.data.years;
        this.selectedYear = response.data.selected_year;
      } catch (error) {
        console.warn("Error en actualización silenciosa", error);
      }
    },
    async fetchSalesData(year) {
      this.loading = true;
      try {
        const response = await API.get(`${this.baseUrl}/dashboard/monthly-sales?year=${year}`);
        
        // Actualizamos los datos del gráfico
        this.series[0].data = response.data.sales_data;
        this.availableYears = response.data.years;
        this.selectedYear = response.data.selected_year;
      } catch (error) {
        console.error("Error al obtener datos de ventas:", error);
      } finally {
        this.loading = false;
      }
    }
  },
  mounted() {
    // Carga inicial de datos
    this.pollingInterval = setInterval(() => {
      this.actualizarSilenciosamente();
    }, 10000);
    this.fetchSalesData(this.selectedYear);
  }
};
</script>