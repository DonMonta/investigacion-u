import { createRouter, createWebHistory } from 'vue-router'
import DashUiView from '../views/DashUiView.vue'
import PeiAllView from '../views/Admin/Mantenimiento/Op_Pei/PeiAllView.vue'
import PlandneAllView from '../views/Admin/Mantenimiento/Op_PLANDE/PlandneAllView.vue'
import ProyectosView from '../views/Admin/Proyectos/Lista_Proyectos/ProyectosView.vue'
import LoginView from '../views/LoginView.vue'

const routes = [
  {
    path: '/site-login',
    name: 'login',
    component: LoginView
  },
  {
    path: '/site-admin',
    name: 'site-admin',
    component: DashUiView
  },
  {
    path: '/site-admin/pei',
    name: 'site-admin-pei',
    component: PeiAllView
  },
  {
    path: '/site-admin/plandne',
    name: 'site-admin-plandne',
    component: PlandneAllView
  },
  {
    path: '/site-admin/proyectos',
    name: 'site-admin-proyectos',
    component: ProyectosView
  },
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
