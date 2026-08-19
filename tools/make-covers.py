#!/usr/bin/env python3
"""Generate original typographic covers for the fictional Kurdish demo catalog."""
import json
import os
import sys

import arabic_reshaper
from bidi.algorithm import get_display
from PIL import Image, ImageDraw, ImageFont

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
FONT_DIR = os.path.join(HERE, "fonts")
BODY_FONT = os.path.join(FONT_DIR, "Vazirmatn.ttf")
DISPLAY_FONT = os.path.join(FONT_DIR, "NotoKufiArabic.ttf")

W, H = 600, 900
CREAM = (246, 241, 230)


def font(path, size):
    return ImageFont.truetype(path, size)


def shape(text):
    """Shape Sorani/Persian text for Pillow builds without libraqm."""
    return get_display(arabic_reshaper.reshape(str(text)))


def text_width(draw, text, selected_font):
    return draw.textlength(shape(text), font=selected_font)


def wrap_title(draw, title, max_width):
    """Wrap a logical RTL title before shaping each visual line."""
    words = title.split()
    size = 54
    while size >= 30:
        selected_font = font(DISPLAY_FONT, size)
        lines, current = [], ""
        for word in words:
            trial = (current + " " + word).strip()
            if text_width(draw, trial, selected_font) <= max_width:
                current = trial
            else:
                if current:
                    lines.append(current)
                current = word
        if current:
            lines.append(current)
        if len(lines) <= 4:
            return lines, selected_font
        size -= 4
    return lines, selected_font


def draw_frame(draw):
    draw.rounded_rectangle([26, 26, W - 26, H - 26], radius=12, outline=CREAM, width=2)
    draw.ellipse([W - 105, 54, W - 61, 98], outline=CREAM, width=2)
    draw.line([58, H - 58, 170, H - 58], fill=CREAM, width=2)


def draw_rtl(draw, xy, text, selected_font, fill, anchor="mm"):
    draw.text(xy, shape(text), font=selected_font, fill=fill, anchor=anchor)


def draw_classic(draw, book, background):
    draw_frame(draw)
    draw_rtl(draw, (W / 2, 112), book["author"], font(BODY_FONT, 27), CREAM)
    draw.line([W / 2 - 64, 148, W / 2 + 64, 148], fill=CREAM, width=2)

    lines, title_font = wrap_title(draw, book["title"], W - 120)
    line_height = title_font.size * 1.4
    start_y = 330 - (len(lines) - 1) * line_height / 2
    for index, line in enumerate(lines):
        draw_rtl(draw, (W / 2, start_y + index * line_height), line, title_font, CREAM)

    draw.line([W / 2 - 64, 625, W / 2 + 64, 625], fill=CREAM, width=2)
    draw_rtl(draw, (W / 2, 675), book.get("imprint", "کتێب"), font(BODY_FONT, 23), CREAM)
    draw_rtl(draw, (W / 2, H - 92), "کتێبخانەی ئینکوێڵ", font(BODY_FONT, 23), CREAM)


def draw_band(draw, book, background):
    band_top, band_height = 350, 250
    draw.rectangle([0, band_top, W, band_top + band_height], fill=CREAM)
    draw_frame(draw)
    draw_rtl(draw, (W / 2, 220), book["author"], font(BODY_FONT, 27), CREAM)

    lines, title_font = wrap_title(draw, book["title"], W - 130)
    line_height = title_font.size * 1.35
    start_y = band_top + band_height / 2 - (len(lines) - 1) * line_height / 2
    for index, line in enumerate(lines):
        draw_rtl(draw, (W / 2, start_y + index * line_height), line, title_font, background)

    draw_rtl(draw, (W / 2, 675), book.get("imprint", "کتێب"), font(BODY_FONT, 23), CREAM)
    draw_rtl(draw, (W / 2, H - 92), "کتێبخانەی ئینکوێڵ", font(BODY_FONT, 23), CREAM)


def draw_cover(book, output_path, index):
    background_hex = book.get("color", "#333333")
    background = tuple(int(background_hex.lstrip("#")[offset : offset + 2], 16) for offset in (0, 2, 4))
    image = Image.new("RGB", (W, H), background)
    draw = ImageDraw.Draw(image)

    if index % 3 == 1:
        draw_band(draw, book, background)
    else:
        draw_classic(draw, book, background)

    image.save(output_path, "PNG", optimize=True)
    return output_path


def main():
    catalog_path = os.path.join(ROOT, "inkwell", "demo", "books.json")
    with open(catalog_path, encoding="utf-8") as catalog_file:
        books = json.load(catalog_file)

    for required_font in (BODY_FONT, DISPLAY_FONT):
        if not os.path.isfile(required_font):
            raise SystemExit(f"Missing cover font: {required_font}")

    output_dir = os.path.join(ROOT, "inkwell", "demo", "covers")
    os.makedirs(output_dir, exist_ok=True)
    expected = {book["sku"] + ".png" for book in books}
    for filename in os.listdir(output_dir):
        if filename.endswith(".png") and filename not in expected:
            os.remove(os.path.join(output_dir, filename))

    for index, book in enumerate(books):
        output = os.path.join(output_dir, book["sku"] + ".png")
        draw_cover(book, output, index)
        print("cover:", book["sku"])

    print(f"Done — {len(books)} original Kurdish covers in {output_dir}")


if __name__ == "__main__":
    sys.exit(main())
