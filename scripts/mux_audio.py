#!/usr/bin/env python3
"""Add Poyenekayan soundtrack to the montage video."""

from __future__ import annotations

import subprocess
from pathlib import Path

VIDEO = Path("/workspace/media/video-escuela-chacaico.mp4")
AUDIO = Path("/workspace/media/poyenekayan-beatriz-pichi-malen.mp3")
TMP = Path("/tmp/video-con-audio.mp4")
ART = Path("/opt/cursor/artifacts/video-escuela-chacaico.mp4")
DEMO = Path("/opt/cursor/artifacts/recording_demo.mp4")
PREVIEW = Path("/opt/cursor/artifacts/preview-con-audio-15s.mp4")


def main() -> None:
    if not AUDIO.exists():
        raise SystemExit(f"Missing audio: {AUDIO}")
    if not VIDEO.exists():
        raise SystemExit(f"Missing video: {VIDEO}")

    subprocess.run(
        [
            "ffmpeg",
            "-y",
            "-i",
            str(VIDEO),
            "-i",
            str(AUDIO),
            "-filter_complex",
            (
                "[1:a]atrim=0:180,asetpts=PTS-STARTPTS,"
                "afade=t=in:st=0:d=1.2,"
                "afade=t=out:st=177:d=3,"
                "loudnorm=I=-14:TP=-1.5:LRA=11,"
                "aformat=sample_fmts=fltp:sample_rates=48000:channel_layouts=stereo[a]"
            ),
            "-map",
            "0:v:0",
            "-map",
            "[a]",
            "-c:v",
            "libx264",
            "-preset",
            "medium",
            "-crf",
            "23",
            "-pix_fmt",
            "yuv420p",
            "-c:a",
            "aac",
            "-b:a",
            "192k",
            "-ac",
            "2",
            "-ar",
            "48000",
            "-shortest",
            "-movflags",
            "+faststart",
            str(TMP),
        ],
        check=True,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    TMP.replace(VIDEO)
    data = VIDEO.read_bytes()
    ART.parent.mkdir(parents=True, exist_ok=True)
    ART.write_bytes(data)
    DEMO.write_bytes(data)
    subprocess.run(
        [
            "ffmpeg",
            "-y",
            "-i",
            str(VIDEO),
            "-t",
            "15",
            "-c:v",
            "libx264",
            "-crf",
            "23",
            "-c:a",
            "aac",
            "-b:a",
            "192k",
            "-movflags",
            "+faststart",
            str(PREVIEW),
        ],
        check=True,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    probe = subprocess.run(
        [
            "ffprobe",
            "-v",
            "error",
            "-show_entries",
            "format=duration",
            "-select_streams",
            "a",
            "-show_entries",
            "stream=codec_name",
            "-of",
            "default=noprint_wrappers=1",
            str(VIDEO),
        ],
        capture_output=True,
        text=True,
        check=True,
    )
    print(f"Updated {VIDEO}")
    print(probe.stdout)


if __name__ == "__main__":
    main()
