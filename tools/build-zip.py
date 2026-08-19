#!/usr/bin/env python3
"""Build a byte-reproducible ZIP archive with normalized metadata."""
from __future__ import annotations

import argparse
import stat
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

FIXED_TIME = (2026, 1, 1, 0, 0, 0)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("source", type=Path)
    parser.add_argument("archive", type=Path)
    args = parser.parse_args()

    source = args.source.resolve()
    root = source.parent
    if not source.is_dir():
        parser.error(f"source directory does not exist: {source}")

    args.archive.unlink(missing_ok=True)
    with ZipFile(args.archive, "w", compression=ZIP_DEFLATED, compresslevel=9) as archive:
        for path in sorted(item for item in source.rglob("*") if item.is_file()):
            relative = path.relative_to(root).as_posix()
            info = ZipInfo(relative, FIXED_TIME)
            info.create_system = 3
            mode = stat.S_IMODE(path.stat().st_mode)
            info.external_attr = (stat.S_IFREG | mode) << 16
            info.compress_type = ZIP_DEFLATED
            archive.writestr(info, path.read_bytes(), compress_type=ZIP_DEFLATED, compresslevel=9)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
