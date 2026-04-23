<?php
require_once __DIR__ . '/config.php';

$db = getDB();

// Crear tablas
$db->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    name TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'docente',
    created_at INTEGER DEFAULT (strftime('%s','now'))
);

CREATE TABLE IF NOT EXISTS cursos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    nivel TEXT NOT NULL,
    letra TEXT NOT NULL,
    profesor TEXT NOT NULL,
    total_alumnos INTEGER NOT NULL DEFAULT 0,
    created_at INTEGER DEFAULT (strftime('%s','now'))
);

CREATE TABLE IF NOT EXISTS alumnos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    rut TEXT NOT NULL UNIQUE,
    curso_id INTEGER REFERENCES cursos(id),
    necesidad TEXT NOT NULL,
    diagnostico TEXT NOT NULL DEFAULT '',
    estado TEXT NOT NULL DEFAULT 'activo',
    fecha_ingreso TEXT NOT NULL,
    created_at INTEGER DEFAULT (strftime('%s','now'))
);

CREATE TABLE IF NOT EXISTS informes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT NOT NULL,
    tipo TEXT NOT NULL,
    alumno_id INTEGER REFERENCES alumnos(id),
    alumno_nombre TEXT NOT NULL,
    estado TEXT NOT NULL DEFAULT 'borrador',
    fecha TEXT NOT NULL,
    descripcion TEXT,
    created_at INTEGER DEFAULT (strftime('%s','now'))
);
");

// Seed: usuario admin
$existing = $db->query("SELECT COUNT(*) as cnt FROM users")->fetch();
if ((int)$existing['cnt'] === 0) {
    $stmt = $db->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', password_hash('admin', PASSWORD_DEFAULT), 'Administrador', 'admin']);
}

// Seed: cursos
$existingCursos = $db->query("SELECT COUNT(*) as cnt FROM cursos")->fetch();
if ((int)$existingCursos['cnt'] === 0) {
    $cursos = [
        ['1° Básico A',  '1° Básico',  'A', 'Ana González',    34, 4],
        ['1° Básico B',  '1° Básico',  'B', 'Pedro Ramírez',   32, 3],
        ['2° Básico A',  '2° Básico',  'A', 'Carmen López',    35, 5],
        ['2° Básico B',  '2° Básico',  'B', 'Jorge Martínez',  33, 4],
        ['3° Básico A',  '3° Básico',  'A', 'Laura Soto',      36, 5],
        ['4° Básico A',  '4° Básico',  'A', 'Roberto Silva',   34, 4],
        ['4° Básico B',  '4° Básico',  'B', 'Alejandra Muñoz', 33, 3],
        ['5° Básico A',  '5° Básico',  'A', 'Felipe Torres',   35, 4],
        ['5° Básico B',  '5° Básico',  'B', 'Isabel Herrera',  34, 5],
        ['6° Básico A',  '6° Básico',  'A', 'Diego Vargas',    36, 3],
        ['1° Medio A',   '1° Medio',   'A', 'Claudia Reyes',   34, 3],
        ['2° Medio B',   '2° Medio',   'B', 'Andrés Castro',   33, 3],
    ];
    $stmt = $db->prepare("INSERT INTO cursos (nombre, nivel, letra, profesor, total_alumnos) VALUES (?,?,?,?,?)");
    foreach ($cursos as $c) {
        $stmt->execute([$c[0], $c[1], $c[2], $c[3], $c[4]]);
    }
}

// Seed: alumnos
$existingAlumnos = $db->query("SELECT COUNT(*) as cnt FROM alumnos")->fetch();
if ((int)$existingAlumnos['cnt'] === 0) {
    // Obtener IDs de cursos
    $cursosMap = [];
    foreach ($db->query("SELECT id, nombre FROM cursos") as $row) {
        $cursosMap[$row['nombre']] = $row['id'];
    }

    $alumnos = [
        ['Camila Rojas Pérez',     '21.345.678-9', '3° Básico A',  'TEA',                'Trastorno del Espectro Autista',         'activo',   '2025-03-01'],
        ['Benjamín Torres Soto',   '21.456.789-0', '5° Básico B',  'TDAH',               'Trastorno por Déficit de Atención',      'activo',   '2025-03-01'],
        ['Valentina Muñoz García', '21.567.890-1', '1° Medio A',   'Dificultad Lectora', 'Dislexia',                               'activo',   '2025-03-01'],
        ['Matías López Fuentes',   '21.678.901-2', '4° Básico A',  'Discapacidad Motora','Parálisis Cerebral Leve',                'activo',   '2025-03-01'],
        ['Isidora Silva Morales',  '21.789.012-3', '2° Básico B',  'TEL',                'Trastorno Específico del Lenguaje',      'activo',   '2025-03-01'],
        ['Nicolás Herrera Jara',   '21.890.123-4', '6° Básico A',  'Discapacidad Visual','Baja Visión',                            'inactivo', '2024-03-01'],
        ['Antonia Vargas Díaz',    '21.901.234-5', '2° Medio B',   'TDAH',               'Trastorno por Déficit de Atención',      'activo',   '2025-03-01'],
    ];
    $stmt = $db->prepare("INSERT INTO alumnos (nombre, rut, curso_id, necesidad, diagnostico, estado, fecha_ingreso) VALUES (?,?,?,?,?,?,?)");
    foreach ($alumnos as $a) {
        $cursoId = $cursosMap[$a[2]] ?? null;
        $stmt->execute([$a[0], $a[1], $cursoId, $a[3], $a[4], $a[5], $a[6]]);
    }
}

// Seed: informes
$existingInformes = $db->query("SELECT COUNT(*) as cnt FROM informes")->fetch();
if ((int)$existingInformes['cnt'] === 0) {
    $alumnosMap = [];
    foreach ($db->query("SELECT id, nombre FROM alumnos") as $row) {
        $alumnosMap[$row['nombre']] = $row['id'];
    }

    $informes = [
        ['Informe Psicopedagógico — 1er Semestre', 'Psicopedagógico', 'Camila Rojas Pérez',     'completado', '2026-03-15'],
        ['Informe de Avance PIE',                  'Avance',          'Benjamín Torres Soto',   'completado', '2026-04-01'],
        ['Plan de Apoyo Individual',               'PAI',             'Valentina Muñoz García', 'borrador',   '2026-04-10'],
        ['Informe de Seguimiento',                 'Seguimiento',     'Matías López Fuentes',   'pendiente',  '2026-04-20'],
        ['Evaluación de Necesidades',              'Evaluación',      'Isidora Silva Morales',  'borrador',   '2026-04-22'],
    ];
    $stmt = $db->prepare("INSERT INTO informes (titulo, tipo, alumno_id, alumno_nombre, estado, fecha) VALUES (?,?,?,?,?,?)");
    foreach ($informes as $i) {
        $alumnoId = $alumnosMap[$i[2]] ?? null;
        $stmt->execute([$i[0], $i[1], $alumnoId, $i[2], $i[3], $i[4]]);
    }
}

echo "✅ Base de datos inicializada correctamente.\n";
