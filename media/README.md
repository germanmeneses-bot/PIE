# Memoria visual — Escuela Chacaico

Portada y video con transiciones generados a partir de las fotos de la visita / actividades escolares.

## Archivos

| Archivo | Descripción |
|---------|-------------|
| `portada-escuela-chacaico.jpg` | Portada 1920×1080 |
| `video-escuela-chacaico.mp4` | Montaje **3:00**, 51 fotos nuevas + audio *Poyenekayan* |
| `poyenekayan-beatriz-pichi-malen.mp3` | Pista de audio usada en el video |

## Regenerar

```bash
python3 scripts/create_portada.py
python3 scripts/create_video.py
```

Requisitos: Python 3 + Pillow, `ffmpeg`.

Las fotos fuente son el lote `01a0eeb0-*.jpg` en la carpeta de assets del entorno.

```bash
python3 scripts/create_portada.py
python3 scripts/create_video.py
python3 scripts/mux_audio.py
```
