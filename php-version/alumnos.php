<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$db = getDB();
$error   = '';
$success = '';

// ── Agregar alumno ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'crear') {
    $nombre       = trim($_POST['nombre']       ?? '');
    $rut          = trim($_POST['rut']          ?? '');
    $curso_id     = (int)($_POST['curso_id']    ?? 0);
    $necesidad    = trim($_POST['necesidad']    ?? '');
    $diagnostico  = trim($_POST['diagnostico']  ?? '');
    $fecha_ingreso = date('Y-m-d');

    if ($nombre && $rut && $necesidad) {
        try {
            $stmt = $db->prepare("INSERT INTO alumnos (nombre, rut, curso_id, necesidad, diagnostico, fecha_ingreso) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$nombre, $rut, $curso_id ?: null, $necesidad, $diagnostico, $fecha_ingreso]);
            $success = 'Alumno agregado correctamente.';
        } catch (Exception $e) {
            $error = 'Error al guardar: ' . $e->getMessage();
        }
    } else {
        $error = 'Nombre, RUT y necesidad son obligatorios.';
    }
}

// ── Eliminar alumno ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $db->prepare("DELETE FROM alumnos WHERE id = ?")->execute([$id]);
        $success = 'Alumno eliminado.';
    }
}

// ── Filtros ────────────────────────────────────────────────────
$search    = trim($_GET['search']    ?? '');
$necesidad = trim($_GET['necesidad'] ?? '');

$where  = [];
$params = [];
if ($search) {
    $where[]  = "(a.nombre LIKE ? OR a.rut LIKE ? OR c.nombre LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($necesidad) {
    $where[]  = "a.necesidad = ?";
    $params[] = $necesidad;
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$alumnos = $db->prepare("
    SELECT a.*, c.nombre as curso_nombre
    FROM alumnos a
    LEFT JOIN cursos c ON a.curso_id = c.id
    $whereSQL
    ORDER BY a.nombre ASC
");
$alumnos->execute($params);
$alumnos = $alumnos->fetchAll();

$cursos = $db->query("
    SELECT id, nombre FROM cursos
    ORDER BY CASE nivel
        WHEN 'Pre-Kinder' THEN 0
        WHEN 'Kinder'     THEN 1
        WHEN '1° Básico'  THEN 2
        WHEN '2° Básico'  THEN 3
        WHEN '3° Básico'  THEN 4
        WHEN '4° Básico'  THEN 5
        WHEN '5° Básico'  THEN 6
        WHEN '6° Básico'  THEN 7
        WHEN '7° Básico'  THEN 8
        WHEN '8° Básico'  THEN 9
        WHEN 'I Medio'    THEN 10
        WHEN 'II Medio'   THEN 11
        WHEN 'III Medio'  THEN 12
        WHEN 'IV Medio'   THEN 13
        ELSE 99
    END, letra
")->fetchAll();

$necesidades = ['TEA', 'TDAH', 'TEL', 'Dificultad Lectora', 'Discapacidad Motora', 'Discapacidad Visual', 'Otra'];

$showModal  = ($success === '' && $error !== '') || isset($_GET['action']) && $_GET['action'] === 'nuevo';
$pageTitle  = 'Alumnos PIE';
$activePage = 'alumnos';
include __DIR__ . '/includes/layout_start.php';
?>

<!-- Header -->
<div class="bg-white border-b border-gray-200 px-8 py-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Alumnos PIE</h2>
            <p class="text-sm text-gray-500 mt-0.5"><?= count($alumnos) ?> alumno(s) encontrado(s)</p>
        </div>
        <button onclick="document.getElementById('modal-alumno').classList.remove('hidden')"
                class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Agregar Alumno
        </button>
    </div>
</div>

<div class="flex-1 p-8 space-y-6">

    <?php if ($success): ?>
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm"><?= h($success) ?></div>
    <?php endif; ?>

    <!-- Filtros -->
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="<?= h($search) ?>"
               placeholder="Buscar por nombre, RUT o curso..."
               class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="necesidad"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
            <option value="">Todas las necesidades</option>
            <?php foreach ($necesidades as $n): ?>
            <option value="<?= h($n) ?>" <?= $necesidad === $n ? 'selected' : '' ?>><?= h($n) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
            Filtrar
        </button>
        <?php if ($search || $necesidad): ?>
        <a href="alumnos.php" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
            Limpiar
        </a>
        <?php endif; ?>
    </form>

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium">Nombre</th>
                        <th class="px-6 py-3 text-left font-medium">RUT</th>
                        <th class="px-6 py-3 text-left font-medium">Curso</th>
                        <th class="px-6 py-3 text-left font-medium">Necesidad</th>
                        <th class="px-6 py-3 text-left font-medium">Diagnóstico</th>
                        <th class="px-6 py-3 text-left font-medium">Estado</th>
                        <th class="px-6 py-3 text-left font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($alumnos as $a): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900"><?= h($a['nombre']) ?></td>
                        <td class="px-6 py-4 text-gray-600 font-mono text-xs"><?= h($a['rut']) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= h($a['curso_nombre'] ?? '—') ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                                <?= h($a['necesidad']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 max-w-xs truncate" title="<?= h($a['diagnostico']) ?>">
                            <?= h($a['diagnostico'] ?: '—') ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($a['estado'] === 'activo'): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Activo</span>
                            <?php else: ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar alumno?')">
                                <input type="hidden" name="_action" value="eliminar">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" class="text-rose-600 hover:text-rose-700 text-xs font-medium cursor-pointer">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($alumnos)): ?>
                    <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">No se encontraron alumnos</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Agregar Alumno -->
<div id="modal-alumno" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Agregar Alumno PIE</h3>
            <button onclick="document.getElementById('modal-alumno').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="_action" value="crear">

            <?php if ($error): ?>
            <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"><?= h($error) ?></div>
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                <input type="text" name="nombre" required placeholder="Ej: Camila Rojas Pérez"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RUT *</label>
                <input type="text" name="rut" required placeholder="Ej: 21.345.678-9"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Curso</label>
                <select name="curso_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Sin asignar</option>
                    <?php foreach ($cursos as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= h($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Necesidad educativa *</label>
                <select name="necesidad" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($necesidades as $n): ?>
                    <option value="<?= h($n) ?>"><?= h($n) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Diagnóstico</label>
                <textarea name="diagnostico" rows="2" placeholder="Descripción del diagnóstico..."
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button"
                        onclick="document.getElementById('modal-alumno').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors cursor-pointer">
                    Guardar Alumno
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($error || isset($_GET['action'])): ?>
<script>document.getElementById('modal-alumno').classList.remove('hidden');</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
