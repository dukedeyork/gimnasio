<?php
// Configuración de la base de datos
// Debes cambiar estos valores por los que te proporcione DonWeb

define('DB_HOST', 'localhost');
define('DB_NAME', 'nombre_base_datos'); // Cambiar por el nombre real
define('DB_USER', 'usuario_db');        // Cambiar por el usuario real
define('DB_PASS', 'password_db');       // Cambiar por la contraseña real

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
