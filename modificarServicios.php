<?php
session_start(); // Iniciar sesión para usar variables de sesión
include("conexion.php"); // Incluir archivo con conexión a la base de datos

// Comprobar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no está logueado
    exit(); // Terminar script para evitar que se siga ejecutando
}

$usuario_nombre = $_SESSION['usuario'];

// Preparar consulta para obtener el campo Admin del usuario actual
$sql_admin = "SELECT Admin FROM usuarios WHERE nombre = ?";
$stmt_admin = mysqli_prepare($conexion, $sql_admin); // Preparar la consulta SQL
mysqli_stmt_bind_param($stmt_admin, "s", $usuario_nombre); // Vincular el parámetro (nombre)
mysqli_stmt_execute($stmt_admin); // Ejecutar la consulta
mysqli_stmt_bind_result($stmt_admin, $admin); // Asociar variable para resultado
mysqli_stmt_fetch($stmt_admin); // Obtener resultado
mysqli_stmt_close($stmt_admin); // Cerrar la sentencia

// Verificar si el usuario es administrador
$es_admin = ($admin == 1);

if (!$es_admin) {
    // Si no es admin, denegar acceso y detener ejecución
    echo "Acceso denegado.";
    exit();
}

// ACTUALIZAR PRECIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_id'])) {
    $id = $_POST['editar_id']; // ID del servicio a actualizar
    $nuevo_precio = $_POST['nuevo_precio']; // Nuevo precio para actualizar

    // Preparar consulta para actualizar el precio
    $sql_update = "UPDATE servicios SET precio = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql_update); // Preparar la consulta
    mysqli_stmt_bind_param($stmt, "di", $nuevo_precio, $id); // Vincular parámetros (double, int)
    mysqli_stmt_execute($stmt); // Ejecutar actualización
    mysqli_stmt_close($stmt); // Cerrar sentencia
}

// ELIMINAR SERVICIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $id = $_POST['eliminar_id']; // ID del servicio a eliminar

    // Preparar consulta para eliminar el servicio
    $sql_delete = "DELETE FROM servicios WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql_delete); // Preparar consulta
    mysqli_stmt_bind_param($stmt, "i", $id); // Vincular parámetro (int)
    mysqli_stmt_execute($stmt); // Ejecutar eliminación
    mysqli_stmt_close($stmt); // Cerrar sentencia
}

// AÑADIR NUEVO SERVICIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_servicio'], $_POST['nuevo_precio'])) {
    $nuevo_servicio = trim($_POST['nuevo_servicio']); // Limpiar espacios del nombre del servicio
    $nuevo_precio = $_POST['nuevo_precio']; // Precio nuevo

    if ($nuevo_servicio !== '') { // Validar que el nombre no esté vacío
        // Preparar consulta para insertar nuevo servicio
        $sql_insert = "INSERT INTO servicios (servicio, precio) VALUES (?, ?)";
        $stmt = mysqli_prepare($conexion, $sql_insert); // Preparar consulta
        mysqli_stmt_bind_param($stmt, "sd", $nuevo_servicio, $nuevo_precio); // Vincular parámetros (string, double)
        mysqli_stmt_execute($stmt); // Ejecutar inserción
        mysqli_stmt_close($stmt); // Cerrar sentencia
    }
}

// Obtener lista actualizada de servicios para mostrar en la tabla
$resultado = mysqli_query($conexion, "SELECT * FROM servicios ORDER BY id ASC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Servicios</title>
    <!-- Bootstrap CSS para estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            color: #000;
            padding: 40px;
        }
        .container {
            max-width: 900px;
        }
        table {
            background-color: #111;
            color: #fff;
        }
        th, td {
            padding: 10px;
        }
        input[type="number"], input[type="text"] {
            width: 100%;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 999;
        }
    </style>
</head>
<body>

<!-- Botón para cerrar sesión -->
<div class="logout-btn">
    <a href="logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
</div>

<div class="container">
    <h2 class="mb-4">Modificar Servicios</h2>

    <!-- Tabla para mostrar servicios -->
    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Servicio</th>
                <th>Precio (€)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($servicio = mysqli_fetch_assoc($resultado)): ?>
                <tr>
                    <td><?php echo $servicio['id']; ?></td>
                    <td><?php echo htmlspecialchars($servicio['servicio']); ?></td>
                    <td>
                        <!-- Formulario para actualizar precio -->
                        <form method="POST" class="d-flex">
                            <input type="hidden" name="editar_id" value="<?php echo $servicio['id']; ?>">
                            <input type="number" step="0.01" name="nuevo_precio" value="<?php echo $servicio['precio']; ?>" class="form-control me-2">
                            <button type="submit" class="btn btn-success btn-sm">Actualizar</button>
                        </form>
                    </td>
                    <td>
                        <!-- Formulario para eliminar servicio -->
                        <form method="POST">
                            <input type="hidden" name="eliminar_id" value="<?php echo $servicio['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Formulario para añadir nuevo servicio -->
    <h4 class="mt-4">Añadir Nuevo Servicio</h4>
    <form method="POST" class="row g-3 mt-2">
        <div class="col-md-6">
            <input type="text" name="nuevo_servicio" placeholder="Nombre del servicio" class="form-control" required>
        </div>
        <div class="col-md-4">
            <input type="number" step="0.01" name="nuevo_precio" placeholder="Precio (€)" class="form-control" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Añadir</button>
        </div>
    </form>
</div>

</body>
</html>
