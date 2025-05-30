<?php
// Iniciar sesión para poder acceder a $_SESSION
session_start();

// Incluir el archivo de conexión a la base de datos
include("conexion.php");

// Verificar si el usuario ha iniciado sesión, si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener el nombre de usuario desde la sesión
$usuario_nombre = $_SESSION['usuario'];

// Preparar la consulta para obtener id y admin del usuario con nombre dado
$sql_usuario = "SELECT id, Admin FROM usuarios WHERE nombre = ?";
$stmt_usuario = mysqli_prepare($conexion, $sql_usuario);

// Vincular el parámetro de la consulta (nombre de usuario)
mysqli_stmt_bind_param($stmt_usuario, "s", $usuario_nombre);

// Ejecutar la consulta
mysqli_stmt_execute($stmt_usuario);

// Obtener el resultado de la consulta
$resultado_usuario = mysqli_stmt_get_result($stmt_usuario);

// Verificar si el usuario existe en la base de datos
if (mysqli_num_rows($resultado_usuario) === 0) {
    // Si no se encuentra usuario, terminar script con mensaje de error
    die("Usuario no encontrado");
}

// Extraer datos del usuario en un array asociativo
$usuario = mysqli_fetch_assoc($resultado_usuario);

// Guardar el id del usuario y si es admin (1) o no (0)
$usuario_id = $usuario['id'];
$es_admin = ($usuario['Admin'] == 1);

// Inicializar variables para ordenar resultados
$ordenar_por = '';
$orden_dir = 'ASC';

// Si el usuario es admin y existe parámetro GET para ordenar
if ($es_admin && isset($_GET['ordenar_por'])) {
    // Guardar campo por el que ordenar
    $ordenar_por = $_GET['ordenar_por'];

    // Guardar dirección del orden (ASC o DESC), por defecto ASC
    $orden_dir = (isset($_GET['orden_dir']) && strtoupper($_GET['orden_dir']) === 'DESC') ? 'DESC' : 'ASC';

    // Validar que el campo ordenar_por es uno de los permitidos
    $campos_validos = ['precio_estimado', 'fecha', 'metros', 'nombre'];

    if (!in_array($ordenar_por, $campos_validos)) {
        // Si no es válido, no ordenar
        $ordenar_por = '';
    }
}

// Construir la consulta SQL según si es admin o no
if ($es_admin) {
    // Consulta para admin, incluye nombre de usuario y permite ordenar
    $sql = "SELECT p.servicio_presupuestado, p.metros, p.precio_estimado, p.fecha, u.nombre 
            FROM presupuestos p 
            JOIN usuarios u ON p.usuario_presupuesto_id = u.id";

    // Añadir cláusula ORDER BY si hay campo válido para ordenar
    if ($ordenar_por !== '') {
        // Se añade el orden dinámicamente
        $sql .= " ORDER BY $ordenar_por $orden_dir";
    } else {
        // Por defecto ordenar por fecha descendente
        $sql .= " ORDER BY p.fecha DESC";
    }

    // Preparar la consulta para admin
    $stmt = mysqli_prepare($conexion, $sql);
} else {
    // Consulta para usuario normal: solo sus presupuestos, ordenados por fecha descendente
    $sql = "SELECT servicio_presupuestado, metros, precio_estimado, fecha 
            FROM presupuestos 
            WHERE usuario_presupuesto_id = ? 
            ORDER BY fecha DESC";

    // Preparar la consulta para usuario normal
    $stmt = mysqli_prepare($conexion, $sql);

    // Vincular parámetro usuario_id para filtrar solo sus presupuestos
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
}

// Ejecutar la consulta
mysqli_stmt_execute($stmt);

// Obtener resultado de la consulta
$resultado = mysqli_stmt_get_result($stmt);
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

<!-- Botón cerrar sesión en la esquina superior derecha -->
<div style="position: absolute; top: 20px; right: 20px; z-index: 999;">
    <?php if (isset($_SESSION['usuario'])): ?>
        <a href="formulario/logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
    <?php endif; ?>
</div>

<div class="container">
    <!-- Título que varía según si es admin o no -->
    <h2 class="mb-4"><?php echo $es_admin ? 'Todos los Presupuestos' : 'Mis Presupuestos'; ?></h2>

    <!-- Formulario para filtro y orden si es admin -->
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

    <!-- Tabla para mostrar los presupuestos -->
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
            <?php 
            // Recorrer resultados y mostrar cada fila en la tabla
            while ($row = mysqli_fetch_assoc($resultado)): ?>
                <tr>
                    <?php if ($es_admin): ?>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($row['servicio_presupuestado']); ?></td>
                    <td><?php echo htmlspecialchars($row['metros']); ?></td>
                    <td><?php echo number_format($row['precio_estimado'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                </tr>
            <?php endwhile; ?>

            <?php 
            // Si no hay resultados mostrar mensaje
            if (mysqli_num_rows($resultado) === 0): ?>
                <tr>
                    <td colspan="<?php echo $es_admin ? 5 : 4; ?>" class="text-center">No hay presupuestos para mostrar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
