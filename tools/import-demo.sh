#!/usr/bin/env bash
#
# Inkwell — one-command demo import.
#
# Usage:
#   WP_CLI="php /path/to/wp-cli.phar" WP_SITE=/path/to/wordpress tools/import-demo.sh
#
# Defaults assume `wp` on PATH and the current directory contains wp-config.php.
#
set -euo pipefail

WP_CLI_BIN="${WP_CLI:-wp}"
WP_SITE_DIR="${WP_SITE:-$(pwd)}"
HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(dirname "$HERE")"

run_wp() {
  if [ "$WP_SITE_DIR" = "$(pwd)" ]; then
    "$WP_CLI_BIN" "$@"
  else
    "$WP_CLI_BIN" --path="$WP_SITE_DIR" "$@"
  fi
}

echo "→ Importing demo content into: $WP_SITE_DIR"
run_wp eval-file "$ROOT/demo-content/import-products.php"

echo "→ Flushing rewrite rules"
run_wp rewrite flush --hard

echo "→ Verifying"
run_wp option get blogname
run_wp product list --fields=name,sku,price 2>/dev/null | head -8 || true

echo "✓ Done. Visit your site — the shop is live."
