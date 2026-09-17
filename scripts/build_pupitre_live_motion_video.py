#!/usr/bin/env python3
"""Live-camera-feel silent video via smooth temporal blends + handheld micro-motion.

Uses dense pose keyframes and linear crossfades (no optical-flow warps that ghost).
"""

from __future__ import annotations

import shutil
import subprocess
import tempfile
from pathlib import Path

import cv2
import numpy as np

ASSETS = Path("/opt/cursor/artifacts/assets")
ROOT = Path("/workspace/public/images/pupitre-inteligente")
OUT_MP4 = Path("/workspace/public/videos/pupitre-inteligente-aula.mp4")
ART_DIR = Path("/opt/cursor/artifacts")

W, H, FPS = 1920, 1080, 30
PAIR_SEC = 0.85
HOLD_LAST = 0.4
SCENE_XFADE = 0.35

SCENES: list[list[str]] = [
    [
        "real/real-01a-aula.jpg",
        "live/seq-aula-01.jpg",
        "live/seq-aula-02.jpg",
        "live/seq-aula-03.jpg",
        "live/live-01-02.jpg",
        "real/real-01b-aula.jpg",
        "live/live-01-03.jpg",
        "real/real-01c-aula.jpg",
        "live/live-01-04.jpg",
    ],
    [
        "real/real-02a-profe.jpg",
        "live/seq-profe-01.jpg",
        "live/seq-profe-02.jpg",
        "live/live-02-02.jpg",
        "real/real-02b-profe.jpg",
        "live/live-02-03.jpg",
        "real/real-02c-profe.jpg",
        "live/live-02-04.jpg",
    ],
    [
        "real/real-03a-nina.jpg",
        "live/seq-nina-01.jpg",
        "live/seq-nina-02.jpg",
        "live/live-03-02.jpg",
        "real/real-03b-nina.jpg",
        "live/live-03-03.jpg",
        "real/real-03c-nina.jpg",
        "live/live-03-04.jpg",
    ],
    [
        "real/real-04a-pupitre.jpg",
        "live/live-04-02.jpg",
        "real/real-04c-pupitre.jpg",
    ],
    ["real/real-04b-paneles.jpg"],
    [
        "real/real-05a-audifono.jpg",
        "real/real-05b-audifono.jpg",
    ],
    [
        "real/real-06a-cierre.jpg",
        "live/live-06-02.jpg",
        "real/real-06b-cierre.jpg",
        "live/live-06-03.jpg",
    ],
]


def run(cmd: list[str]) -> None:
    subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)


def probe(path: Path) -> float:
    return float(
        subprocess.check_output(
            [
                "ffprobe",
                "-v",
                "error",
                "-show_entries",
                "format=duration",
                "-of",
                "default=noprint_wrappers=1:nokey=1",
                str(path),
            ],
            text=True,
        ).strip()
    )


def resolve(rel: str) -> Path:
    kind, name = rel.split("/", 1)
    p = ROOT / kind / name
    if not p.exists():
        p = ASSETS / name
    if not p.exists():
        raise FileNotFoundError(rel)
    return p


def load(path: Path) -> np.ndarray:
    im = cv2.imread(str(path))
    if im is None:
        raise FileNotFoundError(path)
    return cv2.resize(im, (W, H), interpolation=cv2.INTER_AREA)


def handheld(im: np.ndarray, t: float) -> np.ndarray:
    zoom = 1.0 + 0.004 * np.sin(2 * np.pi * t)
    dx = 2.5 * np.sin(2 * np.pi * t * 1.1)
    dy = 1.8 * np.cos(2 * np.pi * t * 0.9)
    m = np.array(
        [[zoom, 0, dx + (1 - zoom) * W * 0.5], [0, zoom, dy + (1 - zoom) * H * 0.5]],
        np.float32,
    )
    return cv2.warpAffine(im, m, (W, H), borderMode=cv2.BORDER_REPLICATE)


def lerp_frames(a: np.ndarray, b: np.ndarray, n: int) -> list[np.ndarray]:
    out = []
    for i in range(n):
        t = i / (n - 1) if n > 1 else 0.0
        s = t * t * (3 - 2 * t)  # smoothstep
        fr = cv2.addWeighted(a, 1 - s, b, s, 0)
        out.append(handheld(fr, t))
    return out


def build_scene(paths: list[Path], out: Path) -> None:
    imgs = [load(p) for p in paths]
    frames: list[np.ndarray] = []
    if len(imgs) == 1:
        n = int(2.6 * FPS)
        for i in range(n):
            frames.append(handheld(imgs[0], i / max(1, n - 1)))
    else:
        n = max(10, int(PAIR_SEC * FPS))
        for i in range(len(imgs) - 1):
            seg = lerp_frames(imgs[i], imgs[i + 1], n)
            frames.extend(seg if i == 0 else seg[1:])
        hold_n = int(HOLD_LAST * FPS)
        for i in range(hold_n):
            frames.append(handheld(imgs[-1], i / max(1, hold_n)))

    tmp = out.parent / (out.stem + "_f")
    if tmp.exists():
        shutil.rmtree(tmp)
    tmp.mkdir()
    for i, fr in enumerate(frames):
        cv2.imwrite(str(tmp / f"f{i:05d}.jpg"), fr, [int(cv2.IMWRITE_JPEG_QUALITY), 90])
    run(
        [
            "ffmpeg",
            "-y",
            "-framerate",
            str(FPS),
            "-i",
            str(tmp / "f%05d.jpg"),
            "-c:v",
            "libx264",
            "-pix_fmt",
            "yuv420p",
            "-crf",
            "20",
            "-preset",
            "veryfast",
            "-an",
            str(out),
        ]
    )
    shutil.rmtree(tmp, ignore_errors=True)


def concat_scenes(clips: list[Path], out: Path) -> None:
    durs = [probe(c) for c in clips]
    parts = []
    timeline = durs[0]
    for i in range(1, len(clips)):
        offset = round(max(0.05, timeline - SCENE_XFADE), 3)
        left = "[0]" if i == 1 else f"[v{i-1}]"
        out_l = "[vout]" if i == len(clips) - 1 else f"[v{i}]"
        parts.append(
            f"{left}[{i}]xfade=transition=fade:duration={SCENE_XFADE}:offset={offset}{out_l}"
        )
        timeline += durs[i] - SCENE_XFADE
    cmd = ["ffmpeg", "-y"]
    for c in clips:
        cmd += ["-i", str(c)]
    cmd += [
        "-filter_complex",
        ";".join(parts),
        "-map",
        "[vout]",
        "-an",
        "-c:v",
        "libx264",
        "-pix_fmt",
        "yuv420p",
        "-crf",
        "21",
        "-preset",
        "medium",
        "-movflags",
        "+faststart",
        str(out),
    ]
    run(cmd)


def main() -> None:
    (ROOT / "live").mkdir(parents=True, exist_ok=True)
    for p in ASSETS.glob("live-*.jpg"):
        shutil.copy2(p, ROOT / "live" / p.name)

    tmp = Path(tempfile.mkdtemp(prefix="pupitre_live_"))
    print(f"tmp={tmp} PAIR_SEC={PAIR_SEC}")
    clips = []
    for si, rels in enumerate(SCENES, 1):
        keys = [resolve(r) for r in rels]
        clip = tmp / f"scene_{si:02d}.mp4"
        print(f"scene {si}: {[k.name for k in keys]}")
        build_scene(keys, clip)
        print(f"  {probe(clip):.2f}s")
        clips.append(clip)

    concat_scenes(clips, OUT_MP4)
    ART_DIR.mkdir(parents=True, exist_ok=True)
    for name in (
        "pupitre_inteligente_en_vivo.mp4",
        "descargar_pupitre_inteligente_aula.mp4",
        "pupitre_inteligente_movimiento_real.mp4",
        "pupitre_inteligente_aula_silencioso.mp4",
    ):
        shutil.copy2(OUT_MP4, ART_DIR / name)

    print(f"duration={probe(OUT_MP4)}")
    print(f"size={OUT_MP4.stat().st_size}")
    print("done", OUT_MP4)


if __name__ == "__main__":
    main()
