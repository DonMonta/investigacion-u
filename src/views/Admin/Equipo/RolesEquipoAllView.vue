<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />
    <div class="space-y-5 sm:space-y-6">

      <RolesEquipo_Inv />

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
//import ComponentCard from "@/components/common/ComponentCard.vue";
import RolesEquipo_Inv from "@/components/admin/mantenimiento/equipo_inv/RolesEquipo_Inv.vue";
const currentPageTitle = ref("Roles del Equipo de Investigación");
</script>
