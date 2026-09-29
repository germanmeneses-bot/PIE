#!/usr/bin/env python3
"""Create a 16:9 video cover (portada) for Escuela Chacaico montage."""

from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageDraw, ImageEnhance, ImageFilter, ImageFont

ASSETS = Path("/home/ubuntu/.cursor/projects/workspace/assets")
OUT = Path("/workspace/media/portada-escuela-chacaico.jpg")
W, H = 1920, 1080

# Strong cultural portrait — boy in yellow makuñ (landscape)
HERO = ASSETS / "01a0eeb0-9c7e-7a9b-90f4-739da6d2f983.jpg"
# Accent strip photos
ACCENTS = [
    ASSETS / "01a0eeb0-9420-7f1a-9016-75669cb6a8eb.jpg",  # kultrun / escenario
    ASSETS / "01a0eeb0-975e-78a5-b077-747634c237f5.jpg",  # palín / bandera
    ASSETS / "01a0eeb0-9983-778f-9a1f-147d1f63800f.jpg",  # Mapudungun class
]


def cover_fit(img: Image.Image, tw: int, th: int) -> Image.Image:
    """Center-crop to fill target size."""
    src = img.convert("RGB")
    scale = max(tw / src.width, th / src.height)
    nw, nh = int(src.width * scale + 0.5), int(src.height * scale + 0.5)
    src = src.resize((nw, nh), Image.Resampling.LANCZOS)
    left = (nw - tw) // 2
    top = (nh - th) // 2
    return src.crop((left, top, left + tw, top + th))


def load_font(path: str, size: int) -> ImageFont.FreeTypeFont:
    return ImageFont.truetype(path, size=size)


def main() -> None:
    hero = cover_fit(Image.open(HERO), W, H)
    # Slight darken + warmth for typography legibility
    hero = ImageEnhance.Brightness(hero).enhance(0.72)
    hero = ImageEnhance.Contrast(hero).enhance(1.08)
    hero = ImageEnhance.Color(hero).enhance(1.12)

    # Soft vignette / bottom gradient for text
    overlay = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    draw = ImageDraw.Draw(overlay)
    for y in range(H):
        # Stronger darkening on left (text side) and bottom
        t = y / H
        left_boost = 0.35
        alpha = int(40 + 170 * (t**1.6) + left_boost * 80)
        alpha = min(220, alpha)
        draw.line([(0, y), (W, y)], fill=(12, 18, 14, alpha))

    # Left panel wash for brand block
    for x in range(0, 820):
        a = int(150 * (1 - x / 820) ** 1.2)
        draw.line([(x, 0), (x, H)], fill=(8, 14, 12, a))

    base = hero.convert("RGBA")
    composed = Image.alpha_composite(base, overlay)

    # Thin geometric accent bar (Mapuche-inspired step pattern)
    accent = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    ad = ImageDraw.Draw(accent)
    gold = (212, 168, 58, 230)
    cream = (236, 228, 210, 220)
    green = (46, 110, 72, 230)
    y0 = H - 28
    ad.rectangle([0, y0, W, H], fill=(18, 28, 22, 240))
    # Step motif
    x = 48
    while x < W - 48:
        ad.rectangle([x, y0 + 8, x + 28, y0 + 20], fill=gold)
        ad.rectangle([x + 32, y0 + 8, x + 44, y0 + 20], fill=cream)
        ad.rectangle([x + 48, y0 + 8, x + 60, y0 + 20], fill=green)
        x += 88

    composed = Image.alpha_composite(composed, accent)
    draw2 = ImageDraw.Draw(composed)

    serif_bold = load_font("/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf", 92)
    serif = load_font("/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf", 42)
    sans = load_font("/usr/share/fonts/truetype/noto/NotoSans-Regular.ttf", 28)
    sans_sm = load_font("/usr/share/fonts/truetype/noto/NotoSans-Regular.ttf", 22)

    # Brand first — hero-level
    title = "Escuela Chacaico"
    subtitle = "Angol · Cultura, aula y comunidad"
    tagline = "Raíces que enseñan"

    tx, ty = 72, 340
    # Soft shadow for title
    draw2.text((tx + 3, ty + 3), title, font=serif_bold, fill=(0, 0, 0, 160))
    draw2.text((tx, ty), title, font=serif_bold, fill=(245, 240, 230, 255))

    draw2.text((tx + 2, ty + 118), tagline, font=serif, fill=(0, 0, 0, 120))
    draw2.text((tx, ty + 116), tagline, font=serif, fill=(212, 168, 58, 255))

    # Divider
    draw2.rectangle([tx, ty + 190, tx + 220, ty + 194], fill=gold)

    draw2.text((tx, ty + 220), subtitle, font=sans, fill=(220, 214, 200, 240))
    draw2.text(
        (tx, ty + 268),
        "Memoria visual · Visitas, tradición Mapuche y aprendizaje",
        font=sans_sm,
        fill=(190, 186, 176, 220),
    )

    # Small accent thumbnails bottom-right (optional visual rhythm)
    thumb_w, thumb_h = 150, 112
    gap = 12
    start_x = W - 72 - (thumb_w * 3 + gap * 2)
    start_y = H - 56 - thumb_h
    for i, path in enumerate(ACCENTS):
        if not path.exists():
            continue
        th = cover_fit(Image.open(path), thumb_w, thumb_h)
        th = ImageEnhance.Brightness(th).enhance(0.9)
        mask = Image.new("L", (thumb_w, thumb_h), 0)
        md = ImageDraw.Draw(mask)
        md.rounded_rectangle([0, 0, thumb_w - 1, thumb_h - 1], radius=6, fill=255)
        px = start_x + i * (thumb_w + gap)
        composed.paste(th, (px, start_y), mask)
        # thin gold frame
        draw2.rounded_rectangle(
            [px - 1, start_y - 1, px + thumb_w, start_y + thumb_h],
            radius=7,
            outline=(212, 168, 58, 180),
            width=2,
        )

    final = composed.convert("RGB")
    OUT.parent.mkdir(parents=True, exist_ok=True)
    final.save(OUT, quality=92, optimize=True)
    # Also copy to artifacts
    art = Path("/opt/cursor/artifacts/portada-escuela-chacaico.jpg")
    final.save(art, quality=92, optimize=True)
    print(f"Wrote {OUT} and {art}")


if __name__ == "__main__":
    main()
