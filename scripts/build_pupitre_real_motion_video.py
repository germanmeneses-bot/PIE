#!/usr/bin/env python3
"""Build silent video with natural people motion (no 3D warps / CGI overlays)."""

from __future__ import annotations

import shutil
import subprocess
import tempfile
from pathlib import Path

ASSETS = Path("/opt/cursor/artifacts/assets")
IMG_OUT = Path("/workspace/public/images/pupitre-inteligente/real")
OUT_MP4 = Path("/workspace/public/videos/pupitre-inteligente-aula.mp4")
ART_DIR = Path("/opt/cursor/artifacts")

SCENE_XFADE = 0.5
HOLD = 3.0  # stable documentary hold on each natural pose
POSE_XFADE = 0.35  # short cut-like dissolve — avoids heavy morph ghosting

SCENES: list[list[str]] = [
    ["real-01a-aula.jpg", "real-01b-aula.jpg", "real-01c-aula.jpg"],
    ["real-02a-profe.jpg", "real-02b-profe.jpg", "real-02c-profe.jpg"],
    ["real-03a-nina.jpg", "real-03b-nina.jpg", "real-03c-nina.jpg"],
    ["real-04a-pupitre.jpg", "real-04c-pupitre.jpg"],
    ["real-04b-paneles.jpg"],
    ["real-05a-audifono.jpg", "real-05b-audifono.jpg"],
    ["real-06a-cierre.jpg", "real-06b-cierre.jpg"],
]


def run(cmd: list[str]) -> None:
    subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)


def probe_duration(path: Path) -> float:
    out = subprocess.check_output(
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
    return float(out)


def still_clip(src: Path, out: Path, duration: float) -> None:
    run(
        [
            "ffmpeg",
            "-y",
            "-loop",
            "1",
            "-i",
            str(src),
            "-vf",
            "scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,setsar=1",
            "-t",
            str(duration),
            "-r",
            "30",
            "-c:v",
            "libx264",
            "-pix_fmt",
            "yuv420p",
            "-crf",
            "20",
            "-an",
            str(out),
        ]
    )


def xfade_pair(a: Path, b: Path, out: Path, duration: float, offset: float) -> None:
    run(
        [
            "ffmpeg",
            "-y",
            "-i",
            str(a),
            "-i",
            str(b),
            "-filter_complex",
            f"[0][1]xfade=transition=fade:duration={duration}:offset={offset},format=yuv420p",
            "-an",
            "-c:v",
            "libx264",
            "-crf",
            "20",
            "-preset",
            "veryfast",
            str(out),
        ]
    )


def scene_to_clip(frames: list[Path], out_mp4: Path) -> None:
    tmp = out_mp4.parent / (out_mp4.stem + "_build")
    if tmp.exists():
        shutil.rmtree(tmp)
    tmp.mkdir(parents=True)

    if len(frames) == 1:
        still_clip(frames[0], out_mp4, HOLD + 0.8)
        shutil.rmtree(tmp, ignore_errors=True)
        return

    # Each pose held, then dissolved into the next (people change pose naturally)
    pose_clips = []
    for i, f in enumerate(frames):
        p = tmp / f"pose_{i:02d}.mp4"
        still_clip(f, p, HOLD)
        pose_clips.append(p)

    current = pose_clips[0]
    for i in range(1, len(pose_clips)):
        merged = tmp / f"merge_{i:02d}.mp4"
        offset = probe_duration(current) - POSE_XFADE
        if offset < 0.05:
            offset = 0.05
        xfade_pair(current, pose_clips[i], merged, POSE_XFADE, offset)
        current = merged

    shutil.copy(current, out_mp4)
    shutil.rmtree(tmp, ignore_errors=True)


def concat_scenes(clips: list[Path], out: Path) -> None:
    if len(clips) == 1:
        shutil.copy(clips[0], out)
        return

    durs = [probe_duration(c) for c in clips]
    parts = []
    timeline = durs[0]
    for i in range(1, len(clips)):
        offset = round(max(0.05, timeline - SCENE_XFADE), 3)
        left = "[0]" if i == 1 else f"[v{i-1}]"
        out_l = "[vout]" if i == len(clips) - 1 else f"[v{i}]"
        parts.append(
            f"{left}[{i}]xfade=transition=fade:duration={SCENE_XFADE}:offset={offset}{out_l}"
        )
        timeline = timeline + durs[i] - SCENE_XFADE

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
        "22",
        "-preset",
        "medium",
        "-movflags",
        "+faststart",
        str(out),
    ]
    run(cmd)


def main() -> None:
    print(f"config HOLD={HOLD} POSE_XFADE={POSE_XFADE} SCENE_XFADE={SCENE_XFADE}")
    IMG_OUT.mkdir(parents=True, exist_ok=True)
    tmp = Path(tempfile.mkdtemp(prefix="pupitre_real_"))
    print(f"tmp={tmp}")

    clips: list[Path] = []
    for si, names in enumerate(SCENES, start=1):
        frames = []
        for name in names:
            src = ASSETS / name
            if not src.exists():
                raise FileNotFoundError(src)
            shutil.copy2(src, IMG_OUT / name)
            frames.append(src)
        clip = tmp / f"scene_{si:02d}.mp4"
        print(f"scene {si}/{len(SCENES)}: {names}")
        scene_to_clip(frames, clip)
        print(f"  duration={probe_duration(clip):.2f}s")
        clips.append(clip)

    print("concat scenes...")
    concat_scenes(clips, OUT_MP4)

    ART_DIR.mkdir(parents=True, exist_ok=True)
    for dest_name in (
        "pupitre_inteligente_movimiento_real.mp4",
        "descargar_pupitre_inteligente_aula.mp4",
        "pupitre_inteligente_aula_silencioso.mp4",
        "pupitre_inteligente_3d_movimiento.mp4",
    ):
        shutil.copy2(OUT_MP4, ART_DIR / dest_name)

    size = OUT_MP4.stat().st_size
    dur = probe_duration(OUT_MP4)
    print(f"duration={dur}\nsize={size}\ndone {OUT_MP4}")


if __name__ == "__main__":
    main()
