<?php
// Punto de entrada: redirige al dashboard (o login si no hay sesión)
require_once __DIR__ . '/includes/config.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
