<?php
session_start();
include("conexion.php");

// Verificar que el usuario ha iniciado sesión y es admin
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario_nombre = $_SESSION['usuario'];
$sql_admin = "SELECT Admin FROM usuarios WHERE nombre = ?";
$stmt_admin = $conexion->prepare($sql_admin);
$stmt_admin->bind_param("s", $usuario_nombre);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();
$usuario = $result_admin->fetch_assoc();
$es_admin = ($usuario['Admin'] == 1);

if (!$es_admin) {
    echo "Acceso denegado.";
    exit();
}

// Actualizar precio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_id'])) {
    $id = $_POST['editar_id'];
    $nuevo_precio = $_POST['nuevo_precio'];

    $sql_update = "UPDATE servicios SET precio = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql_update);
    $stmt->bind_param("di", $nuevo_precio, $id);
    $stmt->execute();
}

// Eliminar servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $id = $_POST['eliminar_id'];

    $sql_delete = "DELETE FROM servicios WHERE id = ?";
    $stmt = $conexion->prepare($sql_delete);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Añadir nuevo servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_servicio'], $_POST['nuevo_precio'])) {
    $nuevo_servicio = trim($_POST['nuevo_servicio']);
    $nuevo_precio = $_POST['nuevo_precio'];

    if ($nuevo_servicio !== '') {
        $sql_insert = "INSERT INTO servicios (servicio, precio) VALUES (?, ?)";
        $stmt = $conexion->prepare($sql_insert);
        $stmt->bind_param("sd", $nuevo_servicio, $nuevo_precio);
        $stmt->execute();
    }
}

// Obtener lista actualizada de servicios
$resultado = $conexion->query("SELECT * FROM servicios ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Servicios</title>
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
        /* Posición del botón cerrar sesión */
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 999;
        }
    </style>
</head>
<body>

<!-- Botón cerrar sesión -->
<div class="logout-btn">
    <a href="logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
</div>

<div class="container">
    <h2 class="mb-4">Modificar Servicios</h2>

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
            <?php while ($servicio = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $servicio['id']; ?></td>
                    <td><?php echo htmlspecialchars($servicio['servicio']); ?></td>
                    <td>
                        <form method="POST" class="d-flex">
                            <input type="hidden" name="editar_id" value="<?php echo $servicio['id']; ?>">
                            <input type="number" step="0.01" name="nuevo_precio" value="<?php echo $servicio['precio']; ?>" class="form-control me-2">
                            <button type="submit" class="btn btn-success btn-sm">Actualizar</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="eliminar_id" value="<?php echo $servicio['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

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
