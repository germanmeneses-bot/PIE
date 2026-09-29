#!/usr/bin/env python3
"""Build a ~3 minute slideshow with xfade transitions from Escuela Chacaico photos."""

from __future__ import annotations

import subprocess
import sys
from pathlib import Path

from PIL import Image, ImageEnhance, ImageFilter

ASSETS = Path("/home/ubuntu/.cursor/projects/workspace/assets")
WORK = Path("/tmp/chacaico-clips")
OUT = Path("/workspace/media/video-escuela-chacaico.mp4")
ART = Path("/opt/cursor/artifacts/video-escuela-chacaico.mp4")
DEMO = Path("/opt/cursor/artifacts/recording_demo.mp4")
PORTADA = Path("/workspace/media/portada-escuela-chacaico.jpg")

W, H = 1920, 1080
FPS = 30
TARGET_DUR = 180.0  # 3 minutes
TITLE_DUR = 5.0
END_DUR = 4.0
XFADE = 0.7
# Stable xfade modes only
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

# Only use the newly uploaded batch (prefix 01a0eeb0-)
PHOTO_GLOB = "01a0eeb0-*.jpg"

SKIP = {
    "01a0eeb0-9aee-7129-ac5c-0a3b057e73ae.jpg",  # watermark FOTOGRAFIA
    "01a0eeb0-92a9-7430-8b17-6565d694fc21.jpg",  # dup. FOTOGRAFIA
    "01a0eeb0-96db-7982-bca4-759d3f698ad2.jpg",  # adultos comiendo al fondo
    "01a0eeb0-96bd-7657-85c5-e2f353204851.jpg",  # adulto comiendo al fondo
}


def select_photos() -> list[str]:
    photos = sorted(p.name for p in ASSETS.glob(PHOTO_GLOB) if p.name not in SKIP)
    if not photos:
        raise SystemExit(f"No photos found matching {PHOTO_GLOB} in {ASSETS}")
    return photos


def letterbox(img: Image.Image) -> Image.Image:
    src = img.convert("RGB")
    bg = src.resize((W, H), Image.Resampling.LANCZOS).filter(ImageFilter.GaussianBlur(28))
    bg = ImageEnhance.Brightness(bg).enhance(0.42)
    scale = min(W / src.width, H / src.height)
    nw, nh = int(src.width * scale), int(src.height * scale)
    fg = src.resize((nw, nh), Image.Resampling.LANCZOS)
    bg.paste(fg, ((W - nw) // 2, (H - nh) // 2))
    return bg


def prepare_frames(photos: list[str], photo_dur: float) -> list[tuple[Path, float]]:
    WORK.mkdir(parents=True, exist_ok=True)
    # Clean previous clips to avoid stale inputs
    for old in WORK.glob("*"):
        old.unlink()

    frames: list[tuple[Path, float]] = []

    if PORTADA.exists():
        title = WORK / "frame_00_title.jpg"
        Image.open(PORTADA).convert("RGB").resize((W, H), Image.Resampling.LANCZOS).save(
            title, quality=92
        )
        frames.append((title, TITLE_DUR))

    for i, name in enumerate(photos, start=1):
        src = ASSETS / name
        out = WORK / f"frame_{i:02d}.jpg"
        letterbox(Image.open(src)).save(out, quality=90)
        frames.append((out, photo_dur))
        print(f"prepared {out.name} ({name})")

    if PORTADA.exists():
        end = WORK / "frame_99_end.jpg"
        Image.open(PORTADA).convert("RGB").resize((W, H), Image.Resampling.LANCZOS).save(
            end, quality=92
        )
        frames.append((end, END_DUR))

    return frames


def encode_clip(frame: Path, out: Path, total_dur: float) -> None:
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
        total = vis + XFADE if i < len(frames) - 1 else vis
        print(f"encoding {clip.name} ({total:.2f}s)")
        encode_clip(frame, clip, total)
        clip_paths.append(clip)
        vis_durs.append(vis)

    expected = sum(vis_durs)
    print(f"expected_duration≈{expected:.1f}s ({expected/60:.2f} min)")

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
    tmp_out = WORK / "raw_xfade.mp4"
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
        "20",
        "-pix_fmt",
        "yuv420p",
        "-movflags",
        "+faststart",
        "-an",
        str(tmp_out),
    ]
    print(f"xfade chain ({n} clips)…")
    result = subprocess.run(cmd, capture_output=True, text=True)
    if result.returncode != 0:
        print(result.stderr[-6000:], file=sys.stderr)
        raise SystemExit(result.returncode)

    # Web-friendly size (~keep under ~40MB for 3 min)
    subprocess.run(
        [
            "ffmpeg",
            "-y",
            "-i",
            str(tmp_out),
            "-c:v",
            "libx264",
            "-preset",
            "medium",
            "-crf",
            "23",
            "-pix_fmt",
            "yuv420p",
            "-movflags",
            "+faststart",
            "-an",
            str(OUT),
        ],
        check=True,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )

    ART.parent.mkdir(parents=True, exist_ok=True)
    data = OUT.read_bytes()
    ART.write_bytes(data)
    DEMO.write_bytes(data)
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
    photos = select_photos()
    n_photos = len(photos)
    # title + photos + end card
    photo_budget = TARGET_DUR - TITLE_DUR - END_DUR
    photo_dur = photo_budget / n_photos
    print(f"{n_photos} photos @ {photo_dur:.3f}s each → target {TARGET_DUR}s")
    frames = prepare_frames(photos, photo_dur)
    print(f"{len(frames)} frames total")
    build_video(frames)


if __name__ == "__main__":
    main()
