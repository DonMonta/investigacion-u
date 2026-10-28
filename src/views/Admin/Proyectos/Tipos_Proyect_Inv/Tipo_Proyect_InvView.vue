<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />
    <div class="space-y-5 sm:space-y-6">

      <Tipo_Proyect_Vinc />

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
//import ComponentCard from "@/components/common/ComponentCard.vue";
import Tipo_Proyect_Vinc from "@/components/admin/proyectos/Tipos_Proyect_Inv/Tip_proyect_inv.vue";
const currentPageTitle = ref("TIPOS DE PROYECTOS DE VINCULACIÓN");
</script>
