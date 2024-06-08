<?php 
  //Creamos una clase para realizar la conexion a la base de datos una sola vez en todo el proyecto
  class Connection{

    private $conn = false;

    public function __construct() {
        //creamos la conexion a la base de datos pasando como parametros la url de la conexion (en este caso localhost)
        // como segundo parametro el usuario por defecto "root" sin contraseña ya que no esta configurada y nombramos la base de datos a la que nos queremos conectar
        $connection = @mysqli_connect('localhost', 'root', '', 'luki');
        
        //validamos que la conexion a la base de datos sea correcta, de no ser el caso no llena la variable global
        if($connection){
            $this->conn = $connection;
        }
    }

    //retornamos la conexion a los archivos que la necesiten
    public function getConnection(){
        return $this->conn;
    }

  }
?>