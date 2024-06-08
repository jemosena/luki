<?php
//incluimos el arcivo que contiene la conexion a la base de datos
include 'conexion.php';
//recibimos los parametros que le estan llegando al archivo por medio de las peticiones post del JS
$post = json_decode(file_get_contents('php://input'));
//si la accion a realizar desde el frontend es listar, llamamos la funcion listar asesoras
if($post->accion == "listar")
{
    //instanciamos la clase
    $asesoras = new Usuarios();
    echo $asesoras->listar();
    return;
}

//si la accion a realizar desde el frontend es crear, llamamos la funcion crear asesora
if($post->accion == "crear")
{
    //instanciamos la clase
    $asesoras = new Usuarios();
    echo $asesoras->crear($post->data);
    return;
}

//si la accion a realizar desde el frontend es consultar, llamamos la funcion consultar asesora
if($post->accion == "consultar")
{
    //instanciamos la clase
    $asesoras = new Usuarios();
    echo $asesoras->consultar($post->idAsesora);
    return;
}

//si la accion a realizar desde el frontend es actualizar, llamamos la funcion actualizar asesora
if($post->accion == "actualizar")
{
    //instanciamos la clase
    $asesoras = new Usuarios();
    echo $asesoras->actualizar($post->data);
    return;
}

//si la accion a realizar desde el frontend es eliminar, llamamos la funcion eliminar asesora
if($post->accion == "eliminar")
{
    //instanciamos la clase
    $asesoras = new Usuarios();
    echo $asesoras->eliminar($post->idAsesora);
    return;
}

//si la accion a realizar desde el frontend es iniciar sesion, llamamos la funcion iniciar sesion
if($post->accion == "iniciar_sesion")
{
    //instanciamos la clase
    $asesoras = new Usuarios();
    echo $asesoras->iniciar_sesion($post->data);
    return;
}

//creamos una clase para controlar todas las acciones que se haran
class Usuarios{
    //creamos una variable global para validar el estado de la coneccion
    protected $conn = false;

    public function __construct() {
        //instanciamos la clase de la coneccion y la obtenemos
        $connectionClass = new Connection();
        $this->conn = $connectionClass->getConnection();
    }

    //funcion para traer todas los usuarios en la base de datos
    public function listar()
    {
        $data = [];
        //Creamos una variable llamada "sql" donde guardamos la instruccion que se tiene que ejecutar en la base de datos
        $sql = "SELECT id_asesora, numero_cedula, nombres, apellidos, direccion, barrio, comuna, ciudad FROM asesoras WHERE estado = 1";
        //Enviamos por medio de la funcion "mysqli_query" la instruccion que se debe ejecutar en la BD y la conexion a la misma
        $result = @mysqli_query($this->conn, $sql);
        //Si la consulta a la base de datos falla saldra un error y no continua con el proceso de pintado de la info en la tabla
        if(!$result){
          return 'error consultado el listado de las asesoras';
        }
        //si la cantidad de asesoras que se encontraron es mayor a cero alimentamos la variable data para devolverlas
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc())
            {
                //enviamos un array por cada asesora encontrada al array de asesoras que se va a retornar
                array_push($data, [
                    "id_asesora" => $row['id_asesora'],
                    'numero_cedula' => $row['numero_cedula'],
                    'nombres' => $row['nombres'],
                    'apellidos' => $row['apellidos'],
                    'direccion' => $row['direccion'],
                    'barrio' => $row['barrio'],
                    'comuna' => $row['comuna'],
                    'ciudad' => $row['ciudad'],
                ]);
            }
        }
        return json_encode($data);
    }

    //funcion para validar si existe un correo y contraseña iguales en el mismo registro en la tabla usuarios
    public function iniciar_sesion($request)
    {
        $sql = "SELECT id_usuario, nombre, apellido FROM usuarios WHERE email = '$request->email' AND contraseña = '$request->contraseña'";
        //ejecutamos el comando para consultar el usuario por medio del correo
        $result = @mysqli_query($this->conn, $sql);
        if($result->num_rows > 0){
            $nombre = '';
            $apellido = '';
            while($row = $result->fetch_assoc())
            {
                //capturamos el nombre y el apellido del usuario para devolver el mensaje
                $nombre = $row['nombre'];
                $apellido = $row['apellido'];
            }
            return json_encode(["status" => true, "msg" => "Bienvenido $nombre $apellido"]); 
        }else{
            return json_encode(["status" => false, "msg" => "No existe ningun usuario registrado con ese correo. Por favor registrese"]); 
        }
    }

    //funcion para validar si el correo ya se encuentra registrado en la plataforma
    public function validar_correo_electronico($email)
    {
        $sql = "SELECT id_usuario FROM usuarios WHERE email = '$email'";
        //ejecutamos el comando para consultar el usuario por medio del correo
        $result = @mysqli_query($this->conn, $sql);
        return $result->num_rows;
    }

    //funcion para crear
    public function crear($request)
    {
        //validamos si el correo electronico ya existe, si deveuelve 1 registro quiere decir que es mayor a 1
        if($this->validar_correo_electronico($request->email) > 0)
        {
            return json_encode(["status" => false, "msg" => "El correo electronico ya existe"]); 
        }

        $data = false;
        $idUsuario = false;
        //armamos el SQL a ejecutar para crear el usuario en la tabla usuarios
        $sql = "INSERT INTO `usuarios`(`nombre`, `apellido`, `email`, `contraseña`, `direccion`, `barrio`, `telefono`)
                VALUES (
                    '$request->nombre',
                    '$request->apellido',
                    '$request->email',
                    '$request->constraseña',
                    '$request->direccion',
                    '$request->barrio',
                    '$request->telefono'
                )";
        //se ejecuta el comando creado en la variable sql para insertar en la BD
        $creacionUsuario = @mysqli_query($this->conn, $sql);

        if($creacionUsuario){
            //consultamos el id del usuario que acabamos de crear por medio del correo electronico
            $sql = "SELECT id_usuario FROM usuarios WHERE email = '$request->email' ORDER BY 1";
            //ejecutamos el comando para consultar el usuario por medio del correo
            $result = @mysqli_query($this->conn, $sql);
            
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc())
                {
                    //capturamos el id del usuario que se encontro en la consulta anterior
                    $idUsuario = $row['id_usuario'];
                }
            }
        }

        //Si se creo correctamente el usuario y viene un rol en la peticion y dicho rol es asesora entonces creamos la asesora
        if($request->rol && $request->rol == 'asesora'){
            //armamos el SQL a ejecutar para crear la asesora en la tabla asesoras
            $sql = "INSERT INTO asesoras(id_usuario, talla_blusa, talla_falda)
                VALUES (
                    '$idUsuario',
                    '$request->tallaBlusa',
                    '$request->tallaFalda'
                )";
            //se ejecuta el comando creado en la variable sql para insertar en la BD
            $creacionAsesora = @mysqli_query($this->conn, $sql);

            if($creacionAsesora){
                return json_encode(["status" => true, "msg" => "Se creo la asesora correctamente"]); 
            }
        }


        //Si se creo correctamente el usuario y viene un rol en la peticion y dicho rol es supervisor entonces creamos el supervisor
        if($request->rol && $request->rol == 'supervisor'){
            //armamos el SQL a ejecutar para crear la asesora en la tabla asesoras
            $sql = "INSERT INTO supervisores(id_usuario, talla_camisa, talla_pantalon, placa_moto)
                VALUES (
                    '$idUsuario',
                    '$request->tallaCamisa',
                    '$request->tallaPantalon',
                    '$request->placaMoto'
                )";
            //se ejecuta el comando creado en la variable sql para insertar en la BD
            $creacionSupervisor = @mysqli_query($this->conn, $sql);

            if($creacionSupervisor){
                return json_encode(["status" => true, "msg" => "Se creo el supervisor correctamente"]); 
            }
        }
    }

    //funcion para eliminar
    public function eliminar($idAsesora){
        $data = false;
        //$sql = "UPDATE `asesoras` SET `estado`= 0 WHERE `id_asesora` = ".$idAsesora;
        $sql = "DELETE FROM asesoras WHERE id_asesora = ".$idAsesora;
        $delete = @mysqli_query($this->conn, $sql);
        if($delete){
            $data = true;
        }
        return $data;
    }

    //funcion para consultar una usuario en especifico
    public function consultar($idAsesora)
    {
        $data = [];
        $sql = "SELECT id_asesora, numero_cedula, nombres, apellidos, direccion, barrio, comuna, ciudad FROM asesoras WHERE id_asesora = ".$idAsesora;
        $result = @mysqli_query($this->conn, $sql);
        if(!$result){
            return 'error consultado el listado de las asesoras';
        }
        //si la cantidad de asesoras que se encontraron es mayor a cero alimentamos la variable data para devolverlas
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc())
            {
                //enviamos un array por cada asesora encontrada al array de asesoras que se va a retornar
                array_push($data, [
                    "id_asesora" => $row['id_asesora'],
                    'numero_cedula' => $row['numero_cedula'],
                    'nombres' => $row['nombres'],
                    'apellidos' => $row['apellidos'],
                    'direccion' => $row['direccion'],
                    'barrio' => $row['barrio'],
                    'comuna' => $row['comuna'],
                    'ciudad' => $row['ciudad'],
                ]);
            }
        }
        return json_encode($data);
    }

    //function para actualizar asesoras
    public function actualizar($request)
    {
        $data = false;
        $sql = "UPDATE `asesoras` SET 
            `numero_cedula`= $request->cedula,
            `nombres`='$request->nombre',
            `apellidos`='$request->apellido',
            `direccion`='$request->direccion',
            `barrio`='$request->barrio',
            `comuna`='$request->comuna',
            `ciudad`='$request->ciudad'
            WHERE id_asesora = ".$request->id_asesora;

        $actualizar = @mysqli_query($this->conn, $sql);
        if($actualizar){
            $data = true;
        }
        return $data;
    }

}