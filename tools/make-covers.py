#!/usr/bin/env python3
"""
Inkwell demo — typographic book cover generator (v2).

Reads inkwell/demo/books.json and renders one 600x900 PNG cover per book
into inkwell/demo/covers/. Two layouts:
  * "classic" (default) — centered title, author top, imprint band
  * "band" — cream band across the middle holding the title
Uses DejaVu Serif — no copyrighted imagery involved.
"""
import json
import os
import sys

from PIL import Image, ImageDraw, ImageFont

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
FONT_DIR = "/usr/share/fonts/truetype/dejavu"
SERIF = os.path.join(FONT_DIR, "DejaVuSerif.ttf")
SERIF_BOLD = os.path.join(FONT_DIR, "DejaVuSerif-Bold.ttf")

W, H = 600, 900
CREAM = (246, 241, 230)


def font(path, size):
    return ImageFont.truetype(path, size)


def wrap_title(draw, title, max_w):
    """Greedy wrap on the bold serif at 54px, shrinking if needed."""
    size = 54
    while size >= 30:
        f = font(SERIF_BOLD, size)
        words = title.split()
        lines, cur = [], ""
        for w in words:
            trial = (cur + " " + w).strip()
            if draw.textlength(trial, font=f) <= max_w:
                cur = trial
            else:
                if cur:
                    lines.append(cur)
                cur = w
        if cur:
            lines.append(cur)
        if len(lines) <= 4:
            return lines, f
        size -= 4
    return lines, f


def draw_classic(d, book, bg):
    # Thin frame.
    d.rectangle([26, 26, W - 26, H - 26], outline=CREAM, width=2)

    # Author, letterspaced caps.
    author = book["author"].upper()
    d.text((W / 2, 110), author, font=font(SERIF, 26), fill=CREAM, anchor="mm")
    rule_w = 64
    d.line([W / 2 - rule_w, 140, W / 2 + rule_w, 140], fill=CREAM, width=2)

    # Title.
    lines, f_title = wrap_title(d, book["title"], W - 120)
    line_h = f_title.size * 1.22
    start_y = 300 - (len(lines) - 1) * line_h / 2
    for i, line in enumerate(lines):
        d.text((W / 2, start_y + i * line_h), line, font=f_title, fill=CREAM, anchor="mm")

    # Imprint + bottom mark.
    d.line([W / 2 - rule_w, 620, W / 2 + rule_w, 620], fill=CREAM, width=2)
    d.text((W / 2, 668), book.get("imprint", "A NOVEL"), font=font(SERIF, 22), fill=CREAM, anchor="mm")
    d.text((W / 2, H - 90), "INKWELL EDITIONS", font=font(SERIF, 24), fill=CREAM, anchor="mm")


def draw_band(d, book, bg):
    # Full-bleed cream band across the middle.
    band_top, band_h = 380, 220
    d.rectangle([0, band_top, W, band_top + band_h], fill=CREAM)

    d.rectangle([26, 26, W - 26, H - 26], outline=CREAM, width=2)

    # Author above the band.
    d.text((W / 2, 240), book["author"].upper(), font=font(SERIF, 26), fill=CREAM, anchor="mm")

    # Title inside the band, in the bg color.
    lines, f_title = wrap_title(d, book["title"], W - 140)
    line_h = f_title.size * 1.2
    start_y = band_top + band_h / 2 - (len(lines) - 1) * line_h / 2
    for i, line in enumerate(lines):
        d.text((W / 2, start_y + i * line_h), line, font=f_title, fill=bg, anchor="mm")

    # Imprint under the band.
    d.text((W / 2, band_top + band_h + 70), book.get("imprint", "A NOVEL"), font=font(SERIF, 22), fill=CREAM, anchor="mm")
    d.text((W / 2, H - 90), "INKWELL EDITIONS", font=font(SERIF, 24), fill=CREAM, anchor="mm")


def draw_cover(book, out_path):
    bg_hex = book.get("color", "#333333")
    bg = tuple(int(bg_hex.lstrip("#")[i : i + 2], 16) for i in (0, 2, 4))
    img = Image.new("RGB", (W, H), bg)
    d = ImageDraw.Draw(img)

    if book.get("style") == "band":
        draw_band(d, book, bg)
    else:
        draw_classic(d, book, bg)

    img.save(out_path, "PNG")
    return out_path


def main():
    with open(os.path.join(ROOT, "inkwell", "demo", "books.json"), encoding="utf-8") as fh:
        books = json.load(fh)

    out_dir = os.path.join(ROOT, "inkwell", "demo", "covers")
    os.makedirs(out_dir, exist_ok=True)

    for book in books:
        out = os.path.join(out_dir, book["sku"] + ".png")
        draw_cover(book, out)
        print("cover:", book["sku"])

    print(f"Done — {len(books)} covers in {out_dir}")


if __name__ == "__main__":
    sys.exit(main())
