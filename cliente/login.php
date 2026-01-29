<?php
session_start();
require_once '../admin/config.php';

// Si ya está logueado, ir al dashboard
if (isset($_SESSION['cliente_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni']);
    $password = $_POST['password'];

    if (empty($dni) || empty($password)) {
        $error = "Por favor ingrese su DNI y contraseña.";
    } else {
        $conn = getDB();
        $stmt = $conn->prepare("SELECT id, nombre, apellido, password FROM clientes WHERE dni = ? AND activo = 1");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $nombre, $apellido, $hashed_password);
            $stmt->fetch();

            // Verificar contraseña
            if ($hashed_password && password_verify($password, $hashed_password)) {
                $_SESSION['cliente_id'] = $id;
                $_SESSION['cliente_nombre'] = $nombre . ' ' . $apellido;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Contraseña incorrecta o usuario no configurado.";
            }
        } else {
            $error = "Cliente no encontrado (o inactivo).";
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Clientes - Gimnasio</title>
    <style>
        body.login-body {
            font-family: sans-serif;
            background-image: linear-gradient(to bottom, #0a0a0a, #fa211b );
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-card h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
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

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background-color: #fa211b;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background-color: #21741aff;
        }

        .error-msg {
            color: #dc2626;
            text-align: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
        }
    </style>
</head>

<body class="login-body">
    <div class="login-card">
        <a href="../index.html"><img src="../img/logo.png" alt="Logo" style="margin-left:auto; margin-right:auto; display:block"></a>
        <h2>Acceso Clientes</h2>
        <?php if ($error): ?>
            <div class="error-msg">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="dni">DNI / Identificación</label>
                <input type="text" id="dni" name="dni" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
        <a href="../index.html" class="back-link">Volver al sitio</a>
    </div>
</body>

</html>