<?php
require_once '../includes/funciones.php';

// Cerrar sesión
cerrarSesion();

// Redirigir a página principal
header("Location: index.php");
exit;
?>