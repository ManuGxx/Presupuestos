<?php
$host = "sql110.infinityfree.com"; 
$user = "if0_38618881";      // Usuario
$pass = "56nxv4K8uIYwNU";     // Contraseña
$db = "if0_38618881_presupuestos";    // Base de datos

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

session_start();
?>
