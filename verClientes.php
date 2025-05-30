<?php
// Iniciar sesión para acceder a variables de sesión
session_start();

// Incluir archivo de conexión a la base de datos
include("conexion.php");

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    // Si no está logueado, redirigir a login
    header("Location: login.php");
    exit();
}

// Guardar el nombre del usuario logueado
$usuario_nombre = $_SESSION['usuario'];

// Preparar consulta para saber si el usuario es admin
$sql = "SELECT Admin FROM usuarios WHERE nombre = ?";

// Preparar la consulta para evitar inyección SQL
$stmt = mysqli_prepare($conexion, $sql);

// Vincular el parámetro del nombre de usuario
mysqli_stmt_bind_param($stmt, "s", $usuario_nombre);

// Ejecutar la consulta
mysqli_stmt_execute($stmt);

// Vincular el resultado a una variable
mysqli_stmt_bind_result($stmt, $admin_valor);

// Obtener el valor de Admin
mysqli_stmt_fetch($stmt);

// Cerrar la consulta preparada
mysqli_stmt_close($stmt);

// Comprobar si el usuario es admin (Admin == 1)
$es_admin = ($admin_valor == 1);

// Si no es admin, denegar acceso y salir
if (!$es_admin) {
    echo "Acceso denegado.";
    exit();
}

// Consulta para obtener usuarios con el conteo de presupuestos
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

// Ejecutar la consulta para obtener clientes y presupuestos
$result_clientes = mysqli_query($conexion, $sql_clientes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Clientes</title>
    <!-- Incluir Bootstrap para estilos -->
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
            <?php
            // Verificar que la consulta devolvió resultados
            if ($result_clientes && mysqli_num_rows($result_clientes) > 0) {
                // Recorrer cada fila con los datos de clientes
                while ($cliente = mysqli_fetch_assoc($result_clientes)) {
                    // Mostrar los datos de cada cliente en la tabla
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($cliente['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($cliente['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($cliente['email']) . "</td>";
                    echo "<td>" . $cliente['total_presupuestos'] . "</td>";
                    echo "</tr>";
                }
            } else {
                // Si no hay clientes con presupuestos, mostrar mensaje
                echo '<tr><td colspan="4" class="text-center">No se encontraron clientes con presupuestos.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
