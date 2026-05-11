<template>
  <div class="min-h-screen xl:flex">
    <app-sidebar />
    <Backdrop />
    <div class="flex-1 transition-all duration-300 ease-in-out"
      :class="[isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]']">
      <app-header />
      <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <slot></slot>
      </div>
      <!-- Footer -->
      <footer class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6 pt-0">
        <p class="text-sm text-center text-gray-500 dark:text-gray-400">
          Designed and Developed by
          <a rel="noopener noreferrer"
            class="text-brand-500 hover:text-brand-600 transition-colors duration-200 font-medium">
            DonMonta
          </a>


        </p>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { useSidebar } from '@/composables/useSidebar'
import AppHeader from './AppHeader.vue'
import AppSidebar from './AppSidebar.vue'
import Backdrop from './Backdrop.vue'

// En JS no necesitas definir tipos para la desestructuración
const { isExpanded, isHovered } = useSidebar()
</script>
<script>
import { getMe } from '@/store/auth';
export default {
  async mounted() {
    await getMe();
    //const usuario = await getMe();
  },
}
</script>