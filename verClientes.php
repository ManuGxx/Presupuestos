<?php
session_start();
include("conexion.php");

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Comprobar si el usuario es admin para acceso restringido opcional (puedes quitar esta parte si quieres que cualquier usuario vea la lista)
$usuario_nombre = $_SESSION['usuario'];
$sql = "SELECT Admin FROM usuarios WHERE nombre = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario_nombre);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$es_admin = ($usuario['Admin'] == 1);

if (!$es_admin) {
    echo "Acceso denegado.";
    exit();
}

// Consulta para obtener usuarios que tienen presupuestos y la cuenta de presupuestos por usuario
$sql_clientes = "
    SELECT 
        u.id, 
        u.nombre, 
        u.email, 
        COUNT(p.id_presupuesto) AS total_presupuestos
    FROM usuarios u
    LEFT JOIN presupuestos p ON u.id = p.usuario_presupuesto_id
    GROUP BY u.id, u.nombre, u.email
    ORDER BY total_presupuestos DESC, u.nombre ASC
";

$result_clientes = $conexion->query($sql_clientes);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Clientes</title>
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
        h2 {
            margin-bottom: 30px;
            text-align: center;
        }
        table {
            background-color: #111;
            color: #fff;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        thead {
            background-color: #222;
        }
        tbody tr:hover {
            background-color: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Clientes y Número de Presupuestos</h2>

    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>ID Cliente</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Presupuestos realizados</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_clientes && $result_clientes->num_rows > 0): ?>
                <?php while ($cliente = $result_clientes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                        <td><?php echo $cliente['total_presupuestos']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No se encontraron clientes con presupuestos.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
