#!/usr/bin/env python3
"""Build a silent kinetic video with 3D perspective camera motion."""

from __future__ import annotations

import math
import shutil
import subprocess
import tempfile
from pathlib import Path

import numpy as np
from PIL import Image, ImageDraw, ImageEnhance, ImageFilter

ROOT = Path("/workspace")
IMG_DIR = ROOT / "public/images/pupitre-inteligente"
OUT_MP4 = ROOT / "public/videos/pupitre-inteligente-aula.mp4"
ART_DIR = Path("/opt/cursor/artifacts")

FPS = 30
CLIP_SEC = 3.5
XFADE = 0.65
W, H = 1920, 1080

# Shot list: (filename, motion mode)
SHOTS = [
    ("01-aula-boche.jpg", "orbit_in"),
    ("09-aula-angulo-dinamico.jpg", "dutch_spin"),
    ("02-pizarra-microfonos.jpg", "dolly_yaw"),
    ("03-nina-pupitre.jpg", "push_in"),
    ("10-pupitre-hero-3d.jpg", "hero_orbit"),
    ("04-detalle-pupitre.jpg", "sweep_right"),
    ("05-paneles-microperforados.jpg", "rise_tilt"),
    ("11-haz-3d-comunicacion.jpg", "fly_through"),
    ("06-comunicacion-priorizada.jpg", "push_in"),
    ("12-audifono-orbit-3d.jpg", "spiral"),
    ("07-audifono-conexion.jpg", "hero_orbit"),
    ("08-cierre-aula.jpg", "pull_out"),
]

TRANSITIONS = [
    "circleopen",
    "diagtl",
    "distance",
    "slideleft",
    "horzopen",
    "wiperight",
    "smoothleft",
    "circleclose",
    "slideright",
    "wipeleft",
    "fade",
]


def find_coeffs(pa: list[tuple[float, float]], pb: list[tuple[float, float]]) -> np.ndarray:
    matrix = []
    for p1, p2 in zip(pa, pb):
        matrix.append([p1[0], p1[1], 1, 0, 0, 0, -p2[0] * p1[0], -p2[0] * p1[1]])
        matrix.append([0, 0, 0, p1[0], p1[1], 1, -p2[1] * p1[0], -p2[1] * p1[1]])
    a = np.asarray(matrix, dtype=float)
    b = np.asarray(pb, dtype=float).reshape(8)
    res = np.linalg.lstsq(a, b, rcond=None)[0]
    return res


def motion_quad(mode: str, t: float, w: int, h: int) -> list[tuple[float, float]]:
    """Return destination quad (TL, TR, BR, BL) for a normalized time t in [0,1]."""
    # Base margins for zoom room
    z = 0.08
    # Mode-specific 3D-ish corner animation
    if mode == "orbit_in":
        yaw = 0.12 * math.sin(2 * math.pi * t)
        pitch = 0.06 * math.cos(2 * math.pi * t)
        zoom = 0.10 + 0.10 * t
    elif mode == "dutch_spin":
        yaw = 0.10 * math.cos(2 * math.pi * t)
        pitch = 0.08 * math.sin(2 * math.pi * t)
        zoom = 0.12 + 0.06 * math.sin(math.pi * t)
        # dutch roll via uneven corners below
    elif mode == "dolly_yaw":
        yaw = -0.14 + 0.28 * t
        pitch = 0.04 * math.sin(math.pi * t)
        zoom = 0.08 + 0.12 * t
    elif mode == "push_in":
        yaw = 0.04 * math.sin(2 * math.pi * t)
        pitch = 0.03 * math.cos(2 * math.pi * t)
        zoom = 0.06 + 0.18 * t
    elif mode == "hero_orbit":
        yaw = 0.18 * math.sin(2 * math.pi * t)
        pitch = 0.10 * math.cos(2 * math.pi * t)
        zoom = 0.10 + 0.08 * abs(math.sin(math.pi * t))
    elif mode == "sweep_right":
        yaw = -0.16 + 0.32 * t
        pitch = 0.05
        zoom = 0.12
    elif mode == "rise_tilt":
        yaw = 0.05 * math.sin(math.pi * t)
        pitch = 0.16 - 0.28 * t
        zoom = 0.10 + 0.10 * t
    elif mode == "fly_through":
        yaw = 0.10 * math.sin(4 * math.pi * t)
        pitch = 0.08 * math.cos(4 * math.pi * t)
        zoom = 0.05 + 0.22 * t
    elif mode == "spiral":
        yaw = 0.16 * math.sin(4 * math.pi * t)
        pitch = 0.16 * math.cos(4 * math.pi * t)
        zoom = 0.08 + 0.14 * t
    elif mode == "pull_out":
        yaw = 0.06 * math.sin(2 * math.pi * t)
        pitch = 0.04
        zoom = 0.22 - 0.14 * t
    else:
        yaw, pitch, zoom = 0.08 * math.sin(2 * math.pi * t), 0.05, 0.1

    # Convert yaw/pitch/zoom into perspective quad (destination points)
    # Positive yaw: left side smaller (receding), right larger
    left_shrink = max(0.0, yaw) * h * 0.55
    right_shrink = max(0.0, -yaw) * h * 0.55
    top_shrink = max(0.0, pitch) * w * 0.35
    bot_shrink = max(0.0, -pitch) * w * 0.35

    m = (z + zoom) * min(w, h)
    dutch = 0.0
    if mode == "dutch_spin":
        dutch = 28 * math.sin(2 * math.pi * t)
    elif mode == "spiral":
        dutch = 18 * math.sin(2 * math.pi * t)

    tl = (m + top_shrink + dutch * 0.15, m + left_shrink - dutch * 0.05)
    tr = (w - m - top_shrink + dutch * 0.05, m + right_shrink + dutch * 0.15)
    br = (w - m - bot_shrink - dutch * 0.15, h - m - right_shrink + dutch * 0.05)
    bl = (m + bot_shrink - dutch * 0.05, h - m - left_shrink - dutch * 0.15)

    # Horizontal/vertical pan
    if mode in ("sweep_right", "dolly_yaw"):
        pan_x = (t - 0.5) * w * 0.08
    elif mode in ("orbit_in", "hero_orbit", "spiral"):
        pan_x = math.sin(2 * math.pi * t) * w * 0.04
    else:
        pan_x = math.sin(2 * math.pi * t) * w * 0.02

    if mode in ("rise_tilt",):
        pan_y = (0.5 - t) * h * 0.10
    elif mode in ("fly_through", "push_in"):
        pan_y = math.cos(2 * math.pi * t) * h * 0.03
    else:
        pan_y = math.cos(2 * math.pi * t) * h * 0.02

    return [
        (tl[0] + pan_x, tl[1] + pan_y),
        (tr[0] + pan_x, tr[1] + pan_y),
        (br[0] + pan_x, br[1] + pan_y),
        (bl[0] + pan_x, bl[1] + pan_y),
    ]


def add_energy_overlay(img: Image.Image, t: float, mode: str) -> Image.Image:
    """Subtle cyan light streaks / glow to sell 3D tech energy (no text)."""
    base = img.convert("RGBA")
    overlay = Image.new("RGBA", base.size, (0, 0, 0, 0))
    draw = ImageDraw.Draw(overlay)
    w, h = base.size

    if mode in ("fly_through", "hero_orbit", "spiral", "dutch_spin", "push_in"):
        # sweeping beam
        x = int((0.15 + 0.7 * t) * w)
        for i, alpha in enumerate((40, 24, 12)):
            draw.line([(x - 40 * i, 0), (x + 80 - 40 * i, h)], fill=(0, 220, 255, alpha), width=6 - i * 2)
        # soft orbs
        for k in range(3):
            cx = int(w * (0.3 + 0.2 * k) + 40 * math.sin(2 * math.pi * (t + k / 3)))
            cy = int(h * (0.35 + 0.15 * math.cos(2 * math.pi * (t + k / 5))))
            r = 18 + 10 * k
            draw.ellipse((cx - r, cy - r, cx + r, cy + r), fill=(80, 240, 255, 28))

    overlay = overlay.filter(ImageFilter.GaussianBlur(radius=3))
    out = Image.alpha_composite(base, overlay).convert("RGB")
    # slight contrast punch
    out = ImageEnhance.Contrast(out).enhance(1.06)
    out = ImageEnhance.Color(out).enhance(1.08)
    return out


def render_clip(src: Path, mode: str, frames_dir: Path) -> None:
    frames_dir.mkdir(parents=True, exist_ok=True)
    im = Image.open(src).convert("RGB")
    # Upscale source for quality when warping
    im = im.resize((int(W * 1.25), int(H * 1.25)), Image.Resampling.LANCZOS)
    sw, sh = im.size
    src_quad = [(0, 0), (sw, 0), (sw, sh), (0, sh)]
    n = int(CLIP_SEC * FPS)

    for i in range(n):
        t = i / max(1, n - 1)
        dst = motion_quad(mode, t, sw, sh)
        coeffs = find_coeffs(dst, src_quad)
        warped = im.transform((sw, sh), Image.Transform.PERSPECTIVE, coeffs, Image.Resampling.BICUBIC)
        # center-crop to 1920x1080
        left = (sw - W) // 2
        top = (sh - H) // 2
        frame = warped.crop((left, top, left + W, top + H))
        frame = add_energy_overlay(frame, t, mode)
        frame.save(frames_dir / f"f{i:04d}.jpg", quality=92, optimize=True)


def encode_clip(frames_dir: Path, out_mp4: Path) -> None:
    subprocess.run(
        [
            "ffmpeg",
            "-y",
            "-framerate",
            str(FPS),
            "-i",
            str(frames_dir / "f%04d.jpg"),
            "-c:v",
            "libx264",
            "-pix_fmt",
            "yuv420p",
            "-crf",
            "18",
            "-preset",
            "veryfast",
            "-an",
            str(out_mp4),
        ],
        check=True,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )


def concat_xfade(clips: list[Path], out: Path) -> None:
    n = len(clips)
    step = CLIP_SEC - XFADE
    parts = []
    for i in range(1, n):
        trans = TRANSITIONS[(i - 1) % len(TRANSITIONS)]
        offset = round(i * step, 3)  # i=1 -> step; wait should be (i)*step with i starting 1 giving step, 2*step...
        # Correct: first offset = CLIP_SEC - XFADE = step
        offset = round(i * step, 3)
        # BUG: i=1 → 1*step = step ✓
        left = "[0]" if i == 1 else f"[v{i-1}]"
        right = f"[{i}]"
        out_l = "[vout]" if i == n - 1 else f"[v{i}]"
        parts.append(f"{left}{right}xfade=transition={trans}:duration={XFADE}:offset={offset}{out_l}")

    # Fix offsets: when adding clip at index i (1-based new clip index), offset = i * step? 
    # Adding 2nd clip (i=1): offset = step = 2.85
    # Adding 3rd (i=2): offset = 2*step
    # So offset = i * step where i is 1..n-1 — YES if step = CLIP-XFADE
    # But wait I used `offset = round(i * step, 3)` which for i=1 is step. Good.
    # Actually WRONG historically: should be offset = i * step for i in 1..n-1:
    # length after k joins = CLIP + k*(CLIP-XFADE)? After first xfade length = 2*CLIP - XFADE = CLIP + step
    # Second xfade offset should be length_so_far - XFADE = CLIP + step - XFADE = step + step = 2*step
    # Yes offset = i * step for i-th xfade (1-based).

    filter_complex = ";".join(parts)
    cmd = ["ffmpeg", "-y"]
    for c in clips:
        cmd += ["-i", str(c)]
    cmd += [
        "-filter_complex",
        filter_complex,
        "-map",
        "[vout]",
        "-an",
        "-c:v",
        "libx264",
        "-pix_fmt",
        "yuv420p",
        "-crf",
        "18",
        "-preset",
        "medium",
        "-movflags",
        "+faststart",
        str(out),
    ]
    subprocess.run(cmd, check=True)


def main() -> None:
    tmp = Path(tempfile.mkdtemp(prefix="pupitre3d_"))
    print(f"tmp={tmp}")
    clips: list[Path] = []

    for idx, (name, mode) in enumerate(SHOTS, start=1):
        src = IMG_DIR / name
        if not src.exists():
            raise FileNotFoundError(src)
        fdir = tmp / f"frames_{idx:02d}"
        clip = tmp / f"clip_{idx:02d}.mp4"
        print(f"[{idx}/{len(SHOTS)}] {name} ({mode})")
        render_clip(src, mode, fdir)
        encode_clip(fdir, clip)
        clips.append(clip)
        shutil.rmtree(fdir, ignore_errors=True)

    print("concat + xfade...")
    # Fix offset bug: i*step where i starts at 1 gives step, 2step... but formula should use:
    # offset_i = i * step  with i=1..  — verified above
    # However first line used `offset = round(i * step, 3)` then commented — let's rebuild filter carefully in concat
    concat_xfade(clips, OUT_MP4)

    ART_DIR.mkdir(parents=True, exist_ok=True)
    for dest in (
        ART_DIR / "pupitre_inteligente_3d_movimiento.mp4",
        ART_DIR / "descargar_pupitre_inteligente_aula.mp4",
        ART_DIR / "pupitre_inteligente_aula_silencioso.mp4",
    ):
        shutil.copy2(OUT_MP4, dest)

    probe = subprocess.check_output(
        [
            "ffprobe",
            "-v",
            "error",
            "-show_entries",
            "format=duration,size",
            "-of",
            "default=noprint_wrappers=1",
            str(OUT_MP4),
        ],
        text=True,
    )
    print(probe)
    print("done", OUT_MP4)


if __name__ == "__main__":
    main()
