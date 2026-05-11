import { computed } from "vue"
import store from "@/store"

export function useUsuario() {
  const rolUsuario = computed(() => store.state.role)
  const emailUsuario = computed(() => store.state.email)
  const idUsuario = computed(() => store.state.idusu)
  const nombreUsuario = computed(() => store.state.name)

  return {
    rolUsuario,
    emailUsuario,
    idUsuario,
    nombreUsuario, 
  }
}