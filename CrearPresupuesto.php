<?php
session_start(); // Inicia la sesión
include("conexion.php"); 

// Muestra errores (para desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Redirige a login si no hay sesión iniciada
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Guardamos el nombre del usuario en una variable
$usuario_nombre = $_SESSION['usuario'];
$mensaje = ""; // Inicializamos mensaje vacío

// Consulta para obtener ID y si es Admin
$sql_usuario = "SELECT id, Admin FROM usuarios WHERE nombre = '" . mysqli_real_escape_string($conexion, $usuario_nombre) . "'";
$resultado_usuario = mysqli_query($conexion, $sql_usuario);

// Si no se encuentra el usuario, se termina la ejecución
if (mysqli_num_rows($resultado_usuario) === 0) {
    die("Usuario no encontrado");
}

// Obtenemos los datos como array 
$usuario = mysqli_fetch_assoc($resultado_usuario);
$usuario_id = $usuario['id'];
$es_admin = $usuario['Admin'] == 1; // Lo pasamos a booleano

// Consultamos los servicios y sus precios
$sql_servicios = "SELECT servicio, precio FROM servicios";
$result_servicios = mysqli_query($conexion, $sql_servicios);

// Creamos un array de precios
$precios = [];
while ($row = mysqli_fetch_assoc($result_servicios)) {
    $precios[$row['servicio']] = $row['precio'];
}

// Si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recogemos los datos del formulario
    $servicio = $_POST['servicio'];
    $metros = floatval($_POST['metros']);
    $fecha = $_POST['fecha'];

    // Validamos el servicio
    if (!isset($precios[$servicio])) {
        $mensaje = "<div class='alert alert-danger mt-3'>Servicio no válido.</div>";
    } elseif ($metros <= 0) {
        $mensaje = "<div class='alert alert-danger mt-3'>Los metros deben ser un número positivo.</div>";
    } else {
        // Calculamos el precio
        $precio_base = $precios[$servicio] * $metros;
        $precio_estimado = $precio_base;
        $metros_int = intval($metros); // Convertimos a entero

        // Preparamos los valores escapados para la consulta
        $usuario_id = intval($usuario_id);
        $usuario_nombre_esc = mysqli_real_escape_string($conexion, $usuario_nombre);
        $servicio_esc = mysqli_real_escape_string($conexion, $servicio);
        $fecha_esc = mysqli_real_escape_string($conexion, $fecha);

        // Creamos la consulta de inserción
        $sql_insert = "
            INSERT INTO presupuestos 
            (usuario_presupuesto_id, usuario_presupuesto_nombre, servicio_presupuestado, metros, precio_estimado, fecha)
            VALUES 
            ($usuario_id, '$usuario_nombre_esc', '$servicio_esc', $metros_int, $precio_estimado, '$fecha_esc')
        ";

        // Ejecutamos la consulta
        if (mysqli_query($conexion, $sql_insert)) {
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
        /* Estilos generales */
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

<!-- Botón de cerrar sesión -->
<div class="logout-btn">
    <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" class="btn btn-danger btn-sm">Cerrar sesión</button>
    </form>
</div>

<div class="container">
    <div class="form-container">
        <h2 class="text-center mb-4">Crear Presupuesto</h2>

        <!-- Mensaje de éxito o error -->
        <?php echo $mensaje; ?>

        <!-- Formulario para crear presupuesto -->
        <form method="post" action="">
            <!-- Selección de servicio -->
            <div class="mb-3">
                <label for="servicio" class="form-label">Tipo de servicio:</label>
                <select name="servicio" id="servicio" class="form-select" required>
                    <?php
                    // Vuelve a hacer una consulta para sacar los servicios y muestra todos los que haya en la base de datos
                    $result_servicios = $conexion->query("SELECT servicio, precio FROM servicios");
                    while ($row = $result_servicios->fetch_assoc()) {
                        $servicio_nombre = htmlspecialchars($row['servicio']);
                        $precio_metro = number_format($row['precio'], 2);
                        echo "<option value=\"$servicio_nombre\">$servicio_nombre ({$precio_metro}€/m²)</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Entrada de metros cuadrados -->
            <div class="mb-3">
                <label for="metros" class="form-label">Metros cuadrados:</label>
                <input type="number" name="metros" id="metros" class="form-control" step="0.1" min="0" required>
            </div>

            <!-- Selección de fecha -->
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha del trabajo:</label>
                <input type="date" name="fecha" id="fecha" class="form-control" required>
            </div>

            <!-- Botón de envío -->
            <button type="submit" class="btn w-100">Calcular y guardar presupuesto</button>
        </form>

        <!-- Aviso para saber que estamos en el modo admin -->
        <?php if ($es_admin): ?>
            <div class="mt-4 text-center text-warning">
                <strong>Modo administrador activado</strong>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
