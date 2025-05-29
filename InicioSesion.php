<?php
session_start();
include("conexion.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $conexion->real_escape_string($_POST["nombre"]);
    $passPlano = $_POST["contrasena"];

    $sql = "SELECT * FROM usuarios WHERE nombre = '$nombre'";
    $resultado = $conexion->query($sql);

    if ($resultado && $resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($passPlano, $usuario['contrasena'])) {
            $_SESSION['usuario'] = $usuario['nombre'];
            header("Location: seleccion.php");
            exit();
        }
    }

    // Mensaje de error si falla cualquier parte
    $mensaje = "
        <div class='alerta-error'>
            &#9888; No se han encontrado los datos de inicio de sesión, inténtelo de nuevo.
        </div>
    ";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            color: #000;
        }
        .form-container {
            max-width: 500px;
            margin: 80px auto;
            background-color: #111;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #555;
            color: #fff;
        }
        .form-control {
            background-color: #222;
            color: #fff;
            border: 1px solid #444;
        }
        .form-control:focus {
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
            color: #000;
        }
        .alerta-error {
            margin-top: 15px;
            background-color: rgba(255, 0, 0, 0.1);
            color: #b30000;
            border: 1px solid #b30000;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .registro-link {
            margin-top: 20px;
            color: #ccc;
        }
        .registro-link a {
            color: #fff;
            text-decoration: underline;
        }
        .registro-link a:hover {
            color: #bbb;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2 class="text-center mb-4">Iniciar Sesión</h2>
        <?php echo $mensaje; ?>
        <form method="POST" novalidate>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña:</label>
                <input type="password" name="contrasena" id="contrasena" class="form-control" required>
            </div>
            <button type="submit" class="btn w-100">Iniciar sesión</button>

            <div class="registro-link text-center">
                ¿No tienes cuenta? <a href="registro.php">Crea una aquí</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
