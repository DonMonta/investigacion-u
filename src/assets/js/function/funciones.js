import Swal from 'sweetalert2';
import API from "@/assets/js/services/axios";

export function mostraralertas(titulo, icono, foco = '') {
    if (foco != '') {
        document.getElementById(foco).focus();
    }
    Swal.fire({
        title: titulo,
        icon: icono,
        customClass: { confirmButton: 'btn btn-secondary', popup: 'animated zoonIn' },
        buttonsStyling: false
    });
}
export function mostraralertas2(titulo, icono) {

    Swal.fire({
        title: titulo,
        icon: icono,
        customClass: { confirmButton: 'btn btn-secondary', popup: 'animated zoonIn' },
        buttonsStyling: false
    });
}

export function confimar(urlconslash, id, titulo, mensaje, actualizarTabla) {
    var url = urlconslash + id;   // 👈 Se construye la URL con el ID

    const swalwithboostrapbutton = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success me-3',
            cancelButton: 'btn btn-danger'
        },
    });

    return swalwithboostrapbutton.fire({
        title: titulo,
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Inhabilitar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((res) => {
        if (res.isConfirmed) {
            return API.delete(url)   //  Ya NO mandamos { data: { id } }
                .then((response) => {
                    mostraralertas(response.data.mensaje ?? 'Habilitado con éxito', 'success');
                    if (typeof actualizarTabla === "function") {
                        actualizarTabla(); // 🔄 refrescar tabla
                    }
                    return response.data;
                })
                .catch(() => {
                    mostraralertas('Error al eliminar', 'error');
                    throw new Error('Error al eliminar');
                });
        } else {
            mostraralertas('Operación cancelada', 'info');
            return null;
        }
    });
}
export function qrconfimar(metodo, url, parametros, titulo, mensaje, actualizarTabla) {
    // 👈 Se construye la URL con el ID

    const swalwithboostrapbutton = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success me-3',
            cancelButton: 'btn btn-danger'
        },
    });

    return swalwithboostrapbutton.fire({
        title: titulo,
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Generar QR',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((res) => {
        if (res.isConfirmed) {
            return API({
                method: metodo,
                url: url,
                data: parametros
            })   //  Ya NO mandamos { data: { id } }
                .then((response) => {
                    mostraralertas(response.data.mensaje ?? 'QR generado con éxito', 'success');
                    if (typeof actualizarTabla === "function") {
                        actualizarTabla(); // 🔄 refrescar tabla
                    }
                    return response.data;
                })
                .catch(() => {
                    mostraralertas('Error al generar QR', 'error');
                });
        } else {
            mostraralertas('Operación cancelada', 'info');
            return null;
        }
    });
}
export function eliminacion(urlconslash, id, titulo, mensaje, actualizarTabla) {
    var url = urlconslash + id;   // 👈 Se construye la URL con el ID

    const swalwithboostrapbutton = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success me-3',
            cancelButton: 'btn btn-danger'
        },
    });

    return swalwithboostrapbutton.fire({
        title: titulo,
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((res) => {
        if (res.isConfirmed) {
            return API.delete(url)   // 👈 Ya NO mandamos { data: { id } }
                .then((response) => {
                    mostraralertas(response.data.mensaje ?? 'Eliminado con éxito', 'success');
                    if (typeof actualizarTabla === "function") {
                        actualizarTabla(); // 🔄 refrescar tabla
                    }
                    return response.data;
                })
                .catch(() => {
                    mostraralertas('Error al eliminar', 'error');
                    throw new Error('Error al eliminar');
                });
        } else {
            mostraralertas('Operación cancelada', 'info');
            return null;
        }
    });
}
export function confimarhabi(urlconslash, id, titulo, mensaje, actualizarTabla) {
    var url = urlconslash + id;

    const swalwithboostrapbutton = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success me-3',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false // Asegura que use tus clases de bootstrap
    });

    return swalwithboostrapbutton.fire({
        title: titulo,
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Habilitar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((res) => {
        if (res.isConfirmed) {
            // Usamos API.delete porque así lo tienes en tu Route::delete
            return API.delete(url)
                .then((response) => {
                    mostraralertas(response.data.mensaje ?? 'Habilitado con éxito', 'success');
                    if (typeof actualizarTabla === "function") {
                        actualizarTabla();
                    }
                    return response.data;
                })
                .catch((error) => {
                    // --- CAPTURA DEL ERROR DE VALIDACIÓN (PEI YA ACTIVO) ---
                    let mensajeError = 'Error al habilitar';

                    if (error.response && error.response.data && error.response.data.mensaje) {
                        // Si el backend envió un mensaje específico (como el del activo existente)
                        mensajeError = error.response.data.mensaje;
                    }

                    mostraralertas(mensajeError, 'error');
                    console.error("Error en habilitar:", error);
                    // No es estrictamente necesario lanzar el error aquí si ya lo manejas con la alerta
                });
        } else {
            // Opcional: puedes quitar esta alerta si crees que molesta al usuario
            mostraralertas('Operación cancelada', 'info');
            return null;
        }
    });
}
export function confimar2(urlconslash, id, titulo, mensaje) {
    var url = urlconslash + id;
    const swalwithboostrapbutton = Swal.mixin({
        customClass: { confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-danger' },
    });
    swalwithboostrapbutton.fire({
        title: titulo,
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((res) => {
        if (res.isConfirmed) {
            enviarsolig('DELETE', { id: id }, url, 'Eliminado con éxito');
        } else {
            mostraralertas('Operacion cancelada', 'info');
        }
    });

}
export function enviarsoli(metodo, parametros, url, mensaje) {
    API({
        method: metodo,
        url: url,
        data: parametros
    }).then(function (res) {
        var estado = res.status;
        if (estado == 200) {
            mostraralertas(mensaje, 'success');
            document.getElementById('IniciarSesion').scrollIntoView({ behavior: 'smooth' });
            /*window.setTimeout(function(){
                window.location.href='/'
            },2000);*/
        } else {
            mostraralertas('No se pudo recuperar la respuesta', 'error');
        }
    }).catch(function (error) {
        mostraralertas('Error Al registrar', 'error');
    });
}
export function enviarsolig(metodo, parametros, url, mensaje) {
    return API({
        method: metodo,
        url: url,
        data: parametros
    }).then(function (res) {
        // Laravel devuelve 200 o 201 para éxito
        if (res.status === 200 || res.status === 201) {
            mostraralertas(mensaje, 'success');
            return true; 
        } else {
            mostraralertas('No se pudo procesar la solicitud', 'error');
            return false;
        }
    }).catch(function (error) {
        // Si el servidor responde con un error (409, 404, 500, etc.)
        if (error.response) {
            if (error.response.status === 409) {
                // Aquí se muestra el mensaje: "El código OE1 ya está registrado..."
                mostraralertas(error.response.data.mensaje, 'warning');
            } else if (error.response.status === 422) {
                // Errores de validación de Laravel
                mostraralertas("Datos inválidos o faltantes", 'warning');
            } else {
                mostraralertas('Error interno del servidor', 'error');
            }
        } else {
            // Error de red o servidor apagado
            mostraralertas('Servidor no disponible', 'error');
        }
        return false; // Retornamos false para que el componente no limpie el form
    });
}
export function enviarsoligtiempo(metodo, parametros, url) {
    return API({
        method: metodo,
        url: url,
        data: parametros
    }).then(function (res) {
        if (res.status == 200) {
            return true; // Retornamos éxito
        } else {
            mostraralertas('No se pudo recuperar la respuesta', 'error');
            return false;
        }
    }).catch(function (error) {
        if (error.response.status == 409) {
            mostraralertas(error.response.data.mensaje, 'warning');

        } else {
            mostraralertas('Servidor no Disponible', 'error');
        }
        return false;
    });
}
export function enviaractualizacionpedido(metodo, parametros, url) {
    return API({
        method: metodo,
        url: url,
        data: parametros
    }).then(function (res) {
        if (res.status == 200) {
            return true; // Retornamos éxito
        } else {
            mostraralertas('No se pudo recuperar la respuesta', 'error');
            return false;
        }
    }).catch(function (error) {
        if (error.response.status == 409) {
            mostraralertas(error.response.data.mensaje, 'warning');

        } else {
            mostraralertas('Servidor no Disponible', 'error');
        }
        return false;
    });
}
export function enviarsoligfoot(metodo, parametros, url, mensaje) {
    return API({
        method: metodo,
        url: url,
        data: parametros
    }).then(function (res) {
        var estado = res.status;
        if (estado == 200) {
            mostraralertas(mensaje, 'success');
            return res;
        } else {
            mostraralertas('No se pudo recuperar la respuesta', 'error');

        }
    }).catch(function (error) {
        console.log(error);
        mostraralertas('Servidor no Disponible', 'error');
    });
}
export function enviarsoligqr(metodo, parametros, url) {
    return API({
        method: metodo,
        url: url,
        data: parametros
    }).then(function (res) {
        var estado = res.status;
        if (estado == 200) {
            return res;
        } else {
            console.log('No se pudo recuperar la respuesta', 'error');

        }
    }).catch(function (error) {
        console.log(error);
    });
}
export async function enviarsoliedit(metodo, parametros, url, mensaje) {
    try {
        var response = await API({
            method: metodo,
            url: url,
            data: parametros
        });


        if (response.data) {
            //console.log(mensaje + ': ' + response.data.mensaje);
            mostraralertas(mensaje, 'success');



        } else {
            mostraralertas('No se pudo recuperar la respuesta', 'error');
            return null;
        }
    } catch (error) {
        console.error('Error:', error.response.data);
        mostraralertas('Servidor no Disponible', 'error');
        throw error;
    }
}
export function elimnarpermanente(urlconslash,id,titulo,mensaje){
    var url = urlconslash+id;
    const swalwithboostrapbutton = Swal.mixin({
        customClass:{confirmButton:'btn btn-success me-3',cancelButton:'btn btn-danger'},
    });
    return swalwithboostrapbutton.fire({
        title:titulo,
        text:mensaje,
        icon:'question',
        showCancelButton:true,
        confirmButtonText:'<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText:'<i class="fa-solid fa-ban"></i> Cancelar'}).then((res)=>{
        if(res.isConfirmed){
            return solicitud('DELETE',{id:id},url,'Eliminado con exito').then(response => {
                return response; 
            });
        }else{
            mostraralertas('Operacion cancelada','info');
            return null;
        }
    });
   
}
export function solicitud(metodo,parametros,url,mensaje){
    return API({
        method:metodo,
        url:url,
        data:parametros
    }).then(function(res){
        var estado = res.status;
        if(estado==200){
            mostraralertas(mensaje,'success');
            return res;   
        }else{
            mostraralertas('No se pudo recuperar la respuesta','error');

        }
    }).catch(function(error){
        if(error.response.status===409){
            mostraralertas(error.response.data.mensaje,'warning');
            
        }else{
            mostraralertas('Servidor no Disponible', 'error');
        } 
    });
}