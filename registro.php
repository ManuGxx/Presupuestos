<?php
// Iniciar sesión
session_start();

// Incluir archivo de conexión (debe usar mysqli en modo procedural)
include("conexion.php");

// Variable para mensajes al usuario
$mensaje = "";

// Comprobar si el formulario fue enviado por POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtener y limpiar los datos del formulario
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $contrasena = $_POST['contrasena'];
    $admin = 0; // Por defecto 0: el usuario no puede decidir si es admin

    // Validar que todos los campos estén completos
    if (empty($nombre) || empty($email) || empty($contrasena)) {
        $mensaje = "<div class='alert alert-danger'>Por favor, complete todos los campos obligatorios.</div>";
    }
    // Validar que el email tenga formato válido
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "<div class='alert alert-danger'>Email no válido.</div>";
    } else {
        // Preparar la consulta para comprobar si el usuario ya existe
        $sql_check = "SELECT id FROM usuarios WHERE email = ? OR nombre = ?";
        $stmt_check = mysqli_prepare($conexion, $sql_check);

        if ($stmt_check) {
            // Enlazar parámetros a la consulta
            mysqli_stmt_bind_param($stmt_check, "ss", $email, $nombre);

            // Ejecutar la consulta
            mysqli_stmt_execute($stmt_check);

            // Obtener los resultados
            $resultado_check = mysqli_stmt_get_result($stmt_check);

            // Comprobar si se encontró algún usuario
            if (mysqli_num_rows($resultado_check) > 0) {
                $mensaje = "<div class='alert alert-danger'>El nombre o email ya están registrados.</div>";
            } else {
                // Encriptar la contraseña
                $hash_password = password_hash($contrasena, PASSWORD_DEFAULT);

                // Preparar consulta para insertar nuevo usuario
                $sql_insert = "INSERT INTO usuarios (nombre, email, contrasena, Admin) VALUES (?, ?, ?, ?)";
                $stmt_insert = mysqli_prepare($conexion, $sql_insert);

                if ($stmt_insert) {
                    // Enlazar los valores
                    mysqli_stmt_bind_param($stmt_insert, "sssi", $nombre, $email, $hash_password, $admin);

                    // Ejecutar la inserción
                    if (mysqli_stmt_execute($stmt_insert)) {
                        $mensaje = "<div class='alert alert-success'>Usuario registrado correctamente.</div>";
                    } else {
                        $mensaje = "<div class='alert alert-danger'>Error al registrar el usuario.</div>";
                    }

                    // Cerrar la sentencia de inserción
                    mysqli_stmt_close($stmt_insert);
                }
            }

            // Cerrar la sentencia de comprobación
            mysqli_stmt_close($stmt_check);
        }
    }

    // Cerrar la conexión a la base de datos
    mysqli_close($conexion);
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            color: #000;
            padding-top: 50px;
        }
        .form-container {
            max-width: 480px;
            margin: 0 auto;
            background-color: #111;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px #555;
            color: #fff;
        }
        .form-control {
            background-color: #222;
            color: #fff;
            border: 1px solid #444;
        }
        .form-control:focus {
            background-color: #111;
            border-color: #777;
            box-shadow: none;
            color: #fff;
        }
        .btn-primary {
            background-color: #f8f9fa;
            color: #000;
            border: 1px solid #ccc;
        }
        .btn-primary:hover {
            background-color: #e0e0e0;
            color: #000;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2 class="mb-4 text-center">Registro de Usuario</h2>

        <?php echo $mensaje; ?>

        <form method="POST" action="registro.php" novalidate>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="100" autofocus>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico:</label>
                <input type="email" id="email" name="email" class="form-control" required maxlength="100">
            </div>

            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" class="form-control" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primary w-100">Registrar</button>
        </form>
    </div>
</div>

</body>
</html>
