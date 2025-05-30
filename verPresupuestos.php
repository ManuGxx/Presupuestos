<?php
session_start();
include("conexion.php");

// Redirigir si no ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario_nombre = $_SESSION['usuario'];

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

// Variables para ordenar (solo para admin)
$ordenar_por = '';
$orden_dir = 'ASC';

if ($es_admin && isset($_GET['ordenar_por'])) {
    $ordenar_por = $_GET['ordenar_por'];
    $orden_dir = (isset($_GET['orden_dir']) && strtoupper($_GET['orden_dir']) === 'DESC') ? 'DESC' : 'ASC';

    // Validar campo ordenar_por
    $campos_validos = ['precio_estimado', 'fecha', 'metros', 'nombre'];
    if (!in_array($ordenar_por, $campos_validos)) {
        $ordenar_por = '';
    }
}

// Construir consulta
if ($es_admin) {
    $sql = "SELECT p.servicio_presupuestado, p.metros, p.precio_estimado, p.fecha, u.nombre 
            FROM presupuestos p 
            JOIN usuarios u ON p.usuario_presupuesto_id = u.id";
    if ($ordenar_por !== '') {
        $sql .= " ORDER BY $ordenar_por $orden_dir";
    } else {
        $sql .= " ORDER BY p.fecha DESC";
    }
    $stmt = $conexion->prepare($sql);
} else {
    $sql = "SELECT servicio_presupuestado, metros, precio_estimado, fecha 
            FROM presupuestos 
            WHERE usuario_presupuesto_id = ? 
            ORDER BY fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
}

$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Presupuestos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            color: #000;
            padding-top: 40px;
        }
        .container {
            max-width: 960px;
        }
        table {
            background-color: #111;
            color: #fff;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        .filtro-form {
            margin-bottom: 20px;
        }
        /* Estilo botón rojo personalizado */
        .btn-danger {
            background-color: #d32f2f !important;
            border-color: #b71c1c !important;
            color: #fff !important;
            transition: background-color 0.3s ease;
        }
        .btn-danger:hover {
            background-color: #b71c1c !important;
            border-color: #7f0000 !important;
            color: #fff !important;
        }
    </style>
</head>
<body>

<div style="position: absolute; top: 20px; right: 20px; z-index: 999;">
    <?php if (isset($_SESSION['usuario'])): ?>
        <a href="formulario/logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
    <?php endif; ?>
</div>

<div class="container">
    <h2 class="mb-4"><?php echo $es_admin ? 'Todos los Presupuestos' : 'Mis Presupuestos'; ?></h2>

    <?php if ($es_admin): ?>
        <form method="GET" class="filtro-form row g-3 align-items-center">
            <div class="col-auto">
                <label for="ordenar_por" class="col-form-label">Ordenar por:</label>
            </div>
            <div class="col-auto">
                <select name="ordenar_por" id="ordenar_por" class="form-select">
                    <option value="" <?php if ($ordenar_por == '') echo 'selected'; ?>>Sin ordenar</option>
                    <option value="precio_estimado" <?php if ($ordenar_por == 'precio_estimado') echo 'selected'; ?>>Precio</option>
                    <option value="fecha" <?php if ($ordenar_por == 'fecha') echo 'selected'; ?>>Fecha</option>
                    <option value="metros" <?php if ($ordenar_por == 'metros') echo 'selected'; ?>>Metros</option>
                    <option value="nombre" <?php if ($ordenar_por == 'nombre') echo 'selected'; ?>>Nombre</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="orden_dir" id="orden_dir" class="form-select">
                    <option value="ASC" <?php if ($orden_dir == 'ASC') echo 'selected'; ?>>Ascendente</option>
                    <option value="DESC" <?php if ($orden_dir == 'DESC') echo 'selected'; ?>>Descendente</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Aplicar</button>
            </div>
        </form>
    <?php endif; ?>

    <table class="table table-striped table-dark">
        <thead>
            <tr>
                <?php if ($es_admin): ?><th>Nombre</th><?php endif; ?>
                <th>Servicio</th>
                <th>Metros</th>
                <th>Precio Estimado (€)</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                    <?php if ($es_admin): ?><td><?php echo htmlspecialchars($row['nombre']); ?></td><?php endif; ?>
                    <td><?php echo htmlspecialchars($row['servicio_presupuestado']); ?></td>
                    <td><?php echo htmlspecialchars($row['metros']); ?></td>
                    <td><?php echo number_format($row['precio_estimado'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($resultado->num_rows === 0): ?>
                <tr>
                    <td colspan="<?php echo $es_admin ? 5 : 4; ?>" class="text-center">No hay presupuestos para mostrar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
