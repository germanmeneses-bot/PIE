# Sistema PIE Escolar — Versión PHP

Aplicación web PHP pura con SQLite. No requiere Node.js ni Composer.

## Requisitos

- PHP 8.0 o superior (con extensiones `pdo_sqlite`, `sqlite3`)
- Servidor web Apache/Nginx **o** usar el servidor built-in de PHP

## Abrir en Visual Studio Code

1. Abrir VS Code en la carpeta `php-version/`:
   ```
   code php-version/
   ```

2. Instalar la extensión recomendada **PHP Server** (`brapifra.phpserver`).

3. Inicializar la base de datos (solo la primera vez):
   ```bash
   php includes/db_setup.php
   ```

4. Iniciar servidor integrado de PHP desde la terminal de VS Code:
   ```bash
   php -S localhost:8080
   ```

5. Abrir en el navegador: [http://localhost:8080](http://localhost:8080)

## Credenciales por defecto

| Usuario | Contraseña |
|---------|-----------|
| `admin` | `admin`   |

## Estructura de archivos

```
php-version/
├── index.php              # Punto de entrada (redirige)
├── login.php              # Página de inicio de sesión
├── logout.php             # Cierra la sesión
├── dashboard.php          # Dashboard principal
├── alumnos.php            # Gestión de alumnos PIE
├── cursos.php             # Vista de cursos
├── informes.php           # Gestión de informes
├── perfil.php             # Perfil del usuario
├── .htaccess              # Config Apache (protección BD)
├── includes/
│   ├── config.php         # Configuración, sesión, helpers
│   ├── db_setup.php       # Crea tablas y datos iniciales
│   ├── sidebar.php        # Sidebar de navegación
│   ├── layout_start.php   # HTML head + apertura layout
│   └── layout_end.php     # Cierre de layout
└── data/
    └── pie_escolar.db     # Base de datos SQLite (se crea automáticamente)
```

## Despliegue en servidor Apache/Nginx

1. Copiar la carpeta `php-version/` al `DocumentRoot` (ej: `/var/www/html/pie/`)
2. Dar permisos de escritura al directorio `data/`:
   ```bash
   chmod 775 data/
   ```
3. Ejecutar el setup de BD:
   ```bash
   php includes/db_setup.php
   ```
4. Acceder a `http://tu-servidor/pie/`

## Tecnologías utilizadas

- **PHP 8** — Lógica del servidor
- **SQLite** via **PDO** — Base de datos
- **Tailwind CSS** (CDN) — Estilos
- Sin dependencias de Composer
