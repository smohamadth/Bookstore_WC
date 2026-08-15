#!/usr/bin/env bash
#
# Inkwell — one-command demo import through the active theme's safe importer.
#
# Usage:
#   WP_CLI="php /path/to/wp-cli.phar" WP_SITE=/path/to/wordpress tools/import-demo.sh
#
set -euo pipefail

WP_CLI_BIN="${WP_CLI:-wp}"
WP_SITE_DIR="${WP_SITE:-$(pwd)}"

run_wp() {
  if [ "$WP_SITE_DIR" = "$(pwd)" ]; then
    "$WP_CLI_BIN" "$@"
  else
    "$WP_CLI_BIN" --path="$WP_SITE_DIR" "$@"
  fi
}

echo "→ Importing demo content into: $WP_SITE_DIR"
run_wp eval '
if ( ! function_exists( "inkwell_demo_import_catalog" ) ) {
    WP_CLI::error( "Activate the Inkwell theme before importing demo content." );
}
$result = inkwell_demo_import_catalog( true );
if ( is_wp_error( $result ) ) {
    WP_CLI::error( $result->get_error_message() );
}
foreach ( $result as $line ) {
    WP_CLI::log( $line );
}
'

echo "→ Verifying"
run_wp option get blogname
run_wp product list --fields=name,sku,price 2>/dev/null | head -8 || true

echo "✓ Done. Visit your site — the shop is live."
