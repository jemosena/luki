//Funcion para capturar y enviar la informacion del forumlario de inicio de sesion
const iniciar_sesion = async () => {
    //objeto que contiene toda la informacion del formulario de inicio de sesion
    const obj = {
      email: document.querySelector("#emailLogin").value,
      contraseña: document.querySelector("#passLogin").value
    };
  
    console.log("formDataaaa: ", obj);
    let res = null;
  
    await axios.post('../backend/acciones.php', {"accion": "iniciar_sesion", "data": obj}).
      then(function(response){
        res = response.data;
      }).catch(function(error){
        console.log("error: ", error);
      });

    console.log("res: ", res);
  
    if(res.status){
      //ingresar al index
      alert(res.msg);
      location.href = 'http://localhost/luki/coord/coord.html';
    }else{
      alert(res.msg);
    }
  
  }