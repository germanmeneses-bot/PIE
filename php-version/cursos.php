<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$db = getDB();

// Orden correcto del sistema escolar chileno
$ordenNiveles = [
    'Pre-Kinder' => 0,
    'Kinder'     => 1,
    '1° Básico'  => 2,
    '2° Básico'  => 3,
    '3° Básico'  => 4,
    '4° Básico'  => 5,
    '5° Básico'  => 6,
    '6° Básico'  => 7,
    '7° Básico'  => 8,
    '8° Básico'  => 9,
    'I Medio'    => 10,
    'II Medio'   => 11,
    'III Medio'  => 12,
    'IV Medio'   => 13,
];

// Colores por etapa
$colorNivel = [
    'Pre-Kinder' => 'bg-pink-100 text-pink-700',
    'Kinder'     => 'bg-rose-100 text-rose-700',
    '1° Básico'  => 'bg-sky-100 text-sky-700',
    '2° Básico'  => 'bg-sky-100 text-sky-700',
    '3° Básico'  => 'bg-sky-100 text-sky-700',
    '4° Básico'  => 'bg-sky-100 text-sky-700',
    '5° Básico'  => 'bg-cyan-100 text-cyan-700',
    '6° Básico'  => 'bg-cyan-100 text-cyan-700',
    '7° Básico'  => 'bg-teal-100 text-teal-700',
    '8° Básico'  => 'bg-teal-100 text-teal-700',
    'I Medio'    => 'bg-violet-100 text-violet-700',
    'II Medio'   => 'bg-violet-100 text-violet-700',
    'III Medio'  => 'bg-purple-100 text-purple-700',
    'IV Medio'   => 'bg-purple-100 text-purple-700',
];

$cursos = $db->query("SELECT * FROM cursos ORDER BY nombre")->fetchAll();

// Ordenar por orden de nivel definido, luego por letra
usort($cursos, function($a, $b) use ($ordenNiveles) {
    $oa = $ordenNiveles[$a['nivel']] ?? 99;
    $ob = $ordenNiveles[$b['nivel']] ?? 99;
    if ($oa !== $ob) return $oa - $ob;
    return strcmp($a['letra'], $b['letra']);
});

$totalAlumnos = array_sum(array_column($cursos, 'total_alumnos'));
$totalPIE     = $db->query("SELECT COUNT(*) FROM alumnos WHERE estado = 'activo'")->fetchColumn();
$totalCursos  = count($cursos);

// Agrupar por nivel (en orden correcto)
$porNivel = [];
foreach ($cursos as $c) {
    $porNivel[$c['nivel']][] = $c;
}

$pageTitle  = 'Cursos';
$activePage = 'cursos';
include __DIR__ . '/includes/layout_start.php';
?>

<!-- Header -->
<div class="bg-white border-b border-gray-200 px-8 py-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Cursos</h2>
        <p class="text-sm text-gray-500 mt-0.5">Distribución de alumnos por curso</p>
    </div>
</div>

<div class="flex-1 p-8 space-y-6">

    <!-- Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= $totalCursos ?></p>
                    <p class="text-sm text-gray-500">Cursos activos</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= $totalAlumnos ?></p>
                    <p class="text-sm text-gray-500">Total alumnos</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= $totalPIE ?></p>
                    <p class="text-sm text-gray-500">Alumnos PIE total</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards por nivel -->
    <?php foreach ($porNivel as $nivel => $listaCursos): ?>
    <div>
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3"><?= h($nivel) ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <?php foreach ($listaCursos as $c):
                // Contar alumnos PIE de este curso
                $stmtPie = $db->prepare("SELECT COUNT(*) FROM alumnos WHERE curso_id = ? AND estado = 'activo'");
                $stmtPie->execute([$c['id']]);
                $pieCount = (int)$stmtPie->fetchColumn();

                $badgeNivel = $colorNivel[$c['nivel']] ?? 'bg-gray-100 text-gray-700';
            ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 relative">
                <!-- Badge PIE -->
                <div class="absolute top-4 right-4">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                        <?= $pieCount ?> PIE
                    </span>
                </div>

                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900"><?= h($c['nombre']) ?></h4>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $badgeNivel ?>"><?= h($c['nivel']) ?></span>
                    </div>
                </div>

                <div class="space-y-1.5 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Jefatura: <?= h($c['profesor']) ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span><?= $c['total_alumnos'] ?> alumnos en total</span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                    <span class="text-xs text-indigo-600 font-medium"><?= $pieCount ?> alumnos PIE</span>
                    <a href="alumnos.php?search=<?= urlencode($c['nombre']) ?>"
                       class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                        Ver detalle →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
