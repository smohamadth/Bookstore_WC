#!/usr/bin/env bash
# Build deterministic install archives after running source regressions.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

npm run make-pot --silent
python3 tools/sync-plugin-modules.py --check
python3 tests/static-audit.py
npm run lint:js --silent
npm run format:check --silent

if command -v php >/dev/null 2>&1; then
  find inkwell inkwell-books -type f -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
fi
if command -v composer >/dev/null 2>&1 && [ -d vendor ]; then
  composer phpcs
fi

TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT

python3 tools/build-zip.py inkwell inkwell.zip
python3 tools/build-zip.py inkwell-books inkwell-books.zip
cp inkwell.zip "$TMP_DIR/inkwell.zip"
cp inkwell-books.zip "$TMP_DIR/inkwell-books.zip"

# A second build must be byte-identical, not just contain the same files.
python3 tools/build-zip.py inkwell inkwell.zip
python3 tools/build-zip.py inkwell-books inkwell-books.zip
cmp "$TMP_DIR/inkwell.zip" inkwell.zip
cmp "$TMP_DIR/inkwell-books.zip" inkwell-books.zip

unzip -tq inkwell.zip
unzip -tq inkwell-books.zip
python3 tests/static-audit.py --archives
sha256sum inkwell.zip inkwell-books.zip
