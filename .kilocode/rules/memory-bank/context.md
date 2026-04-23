# Active Context: Sistema PIE Escolar

## Current State

**App Status**: ✅ Sistema PIE con autenticación y base de datos SQLite

Sistema PIE Escolar completo con login obligatorio (usuario: admin / contraseña: admin), base de datos SQLite via Drizzle ORM, y protección de rutas por middleware.

## Recently Completed

- [x] Base Next.js 16 setup with App Router
- [x] TypeScript configuration with strict mode
- [x] Tailwind CSS 4 integration
- [x] ESLint configuration
- [x] Sistema PIE Escolar completo con sidebar de navegación
- [x] Dashboard con estadísticas, tabla de alumnos recientes y acciones rápidas
- [x] Sección Alumnos PIE con tabla filtrable y modal de creación
- [x] Sección Cursos con tarjetas por nivel y resumen estadístico
- [x] Sección Informes con lista de informes y modal completo de creación
- [x] Sección Mi Perfil con edición de datos, actividad reciente y cambio de contraseña
- [x] Base de datos SQLite con Drizzle ORM (tablas: users, alumnos, cursos, informes)
- [x] Autenticación con cookie de sesión (httpOnly, 8 horas)
- [x] Página de login con diseño acorde al sistema
- [x] Middleware que protege todas las rutas excepto /login y /api/auth/*
- [x] API routes: POST /api/auth/login y POST /api/auth/logout
- [x] Botón "Cerrar sesión" en Sidebar
- [x] Usuario admin por defecto: admin / admin (creado en seed)

## Current Structure

| File/Directory | Purpose | Status |
|----------------|---------|--------|
| `src/app/page.tsx` | Redirect a /dashboard | ✅ Ready |
| `src/app/layout.tsx` | Root layout | ✅ Ready |
| `src/app/login/page.tsx` | Página de login | ✅ Ready |
| `src/app/(main)/layout.tsx` | Layout con Sidebar | ✅ Ready |
| `src/app/(main)/dashboard/page.tsx` | Dashboard principal | ✅ Ready |
| `src/app/(main)/alumnos/page.tsx` | Alumnos PIE | ✅ Ready |
| `src/app/(main)/cursos/page.tsx` | Cursos | ✅ Ready |
| `src/app/(main)/informes/page.tsx` | Informes | ✅ Ready |
| `src/app/(main)/perfil/page.tsx` | Mi Perfil | ✅ Ready |
| `src/components/Sidebar.tsx` | Sidebar con logout | ✅ Ready |
| `src/middleware.ts` | Protección de rutas | ✅ Ready |
| `src/db/schema.ts` | Tablas: users, alumnos, cursos, informes | ✅ Ready |
| `src/db/index.ts` | Cliente Drizzle | ✅ Ready |
| `src/db/migrate.ts` | Script de migraciones | ✅ Ready |
| `src/db/seed.ts` | Datos iniciales (admin) | ✅ Ready |
| `src/db/migrations/` | Migraciones SQL generadas | ✅ Ready |
| `src/app/api/auth/login/route.ts` | API login | ✅ Ready |
| `src/app/api/auth/logout/route.ts` | API logout | ✅ Ready |
| `drizzle.config.ts` | Config Drizzle Kit | ✅ Ready |

## Current Focus

Sistema PIE Escolar con autenticación y BD. Próximos pasos:
1. Conectar formularios de alumnos/cursos/informes a la BD real
2. Agregar gestión de usuarios desde el panel admin
3. Mejorar seguridad (hashear contraseñas con bcrypt)

## Quick Start Guide

### To add a new page:

Create a file at `src/app/[route]/page.tsx`:
```tsx
export default function NewPage() {
  return <div>New page content</div>;
}
```

### To add components:

Create `src/components/` directory and add components:
```tsx
// src/components/ui/Button.tsx
export function Button({ children }: { children: React.ReactNode }) {
  return <button className="px-4 py-2 bg-blue-600 text-white rounded">{children}</button>;
}
```

### To add a database:

Follow `.kilocode/recipes/add-database.md`

### To add API routes:

Create `src/app/api/[route]/route.ts`:
```tsx
import { NextResponse } from "next/server";

export async function GET() {
  return NextResponse.json({ message: "Hello" });
}
```

## Available Recipes

| Recipe | File | Use Case |
|--------|------|----------|
| Add Database | `.kilocode/recipes/add-database.md` | Data persistence with Drizzle + SQLite |

## Pending Improvements

- [ ] Add more recipes (auth, email, etc.)
- [ ] Add example components
- [ ] Add testing setup recipe

## Session History

| Date | Changes |
|------|---------|
| Initial | Template created with base setup |
| 2026-04-23 | Sistema PIE Escolar completo: sidebar + dashboard + alumnos + cursos + informes + perfil |
| 2026-04-23 | BD SQLite con Drizzle ORM + autenticación login/logout + middleware de protección de rutas |
