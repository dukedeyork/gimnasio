<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

// Obtener conteo rápido de clientes
$conn = getDB();
$sql = "SELECT COUNT(*) as total FROM clientes WHERE activo = 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_clientes = $row['total'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gimnasio</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background-color: #f3f4f6;
            color: #333;
        }

        .admin-nav {
            background: #111;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .admin-nav a {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
        }

        .admin-nav .brand {
            font-size: 1.25rem;
            font-weight: bold;
            color: #f36100;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .stat-card h3 {
            margin: 0;
            color: #555;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #f36100;
            margin: 0.5rem 0;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-btn {
            display: block;
            background: #fff;
            padding: 2rem;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid transparent;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-color: #f36100;
        }

        .action-btn h4 {
            margin-top: 0;
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div class="brand">Gimnasio Admin</div>
        <div class="menu">
            <a href="dashboard.php" style="color: #f36100;">Dashboard</a>
            <a href="clientes.php">Clientes</a>
            <a href="rutinas.php">Rutinas</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <h1>Bienvenido,
            <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
        </h1>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Clientes Activos</h3>
                <div class="value">
                    <?php echo $total_clientes; ?>
                </div>
            </div>
            <!-- Aquí se pueden agregar más estadísticas a futuro -->
        </div>

        <h2>Accesos Rápidos</h2>
        <div class="action-grid">
            <a href="clientes.php" class="action-btn">
                <h4>Ver Clientes</h4>
                <p>Listado completo y gestión</p>
            </a>
            <a href="cliente_form.php" class="action-btn">
                <h4>Nuevo Cliente</h4>
                <p>Dar de alta un socio</p>
            </a>
            <a href="rutinas.php" class="action-btn">
                <h4>Rutinas</h4>
                <p>Crear y editar ejercicios</p>
            </a>
        </div>
    </div>
</body>

</html>