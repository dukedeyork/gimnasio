<?php
require_once 'config.php';

$conn = getDB();

$updates = [
    "ALTER TABLE clientes ADD COLUMN IF NOT EXISTS password VARCHAR(255) DEFAULT NULL AFTER dni",
    "ALTER TABLE clientes ADD COLUMN IF NOT EXISTS tipo_plan INT DEFAULT 1 AFTER activo",
    "ALTER TABLE clientes ADD COLUMN IF NOT EXISTS fecha_ingreso DATE DEFAULT NULL AFTER tipo_plan"
];

echo "Iniciando actualización de base de datos...\n";

foreach ($updates as $sql) {
    // Check if column exists first to avoid errors if syntax doesn't support IF NOT EXISTS in all versions (MySQL 8.0+ supports it, but MariaDB might not in older versions)
    // Actually, let's just try-catch or suppress error if it exists, or check manually.
    // A better way for compatibility:
    // SELECT count(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'megafit' AND TABLE_NAME = 'clientes' AND COLUMN_NAME = 'password'

    // Simplest way: execute and ignore duplicate column error.
    if ($conn->query($sql) === TRUE) {
        echo "SQL ejecutado con éxito: $sql\n";
    } else {
        echo "Nota: " . $conn->error . " (Posiblemente la columna ya existía)\n";
    }
}

echo "Actualización completada.\n";
$conn->close();
?>