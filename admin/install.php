<?php
require_once 'config.php';

// Intentar conectar solo para verificar credenciales (la función getDB conecta a la DB específica)
// Si la DB no existe, fallará aquí. El usuario debe crearla en DonWeb primero.
$conn = getDB();

$queries = [];

// Tabla de Administradores
$queries[] = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Tabla de Clientes
$queries[] = "CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) UNIQUE,
    email VARCHAR(100),
    telefono VARCHAR(20),
    fecha_nacimiento DATE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1
)";

// Crear usuario admin por defecto si no existe ninguno
// Password por defecto: admin123
$default_admin_user = 'admin';
$default_admin_pass = password_hash('admin123', PASSWORD_DEFAULT);

$queries[] = "INSERT INTO admin_users (username, password) 
              SELECT * FROM (SELECT '$default_admin_user', '$default_admin_pass') AS tmp
              WHERE NOT EXISTS (
                  SELECT username FROM admin_users WHERE username = '$default_admin_user'
              ) LIMIT 1";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación Base de Datos - Gimnasio</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; padding: 1rem; background-color: #f4f4f4; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Instalación del Sistema</h1>
        <ul>
        <?php
        foreach ($queries as $sql) {
            if ($conn->query($sql) === TRUE) {
                // Simplificamos el mensaje para el insert condicional
                $msg = "Operación ejecutada correctamente.";
                if (strpos($sql, 'CREATE TABLE') !== false) {
                    preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $matches);
                    $tableName = $matches[1] ?? 'tabla';
                    $msg = "Tabla <strong>$tableName</strong> verificada/creada.";
                } elseif (strpos($sql, 'INSERT INTO') !== false) {
                    $msg = "Usuario administrador verificado/creado.";
                }
                echo "<li><span class='success'>✔</span> $msg</li>";
            } else {
                echo "<li><span class='error'>✘</span> Error: " . $conn->error . "</li>";
            }
        }
        $conn->close();
        ?>
        </ul>
        <p>Instalación finalizada. Por favor, <strong>elimina este archivo (install.php)</strong> una vez verificado el funcionamiento.</p>
        <p><a href="index.php">Ir al Login</a></p>
    </div>
</body>
</html>
