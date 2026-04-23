import { sqliteTable, text, integer } from "drizzle-orm/sqlite-core";

// Tabla de usuarios del sistema
export const users = sqliteTable("users", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  username: text("username").notNull().unique(),
  password: text("password").notNull(),
  name: text("name").notNull(),
  role: text("role").notNull().default("docente"), // 'admin' | 'docente'
  createdAt: integer("created_at", { mode: "timestamp" }).$defaultFn(() => new Date()),
});

// Tabla de cursos
export const cursos = sqliteTable("cursos", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  nombre: text("nombre").notNull(),         // ej: "1° Básico A"
  nivel: text("nivel").notNull(),           // ej: "1° Básico"
  letra: text("letra").notNull(),           // ej: "A"
  profesor: text("profesor").notNull(),
  totalAlumnos: integer("total_alumnos").notNull().default(0),
  createdAt: integer("created_at", { mode: "timestamp" }).$defaultFn(() => new Date()),
});

// Tabla de alumnos PIE
export const alumnos = sqliteTable("alumnos", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  nombre: text("nombre").notNull(),
  rut: text("rut").notNull().unique(),
  cursoId: integer("curso_id").references(() => cursos.id),
  necesidad: text("necesidad").notNull(),   // ej: "DEA", "TDAH", "TEA"
  estado: text("estado").notNull().default("activo"), // 'activo' | 'inactivo'
  fechaIngreso: text("fecha_ingreso").notNull(),
  createdAt: integer("created_at", { mode: "timestamp" }).$defaultFn(() => new Date()),
});

// Tabla de informes
export const informes = sqliteTable("informes", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  titulo: text("titulo").notNull(),
  tipo: text("tipo").notNull(),             // ej: "PACI", "Evaluación", "Seguimiento"
  alumnoId: integer("alumno_id").references(() => alumnos.id),
  alumnoNombre: text("alumno_nombre").notNull(),
  estado: text("estado").notNull().default("borrador"), // 'borrador' | 'completado'
  fecha: text("fecha").notNull(),
  descripcion: text("descripcion"),
  createdAt: integer("created_at", { mode: "timestamp" }).$defaultFn(() => new Date()),
});
