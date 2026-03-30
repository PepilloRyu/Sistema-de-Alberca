<?php
// database.php - Conexión y funciones de base de datos
require_once 'config.php';

class Database {
    private $connection;
    private static $instance = null;
    
    // Constructor privado para patrón Singleton
    private function __construct() {
        try {
            $this->connection = new mysqli(
                DB_HOST, 
                DB_USER, 
                DB_PASS, 
                DB_NAME, 
                DB_PORT
            );
            
            // Verificar conexión
            if ($this->connection->connect_error) {
                throw new Exception("Error de conexión: " . $this->connection->connect_error);
            }
            
            // Establecer charset
            $this->connection->set_charset(DB_CHARSET);
            
        } catch (Exception $e) {
            die("Error en la base de datos: " . $e->getMessage());
        }
    }
    
    // Obtener instancia única de la conexión
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    // Obtener la conexión
    public function getConnection() {
        return $this->connection;
    }
    
    // Ejecutar consulta SQL
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    // Preparar consulta con parámetros
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    // Obtener último ID insertado
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    // Escapar string para evitar inyección SQL
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
    
    // Cerrar conexión
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    // Destructor para cerrar conexión automáticamente
    public function __destruct() {
        $this->close();
    }
}

// Función helper para obtener la conexión fácilmente
function getDB() {
    return Database::getInstance()->getConnection();
}

// Función helper para ejecutar consultas
function executeQuery($sql) {
    return Database::getInstance()->query($sql);
}
?>