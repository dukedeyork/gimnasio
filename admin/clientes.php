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
    // Borrado lógico o físico? El requerimiento dice eliminar. Hacemos borrado físico para simplificar o update activo=0.
    // Usualmente mejor activo=0.
    $stmt = $conn->prepare("UPDATE clientes SET activo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "Cliente eliminado correctamente.";
    } else {
        $error = "Error al eliminar.";
    }
    $stmt->close();
    $conn->close();
}

$conn = getDB();
$sql = "SELECT * FROM clientes WHERE activo = 1 ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Gimnasio</title>
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
            color: #f36100;
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
            background-color: #f36100;
        }

        .btn-primary:hover {
            background-color: #d45300;
        }

        .btn-secondary {
            background-color: #6b7280;
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

        .actions {
            white-space: nowrap;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div class="brand">Gimnasio Admin</div>
        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="clientes.php" style="color: #f36100;">Clientes</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <div class="header-actions">
            <h1>Listado de Clientes</h1>
            <a href="cliente_form.php" class="btn btn-primary">+ Nuevo Cliente</a>
        </div>

        <?php if (isset($msg)): ?>
            <div style="background:#d1fae5; color:#065f46; padding:1rem; border-radius:4px; margin-bottom:1rem;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>DNI</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo $row['id']; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['dni']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['telefono']); ?>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($row['fecha_registro'])); ?>
                                </td>
                                <td class="actions">
                                    <a href="cliente_form.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Editar</a>
                                    <a href="clientes.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger"
                                        onclick="return confirm('¿Seguro que deseas eliminar este cliente?')">Borrar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                No hay clientes registrados aún.
            </div>
        <?php endif; ?>
        <?php $conn->close(); ?>
    </div>
</body>

</html>