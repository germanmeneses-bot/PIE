<?php
// $activePage debe estar definida en la página que incluye este archivo
$activePage = $activePage ?? '';
$user = currentUser();
$navItems = [
    ['href' => 'dashboard.php', 'label' => 'Dashboard',   'page' => 'dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
    ['href' => 'informes.php', 'label' => 'Informes',     'page' => 'informes',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
    ['href' => 'alumnos.php',  'label' => 'Alumnos PIE',  'page' => 'alumnos',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'],
    ['href' => 'cursos.php',   'label' => 'Cursos',       'page' => 'cursos',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
    ['href' => 'perfil.php',   'label' => 'Mi Perfil',    'page' => 'perfil',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
];

$initials = '';
if (!empty($user['name'])) {
    $parts = explode(' ', $user['name']);
    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
?>
<aside class="w-64 min-h-screen bg-indigo-900 text-white flex flex-col shadow-xl flex-shrink-0">

    <!-- Logo -->
    <div class="px-6 py-6 border-b border-indigo-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-400 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-indigo-300 uppercase tracking-wider">Sistema</p>
                <h1 class="text-sm font-bold leading-tight">PIE Escolar</h1>
            </div>
        </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-4 py-6 space-y-1">
        <?php foreach ($navItems as $item):
            $isActive = $activePage === $item['page'];
            $cls = $isActive
                ? 'bg-indigo-600 text-white shadow-md'
                : 'text-indigo-200 hover:bg-indigo-800 hover:text-white';
        ?>
        <a href="<?= h($item['href']) ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group <?= $cls ?>">
            <span class="flex-shrink-0 <?= $isActive ? 'text-white' : 'text-indigo-300 group-hover:text-white' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?= $item['icon'] ?>
                </svg>
            </span>
            <span class="text-sm font-medium"><?= h($item['label']) ?></span>
            <?php if ($isActive): ?>
            <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-300"></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- Footer / Logout -->
    <div class="px-6 py-4 border-t border-indigo-700 space-y-3">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-xs font-bold">
                <?= h($initials) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium truncate"><?= h($user['name'] ?? 'Usuario') ?></p>
                <p class="text-xs text-indigo-400 truncate"><?= h($user['username'] ?? '') ?></p>
            </div>
        </div>
        <a href="logout.php"
           class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-indigo-300 hover:bg-indigo-800 hover:text-white transition-all duration-200 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Cerrar sesión
        </a>
    </div>
</aside>
