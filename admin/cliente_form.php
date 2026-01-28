<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

$id = '';
$nombre = '';
$apellido = '';
$dni = '';
$email = '';
$telefono = '';
$fecha_nacimiento = '';
$genero = 'hombre'; // Default
$tipo_plan = 1;
$fecha_ingreso = date('Y-m-d'); // Default to today
$activo = 1; // Default active
$is_edit = false;
$msg = '';
$error = '';

$conn = getDB();

// Si viene ID por GET, es modo EDICIÓN
if (isset($_GET['id'])) {
    $is_edit = true;
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $nombre = $row['nombre'];
        $apellido = $row['apellido'];
        $dni = $row['dni'];
        $email = $row['email'];
        $telefono = $row['telefono'];
        $fecha_nacimiento = $row['fecha_nacimiento'];
        $genero = isset($row['genero']) ? $row['genero'] : 'hombre';
        $tipo_plan = isset($row['tipo_plan']) ? $row['tipo_plan'] : 1;
        $fecha_ingreso = isset($row['fecha_ingreso']) ? $row['fecha_ingreso'] : date('Y-m-d');
        $activo = isset($row['activo']) ? $row['activo'] : 1;
    }
    $stmt->close();
}

// Procesar Formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni = trim($_POST['dni']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $tipo_plan = intval($_POST['tipo_plan']);
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $activo = intval($_POST['activo']);
    $password = $_POST['password'];

    if (empty($nombre) || empty($apellido) || empty($dni)) {
        $error = "Nombre, Apellido y DNI son obligatorios.";
    } else {
        if ($is_edit) {
            // Update
            // Password only updated if not empty
            // Types: s(nombre), s(apellido), s(dni), s(email), s(telefono), s(fecha_nacimiento), s(genero), i(tipo_plan), s(fecha_ingreso), i(activo), s(pass), i(id)
            if (!empty($password)) {
                $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE clientes SET nombre=?, apellido=?, dni=?, email=?, telefono=?, fecha_nacimiento=?, genero=?, tipo_plan=?, fecha_ingreso=?, activo=?, password=? WHERE id=?");
                $stmt->bind_param("sssssssissii", $nombre, $apellido, $dni, $email, $telefono, $fecha_nacimiento, $genero, $tipo_plan, $fecha_ingreso, $activo, $hashed_pass, $id);
            } else {
                // Types: s(nombre), s(apellido), s(dni), s(email), s(telefono), s(fecha_nacimiento), s(genero), i(tipo_plan), s(fecha_ingreso), i(activo), i(id)
                $stmt = $conn->prepare("UPDATE clientes SET nombre=?, apellido=?, dni=?, email=?, telefono=?, fecha_nacimiento=?, genero=?, tipo_plan=?, fecha_ingreso=?, activo=? WHERE id=?");
                $stmt->bind_param("sssssssisii", $nombre, $apellido, $dni, $email, $telefono, $fecha_nacimiento, $genero, $tipo_plan, $fecha_ingreso, $activo, $id);
            }

            if ($stmt->execute()) {
                $msg = "Cliente actualizado correctamente.";
            } else {
                $error = "Error al actualizar (posible DNI duplicado). " . $conn->error;
            }
        } else {
            // Insert
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            // Types: s(nombre), s(apellido), s(dni), s(email), s(telefono), s(fecha_nacimiento), s(genero), i(tipo_plan), s(fecha_ingreso), i(activo), s(pass)
            $stmt = $conn->prepare("INSERT INTO clientes (nombre, apellido, dni, email, telefono, fecha_nacimiento, genero, tipo_plan, fecha_ingreso, activo, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssisis", $nombre, $apellido, $dni, $email, $telefono, $fecha_nacimiento, $genero, $tipo_plan, $fecha_ingreso, $activo, $hashed_pass);

            if ($stmt->execute()) {
                $msg = "Cliente creado exitosamente.";
                // Limpiar form
                $nombre = $apellido = $dni = $email = $telefono = $fecha_nacimiento = '';
                $genero = 'hombre';
                $tipo_plan = 1;
                $fecha_ingreso = date('Y-m-d');
            } else {
                $error = "Error al crear (posible DNI duplicado). " . $conn->error;
            }
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
    <title>
        <?php echo $is_edit ? 'Editar' : 'Nuevo'; ?> Cliente - Gimnasio
    </title>
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

        .form-group input,
        .form-group select {
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
            font-size: 1rem;
        }

        .btn-submit:hover {
            background-color: #d45300;
        }

        .btn-cancel {
            background-color: #6b7280;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
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
            <a href="clientes.php" style="color: #fa211b;">Clientes</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <h1>
            <?php echo $is_edit ? 'Editar datos de cliente' : 'Registrar Nuevo Cliente'; ?>
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

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido *</label>
                        <input type="text" id="apellido" name="apellido"
                            value="<?php echo htmlspecialchars($apellido); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dni">DNI / Identificación * (Usuario)</label>
                        <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($dni); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                            value="<?php echo htmlspecialchars($fecha_nacimiento); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="genero">Género</label>
                        <select id="genero" name="genero">
                            <option value="hombre" <?php echo ($genero == 'hombre') ? 'selected' : ''; ?>>Hombre</option>
                            <option value="mujer" <?php echo ($genero == 'mujer') ? 'selected' : ''; ?>>Mujer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>">
                </div>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 1.5rem 0;">

        <div class="form-row">
            <div class="form-group">
                <label for="activo">Estado</label>
                <select id="activo" name="activo">
                    <option value="1" <?php echo ($activo == 1) ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo ($activo == 0) ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tipo_plan">Tipo de Plan (Meses)</label>
                <select id="tipo_plan" name="tipo_plan">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($tipo_plan == $i) ? 'selected' : ''; ?>>
                            <?php echo $i . ' Mes' . ($i > 1 ? 'es' : ''); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="fecha_ingreso">Fecha de Ingreso</label>
                <input type="date" id="fecha_ingreso" name="fecha_ingreso"
                    value="<?php echo htmlspecialchars($fecha_ingreso); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="password">Contraseña
                <?php echo $is_edit ? '(Dejar en blanco para mantener la actual)' : '*'; ?></label>
            <input type="password" id="password" name="password" <?php echo $is_edit ? '' : 'required'; ?>>
        </div>

        <div style="margin-top: 1rem;">
            <button type="submit" class="btn-submit">
                <?php echo $is_edit ? 'Guardar Cambios' : 'Registrar Cliente'; ?>
            </button>
            <a href="clientes.php" class="btn-cancel">Cancelar</a>
        </div>
        </form>
    </div>
    </div>
</body>

</html>