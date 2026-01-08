<?php
require_once 'config.php';

$conn = getDB();

$updates = [
    "ALTER TABLE clientes ADD COLUMN IF NOT EXISTS genero ENUM('hombre', 'mujer') DEFAULT 'hombre' AFTER fecha_nacimiento"
];

echo "Iniciando actualización de base de datos (Género)...\n";

foreach ($updates as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "SQL ejecutado con éxito: $sql\n";
    } else {
        echo "Nota: " . $conn->error . " (Posiblemente la columna ya existía)\n";
    }
}

echo "Actualización completada.\n";
$conn->close();
?>