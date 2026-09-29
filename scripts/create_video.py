#!/usr/bin/env python3
"""Build a slideshow video with xfade transitions from Escuela Chacaico photos."""

from __future__ import annotations

import subprocess
import sys
from pathlib import Path

from PIL import Image, ImageEnhance, ImageFilter

ASSETS = Path("/home/ubuntu/.cursor/projects/workspace/assets")
WORK = Path("/tmp/chacaico-clips")
OUT = Path("/workspace/media/video-escuela-chacaico.mp4")
ART = Path("/opt/cursor/artifacts/video-escuela-chacaico.mp4")
PORTADA = Path("/workspace/media/portada-escuela-chacaico.jpg")

W, H = 1920, 1080
FPS = 30
CLIP_DUR = 2.6
XFADE = 0.65
# Stick to stable xfade modes (avoid circlecrop/radial/fadeblack — they flash black)
TRANSITIONS = [
    "fade",
    "dissolve",
    "smoothleft",
    "slideleft",
    "wipeleft",
    "smoothright",
    "slideright",
    "wiperight",
    "hblur",
    "distance",
]

SELECTED = [
    "01a0eab2-85c9-73ab-b214-5d4b766207d5.jpg",
    "01a0eab2-85e4-7be8-848a-388e8881445d.jpg",
    "01a0eab2-8617-7c3d-870a-58dc77143cd9.jpg",
    "01a0eab2-8631-7165-bcc9-4127e64d6040.jpg",
    "01a0eab2-881a-7178-96ae-50847d1ed912.jpg",
    "01a0eab2-88a2-7bcd-9156-eddbfcb33d01.jpg",
    "01a0eab2-894a-7958-ae1b-7d15343791b4.jpg",
    "01a0eab2-8c8d-7711-bcd4-57d5c92264c0.jpg",
    # skip 8bf5 — has large "FOTOGRAFIA" watermark
    "01a0eab2-8c13-7ebd-9cfe-44e2722a8adc.jpg",
    "01a0eab2-8c32-7c1f-b31f-24fbdd46ad6e.jpg",
    "01a0eab2-8c51-72de-99a0-3497b84a1737.jpg",
    "01a0eab2-8c6f-7194-90c7-4201bb02fd8e.jpg",
    "01a0eab2-8d05-7502-834f-ad9413437047.jpg",
    "01a0eab2-8d65-7205-bf53-b393da08b94e.jpg",
    "01a0eab2-8ddb-7d00-8470-3046ca34902b.jpg",
    "01a0eab2-8e4d-79f5-b292-a7f63e15d2c1.jpg",
    "01a0eab2-8ebe-79a4-b874-e1b7b7265977.jpg",
    "01a0eab2-8f31-76a8-a8c6-e0c59b450b6c.jpg",
    "01a0eab2-914f-721e-80e9-d4341f6da5cd.jpg",
    "01a0eab2-91c9-7202-af38-962a1ec100a0.jpg",
]


def letterbox(img: Image.Image) -> Image.Image:
    src = img.convert("RGB")
    bg = src.resize((W, H), Image.Resampling.LANCZOS).filter(ImageFilter.GaussianBlur(28))
    bg = ImageEnhance.Brightness(bg).enhance(0.42)
    scale = min(W / src.width, H / src.height)
    nw, nh = int(src.width * scale), int(src.height * scale)
    fg = src.resize((nw, nh), Image.Resampling.LANCZOS)
    bg.paste(fg, ((W - nw) // 2, (H - nh) // 2))
    return bg


def prepare_frames() -> list[tuple[Path, float]]:
    """Return (frame_path, visible_duration) pairs."""
    WORK.mkdir(parents=True, exist_ok=True)
    frames: list[tuple[Path, float]] = []

    if PORTADA.exists():
        title = WORK / "frame_00_title.jpg"
        Image.open(PORTADA).convert("RGB").resize((W, H), Image.Resampling.LANCZOS).save(
            title, quality=92
        )
        frames.append((title, 3.8))

    for i, name in enumerate(SELECTED, start=1):
        src = ASSETS / name
        if not src.exists():
            print(f"skip missing {name}", file=sys.stderr)
            continue
        out = WORK / f"frame_{i:02d}.jpg"
        letterbox(Image.open(src)).save(out, quality=90)
        frames.append((out, CLIP_DUR))
        print(f"prepared {out.name}")
    return frames


def encode_clip(frame: Path, out: Path, total_dur: float) -> None:
    """Static still clip (reliable + fast)."""
    cmd = [
        "ffmpeg",
        "-y",
        "-loop",
        "1",
        "-framerate",
        str(FPS),
        "-i",
        str(frame),
        "-t",
        f"{total_dur:.3f}",
        "-vf",
        f"scale={W}:{H},format=yuv420p",
        "-c:v",
        "libx264",
        "-preset",
        "ultrafast",
        "-crf",
        "18",
        "-pix_fmt",
        "yuv420p",
        "-an",
        str(out),
    ]
    subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)


def build_video(frames: list[tuple[Path, float]]) -> None:
    if len(frames) < 2:
        raise SystemExit("Need at least 2 frames")

    clip_paths: list[Path] = []
    vis_durs: list[float] = []
    for i, (frame, vis) in enumerate(frames):
        clip = WORK / f"clip_{i:02d}.mp4"
        # clip must be long enough for its visible part + outgoing xfade
        # last clip only needs visible duration (no outgoing xfade needed beyond end)
        total = vis + XFADE if i < len(frames) - 1 else vis
        print(f"encoding {clip.name} ({total:.2f}s)")
        encode_clip(frame, clip, total)
        clip_paths.append(clip)
        vis_durs.append(vis)

    n = len(clip_paths)
    inputs: list[str] = []
    for p in clip_paths:
        inputs.extend(["-i", str(p)])

    filter_parts: list[str] = []
    current = "[0:v]"
    timeline = 0.0
    for i in range(1, n):
        timeline += vis_durs[i - 1]
        offset = timeline
        tr = TRANSITIONS[(i - 1) % len(TRANSITIONS)]
        out_label = f"[v{i}]" if i < n - 1 else "[vout]"
        filter_parts.append(
            f"{current}[{i}:v]xfade=transition={tr}:duration={XFADE:.3f}:offset={offset:.3f}{out_label}"
        )
        current = out_label

    filtergraph = ";".join(filter_parts)
    OUT.parent.mkdir(parents=True, exist_ok=True)
    cmd = [
        "ffmpeg",
        "-y",
        *inputs,
        "-filter_complex",
        filtergraph,
        "-map",
        "[vout]",
        "-r",
        str(FPS),
        "-c:v",
        "libx264",
        "-preset",
        "medium",
        "-crf",
        "19",
        "-pix_fmt",
        "yuv420p",
        "-movflags",
        "+faststart",
        "-an",
        str(OUT),
    ]
    print("xfade chain…")
    result = subprocess.run(cmd, capture_output=True, text=True)
    if result.returncode != 0:
        print(result.stderr[-5000:], file=sys.stderr)
        raise SystemExit(result.returncode)

    ART.parent.mkdir(parents=True, exist_ok=True)
    ART.write_bytes(OUT.read_bytes())
    probe = subprocess.run(
        [
            "ffprobe",
            "-v",
            "error",
            "-show_entries",
            "format=duration,size",
            "-of",
            "default=noprint_wrappers=1",
            str(OUT),
        ],
        capture_output=True,
        text=True,
        check=True,
    )
    print(f"Wrote {OUT}")
    print(probe.stdout)


def main() -> None:
    frames = prepare_frames()
    print(f"{len(frames)} frames")
    build_video(frames)


if __name__ == "__main__":
    main()
