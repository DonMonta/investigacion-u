<template>
  <slot></slot>
</template>

<script setup>
import { useSidebarProvider } from '@/composables/useSidebar'

// Inicializa el estado del sidebar para los componentes descendientes
useSidebarProvider()
</script>