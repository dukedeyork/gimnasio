<?php
session_start();
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../admin/config.php';

$conn = getDB();
$id = $_SESSION['cliente_id'];

// Obtener datos actualizados del cliente
$stmt = $conn->prepare("SELECT nombre, apellido, dni, fecha_ingreso, tipo_plan FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$cliente = $res->fetch_assoc();
$stmt->close();

if (!$cliente) {
    // Si no existe (borrado?), salir
    session_destroy();
    header("Location: login.php");
    exit;
}

// Lógica de días usados
$fecha_ingreso = $cliente['fecha_ingreso'];
$dias_usados = 0;
if ($fecha_ingreso) {
    $start = new DateTime($fecha_ingreso);
    $end = new DateTime();
    $diff = $start->diff($end);
    $dias_usados = $diff->days;
    // Si fecha ingreso es futuro, son 0 dias (o negativo? mejor poner 0)
    if ($start > $end)
        $dias_usados = 0;
}

// Cambio de contraseña
$msg = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (empty($new_pass)) {
        $error = "La contraseña no puede estar vacía.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE clientes SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $id);
        if ($stmt->execute()) {
            $msg = "Contraseña actualizada correctamente.";
        } else {
            $error = "Error al actualizar.";
        }
        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Gimnasio</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            color: #333;
        }

        .nav {
            background: #111;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .nav .brand {
            font-size: 1.25rem;
            font-weight: bold;
            color: #fa211b;
        }

        .nav a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
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
            margin-bottom: 2rem;
        }

        .h2-title {
            border-bottom: 2px solid #fa211b;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #eee;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #fa211b;
            margin: 0.5rem 0;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
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

        .alert-success {
            color: green;
            margin-bottom: 1rem;
        }

        .alert-error {
            color: red;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <nav class="nav">
        <div class="brand">Gimnasio - Mi Cuenta</div>
        <div>
            <span style="text-transform: uppercase;">Hola,
                <?php echo htmlspecialchars($cliente['nombre']); ?>
            </span>
            <a href="logout.php" style="text-transform: uppercase;">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">

        <div class="card">
            <h2 class="h2-title">Estado de Membresía</h2>

            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-label">Plan Seleccionado</div>
                    <div class="stat-value">
                        <?php echo $cliente['tipo_plan']; ?> Mes(es)
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Fecha de Ingreso</div>
                    <div class="stat-value" style="font-size: 1.25rem; line-height: 2.5rem;">
                        <?php echo $fecha_ingreso ? date('d/m/Y', strtotime($fecha_ingreso)) : 'N/A'; ?>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Días Transcurridos</div>
                    <div class="stat-value">
                        <?php echo $dias_usados; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $stmt = $conn->prepare("SELECT cr.dia_entrenamiento, r.nombre_ejercicio, r.musculos, r.peso_sugerido, r.espacio_maquina, r.series, r.repeticiones 
                                FROM cliente_rutinas cr 
                                JOIN rutinas r ON cr.rutina_id = r.id 
                                WHERE cr.cliente_id = ? 
                                ORDER BY cr.dia_entrenamiento ASC, cr.fecha_asignacion ASC");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res_rutinas = $stmt->get_result();

        $rutinas_por_dia = [];
        if ($res_rutinas->num_rows > 0) {
            while ($r = $res_rutinas->fetch_assoc()) {
                $rutinas_por_dia[$r['dia_entrenamiento']][] = $r;
            }
        }
        $stmt->close();
        ?>

        <div class="card">
            <h2 class="h2-title">Mi Rutina Asignada</h2>
            <?php if (!empty($rutinas_por_dia)): ?>
                <?php foreach ($rutinas_por_dia as $dia => $ejercicios): ?>
                    <h3
                        style="background: #fa211b; color: white; padding: 0.5rem 1rem; border-radius: 4px; margin-top: 1.5rem; display: inline-block;">
                        Día <?php echo $dia; ?>
                    </h3>
                    <div style="overflow-x:auto; margin-bottom: 1rem;">
                        <table style="width:100%; border-collapse: collapse; text-align: left; margin-top: 0.5rem;">
                            <thead>
                                <tr style="background:#f9f9f9; border-bottom:1px solid #eee;">
                                    <th style="padding:0.75rem; width: 50px; text-align: center;">Estado</th>
                                    <th style="padding:0.75rem;">Ejercicio</th>
                                    <th style="padding:0.75rem;">Series / Reps</th>
                                    <th style="padding:0.75rem;">Peso</th>
                                    <th style="padding:0.75rem;">Máquina/Espacio</th>
                                    <th style="padding:0.75rem;">Músculos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ejercicios as $r):
                                    // ID único para persistencia local: dia + rutina + params
                                    $chk_id = 'chk_d' . $dia . '_' . md5($r['nombre_ejercicio'] . $r['series'] . $r['repeticiones']);
                                    ?>
                                    <tr style="border-bottom:1px solid #eee;">
                                        <td style="padding:0.75rem; text-align:center;">
                                            <input type="checkbox" id="<?php echo $chk_id; ?>" class="routine-check"
                                                style="transform: scale(1.5); cursor: pointer; accent-color: #fa211b;">
                                        </td>
                                        <td style="padding:0.75rem; font-weight:bold; color:#fa211b;">
                                            <?php echo htmlspecialchars($r['nombre_ejercicio']); ?>
                                        </td>
                                        <td style="padding:0.75rem;"><?php echo $r['series']; ?> x <?php echo $r['repeticiones']; ?>
                                        </td>
                                        <td style="padding:0.75rem;"><?php echo htmlspecialchars($r['peso_sugerido']); ?></td>
                                        <td style="padding:0.75rem;"><?php echo htmlspecialchars($r['espacio_maquina']); ?></td>
                                        <td style="padding:0.75rem; font-size:0.9rem; color:#666;">
                                            <?php echo htmlspecialchars($r['musculos']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#666;">Aún no tienes rutinas asignadas. Habla con tu instructor.</p>
            <?php endif; ?>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const checkboxes = document.querySelectorAll('.routine-check');
                const today = new Date().toISOString().split('T')[0];
                const storageKeyPrefix = 'gym_routine_check_' + <?php echo $id; ?> + '_' + today + '_';

                // Cargar estado guardado
                checkboxes.forEach(chk => {
                    const savedState = localStorage.getItem(storageKeyPrefix + chk.id);
                    if (savedState === 'checked') {
                        chk.checked = true;
                        highlightRow(chk);
                    }

                    chk.addEventListener('change', function () {
                        if (this.checked) {
                            localStorage.setItem(storageKeyPrefix + this.id, 'checked');
                            highlightRow(this);
                        } else {
                            localStorage.removeItem(storageKeyPrefix + this.id);
                            unhighlightRow(this);
                        }
                    });
                });

                function highlightRow(checkbox) {
                    const row = checkbox.closest('tr');
                    row.style.backgroundColor = '#d1fae5'; // Verde suave
                    row.style.opacity = '0.7';
                }

                function unhighlightRow(checkbox) {
                    const row = checkbox.closest('tr');
                    row.style.backgroundColor = '';
                    row.style.opacity = '1';
                }
            });
        </script>

        <div class="card">
            <h2 class="h2-title">Seguridad</h2>
            <p>Puedes cambiar tu contraseña de acceso aquí.</p>

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
                    <label>Nueva Contraseña</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirmar Nueva Contraseña</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-submit">Actualizar Contraseña</button>
            </form>
        </div>

    </div>
    <?php $conn->close(); ?>
</body>

</html>