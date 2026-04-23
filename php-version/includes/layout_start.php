<?php
// Debe definirse $pageTitle y $activePage antes de incluir este archivo
$pageTitle  = $pageTitle  ?? 'Sistema PIE';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> — Sistema PIE Escolar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 antialiased">
<div class="flex min-h-screen">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="flex-1 flex flex-col overflow-hidden">
