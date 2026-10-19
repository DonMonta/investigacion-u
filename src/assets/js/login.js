import { mostraralertas } from "@/assets/js/function/funciones";
import { enviarsolilogin } from "@/assets/js/function/login_function";
import store from "@/store";
import { getMe } from '@/store/auth';
export default {
  data() {
    return {
      email: "",
      password: "",
      isLoggingIn: false,
      url2: `${__API_INVESTIGACION__}/inves/login`,
    };
  },
  methods: {
    async login() {
      if (this.isLoggingIn) return;
      try {
        this.isLoggingIn = true;
        var parametros = {
          LoginUsu: this.email.trim(),
          ClaveUsu: this.password.trim(),
        };
        console.log('Enviando solicitud de login con parámetros:', parametros);
        const response = await enviarsolilogin('POST', parametros, this.url2, 'Logueado');
        console.log('Respuesta del login:', response);
        if (response.error) {
          mostraralertas(response.mensaje, 'warning');
          this.isLoggingIn = false;
        } else if (response) {
          const role = response.Rol;
          const tok = response.token;
          if (role === 'sotics' || role === 'atics' || role === 'sa' || role === 'ainv') {
            mostraralertas('LE DAMOS LA BIENVENIDA ADMIN ' + (response.name || ''), 'success');
            this.$router.push('/site-admin');
          } 
          else if (role === 'D' || role === 'A' || role === 'TDO' || role === 'T') {
            mostraralertas('LE DAMOS LA BIENVENIDA ' + (response.name || ''), 'success');
            this.$router.push('/site-admin/docperfil');
          }
          else if (role === 'est'){
            mostraralertas('LE DAMOS LA BIENVENIDA ESTUDIANTE ' + (response.name || ''), 'success');
            this.$router.push('/site-admin/perfil');
          }
        }
      } catch (error) {
        this.isLoggingIn = false;
        console.error("Error en login:", error);
        if (error.response?.data?.mensaje) {
          mostraralertas(error.response.data.mensaje, 'warning');
        } else {
          mostraralertas('No se pudo conectar con el servidor o error inesperado.', 'error');
        }
      }
    },
  },
};
