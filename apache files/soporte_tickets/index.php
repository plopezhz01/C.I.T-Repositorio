<?php
session_start();
require_once 'includes/config.php';

// Redirigir según el estado de la sesión
if (isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();
?>
