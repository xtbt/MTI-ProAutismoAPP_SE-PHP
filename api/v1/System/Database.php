<?php
    class Database {
        // Connection properties
        private $DB_Controller = DB_CONTROLLER;
        private $DB_Host = DB_HOST;
        private $DB_Port = DB_PORT;
        private $DB_Name = DB_NAME;
        private $DB_User = DB_USER;
        private $DB_Password = DB_PASSWORD;
        private $DB_Connector;
        
        protected static $DB_Instance = NULL; // Singleton

        private function __construct() {
            $this->DB_Connector = NULL;
            try {
                $this->DB_Connector = new PDO($this->DB_Controller.':host='.$this->DB_Host.';port='.$this->DB_Port.';dbname='.$this->DB_Name, $this->DB_User, $this->DB_Password);
                $this->DB_Connector->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->DB_Connector->exec("SET CHARACTER SET utf8");
            }
            catch (PDOException $ex) {
                die('CANNOT CREATE DATABASE CONNECTION');
            };
        }

        // Return Singleton instance to the model
        public static function getInstance() {
            return self::$DB_Instance === NULL ? self::$DB_Instance = new self : self::$DB_Instance;
        }

        // Return DB connector
        public function getConnector() {
            return $this->DB_Connector;
        }

        /*
        // Manual DB Connection (Not for singleton)
        public function conectar() {
            $this->DB_Connector = null;
            try {
                $this->DB_Connector = new PDO($this->DB_Controller.':DB_Host='.$this->DB_Host.';dbname='.$this->DB_Name, $this->DB_User, $this->DB_Password);
                $this->DB_Connector->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->DB_Connector->exec("SET CHARACTER SET utf8");
            }
            catch (PDOException $ex) {
                return 0; // Error de conexión
            }
            return $this->DB_Connector;
        }
        */
    }
?>