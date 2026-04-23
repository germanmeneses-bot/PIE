<?php
require_once __DIR__ . '/includes/config.php';

// Si ya está logueado, redirigir al dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Usuario y contraseña son requeridos';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user']    = [
                'id'       => $user['id'],
                'username' => $user['username'],
                'name'     => $user['name'],
                'role'     => $user['role'],
            ];
            $redirect = $_GET['redirect'] ?? 'dashboard.php';
            header('Location: ' . h($redirect));
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Sistema PIE Escolar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {} }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-900 via-indigo-800 to-indigo-700 flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-400 rounded-2xl mb-4 shadow-lg">
            <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">Sistema PIE</h1>
        <p class="text-indigo-300 mt-1 text-sm">Programa de Integración Escolar</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Iniciar sesión</h2>

        <form method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . h($_GET['redirect']) : '' ?>" class="space-y-5">

            <!-- Usuario -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1.5">Usuario</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input id="username" name="username" type="text"
                           value="<?= h($_POST['username'] ?? '') ?>"
                           placeholder="Ingrese su usuario" required autocomplete="username"
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>
            </div>

            <!-- Contraseña -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input id="password" name="password" type="password"
                           placeholder="Ingrese su contraseña" required autocomplete="current-password"
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>
            </div>

            <?php if ($error): ?>
            <div class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <?= h($error) ?>
            </div>
            <?php endif; ?>

            <button type="submit"
                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm transition-colors duration-200 cursor-pointer">
                Ingresar
            </button>
        </form>
    </div>

    <p class="text-center text-indigo-400 text-xs mt-6">
        &copy; <?= date('Y') ?> Sistema PIE Escolar
    </p>
</div>

</body>
</html>
