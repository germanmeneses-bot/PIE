<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$db = getDB();

// Estadísticas desde BD
$totalAlumnos  = (int)$db->query("SELECT COUNT(*) FROM alumnos WHERE estado = 'activo'")->fetchColumn();
$totalCursos   = (int)$db->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
$totalInformes = (int)$db->query("SELECT COUNT(*) FROM informes")->fetchColumn();
$pendientes    = (int)$db->query("SELECT COUNT(*) FROM informes WHERE estado = 'pendiente' OR estado = 'borrador'")->fetchColumn();

// Últimos 5 alumnos
$recientes = $db->query("
    SELECT a.nombre, a.necesidad, a.estado, c.nombre as curso
    FROM alumnos a
    LEFT JOIN cursos c ON a.curso_id = c.id
    ORDER BY a.created_at DESC
    LIMIT 5
")->fetchAll();

$user       = currentUser();
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
include __DIR__ . '/includes/layout_start.php';
?>

<!-- Page Header -->
<div class="bg-white border-b border-gray-200 px-8 py-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
            <p class="text-sm text-gray-500 mt-0.5">Año escolar <?= date('Y') ?> — Bienvenido/a, <?= h($user['name'] ?? 'Usuario') ?></p>
        </div>
        <span class="text-xs bg-indigo-50 text-indigo-700 font-medium px-3 py-1.5 rounded-full border border-indigo-100">
            <?= date('d/m/Y') ?>
        </span>
    </div>
</div>

<div class="flex-1 p-8 space-y-8">

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <?php
        $stats = [
            ['label' => 'Alumnos PIE',  'value' => $totalAlumnos,  'color' => 'bg-indigo-500',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
            ['label' => 'Cursos',       'value' => $totalCursos,   'color' => 'bg-emerald-500', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
            ['label' => 'Informes',     'value' => $totalInformes, 'color' => 'bg-amber-500',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
            ['label' => 'Pendientes',   'value' => $pendientes,    'color' => 'bg-rose-500',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ];
        foreach ($stats as $s): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 <?= $s['color'] ?> rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?= $s['icon'] ?>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900"><?= $s['value'] ?></p>
                <p class="text-sm text-gray-500"><?= h($s['label']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Students Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Alumnos Recientes</h3>
            <a href="alumnos.php" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Ver todos →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium">Nombre</th>
                        <th class="px-6 py-3 text-left font-medium">Curso</th>
                        <th class="px-6 py-3 text-left font-medium">Necesidad</th>
                        <th class="px-6 py-3 text-left font-medium">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($recientes as $alumno): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900"><?= h($alumno['nombre']) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= h($alumno['curso'] ?? '—') ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                                <?= h($alumno['necesidad']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($alumno['estado'] === 'activo'): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Activo</span>
                            <?php else: ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactivo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recientes)): ?>
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No hay alumnos registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
        <div class="flex flex-wrap gap-3">
            <a href="informes.php?action=nuevo"
               class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Informe
            </a>
            <a href="alumnos.php?action=nuevo"
               class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Agregar Alumno
            </a>
            <a href="cursos.php"
               class="flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Ver Cursos
            </a>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
