<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario_nombre = $_SESSION['usuario'];

// Consultar si es admin
$sql = "SELECT Admin FROM usuarios WHERE nombre = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario_nombre);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$es_admin = ($usuario['Admin'] == 1);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Usuario</title>
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
    <?php if (isset($_SESSION['usuario'])): ?>
        <a href="logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
    <?php endif; ?>
</div>

<div class="container">
    <div class="panel-container">
        <h2 class="mb-4">¿Qué deseas hacer?</h2>

        <a href="CrearPresupuesto.php" class="btn btn-option">Crear Presupuesto</a>
        <a href="verPresupuestos.php" class="btn btn-option">Ver Presupuestos Anteriores</a>

        <?php if ($es_admin): ?>
            <a href="modificarServicios.php" class="btn btn-option">Modificar Servicios</a>
            <a href="verClientes.php" class="btn btn-option">Ver Clientes</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
