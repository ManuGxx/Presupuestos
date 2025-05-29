<?php
session_start();
session_unset();
session_destroy();
header("Location: https://pintadosydecorados.wuaze.com/"); // Redirige a la página de inicio de sesión
exit();
