import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import '@fortawesome/fontawesome-free/css/all.min.css'
import VueApexCharts from 'vue3-apexcharts'
import './assets/styles/admin/main.css' 

createApp(App).use(store).use(router).use(VueApexCharts).mount('#app')
