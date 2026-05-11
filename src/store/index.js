import { createStore } from 'vuex'

export default createStore({
  state: {
    role: localStorage.getItem('Rol_inves') || null,
    email: localStorage.getItem('email_inves') || null,
    idusu: localStorage.getItem('id_inves') || null,
    name: localStorage.getItem('name_inves') || null,
    token: localStorage.getItem('token_inves') || null,
    token_type: localStorage.getItem('token_type_inves') || null,
  },
  getters: {
    getIdusu: state => state.idusu,
    isAuthenticated: state => !!state.token,
    getFullToken: state => `${state.token_type} ${state.token}`,
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
    logout_inves(state) {
      // Limpia el state y localStorage al cerrar sesión
      state.role = null;
      state.email = null;
      state.idusu = null;
      state.name = null;
      state.token = null;
      state.token_type = null;

      localStorage.removeItem('Rol_inves');
      localStorage.removeItem('email_inves');
      localStorage.removeItem('id_inves');
      localStorage.removeItem('name_inves');
      localStorage.removeItem('token_inves');
      localStorage.removeItem('token_type_inves');
      localStorage.removeItem('user_inves');
    },
  },
  actions: {},
  modules: {}
})
