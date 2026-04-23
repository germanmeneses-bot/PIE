<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$db = getDB();
$error   = '';
$success = '';

// ── Crear informe ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'crear') {
    $titulo        = trim($_POST['titulo']        ?? '');
    $tipo          = trim($_POST['tipo']          ?? '');
    $alumno_id     = (int)($_POST['alumno_id']    ?? 0);
    $alumno_nombre = trim($_POST['alumno_nombre'] ?? '');
    $estado        = trim($_POST['estado']        ?? 'borrador');
    $fecha         = trim($_POST['fecha']         ?? date('Y-m-d'));
    $descripcion   = trim($_POST['descripcion']   ?? '');

    if ($titulo && $tipo) {
        $stmt = $db->prepare("INSERT INTO informes (titulo, tipo, alumno_id, alumno_nombre, estado, fecha, descripcion) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$titulo, $tipo, $alumno_id ?: null, $alumno_nombre, $estado, $fecha, $descripcion]);
        $success = 'Informe creado correctamente.';
    } else {
        $error = 'Título y tipo son obligatorios.';
    }
}

// ── Eliminar informe ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $db->prepare("DELETE FROM informes WHERE id = ?")->execute([$id]);
        $success = 'Informe eliminado.';
    }
}

// ── Datos ──────────────────────────────────────────────────────
$informes = $db->query("
    SELECT i.*, a.nombre as alumno_real
    FROM informes i
    LEFT JOIN alumnos a ON i.alumno_id = a.id
    ORDER BY i.fecha DESC
")->fetchAll();

$alumnos = $db->query("SELECT id, nombre FROM alumnos WHERE estado = 'activo' ORDER BY nombre")->fetchAll();

$completados = count(array_filter($informes, fn($i) => $i['estado'] === 'completado'));
$borradores  = count(array_filter($informes, fn($i) => $i['estado'] === 'borrador'));
$pendientes  = count(array_filter($informes, fn($i) => $i['estado'] === 'pendiente'));

// Definición de tipos con ícono SVG, descripción y color
$tiposInfo = [
    'Psicológico' => [
        'desc'  => 'Evaluación cognitiva, emocional y conductual del estudiante. Incluye aplicación de pruebas psicológicas y análisis integral.',
        'emoji' => '🧠',
        'color' => 'indigo',
    ],
    'Psicológico Complementario' => [
        'desc'  => 'Evaluación psicológica complementaria con metodologías especializadas. Análisis cualitativo y cuantitativo adaptado.',
        'emoji' => '🧠',
        'color' => 'violet',
    ],
    'Terapia Ocupacional' => [
        'desc'  => 'Evaluación de motricidad, procesamiento sensorial y actividades de la vida diaria. Análisis funcional ocupacional.',
        'emoji' => '🖐️',
        'color' => 'amber',
    ],
    'Fonoaudiológico' => [
        'desc'  => 'Evaluación del lenguaje, habla, voz y audición. Análisis de habilidades comunicativas y trastornos del lenguaje.',
        'emoji' => '🗣️',
        'color' => 'sky',
    ],
    'Psicopedagógico' => [
        'desc'  => 'Evaluación académica, lectoescritura y matemáticas. Análisis de estrategias de aprendizaje y dificultades educativas.',
        'emoji' => '📚',
        'color' => 'emerald',
    ],
    'PAI' => [
        'desc'  => 'Plan de Apoyo Individual. Documento con objetivos, estrategias y apoyos diferenciados para el estudiante.',
        'emoji' => '📋',
        'color' => 'teal',
    ],
    'Avance' => [
        'desc'  => 'Seguimiento periódico del progreso académico y funcional del alumno PIE respecto a los objetivos planteados.',
        'emoji' => '📈',
        'color' => 'cyan',
    ],
    'Derivación' => [
        'desc'  => 'Solicitud formal de derivación a especialistas externos o internos para atención complementaria del estudiante.',
        'emoji' => '📤',
        'color' => 'rose',
    ],
];
$tipos = array_keys($tiposInfo);

$estadoBadge = [
    'completado' => 'bg-emerald-100 text-emerald-700',
    'borrador'   => 'bg-amber-100 text-amber-700',
    'pendiente'  => 'bg-rose-100 text-rose-700',
];

$pageTitle  = 'Informes';
$activePage = 'informes';
include __DIR__ . '/includes/layout_start.php';
?>

<!-- Header -->
<div class="bg-white border-b border-gray-200 px-8 py-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Informes</h2>
            <p class="text-sm text-gray-500 mt-0.5"><?= count($informes) ?> informe(s) registrado(s)</p>
        </div>
        <button onclick="document.getElementById('modal-informe').classList.remove('hidden')"
                class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Crear Informe
        </button>
    </div>
</div>

<div class="flex-1 p-8 space-y-6">

    <?php if ($success): ?>
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm"><?= h($success) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-3 h-3 rounded-full bg-emerald-500 flex-shrink-0"></div>
            <div>
                <p class="text-xl font-bold text-gray-900"><?= $completados ?></p>
                <p class="text-xs text-gray-500">Completados</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-3 h-3 rounded-full bg-amber-500 flex-shrink-0"></div>
            <div>
                <p class="text-xl font-bold text-gray-900"><?= $borradores ?></p>
                <p class="text-xs text-gray-500">Borradores</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-3 h-3 rounded-full bg-rose-500 flex-shrink-0"></div>
            <div>
                <p class="text-xl font-bold text-gray-900"><?= $pendientes ?></p>
                <p class="text-xs text-gray-500">Pendientes</p>
            </div>
        </div>
    </div>

    <!-- Grid de tipos de informe -->
    <?php
    $colorMap = [
        'indigo'  => ['bg' => 'bg-indigo-50',  'icon' => 'bg-indigo-100 text-indigo-600',  'btn' => 'bg-indigo-600 hover:bg-indigo-700',  'border' => 'hover:border-indigo-200'],
        'violet'  => ['bg' => 'bg-violet-50',  'icon' => 'bg-violet-100 text-violet-600',  'btn' => 'bg-violet-600 hover:bg-violet-700',  'border' => 'hover:border-violet-200'],
        'amber'   => ['bg' => 'bg-amber-50',   'icon' => 'bg-amber-100 text-amber-600',    'btn' => 'bg-amber-500 hover:bg-amber-600',    'border' => 'hover:border-amber-200'],
        'sky'     => ['bg' => 'bg-sky-50',     'icon' => 'bg-sky-100 text-sky-600',        'btn' => 'bg-sky-600 hover:bg-sky-700',        'border' => 'hover:border-sky-200'],
        'emerald' => ['bg' => 'bg-emerald-50', 'icon' => 'bg-emerald-100 text-emerald-600','btn' => 'bg-emerald-600 hover:bg-emerald-700','border' => 'hover:border-emerald-200'],
        'teal'    => ['bg' => 'bg-teal-50',    'icon' => 'bg-teal-100 text-teal-600',      'btn' => 'bg-teal-600 hover:bg-teal-700',      'border' => 'hover:border-teal-200'],
        'cyan'    => ['bg' => 'bg-cyan-50',    'icon' => 'bg-cyan-100 text-cyan-600',      'btn' => 'bg-cyan-600 hover:bg-cyan-700',      'border' => 'hover:border-cyan-200'],
        'rose'    => ['bg' => 'bg-rose-50',    'icon' => 'bg-rose-100 text-rose-600',      'btn' => 'bg-rose-600 hover:bg-rose-700',      'border' => 'hover:border-rose-200'],
    ];
    ?>
    <div>
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Tipos de informe disponibles</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <?php foreach ($tiposInfo as $tipo => $info):
                $c = $colorMap[$info['color']];
            ?>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3 transition-all duration-200 <?= $c['border'] ?> hover:shadow-md">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl flex-shrink-0 <?= $c['icon'] ?>">
                        <?= $info['emoji'] ?>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm leading-snug"><?= h($tipo) ?></h4>
                </div>
                <p class="text-xs text-gray-500 leading-relaxed flex-1"><?= h($info['desc']) ?></p>
                <button
                    onclick="abrirModalConTipo(<?= json_encode($tipo) ?>)"
                    class="w-full py-2 rounded-lg text-xs font-medium text-white transition-colors cursor-pointer <?= $c['btn'] ?>">
                    Crear Informe
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium">Título</th>
                        <th class="px-6 py-3 text-left font-medium">Alumno</th>
                        <th class="px-6 py-3 text-left font-medium">Tipo</th>
                        <th class="px-6 py-3 text-left font-medium">Fecha</th>
                        <th class="px-6 py-3 text-left font-medium">Estado</th>
                        <th class="px-6 py-3 text-left font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($informes as $inf):
                        $badge = $estadoBadge[$inf['estado']] ?? 'bg-gray-100 text-gray-600';
                        $estadoLabel = ucfirst($inf['estado']);
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900 max-w-xs truncate" title="<?= h($inf['titulo']) ?>">
                            <?= h($inf['titulo']) ?>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= h($inf['alumno_nombre'] ?: '—') ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                <?= h($inf['tipo']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= h($inf['fecha']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badge ?>">
                                <?= h($estadoLabel) ?>
                            </span>
                        </td>
                         <td class="px-6 py-4 flex items-center gap-3">
                            <button
                                onclick="verInforme(<?= htmlspecialchars(json_encode($inf), ENT_QUOTES) ?>)"
                                class="text-indigo-600 hover:text-indigo-700 text-xs font-medium cursor-pointer">
                                Ver
                            </button>
                            <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar informe?')">
                                <input type="hidden" name="_action" value="eliminar">
                                <input type="hidden" name="id" value="<?= $inf['id'] ?>">
                                <button type="submit" class="text-rose-600 hover:text-rose-700 text-xs font-medium cursor-pointer">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($informes)): ?>
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No hay informes registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Crear Informe -->
<div id="modal-informe" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <h3 class="text-lg font-semibold text-gray-900">Crear Informe</h3>
            <button onclick="document.getElementById('modal-informe').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" class="flex flex-col flex-1 overflow-hidden">
            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                <input type="hidden" name="_action" value="crear">

                <?php if ($error): ?>
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"><?= h($error) ?></div>
                <?php endif; ?>

                <!-- Tipo (botones) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de informe *</label>
                    <div class="flex flex-wrap gap-2" id="tipo-btns">
                        <?php foreach ($tipos as $t): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="tipo" value="<?= h($t) ?>" class="sr-only peer" required>
                            <span class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 text-gray-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 transition-colors">
                                <?= h($t) ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alumno</label>
                    <select name="alumno_id" id="alumno-select"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Sin asignar</option>
                        <?php foreach ($alumnos as $al): ?>
                        <option value="<?= $al['id'] ?>" data-nombre="<?= h($al['nombre']) ?>"><?= h($al['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="alumno_nombre" id="alumno-nombre">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                    <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                    <input type="text" name="titulo" required placeholder="Ej: Informe Psicopedagógico — 1er Semestre"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="Descripción del informe..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="borrador">Borrador</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="completado">Completado</option>
                    </select>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 flex-shrink-0">
                <button type="button"
                        onclick="document.getElementById('modal-informe').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors cursor-pointer">
                    Crear Informe
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Sincronizar nombre del alumno al campo hidden
document.getElementById('alumno-select').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('alumno-nombre').value = opt.dataset.nombre || '';
});

// Abrir modal con tipo preseleccionado
function abrirModalConTipo(tipo) {
    // Marcar el radio correspondiente
    const radios = document.querySelectorAll('input[name="tipo"]');
    radios.forEach(r => {
        r.checked = (r.value === tipo);
    });
    // Auto-completar título si está vacío
    const tituloInput = document.querySelector('input[name="titulo"]');
    if (!tituloInput.value) {
        tituloInput.value = 'Informe ' + tipo;
    }
    document.getElementById('modal-informe').classList.remove('hidden');
}
</script>

<?php if ($error || (isset($_GET['action']) && $_GET['action'] === 'nuevo')): ?>
<script>document.getElementById('modal-informe').classList.remove('hidden');</script>
<?php endif; ?>

<!-- Modal Ver Informe -->
<div id="modal-ver" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">

        <!-- Header del modal -->
        <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div id="ver-emoji" class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-xl flex-shrink-0"></div>
                <div>
                    <h3 id="ver-titulo" class="text-lg font-semibold text-gray-900"></h3>
                    <p id="ver-tipo-badge" class="mt-0.5"></p>
                </div>
            </div>
            <button onclick="document.getElementById('modal-ver').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 cursor-pointer flex-shrink-0 ml-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Cuerpo -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6">

            <!-- Datos principales -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 mb-1">Alumno</p>
                    <p id="ver-alumno" class="text-sm font-semibold text-gray-900">—</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 mb-1">Fecha</p>
                    <p id="ver-fecha" class="text-sm font-semibold text-gray-900">—</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 mb-1">Estado</p>
                    <p id="ver-estado" class="text-sm font-semibold"></p>
                </div>
            </div>

            <!-- Descripción -->
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Descripción</h4>
                <div id="ver-descripcion" class="bg-gray-50 rounded-xl p-4 text-sm text-gray-700 leading-relaxed min-h-[80px]"></div>
            </div>

            <!-- Metadatos -->
            <div class="border-t border-gray-100 pt-4 flex items-center justify-between text-xs text-gray-400">
                <span>ID de informe: <span id="ver-id" class="font-mono"></span></span>
                <span>Registrado en Sistema PIE</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center flex-shrink-0">
            <button id="ver-btn-imprimir" onclick="imprimirInforme()"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </button>
            <button onclick="document.getElementById('modal-ver').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors cursor-pointer">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
// Mapa de emojis por tipo
const tipoEmoji = <?= json_encode(array_map(fn($v) => $v['emoji'], $tiposInfo)) ?>;
const tipoColor = <?= json_encode(array_map(fn($v) => $v['color'], $tiposInfo)) ?>;

const estadoClases = {
    completado: 'bg-emerald-100 text-emerald-700',
    borrador:   'bg-amber-100 text-amber-700',
    pendiente:  'bg-rose-100 text-rose-700',
};

const colorBg = {
    indigo: 'bg-indigo-100', violet: 'bg-violet-100', amber: 'bg-amber-100',
    sky: 'bg-sky-100', emerald: 'bg-emerald-100', teal: 'bg-teal-100',
    cyan: 'bg-cyan-100', rose: 'bg-rose-100',
};

let informeActual = null;

function verInforme(informe) {
    informeActual = informe;

    const tipo  = informe.tipo || '';
    const emoji = tipoEmoji[tipo] || '📄';
    const color = tipoColor[tipo] || 'indigo';

    // Emoji e ícono
    document.getElementById('ver-emoji').textContent = emoji;
    document.getElementById('ver-emoji').className =
        'w-10 h-10 rounded-lg flex items-center justify-center text-xl flex-shrink-0 ' + (colorBg[color] || 'bg-indigo-100');

    // Título
    document.getElementById('ver-titulo').textContent = informe.titulo || '—';

    // Badge tipo
    document.getElementById('ver-tipo-badge').innerHTML =
        `<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">${tipo}</span>`;

    // Alumno
    document.getElementById('ver-alumno').textContent = informe.alumno_nombre || '—';

    // Fecha (formatear de YYYY-MM-DD a DD/MM/YYYY)
    const fecha = informe.fecha || '';
    if (fecha && fecha.includes('-')) {
        const [y, m, d] = fecha.split('-');
        document.getElementById('ver-fecha').textContent = `${d}/${m}/${y}`;
    } else {
        document.getElementById('ver-fecha').textContent = fecha || '—';
    }

    // Estado
    const estado = informe.estado || 'borrador';
    const estadoCls = estadoClases[estado] || 'bg-gray-100 text-gray-600';
    document.getElementById('ver-estado').innerHTML =
        `<span class="px-2.5 py-0.5 rounded-full text-xs font-medium ${estadoCls}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;

    // Descripción
    const desc = informe.descripcion || '';
    document.getElementById('ver-descripcion').textContent = desc || 'Sin descripción registrada.';

    // ID
    document.getElementById('ver-id').textContent = '#' + (informe.id || '—');

    document.getElementById('modal-ver').classList.remove('hidden');
}

function imprimirInforme() {
    if (!informeActual) return;
    const i = informeActual;
    const fecha = (i.fecha || '').includes('-')
        ? i.fecha.split('-').reverse().join('/')
        : (i.fecha || '—');

    const win = window.open('', '_blank');
    win.document.write(`
        <!DOCTYPE html><html lang="es"><head>
        <meta charset="UTF-8">
        <title>${i.titulo || 'Informe'}</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; color: #1f2937; max-width: 700px; margin: 0 auto; }
            h1 { font-size: 22px; margin-bottom: 4px; }
            .meta { color: #6b7280; font-size: 13px; margin-bottom: 24px; }
            .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
            .cell { background: #f9fafb; border-radius: 8px; padding: 12px; }
            .cell label { display: block; font-size: 11px; color: #9ca3af; margin-bottom: 4px; }
            .cell span { font-size: 14px; font-weight: 600; }
            h2 { font-size: 14px; color: #374151; margin-bottom: 8px; }
            .desc { background: #f9fafb; border-radius: 8px; padding: 16px; font-size: 14px; line-height: 1.6; min-height: 80px; }
            .footer { margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 11px; color: #9ca3af; }
        </style>
        </head><body>
        <h1>${i.titulo || '—'}</h1>
        <p class="meta">Tipo: ${i.tipo || '—'} &nbsp;·&nbsp; ID #${i.id}</p>
        <div class="grid">
            <div class="cell"><label>Alumno</label><span>${i.alumno_nombre || '—'}</span></div>
            <div class="cell"><label>Fecha</label><span>${fecha}</span></div>
            <div class="cell"><label>Estado</label><span>${(i.estado || '').charAt(0).toUpperCase() + (i.estado || '').slice(1)}</span></div>
        </div>
        <h2>Descripción</h2>
        <div class="desc">${i.descripcion || 'Sin descripción registrada.'}</div>
        <div class="footer">Sistema PIE Escolar &nbsp;·&nbsp; Impreso el ${new Date().toLocaleDateString('es-CL')}</div>
        </body></html>
    `);
    win.document.close();
    win.print();
}
</script>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
