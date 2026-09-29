# Memoria visual — Escuela Chacaico

Portada y video con transiciones generados a partir de las fotos de la visita / actividades escolares.

## Archivos

| Archivo | Descripción |
|---------|-------------|
| `portada-escuela-chacaico.jpg` | Portada 1920×1080 |
| `video-escuela-chacaico.mp4` | Montaje **3:00** con transiciones (xfade), 44 fotos + portada |

## Regenerar

```bash
python3 scripts/create_portada.py
python3 scripts/create_video.py
```

Requisitos: Python 3 + Pillow, `ffmpeg`.

Las fotos fuente se esperan en la carpeta de assets del entorno (o ajusta `ASSETS` en los scripts).
