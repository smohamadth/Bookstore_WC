#!/usr/bin/env python3
"""Generate companion-plugin modules from the theme's compatibility fallbacks."""
from __future__ import annotations

import argparse
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MODULES = ("books.php", "newsletter.php")


def transformed(source: str) -> str:
    """Use the plugin slug as the translation domain without renaming APIs."""
    return source.replace("'inkwell'", "'inkwell-books'")


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--check", action="store_true", help="fail instead of updating generated modules")
    args = parser.parse_args()

    stale: list[str] = []
    for filename in MODULES:
        source = ROOT / "inkwell" / "inc" / filename
        target = ROOT / "inkwell-books" / "includes" / filename
        expected = transformed(source.read_text(encoding="utf-8"))
        actual = target.read_text(encoding="utf-8") if target.exists() else ""
        if actual == expected:
            continue
        if args.check:
            stale.append(filename)
        else:
            target.parent.mkdir(parents=True, exist_ok=True)
            target.write_text(expected, encoding="utf-8")
            print(f"updated {target.relative_to(ROOT)}")

    if stale:
        print("Plugin modules are stale: " + ", ".join(stale))
        print("Run: python3 tools/sync-plugin-modules.py")
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
