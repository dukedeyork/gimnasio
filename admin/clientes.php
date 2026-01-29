<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

// Lógica de Eliminación
// Lógica de Eliminación (ahora es desactivación) - Mantenemos esto o lo integramos con el cambio de estado?
// Si se pide 'delete', lo marcamos como inactivo (0).
// Lógica de Eliminación (Borrado Físico)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn = getDB();
    $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $msg = "Cliente eliminado permanentemente.";
        } else {
            $msg = "El cliente no existe o ya fue eliminado.";
        }
    } else {
        $error = "Error al eliminar: " . $conn->error;
    }
    $stmt->close();
    $conn->close();
}

// Lógica para cambio de estado mediante selector
if (isset($_POST['update_status']) && isset($_POST['cliente_id']) && isset($_POST['nuevo_estado'])) {
    $id = intval($_POST['cliente_id']);
    $estado = intval($_POST['nuevo_estado']); // 0 o 1
    $conn = getDB();
    $stmt = $conn->prepare("UPDATE clientes SET activo = ? WHERE id = ?");
    $stmt->bind_param("ii", $estado, $id);
    if ($stmt->execute()) {
        $msg = "Estado actualizado correctamente.";
    } else {
        $error = "Error al actualizar estado.";
    }
    $stmt->close();
    $conn->close();
}

$conn = getDB();
// Mostrar todos (activos e inactivos)
$sql = "SELECT * FROM clientes ORDER BY activo DESC, id DESC";
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
            color: #fa211b;
        }

        .container {
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

        /* Responsive Styles */
        .mobile-cards {
            display: none;
        }

        @media (max-width: 992px) {
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

            .client-card {
                background: white;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                border-left: 4px solid #fa211b;
            }

            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 0.75rem;
                border-bottom: 1px solid #eee;
                padding-bottom: 0.5rem;
            }

            .card-title {
                font-weight: bold;
                font-size: 1.1rem;
                color: #111;
                text-transform: capitalize;
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
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .card-actions .btn {
                flex: 1;
                text-align: center;
                font-size: 0.85rem;
                padding: 0.4rem 0.2rem;
            }

            .status-badge {
                padding: 0.2rem 0.5rem;
                border-radius: 4px;
                font-size: 0.8rem;
                font-weight: bold;
            }
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div class="brand">Gimnasio Admin</div>
        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="clientes.php" style="color: #fa211b;">Clientes</a>
            <a href="rutinas.php">Rutinas</a>
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

        <?php
        $clientes = [];
        $today = new DateTime();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Procesar datos para cada cliente
                $tipo_plan = isset($row['tipo_plan']) ? intval($row['tipo_plan']) : 1;
                $ingreso = isset($row['fecha_ingreso']) ? $row['fecha_ingreso'] : null;

                $transcurrido_str = "N/A";
                $restante_str = "N/A";
                $color_restante = "#333";

                if ($ingreso) {
                    $fecha_ingreso_dt = new DateTime($ingreso);
                    $fecha_vencimiento = clone $fecha_ingreso_dt;
                    $fecha_vencimiento->modify("+$tipo_plan months");

                    if ($today >= $fecha_ingreso_dt) {
                        $diff_trans = $fecha_ingreso_dt->diff($today);
                        $transcurrido_str = $diff_trans->days . " d";
                    } else {
                        $transcurrido_str = "0 d (Futuro)";
                    }

                    if ($fecha_vencimiento > $today) {
                        $diff_rest = $today->diff($fecha_vencimiento);
                        $restante_str = $diff_rest->days . " días";
                        if ($diff_rest->days < 5)
                            $color_restante = "#dc2626";
                        elseif ($diff_rest->days < 10)
                            $color_restante = "#f59e0b";
                        else
                            $color_restante = "#10b981";
                    } else {
                        $restante_str = "Vencido";
                        $color_restante = "#dc2626";
                    }
                }

                $row['transcurrido_str'] = $transcurrido_str;
                $row['restante_str'] = $restante_str;
                $row['color_restante'] = $color_restante;
                $row['tipo_plan_str'] = $tipo_plan . ' Mes' . ($tipo_plan > 1 ? 'es' : '');

                $clientes[] = $row;
            }
        }
        ?>

        <?php if (!empty($clientes)): ?>
            <!-- Desktop Table -->
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Género</th>
                            <th>Plan</th>
                            <th>Fecha Ingreso</th>
                            <th>Transcurrido</th>
                            <th>Restante</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $row): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td style="text-transform: capitalize;">
                                    <?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['dni']); ?></td>
                                <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars(isset($row['genero']) ? $row['genero'] : 'hombre')); ?>
                                </td>
                                <td><?php echo $row['tipo_plan_str']; ?></td>
                                <td><?php echo $row['fecha_ingreso'] ? date('d/m/Y', strtotime($row['fecha_ingreso'])) : '-'; ?>
                                </td>
                                <td><?php echo $row['transcurrido_str']; ?></td>
                                <td style="color: <?php echo $row['color_restante']; ?>; font-weight: bold;">
                                    <?php echo $row['restante_str']; ?>
                                </td>
                                <td>
                                    <form method="POST" action="" style="margin:0;">
                                        <input type="hidden" name="cliente_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="nuevo_estado" onchange="this.form.submit()"
                                            style="padding: 0.25rem; border-radius: 4px; border: 1px solid #ccc; 
                                                       background-color: <?php echo $row['activo'] ? '#d1fae5' : '#fee2e2'; ?>; 
                                                       color: <?php echo $row['activo'] ? '#065f46' : '#991b1b'; ?>; font-weight:bold;">
                                            <option value="1" <?php echo $row['activo'] ? 'selected' : ''; ?>>Activo</option>
                                            <option value="0" <?php echo !$row['activo'] ? 'selected' : ''; ?>>Inactivo</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="actions">
                                    <a href="asignar_rutinas.php?cliente_id=<?php echo $row['id']; ?>" class="btn"
                                        style="background-color: #8b5cf6; font-size: 0.9rem; padding: 0.25rem 0.5rem;">Rutina</a>
                                    <a href="cliente_form.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Editar</a>
                                    <a href="clientes.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger"
                                        onclick="return confirm('¿Seguro que deseas eliminar este cliente?')">Borrar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="mobile-cards">
                <?php foreach ($clientes as $row): ?>
                    <div class="client-card">
                        <div class="card-header">
                            <div class="card-title"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?>
                            </div>
                            <span class="status-badge"
                                style="background: <?php echo $row['activo'] ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $row['activo'] ? '#065f46' : '#991b1b'; ?>;">
                                <?php echo $row['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </div>
                        <div class="card-detail"><strong>DNI:</strong> <?php echo htmlspecialchars($row['dni']); ?></div>
                        <div class="card-detail"><strong>Tel:</strong> <?php echo htmlspecialchars($row['telefono']); ?></div>
                        <div class="card-detail"><strong>Plan:</strong> <?php echo $row['tipo_plan_str']; ?></div>
                        <div class="card-detail"><strong>Vence en:</strong>
                            <span style="color: <?php echo $row['color_restante']; ?>; font-weight: bold;">
                                <?php echo $row['restante_str']; ?>
                            </span>
                        </div>

                        <div class="card-actions">
                            <form method="POST" action="" style="margin:0; flex: 1; display: flex;">
                                <input type="hidden" name="cliente_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="nuevo_estado" value="<?php echo $row['activo'] ? '0' : '1'; ?>">
                                <button type="submit"
                                    class="btn <?php echo $row['activo'] ? 'btn-secondary' : 'btn-primary'; ?>"
                                    style="width: 100%;">
                                    <?php echo $row['activo'] ? 'Desactivar' : 'Activar'; ?>
                                </button>
                            </form>
                            <a href="asignar_rutinas.php?cliente_id=<?php echo $row['id']; ?>" class="btn"
                                style="background-color: #8b5cf6;">Rutina</a>
                            <a href="cliente_form.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Editar</a>
                            <a href="clientes.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger"
                                onclick="return confirm('¿Eliminar cliente?')">Borrar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
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