<?php
// Iniciar la sesión para usar variables de sesión
session_start();

// Incluir el archivo de conexión a la base de datos
include("conexion.php");

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión, redirigir a la página de login
    header("Location: login.php");
    exit();
}

// Guardar el nombre del usuario que está logueado
$usuario_nombre = $_SESSION['usuario'];

// Preparar la consulta SQL para obtener el campo Admin del usuario actual
$sql = "SELECT Admin FROM usuarios WHERE nombre = ?";

// Preparar la consulta para evitar inyección SQL
$stmt = mysqli_prepare($conexion, $sql);

// Vincular el parámetro (nombre del usuario) a la consulta preparada
mysqli_stmt_bind_param($stmt, "s", $usuario_nombre);

// Ejecutar la consulta
mysqli_stmt_execute($stmt);

// Obtener el resultado de la consulta
mysqli_stmt_bind_result($stmt, $admin_valor);

// Obtener el valor de Admin (0 o 1)
mysqli_stmt_fetch($stmt);

// Cerrar la consulta preparada
mysqli_stmt_close($stmt);

// Determinar si el usuario es admin (true si Admin == 1, false si no)
$es_admin = ($admin_valor == 1);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Usuario</title>
    <!-- Incluir CSS de Bootstrap para estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            color: #000;
        }
        .panel-container {
            max-width: 500px;
            margin: 100px auto;
            background-color: #111;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px #555;
            color: #fff;
            text-align: center;
        }
        .btn-option {
            background-color: #222;
            color: #fff;
            border: 1px solid #444;
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 18px;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
        }
        .btn-option:hover {
            background-color: #333;
            transform: scale(1.03);
        }
    </style>
</head>
<body>

<!-- Botón Cerrar sesión arriba a la derecha -->
<div style="position: fixed; top: 20px; right: 20px; z-index: 999;">
    <?php 
    // Mostrar el botón de cerrar sesión solo si hay sesión iniciada
    if (isset($_SESSION['usuario'])) { 
    ?>
        <a href="logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
    <?php 
    } 
    ?>
</div>

<div class="container">
    <div class="panel-container">
        <h2 class="mb-4">¿Qué deseas hacer?</h2>

        <!-- Enlaces para acciones que cualquier usuario puede hacer -->
        <a href="CrearPresupuesto.php" class="btn btn-option">Crear Presupuesto</a>
        <a href="verPresupuestos.php" class="btn btn-option">Ver Presupuestos Anteriores</a>

        <?php 
        // Si el usuario es admin, mostrar opciones exclusivas de administrador
        if ($es_admin) { 
        ?>
            <a href="modificarServicios.php" class="btn btn-option">Modificar Servicios</a>
            <a href="verClientes.php" class="btn btn-option">Ver Clientes</a>
        <?php 
        } 
        ?>
    </div>
</div>

</body>
</html>
