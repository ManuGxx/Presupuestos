<?php
session_start();
include("conexion.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Redirigir si no ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario_nombre = $_SESSION['usuario'];
$mensaje = "";

// Obtener datos del usuario
$sql_usuario = "SELECT id, Admin FROM usuarios WHERE nombre = ?";
$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("s", $usuario_nombre);
$stmt_usuario->execute();
$resultado_usuario = $stmt_usuario->get_result();

if ($resultado_usuario->num_rows === 0) {
    die("Usuario no encontrado");
}

$usuario = $resultado_usuario->fetch_assoc();
$usuario_id = $usuario['id'];
$es_admin = $usuario['Admin'] == 1;

// Obtener servicios y precios
$result_servicios = $conexion->query("SELECT servicio, precio FROM servicios");
$precios = [];
while ($row = $result_servicios->fetch_assoc()) {
    $precios[$row['servicio']] = $row['precio'];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $servicio = $_POST['servicio'];
    $metros = floatval($_POST['metros']);
    $fecha = $_POST['fecha'];

    if (!isset($precios[$servicio])) {
        $mensaje = "<div class='alert alert-danger mt-3'>Servicio no válido.</div>";
    } elseif ($metros <= 0) {
        $mensaje = "<div class='alert alert-danger mt-3'>Los metros deben ser un número positivo.</div>";
    } else {
        $precio_base = $precios[$servicio] * $metros;

        // Variables para bind_param (deben ser variables)
        $precio_estimado = $precio_base;
        $metros_int = intval($metros);

        // Insertar en base de datos con nombres reales de columnas
        $stmt = $conexion->prepare("INSERT INTO presupuestos (usuario_presupuesto_id, usuario_presupuesto_nombre, servicio_presupuestado, metros, precio_estimado, fecha) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issids", $usuario_id, $usuario_nombre, $servicio, $metros_int, $precio_estimado, $fecha);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert alert-success mt-3'>Presupuesto creado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger mt-3'>Error al guardar el presupuesto.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Presupuesto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            color: #000;
        }
        .form-container {
            max-width: 600px;
            margin: 80px auto;
            background-color: #111;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #555;
            color: #fff;
        }
        .form-control, .form-select {
            background-color: #222;
            color: #fff;
            border: 1px solid #444;
        }
        .form-control:focus, .form-select:focus {
            background-color: #111;
            color: #fff;
            border-color: #777;
            box-shadow: none;
        }
        .btn {
            background-color: #f8f9fa;
            color: #000;
            border: 1px solid #ccc;
        }
        .btn:hover {
            background-color: #e0e0e0;
        }
        /* Botón cerrar sesión en esquina superior derecha */
        .logout-btn {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 1000;
        }
        .logout-btn .btn-danger {
    background-color: #d32f2f;
    border-color: #b71c1c;
    color: #fff;
    transition: background-color 0.3s ease;
}

.logout-btn .btn-danger:hover {
    background-color: #b71c1c;
    border-color: #7f0000;
    color: #fff;
}

    </style>
</head>
<body>

<!-- Botón Cerrar sesión -->
<div class="logout-btn">
    <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" class="btn btn-danger btn-sm">Cerrar sesión</button>
    </form>
</div>

<div class="container">
    <div class="form-container">
        <h2 class="text-center mb-4">Crear Presupuesto</h2>

        <?php echo $mensaje; ?>

        <form method="post" action="">
            <div class="mb-3">
                <label for="servicio" class="form-label">Tipo de servicio:</label>
                <select name="servicio" id="servicio" class="form-select" required>
                    <?php
                    // Cargar servicios y precio desde la tabla servicios
                    $result_servicios = $conexion->query("SELECT servicio, precio FROM servicios");
                    while ($row = $result_servicios->fetch_assoc()) {
                        $servicio_nombre = htmlspecialchars($row['servicio']);
                        $precio_metro = number_format($row['precio'], 2);
                        echo "<option value=\"$servicio_nombre\">$servicio_nombre ({$precio_metro}€/m²)</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="metros" class="form-label">Metros cuadrados:</label>
                <input type="number" name="metros" id="metros" class="form-control" step="0.1" min="0" required>
            </div>

            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha del trabajo:</label>
                <input type="date" name="fecha" id="fecha" class="form-control" required>
            </div>

            <button type="submit" class="btn w-100">Calcular y guardar presupuesto</button>
        </form>

        <?php if ($es_admin): ?>
            <div class="mt-4 text-center text-warning">
                <strong>Modo administrador activado</strong>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
