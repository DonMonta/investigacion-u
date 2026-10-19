import { computed } from "vue"
import store from "@/store"

export function useUsuario() {
  const rolUsuario = computed(() => store.state.role)
  const emailUsuario = computed(() => store.state.email)
  const idUsuario = computed(() => store.state.idusu)
  const nombreUsuario = computed(() => store.state.name)
  const carreraUsuario = computed(() => store.state.carrera)
  const nivelUsuario = computed(() => store.state.nivel)
  const idcarreraUsuario = computed(() => store.state.idcarrera)
  const facultadUsuario = computed(() => store.state.facultad)
  const departamentoUsuario = computed(() => store.state.departamento)

  return {
    rolUsuario,
    emailUsuario,
    idUsuario,
    nombreUsuario, 
    carreraUsuario,
    nivelUsuario,
    idcarreraUsuario,
    facultadUsuario,
    departamentoUsuario
  }
}