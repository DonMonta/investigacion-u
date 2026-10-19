import { createStore } from 'vuex'

export default createStore({
  state: {
    role: localStorage.getItem('Rol_inves') || null,
    email: localStorage.getItem('email_inves') || null,
    idusu: localStorage.getItem('id_inves') || null,
    name: localStorage.getItem('name_inves') || null,
    carrera: localStorage.getItem('carrera_inves') || null,
    nivel: localStorage.getItem('nivel_inves') || null,
    departamento: localStorage.getItem('departamento_inves') || null,
    idcarrera: localStorage.getItem('idcarrera_inves') || null,
    facultad: localStorage.getItem('facultad_inves') || null,
    token: localStorage.getItem('token_inves') || null,
    token_type: localStorage.getItem('token_type_inves') || null,
    proyectos_ids: JSON.parse(localStorage.getItem('proyectos_ids_inves') || '[]'),
    proyectos_detalles: JSON.parse(localStorage.getItem('proyectos_detalles_inves') || '[]'),
  },
  getters: {
    getIdusu: state => state.idusu,
    getEmail: state => state.email,
    getName: state => state.name,
    getCarrera: state => state.carrera,
    getNivel: state => state.nivel,
    getIdcarrera: state => state.idcarrera,
    getFacultad: state => state.facultad,
    getRole: state => state.role,
    getDepartamento: state => state.departamento,
    isAuthenticated: state => !!state.token,
    getFullToken: state => `${state.token_type} ${state.token}`,
    getProyectosIds: state => state.proyectos_ids,
    getProyectosDetalles: state => state.proyectos_detalles,
  },
  mutations: {
    setRol_inves(state, nuevoRol) {
      state.role = nuevoRol;
      localStorage.setItem('Rol_inves', nuevoRol);
    },
    setemail_inves(state, nuevoemail) {
      state.email = nuevoemail;
      localStorage.setItem('email_inves', nuevoemail);
    },
    setid_inves(state, nuevoid) {
      state.idusu = nuevoid;
      localStorage.setItem('id_inves', nuevoid);
    },
    setname_inves(state, nuevoname) {
      state.name = nuevoname;
      localStorage.setItem('name_inves', nuevoname);
    },
    setToken_inves(state, token) {
      state.token = token;
      localStorage.setItem('token_inves', token);
    },
    setTokenType_inves(state, type) {
      state.token_type = type;
      localStorage.setItem('token_type_inves', type);
    },
    setCarrera_inves(state, carrera) {
      state.carrera = carrera;
      localStorage.setItem('carrera_inves', carrera);
    },
    setNivel_inves(state, nivel) {
      state.nivel = nivel;
      localStorage.setItem('nivel_inves', nivel);
    },
    setIdcarrera_inves(state, idcarrera) {
      state.idcarrera = idcarrera;
      localStorage.setItem('idcarrera_inves', idcarrera);
    },
    setFacultad_inves(state, facultad) {
      state.facultad = facultad;
      localStorage.setItem('facultad_inves', facultad);
    },
    setProyectosIds_inves(state, ids) {
      state.proyectos_ids = ids || [];
      localStorage.setItem('proyectos_ids_inves', JSON.stringify(ids || []));
    },
    setProyectosDetalles_inves(state, detalles) {
      state.proyectos_detalles = detalles || [];
      localStorage.setItem('proyectos_detalles_inves', JSON.stringify(detalles || []));
    },
    setDepartamento_inves(state, departamento) {
      state.departamento = departamento;
      localStorage.setItem('departamento_inves', departamento);
    },
    logout_inves(state) {
      // Limpia el state y localStorage al cerrar sesión
      state.role = null;
      state.email = null;
      state.idusu = null;
      state.name = null;
      state.token = null;
      state.token_type = null;
      state.proyectos_ids = [];
      state.proyectos_detalles = [];
      localStorage.removeItem('Rol_inves');
      localStorage.removeItem('email_inves');
      localStorage.removeItem('id_inves');
      localStorage.removeItem('name_inves');
      localStorage.removeItem('token_inves');
      localStorage.removeItem('token_type_inves');
      localStorage.removeItem('user_inves');
      localStorage.removeItem('carrera_inves');
      localStorage.removeItem('nivel_inves');
      localStorage.removeItem('idcarrera_inves');
      localStorage.removeItem('facultad_inves');
      localStorage.removeItem('proyectos_ids_inves');
      localStorage.removeItem('proyectos_detalles_inves');
      localStorage.removeItem('departamento_inves');
    },
  },
  actions: {},
  modules: {}
})
