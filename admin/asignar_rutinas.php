<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

if (!isset($_GET['cliente_id'])) {
    header("Location: clientes.php");
    exit;
}

$cliente_id = intval($_GET['cliente_id']);
$conn = getDB();

// Obtener datos del cliente
$stmt = $conn->prepare("SELECT nombre, apellido FROM clientes WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$res = $stmt->get_result();
$cliente = $res->fetch_assoc();
$stmt->close();

if (!$cliente) {
    die("Cliente no encontrado.");
}

// Agregar Rutina
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rutina_id'])) {
    $rutina_id = intval($_POST['rutina_id']);
    $dia = isset($_POST['dia']) ? intval($_POST['dia']) : 1;
    // Verificar si ya la tiene asignada para no duplicar (opcional, el requerimiento dice "tantas como quiera", permitimos duplicados?)
    // Asumiremos que puede repetir ejercicio, asi que insert directo.
    $stmt = $conn->prepare("INSERT INTO cliente_rutinas (cliente_id, rutina_id, dia_entrenamiento) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $cliente_id, $rutina_id, $dia);
    $stmt->execute();
    $stmt->close();
}

// Eliminar Rutina Asignada
if (isset($_GET['remove'])) {
    $cr_id = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM cliente_rutinas WHERE id = ? AND cliente_id = ?");
    $stmt->bind_param("ii", $cr_id, $cliente_id);
    $stmt->execute();
    $stmt->close();
}

// Obtener Rutinas Asignadas
$sql_asignadas = "SELECT cr.id as asignacion_id, cr.dia_entrenamiento, r.nombre_ejercicio, r.series, r.repeticiones, r.peso_sugerido 
                  FROM cliente_rutinas cr 
                  JOIN rutinas r ON cr.rutina_id = r.id 
                  WHERE cr.cliente_id = $cliente_id 
                  ORDER BY cr.dia_entrenamiento ASC, cr.fecha_asignacion ASC";
$res_asignadas = $conn->query($sql_asignadas);

// Obtener Todas las Rutinas (para el select)
$sql_todas = "SELECT id, nombre_ejercicio FROM rutinas ORDER BY nombre_ejercicio ASC";
$res_todas = $conn->query($sql_todas);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Rutinas - Gimnasio</title>
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
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        h1 span {
            font-weight: 300;
            color: #666;
            font-size: 0.8em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th,
        td {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f9f9f9;
        }

        .btn-add {
            background: #f36100;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-remove {
            color: #dc2626;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .form-inline {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        select {
            padding: 0.5rem;
            flex-grow: 1;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #666;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div class="brand">Gimnasio Admin</div>
        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="clientes.php" style="color: #f36100;">Clientes</a>
            <a href="rutinas.php">Rutinas</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <a href="clientes.php" class="back-link">← Volver a Clientes</a>
        <h1>Rutina de: <span>
                <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
            </span></h1>

        <div class="card">
            <h3>Asignar Nuevo Ejercicio</h3>
            <form method="POST" class="form-inline">
                <select name="rutina_id" required>
                    <option value="">-- Seleccionar Ejercicio --</option>
                    <?php while ($r = $res_todas->fetch_assoc()): ?>
                        <option value="<?php echo $r['id']; ?>">
                            <?php echo htmlspecialchars($r['nombre_ejercicio']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <select name="dia" style="max-width: 150px;">
                    <option value="1">Día 1</option>
                    <option value="2">Día 2</option>
                    <option value="3">Día 3</option>
                    <option value="4">Día 4</option>
                    <option value="5">Día 5</option>
                </select>
                <button type="submit" class="btn-add">Agregar a Rutina</button>
            </form>
        </div>

        <div class="card">
            <h3>Ejercicios Asignados</h3>
            <?php if ($res_asignadas->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Día</th>
                            <th>Ejercicio</th>
                            <th>Series</th>
                            <th>Reps</th>
                            <th>Peso</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $res_asignadas->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong>Día <?php echo $row['dia_entrenamiento']; ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['nombre_ejercicio']); ?>
                                </td>
                                <td>
                                    <?php echo $row['series']; ?>
                                </td>
                                <td>
                                    <?php echo $row['repeticiones']; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['peso_sugerido']); ?>
                                </td>
                                <td>
                                    <a href="asignar_rutinas.php?cliente_id=<?php echo $cliente_id; ?>&remove=<?php echo $row['asignacion_id']; ?>"
                                        class="btn-remove" onclick="return confirm('¿Quitar de la rutina?')">X</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#777; font-style:italic; margin-top:1rem;">Este cliente no tiene ejercicios asignados.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>