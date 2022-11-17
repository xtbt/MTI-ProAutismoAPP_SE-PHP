<?php
    class ControlSesion {
        // Parámetros de Acceso
        private $nombre_sesion = NOMBRE_PROYECTO;
        private $id_usr_ses = null;
        private $id_dom_ses = null;
        private $id_clase_usr_ses = null;
        private $nombre_usr_ses = null;
        private $contrasena_usr_hash = null;
        private $nombre_ses = null;
        private $conectorBD = null;

        // ARRAY de respuesta de la clase
        private $respuesta = [
            'msj'   => null
        ];

        // Constructor que se ejecuta al inicio de cada SCRIPT
        function __construct() {
            session_name($this->nombre_sesion);
            session_start();
            if (!$this->usuarioAutenticado()) {
                $_SESSION['nombre_usr_ses'] = "Usuario Desconocido";
                $_SESSION['id_clase_usr_ses'] = 999;
            };
        }

        // Indicador de usuario loggeado.... o no
        public function usuarioAutenticado() {
            if (isset($_SESSION['usuario_autenticado']) && $_SESSION['usuario_autenticado'] == true) {
                $this->id_usr_ses = $_SESSION['id_usr_ses'];
                $this->id_dom_ses = $_SESSION['id_dom_ses'];
                $this->id_clase_usr_ses = $_SESSION['id_clase_usr_ses'];
                $this->nombre_usr_ses = $_SESSION['nombre_usr_ses'];
                $this->contrasena_usr_hash = $_SESSION['contrasena_usr_hash'];
                $this->nombre_ses = $_SESSION['nombre_ses'];
                return true;
            }
            $_SESSION['usuario_autenticado'] = false;
            return false;
        }

        // Iniciar sesión con credenciales de usuario
        public function iniciar($txt_nombre_usr_ses, $txt_contrasena_usr_ses) {
            $this->conectorBD = BaseDeDatos::InstanciaUnica()->obtenerConector(); // Obtener conector único a la Base de Datos
            // Buscar usuario en la Base de Datos
            try {
                $SQL_Consulta = "SELECT 
                    id_usr, 
                    id_dom, 
                    id_clase_usr, 
                    contrasena_usr, 
                    nombre 
                    FROM 
                    tbl_usuarios 
                    WHERE 
                    nombre_usr = :nombre_usr AND 
                    estado <> 0";
            
                $SQL_Sentencia = $this->conectorBD->prepare($SQL_Consulta);
                $SQL_Sentencia->bindParam(':nombre_usr', $txt_nombre_usr_ses, PDO::PARAM_STR);
                $SQL_Sentencia->execute();
        
                if ($SQL_Sentencia->rowCount() != 0) { // Si se encontró usuario en la BD
                    $usuario = $SQL_Sentencia->fetchObject(); // Obtener datos de usuario
                    $contrasena_usr_hash = $usuario->contrasena_usr; // Obtener contraseña encriptada
                    // Comparar contraseña ingresada en la forma con la encriptada en la BD
                    if (password_verify($txt_contrasena_usr_ses, $contrasena_usr_hash)) {
                        $this->id_usr_ses = $usuario->id_usr;
                        $this->id_dom_ses = $usuario->id_dom;
                        $this->id_clase_usr_ses = $usuario->id_clase_usr;
                        $this->nombre_usr_ses = $txt_nombre_usr_ses;
                        $this->contrasena_usr_hash = $contrasena_usr_hash;
                        $this->nombre_ses = $usuario->nombre;
                        $this->cargarDatosSesion();
                        $this->respuesta['msj'] = new MensajeDeSistema('Clase:'.get_class($this), 2201); // Ok: Inicio de sesión exitoso
                        return $this->respuesta;
                    }
                    else {
                        $this->respuesta['msj'] = new MensajeDeSistema('Clase:'.get_class($this), 6201); // Error: Datos de inicio de sesión incorrectos
                        return $this->respuesta;
                    };
                }
                else { // Si no se encontró el usuario en la BD
                    $this->respuesta['msj'] = new MensajeDeSistema('Clase:'.get_class($this), 6201); // Error: Datos de inicio de sesión incorrectos
                    return $this->respuesta;
                };
            }
            catch (PDOException $ex) {
                $this->respuesta['msj'] = new MensajeDeSistema('Clase:'.get_class($this), 9105); // Error: Datos de inicio de sesión incorrectos
                return $this->respuesta;
            };
        }

        // Cargar Datos de usuario en sesión
        private function cargarDatosSesion() {
            $_SESSION['id_usr_ses'] = $this->id_usr_ses;
            $_SESSION['id_dom_ses'] = $this->id_dom_ses;
            $_SESSION['id_clase_usr_ses'] = $this->id_clase_usr_ses;
            $_SESSION['nombre_usr_ses'] = $this->nombre_usr_ses;
            $_SESSION['contrasena_usr_hash'] = $this->contrasena_usr_hash;
            $_SESSION['nombre_ses'] = $this->nombre_ses;
            $_SESSION['usuario_autenticado'] = true;
            /* LA ASIGNACIÓN DE VALORES EN LOS PARÁMETROS DE LA CLASE SE HACE 
            AL CARGAR LA SESIÓN EN CADA SCRIPT (función usuarioAutenticado) */
        }

        // Destructor de datos de clase y datos de sesión
        public function finalizar() {
            session_destroy();
        }
    }
?>