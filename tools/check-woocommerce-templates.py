#!/usr/bin/env python3
"""Validate Inkwell WooCommerce overrides against the pinned template manifest."""
from __future__ import annotations

import argparse
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
THEME_TEMPLATES = ROOT / "inkwell" / "woocommerce"
MANIFEST = ROOT / "tests" / "woocommerce-template-versions.json"
VERSION_PATTERN = re.compile(r"@version\s+([0-9.]+)", re.IGNORECASE)


def template_version(path: Path) -> str:
    match = VERSION_PATTERN.search(path.read_text(encoding="utf-8"))
    if not match:
        raise ValueError(f"No @version header in {path}")
    return match.group(1)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--woocommerce-templates",
        type=Path,
        help="Optional path to the installed WooCommerce templates directory",
    )
    args = parser.parse_args()

    manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))
    expected = manifest["templates"]
    actual_files = {
        path.relative_to(THEME_TEMPLATES).as_posix()
        for path in THEME_TEMPLATES.rglob("*.php")
    }
    if actual_files != set(expected):
        unexpected = sorted(actual_files - set(expected))
        missing = sorted(set(expected) - actual_files)
        raise SystemExit(f"Override set mismatch; unexpected={unexpected}, missing={missing}")

    errors: list[str] = []
    for relative, pinned_version in sorted(expected.items()):
        theme_version = template_version(THEME_TEMPLATES / relative)
        if theme_version != pinned_version:
            errors.append(f"{relative}: theme={theme_version}, manifest={pinned_version}")
        if args.woocommerce_templates:
            upstream_file = args.woocommerce_templates / relative
            if not upstream_file.is_file():
                errors.append(f"{relative}: missing from installed WooCommerce")
            else:
                upstream_version = template_version(upstream_file)
                if theme_version != upstream_version:
                    errors.append(f"{relative}: theme={theme_version}, installed WooCommerce={upstream_version}")

    if errors:
        raise SystemExit("\n".join(errors))
    print(f"PASS: {len(expected)} overrides match WooCommerce {manifest['woocommerce']} manifest")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
