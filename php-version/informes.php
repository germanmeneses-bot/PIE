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

$tipos = ['Psicopedagógico', 'Avance', 'PAI', 'Seguimiento', 'Evaluación', 'Derivación'];

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

    <!-- Tipos disponibles -->
    <div class="flex flex-wrap gap-2">
        <?php foreach ($tipos as $t): ?>
        <span class="px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700"><?= h($t) ?></span>
        <?php endforeach; ?>
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
                        <td class="px-6 py-4">
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
</script>

<?php if ($error || (isset($_GET['action']) && $_GET['action'] === 'nuevo')): ?>
<script>document.getElementById('modal-informe').classList.remove('hidden');</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
