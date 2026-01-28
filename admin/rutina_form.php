<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

$id = '';
$nombre = '';
$musculos = '';
$peso = '';
$maquina = '';
$series = '';
$reps = '';
$is_edit = false;
$msg = '';
$error = '';

$conn = getDB();

if (isset($_GET['id'])) {
    $is_edit = true;
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM rutinas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $nombre = $row['nombre_ejercicio'];
        $musculos = $row['musculos'];
        $peso = $row['peso_sugerido'];
        $maquina = $row['espacio_maquina'];
        $series = $row['series'];
        $reps = $row['repeticiones'];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $musculos = trim($_POST['musculos']);
    $peso = trim($_POST['peso']);
    $maquina = trim($_POST['maquina']);
    $series = intval($_POST['series']);
    $reps = intval($_POST['reps']);

    if (empty($nombre)) {
        $error = "El nombre del ejercicio es obligatorio.";
    } else {
        if ($is_edit) {
            $stmt = $conn->prepare("UPDATE rutinas SET nombre_ejercicio=?, musculos=?, peso_sugerido=?, espacio_maquina=?, series=?, repeticiones=? WHERE id=?");
            $stmt->bind_param("ssssiii", $nombre, $musculos, $peso, $maquina, $series, $reps, $id);
            if ($stmt->execute())
                $msg = "Actualizado correctamente.";
            else
                $error = "Error al actualizar.";
        } else {
            $stmt = $conn->prepare("INSERT INTO rutinas (nombre_ejercicio, musculos, peso_sugerido, espacio_maquina, series, repeticiones) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssii", $nombre, $musculos, $peso, $maquina, $series, $reps);
            if ($stmt->execute()) {
                $msg = "Creado exitosamente.";
                $nombre = $musculos = $peso = $maquina = $series = $reps = '';
            } else
                $error = "Error al crear.";
        }
        if (isset($stmt))
            $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejercicio - Gimnasio</title>
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn-submit {
            background-color: #fa211b;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-cancel {
            background-color: #6b7280;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin-left: 1rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
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
        <h1>
            <?php echo $is_edit ? 'Editar Ejercicio' : 'Nuevo Ejercicio'; ?>
        </h1>
        <div class="card">
            <?php if ($msg): ?>
                <div class="alert-success">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nombre del Ejercicio *</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Músculos</label>
                        <input type="text" name="musculos" value="<?php echo htmlspecialchars($musculos); ?>"
                            placeholder="Ej: Pecho, Tríceps">
                    </div>
                    <div class="form-group">
                        <label>Máquina / Espacio</label>
                        <input type="text" name="maquina" value="<?php echo htmlspecialchars($maquina); ?>"
                            placeholder="Ej: Banco Plano">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Peso Sugerido</label>
                        <input type="text" name="peso" value="<?php echo htmlspecialchars($peso); ?>"
                            placeholder="Ej: 20kg">
                    </div>
                    <div class="form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                        <div>
                            <label>Series</label>
                            <input type="number" name="series" value="<?php echo htmlspecialchars($series); ?>">
                        </div>
                        <div>
                            <label>Repeticiones</label>
                            <input type="number" name="reps" value="<?php echo htmlspecialchars($reps); ?>">
                        </div>
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn-submit">Guardar</button>
                    <a href="rutinas.php" class="btn-cancel">Volver</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>