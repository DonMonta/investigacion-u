<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />
    <div class="space-y-5 sm:space-y-6">

      <LineaInvestigacion />

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
//import ComponentCard from "@/components/common/ComponentCard.vue";
import LineaInvestigacion from "@/components/admin/mantenimiento/linea_investigacion/LineaInvestigacion.vue";
const currentPageTitle = ref("LÍNEAS DE INVESTIGACIÓN");
</script>
