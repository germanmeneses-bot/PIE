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

# Prefer narrative order; remaining assets appended alphabetically
PREFERRED = [
    # Aula
    "01a0eab2-85c9-73ab-b214-5d4b766207d5.jpg",
    "01a0eab2-85e4-7be8-848a-388e8881445d.jpg",
    "01a0eab2-85fd-7f82-a7cb-56552fe45409.jpg",
    "01a0eab2-8617-7c3d-870a-58dc77143cd9.jpg",
    "01a0eab2-8631-7165-bcc9-4127e64d6040.jpg",
    "01a0eab2-8665-7830-8715-186017981699.jpg",
    "01a0eab2-8682-7b17-9dac-339014dba88a.jpg",
    "01a0eab2-869d-7258-a49c-c00a33186122.jpg",
    "01a0eab2-86b9-72db-85a0-e42c68a6e0ac.jpg",
    "01a0eab2-86d5-7eb5-9963-385a0da4ab2b.jpg",
    "01a0eab2-86f1-7aa4-8742-fd11091f1793.jpg",
    "01a0eab2-870c-7446-9d18-76f4d3dac4eb.jpg",
    "01a0eab2-8725-7bc0-84ee-78b141be1d3e.jpg",
    "01a0eab2-875b-7297-9291-6da03317d2f4.jpg",
    "01a0eab2-8775-7e4f-b5ae-11a05aaf6bf5.jpg",
    "01a0eab2-878f-7125-9778-5421fdd7457e.jpg",
    # Comunidad / naturaleza
    "01a0eab2-881a-7178-96ae-50847d1ed912.jpg",
    "01a0eab2-88a2-7bcd-9156-eddbfcb33d01.jpg",
    "01a0eab2-88c7-7f75-a913-21b313673bac.jpg",
    "01a0eab2-894a-7958-ae1b-7d15343791b4.jpg",
    "01a0eab2-89d3-7e53-9768-e57a43c5c752.jpg",
    "01a0eab2-8a5e-77fd-8222-619bcf7a4b7b.jpg",
    "01a0eab2-8ae2-782e-b37b-54c43a018f7d.jpg",
    "01a0eab2-8b69-7dc7-89b8-3e4be346787a.jpg",
    "01a0eab2-8bd2-796c-9323-b35de2e91ee4.jpg",
    "01a0eab2-8c8d-7711-bcd4-57d5c92264c0.jpg",
    # Tradición Mapuche
    "01a0eab2-8c13-7ebd-9cfe-44e2722a8adc.jpg",
    "01a0eab2-8c32-7c1f-b31f-24fbdd46ad6e.jpg",
    "01a0eab2-8c51-72de-99a0-3497b84a1737.jpg",
    "01a0eab2-8c6f-7194-90c7-4201bb02fd8e.jpg",
    # Visita Escuela Chacaico
    "01a0eab2-8d05-7502-834f-ad9413437047.jpg",
    "01a0eab2-8d65-7205-bf53-b393da08b94e.jpg",
    "01a0eab2-8ddb-7d00-8470-3046ca34902b.jpg",
    "01a0eab2-8e4d-79f5-b292-a7f63e15d2c1.jpg",
    "01a0eab2-8ebe-79a4-b874-e1b7b7265977.jpg",
    "01a0eab2-8f31-76a8-a8c6-e0c59b450b6c.jpg",
    "01a0eab2-8f9e-7eab-99ca-51f774e41cd2.jpg",
    "01a0eab2-900d-76dd-b106-efcc95a8a809.jpg",
    "01a0eab2-9073-7650-9d0c-fe61eda37fc8.jpg",
    "01a0eab2-90dc-708b-ac51-2136ba45c04f.jpg",
    # Aprendizaje Mapudungun
    "01a0eab2-914f-721e-80e9-d4341f6da5cd.jpg",
    "01a0eab2-91c9-7202-af38-962a1ec100a0.jpg",
]

SKIP = {
    "01a0eab2-8bf5-7e2c-82f0-cf4a15f6f592.jpg",  # watermark FOTOGRAFIA
    "01a0eab2-8740-7c79-ad8b-a7fd08a64a80.jpg",  # selfie comiendo (~1:02)
    "01a0eab2-864b-7bba-a396-96202b1de5f6.jpg",  # misma selfie comiendo (duplicado)
}


def select_photos() -> list[str]:
    available = {p.name for p in ASSETS.glob("*.jpg")} - SKIP
    ordered: list[str] = []
    seen: set[str] = set()
    for name in PREFERRED:
        if name in available and name not in seen:
            ordered.append(name)
            seen.add(name)
    for name in sorted(available):
        if name not in seen:
            ordered.append(name)
            seen.add(name)
    return ordered


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
