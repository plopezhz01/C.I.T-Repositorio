<?php
// Configuración de sesión (DEBE ir ANTES de session_start)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS
}

// Configuración de la base de datos
define('DB_HOST', 'soporte.cpick8cz8rta.us-east-1.rds.amazonaws.com');
define('DB_USER', 'root');
define('DB_PASS', 'Gruposiete');
define('DB_NAME', 'soporte');

// Conexión a la base de datos
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("Error al conectar con la base de datos: " . $e->getMessage());
    }
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['cliente_id']) && isset($_SESSION['cliente_nombre']);
}

// Función para redirigir si no está logueado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
?>
