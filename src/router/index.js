import { createRouter, createWebHistory } from "vue-router";
import DashUiView from "../views/DashUiView.vue";
import PeiAllView from "../views/Admin/Mantenimiento/Op_Pei/PeiAllView.vue";
import PlandneAllView from "../views/Admin/Mantenimiento/Op_PLANDE/PlandneAllView.vue";
import ProyectosView from "../views/Admin/Proyectos/Lista_Proyectos/ProyectosView.vue";
import Agenda_ODSAllView from "../views/Admin/Mantenimiento/Op_Agenda_ODS/Agenda_ODSAllView.vue";
import DominioAcademicoAllView from "../views/Admin/Mantenimiento/Op_dominio_Aca/Dom_AcademicoAllView.vue";
import LinInvestigaAllView from "../views/Admin/Mantenimiento/Op_Lin_Inves/Lin_InvestigaAllView.vue";
import SubAreaUnescoAllView from "../views/Admin/Mantenimiento/Op_SubArea/SubAreaUnescoAllView.vue";
import ZonaPlanificacionAllView from "../views/Admin/Mantenimiento/Op_ZonaPlan/Zona_PlanificacionAllView.vue";
import EmpresaAllView from "../views/Admin/Mantenimiento/Op_Empresa/EmpresaAllView.vue";
import ConvocatoriaView from "../views/Admin/Proyectos/Convocatoria/ConvocatoriaView.vue";
import TipoProyectoView from "../views/Admin/Proyectos/Tipos_Proyect_Inv/Tipo_Proyect_InvView.vue";
import RolesEquipoAllView from "../views/Admin/Equipo/RolesEquipoAllView.vue";
import EquipoAllView from "../views/Admin/Equipo/EquipoAllView.vue";
import ResponsableInvAllView from '../views/Admin/Equipo/ResponsableInvAllView.vue'
import LoginView from "../views/LoginView.vue";

const routes = [
  {
    path: "/site-login",
    name: "login",
    component: LoginView,
  },
  {
    path: "/site-admin",
    name: "site-admin",
    component: DashUiView,
  },
  {
    path: "/site-admin/pei",
    name: "site-admin-pei",
    component: PeiAllView,
  },
  {
    path: "/site-admin/plandne",
    name: "site-admin-plandne",
    component: PlandneAllView,
  },
  {
    path: "/site-admin/agenda_ods",
    name: "site-admin-agenda_ods",
    component: Agenda_ODSAllView,
  },
  {
    path: "/site-admin/dominio_academico",
    name: "site-admin-dominio_academico",
    component: DominioAcademicoAllView,
  },
  {
    path: "/site-admin/linea_investigacion",
    name: "site-admin-linea_investigacion",
    component: LinInvestigaAllView,
  },
  {
    path: "/site-admin/subarea_unesco",
    name: "site-admin-subarea_unesco",
    component: SubAreaUnescoAllView,
  },
  {
    path: "/site-admin/zona_planificacion",
    name: "site-admin-zona_planificacion",
    component: ZonaPlanificacionAllView,
  },
  {
    path: "/site-admin/empresa",
    name: "site-admin-empresa",
    component: EmpresaAllView,
  },
  {
    path: "/site-admin/proyectos/convocatoria",
    name: "site-admin-proyectos-convocatoria",
    component: ConvocatoriaView,
  },
  {
    path: "/site-admin/proyectos/tipo_proyecto_investigacion",
    name: "site-admin-proyectos-tipo_proyecto_investigacion",
    component: TipoProyectoView,
  },
  {
    path: "/site-admin/roles_equipo",
    name: "site-admin-roles_equipo",
    component: RolesEquipoAllView,
  },
  {
    path: "/site-admin/equipo",
    name: "site-admin-equipo",
    component: EquipoAllView,
  },
  {
    path: "/site-admin/responsables_inv",
    name: "site-admin-responsables_inv",
    component: ResponsableInvAllView,
  },
  {
    path: "/site-admin/proyectos",
    name: "site-admin-proyectos",
    component: ProyectosView,
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
