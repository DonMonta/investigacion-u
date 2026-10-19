<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />
    <div class="space-y-5 sm:space-y-6">

      <Zona_Planificacion />

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
//import ComponentCard from "@/components/common/ComponentCard.vue";
import Zona_Planificacion from "@/components/admin/mantenimiento/zona_planificacion/Zona_planificacion.vue";
const currentPageTitle = ref("Zonas de Planificación");
</script>
