# Active Context: Sistema PIE Escolar

## Current State

**App Status**: ✅ Sistema PIE funcional desplegado

The template is a clean Next.js 16 starter with TypeScript and Tailwind CSS 4. It's ready for AI-assisted expansion to build any type of application.

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

## Current Structure

| File/Directory | Purpose | Status |
|----------------|---------|--------|
| `src/app/page.tsx` | Redirect a /dashboard | ✅ Ready |
| `src/app/layout.tsx` | Root layout | ✅ Ready |
| `src/app/(main)/layout.tsx` | Layout con Sidebar | ✅ Ready |
| `src/app/(main)/dashboard/page.tsx` | Dashboard principal | ✅ Ready |
| `src/app/(main)/alumnos/page.tsx` | Alumnos PIE | ✅ Ready |
| `src/app/(main)/cursos/page.tsx` | Cursos | ✅ Ready |
| `src/app/(main)/informes/page.tsx` | Informes | ✅ Ready |
| `src/app/(main)/perfil/page.tsx` | Mi Perfil | ✅ Ready |
| `src/components/Sidebar.tsx` | Sidebar de navegación | ✅ Ready |

## Current Focus

Sistema PIE Escolar completamente funcional. Posibles mejoras futuras:
1. Integrar base de datos (ver receta add-database.md)
2. Agregar autenticación
3. Conectar formularios a API routes reales

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
