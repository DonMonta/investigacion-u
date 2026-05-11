<template>
  <div class="relative" ref="dropdownRef">
    <button
      class="flex items-center text-gray-700 dark:text-gray-400"
      @click.prevent="toggleDropdown"
    >
      <span class="mr-3 overflow-hidden rounded-full h-11 w-11">
        <img src="@/assets/img/logo/logo-icon.svg" alt="User" />
      </span>

      <span class="block mr-1 font-medium text-theme-sm">{{nombreUsuario}} </span>

      <ChevronDownIcon :class="{ 'rotate-180': dropdownOpen }" />
    </button>

    <!-- Dropdown Start -->
    <div
      v-if="dropdownOpen"
      class="absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
    >
      <div>
        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
          {{nombreUsuario}}
        </span>
        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">
          {{emailUsuario}}
        </span>
      </div>

      
      <button
        @click="cerrarsesion"
        class="flex items-center gap-3 px-3 py-2 mt-3 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
      >
        <LogoutIcon
          class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
        />
        Sign out
      </button>
    </div>
    <!-- Dropdown End -->
  </div>
</template>

<script setup>
import { ChevronDownIcon, LogoutIcon } from '@/icons'
import { onMounted, onUnmounted, ref } from 'vue'
import { useUsuario } from "@/composables/useUsuario"
import API from "@/assets/js/services/axios"

// Estado
const dropdownOpen = ref(false)
const dropdownRef = ref(null)

const {
  nombreUsuario,
  emailUsuario
} = useUsuario()

// Lógica del Dropdown
const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

// Lógica de Sesión
const cerrarsesion = async () => {
  try {
    const token = localStorage.getItem("token_vinc")

    if (!token) {
      console.warn("⚠️ No hay token, cerrando sesión localmente...")
      localStorage.clear()
      window.location.href = "/site/site-login"
      return
    }

    const response = await API.get(
      "/vin/logout",
      {},
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    )

    console.log("✅ Sesión cerrada:", response.data)
    localStorage.clear()
    window.location.href = "/site/site-login"
  } catch (error) {
    console.error("❌ Error al cerrar sesión:", error.response?.data || error)
    localStorage.clear()
    window.location.href = "/site/site-login"
  }
}

// Ciclo de vida
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

