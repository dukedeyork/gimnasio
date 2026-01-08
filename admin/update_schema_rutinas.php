<?php
require_once 'config.php';

$conn = getDB();

$queries = [];

// Tabla de Rutinas (Definición de ejercicios)
$queries[] = "CREATE TABLE IF NOT EXISTS rutinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_ejercicio VARCHAR(150) NOT NULL,
    musculos VARCHAR(255),
    peso_sugerido VARCHAR(100),
    espacio_maquina VARCHAR(150),
    series INT,
    repeticiones INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Tabla de asignación Cliente <-> Rutinas
$queries[] = "CREATE TABLE IF NOT EXISTS cliente_rutinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    rutina_id INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (rutina_id) REFERENCES rutinas(id) ON DELETE CASCADE
)";

echo "Iniciando actualización de base de datos (Rutinas)...\n";

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "SQL registrado: " . substr($sql, 0, 50) . "...\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

echo "Tablas de rutinas verificadas/creadas.\n";
$conn->close();
?>