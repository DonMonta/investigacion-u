//Funcion para el login del usuario
import axios from "axios";
import store from "@/store";
export async function enviarsolilogin(method, parametros, url, mensaje) {
  try {
    const response = await axios({
      method: method,
      url: url,
      data: parametros,
    });
  //console.log(response);

    if (response.data && response.data.token) {
      if (response.data.error) {
        return {
          error: response.data.error,
          clave: response.data.clave,
          mensaje: response.data.mensaje,
        };
      }
      else if(response.data.Role === "sotics" || response.data.Role === "atics" || response.data.Role === "sa" || response.data.Role === "ainv") {
        store.commit("setRol_inves", response.data.Role);
        store.commit("setemail_inves", response.data.email);
        //store.commit("setid_inves", response.data.id);

        store.commit("setname_inves", response.data.name);
        store.commit("setToken_inves", response.data.token);
        store.commit("setTokenType_inves", response.data.token_type || "Bearer");
        return {
          token: response.data.token,
          Rol: response.data.Role,
          //id: response.data.id,
          name: response.data.name,
          email: response.data.email,
        };
      }
    } else {
      console.error("Respuesta inesperada:", response);
      return null;
    }
  } catch (error) {
    console.error("Error:", error.response.data);
    throw error;
  }
}
