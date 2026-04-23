CREATE TABLE `alumnos` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`nombre` text NOT NULL,
	`rut` text NOT NULL,
	`curso_id` integer,
	`necesidad` text NOT NULL,
	`estado` text DEFAULT 'activo' NOT NULL,
	`fecha_ingreso` text NOT NULL,
	`created_at` integer,
	FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`) ON UPDATE no action ON DELETE no action
);
--> statement-breakpoint
CREATE UNIQUE INDEX `alumnos_rut_unique` ON `alumnos` (`rut`);--> statement-breakpoint
CREATE TABLE `cursos` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`nombre` text NOT NULL,
	`nivel` text NOT NULL,
	`letra` text NOT NULL,
	`profesor` text NOT NULL,
	`total_alumnos` integer DEFAULT 0 NOT NULL,
	`created_at` integer
);
--> statement-breakpoint
CREATE TABLE `informes` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`titulo` text NOT NULL,
	`tipo` text NOT NULL,
	`alumno_id` integer,
	`alumno_nombre` text NOT NULL,
	`estado` text DEFAULT 'borrador' NOT NULL,
	`fecha` text NOT NULL,
	`descripcion` text,
	`created_at` integer,
	FOREIGN KEY (`alumno_id`) REFERENCES `alumnos`(`id`) ON UPDATE no action ON DELETE no action
);
--> statement-breakpoint
CREATE TABLE `users` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`username` text NOT NULL,
	`password` text NOT NULL,
	`name` text NOT NULL,
	`role` text DEFAULT 'docente' NOT NULL,
	`created_at` integer
);
--> statement-breakpoint
CREATE UNIQUE INDEX `users_username_unique` ON `users` (`username`);