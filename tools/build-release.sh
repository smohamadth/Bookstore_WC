#!/usr/bin/env bash
# Build reproducible install archives after running source regressions.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

npm run make-pot --silent
python3 tests/static-audit.py
npm run lint:js --silent
npm run format:check --silent

# Keep the backward-compatible theme fallbacks and companion plugin in sync.
cmp -s inkwell/inc/books.php inkwell-books/includes/books.php || {
  echo "Plugin books module is out of sync with the theme fallback." >&2
  exit 1
}
cmp -s inkwell/inc/newsletter.php inkwell-books/includes/newsletter.php || {
  echo "Plugin newsletter module is out of sync with the theme fallback." >&2
  exit 1
}

rm -f inkwell.zip inkwell-books.zip
zip -X -qr inkwell.zip inkwell
zip -X -qr inkwell-books.zip inkwell-books
unzip -tq inkwell.zip
unzip -tq inkwell-books.zip

python3 tests/static-audit.py --archives
sha256sum inkwell.zip inkwell-books.zip
