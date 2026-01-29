<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

// Lógica de Eliminación
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn = getDB();
    $stmt = $conn->prepare("DELETE FROM rutinas WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "Rutina eliminada correctamente.";
    } else {
        $error = "Error al eliminar.";
    }
    $stmt->close();
    $conn->close();
}

$conn = getDB();
$sql = "SELECT * FROM rutinas ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Rutinas - Gimnasio</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f3f4f6;
            margin: 0;
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
            color: #fa211b;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn-primary {
            background-color: #fa211b;
        }

        .btn-primary:hover {
            background-color: #d45300;
        }

        .btn-danger {
            background-color: #dc2626;
            padding: 0.25rem 0.5rem;
            font-size: 0.9rem;
        }

        .btn-edit {
            background-color: #2563eb;
            padding: 0.25rem 0.5rem;
            font-size: 0.9rem;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #4b5563;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        /* Responsive Styles */
        .mobile-cards {
            display: none;
        }

        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                padding: 1rem;
            }

            .admin-nav .menu {
                margin-top: 1rem;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
            }

            .admin-nav a {
                margin: 0;
                font-size: 0.9rem;
            }

            .header-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .header-actions h1 {
                font-size: 1.5rem;
                margin: 0;
            }

            table {
                display: none;
            }

            .mobile-cards {
                display: block;
            }

            .routine-card {
                background: white;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                border-left: 4px solid #fa211b;
            }

            .card-title {
                font-weight: bold;
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
                color: #111;
            }

            .card-detail {
                font-size: 0.9rem;
                margin-bottom: 0.25rem;
                color: #4b5563;
            }

            .card-detail strong {
                color: #111;
            }

            .card-actions {
                margin-top: 1rem;
                display: flex;
                gap: 0.5rem;
            }

            .card-actions .btn {
                flex: 1;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div class="brand">Gimnasio Admin</div>
        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="clientes.php">Clientes</a>
            <a href="rutinas.php" style="color: #fa211b;">Rutinas</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <div class="header-actions">
            <h1>Ejercicios / Rutinas</h1>
            <a href="rutina_form.php" class="btn btn-primary">+ Nuevo Ejercicio</a>
        </div>

        <?php if (isset($msg)): ?>
            <div style="background:#d1fae5; color:#065f46; padding:1rem; border-radius:4px; margin-bottom:1rem;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <?php
        $rutinas = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rutinas[] = $row;
            }
        }
        ?>

        <?php if (!empty($rutinas)): ?>
            <!-- Desktop Table -->
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Ejercicio</th>
                            <th>Músculos</th>
                            <th>Peso</th>
                            <th>Máquina/Espacio</th>
                            <th>Series</th>
                            <th>Reps</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rutinas as $row): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($row['nombre_ejercicio']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['musculos']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['peso_sugerido']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['espacio_maquina']); ?>
                                </td>
                                <td>
                                    <?php echo $row['series']; ?>
                                </td>
                                <td>
                                    <?php echo $row['repeticiones']; ?>
                                </td>
                                <td class="actions">
                                    <a href="rutina_form.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Editar</a>
                                    <a href="rutinas.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger"
                                        onclick="return confirm('¿Eliminar este ejercicio?')">Borrar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="mobile-cards">
                <?php foreach ($rutinas as $row): ?>
                    <div class="routine-card">
                        <div class="card-title"><?php echo htmlspecialchars($row['nombre_ejercicio']); ?></div>
                        <div class="card-detail"><strong>Músculos:</strong> <?php echo htmlspecialchars($row['musculos']); ?>
                        </div>
                        <div class="card-detail"><strong>Máquina:</strong>
                            <?php echo htmlspecialchars($row['espacio_maquina']); ?></div>
                        <div class="card-detail"><strong>Peso:</strong> <?php echo htmlspecialchars($row['peso_sugerido']); ?>
                        </div>
                        <div class="card-detail"><strong>Series/Reps:</strong> <?php echo $row['series']; ?> x
                            <?php echo $row['repeticiones']; ?></div>
                        <div class="card-actions">
                            <a href="rutina_form.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Editar</a>
                            <a href="rutinas.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger"
                                onclick="return confirm('¿Eliminar este ejercicio?')">Borrar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="empty-state">
                No hay ejercicios creados.
            </div>
        <?php endif; ?>
        <?php $conn->close(); ?>
    </div>
</body>

</html>