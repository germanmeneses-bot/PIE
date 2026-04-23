<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$db      = getDB();
$user    = currentUser();
$userId  = $user['id'];
$success = '';
$error   = '';

// Cargar datos actuales del usuario
$userData = $db->prepare("SELECT * FROM users WHERE id = ?")->execute([$userId])
    ? $db->prepare("SELECT * FROM users WHERE id = ?")->execute([$userId]) // workaround
    : null;
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

// ── Actualizar perfil ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    if ($name) {
        $db->prepare("UPDATE users SET name = ? WHERE id = ?")->execute([$name, $userId]);
        $_SESSION['user']['name'] = $name;
        $userData['name'] = $name;
        $success = 'Perfil actualizado correctamente.';
    } else {
        $error = 'El nombre no puede estar vacío.';
    }
}

// ── Cambiar contraseña ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'change_password') {
    $current  = $_POST['current_password']  ?? '';
    $new      = $_POST['new_password']      ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';

    if (!password_verify($current, $userData['password'])) {
        $error = 'La contraseña actual es incorrecta.';
    } elseif (strlen($new) < 4) {
        $error = 'La nueva contraseña debe tener al menos 4 caracteres.';
    } elseif ($new !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);
        $success = 'Contraseña cambiada correctamente.';
    }
}

// Stats del usuario
$totalAlumnos  = (int)$db->query("SELECT COUNT(*) FROM alumnos WHERE estado = 'activo'")->fetchColumn();
$totalInformes = (int)$db->query("SELECT COUNT(*) FROM informes")->fetchColumn();
$totalCursos   = (int)$db->query("SELECT COUNT(*) FROM cursos")->fetchColumn();

$initials = '';
$parts = explode(' ', $userData['name'] ?? '');
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));

$pageTitle  = 'Mi Perfil';
$activePage = 'perfil';
include __DIR__ . '/includes/layout_start.php';
?>

<!-- Header -->
<div class="bg-white border-b border-gray-200 px-8 py-6">
    <h2 class="text-2xl font-bold text-gray-900">Mi Perfil</h2>
    <p class="text-sm text-gray-500 mt-0.5">Gestiona tu información personal</p>
</div>

<div class="flex-1 p-8">

    <?php if ($success): ?>
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm"><?= h($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Columna izquierda: Avatar + stats -->
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
                <div class="w-24 h-24 bg-indigo-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mb-4">
                    <?= h($initials) ?>
                </div>
                <h3 class="font-semibold text-gray-900 text-lg"><?= h($userData['name'] ?? '') ?></h3>
                <p class="text-sm text-gray-500 mt-0.5">@<?= h($userData['username'] ?? '') ?></p>
                <span class="mt-2 px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                    <?= $userData['role'] === 'admin' ? 'Administrador' : 'Docente' ?>
                </span>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-4">Resumen de actividad</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Alumnos PIE</span>
                        <span class="text-sm font-semibold text-gray-900"><?= $totalAlumnos ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Informes</span>
                        <span class="text-sm font-semibold text-gray-900"><?= $totalInformes ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Cursos</span>
                        <span class="text-sm font-semibold text-gray-900"><?= $totalCursos ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Formularios -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Información personal -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h4 class="font-semibold text-gray-900 mb-4">Información Personal</h4>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="_action" value="update_profile">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                        <input type="text" name="name" value="<?= h($userData['name'] ?? '') ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                        <input type="text" value="<?= h($userData['username'] ?? '') ?>" disabled
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                        <input type="text" value="<?= $userData['role'] === 'admin' ? 'Administrador' : 'Docente' ?>" disabled
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors cursor-pointer">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            <!-- Cambiar contraseña -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h4 class="font-semibold text-gray-900 mb-4">Cambiar Contraseña</h4>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="_action" value="change_password">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña actual</label>
                        <input type="password" name="current_password" required autocomplete="current-password"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
                        <input type="password" name="new_password" required autocomplete="new-password"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar nueva contraseña</label>
                        <input type="password" name="confirm_password" required autocomplete="new-password"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors cursor-pointer">
                            Actualizar contraseña
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
