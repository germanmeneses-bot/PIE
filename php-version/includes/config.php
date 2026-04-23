<?php
define('DB_PATH', __DIR__ . '/../data/pie_escolar.db');
define('BASE_URL', '');   // Dejar vacío si está en raíz; cambiar a '/pie' si está en subcarpeta

session_start();

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
    }
    return $pdo;
}

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function currentUser(): array {
    return $_SESSION['user'] ?? [];
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
