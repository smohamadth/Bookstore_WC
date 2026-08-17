#!/usr/bin/env python3
"""Fast dependency-free release regressions for the Inkwell theme."""
from __future__ import annotations

import hashlib
import json
import re
import sys
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / "inkwell"
PLUGIN = ROOT / "inkwell-books"


def text(path: Path) -> str:
    return path.read_text(encoding="utf-8")


def check(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)


style = text(THEME / "style.css")
wc_css = text(THEME / "css/woocommerce.css")
functions = text(THEME / "functions.php")
wc = text(THEME / "inc/woocommerce.php")
books = text(THEME / "inc/books.php")
newsletter = text(THEME / "inc/newsletter.php")
product = text(THEME / "woocommerce/content-product.php")
category = text(THEME / "woocommerce/content-product-cat.php")
search_form = text(THEME / "searchform.php")
search_template = text(THEME / "search.php")
importer = text(THEME / "inc/demo-import.php")
js = text(THEME / "js/main.js")
theme_json = json.loads(text(THEME / "theme.json"))

# Release and original grid/menu regressions.
check(re.search(r"^Version:\s*2\.0\.5\s*$", style, re.M) is not None, "Theme header version mismatch")
check("define( 'INKWELL_VERSION', '2.0.5' );" in functions, "Runtime version mismatch")
check("after_switch_theme" in functions and "flush_rewrite_rules" in functions, "Theme activation does not refresh author rewrites")
check(re.search(r"\.inkwell-shop-grid::before,\s*\.inkwell-shop-grid::after\s*\{[^}]*content\s*:\s*none\s*!important;[^}]*display\s*:\s*none", wc_css, re.S) is not None, "Grid clearfix regression")
check(".main-navigation .menu-item-has-children > a::after" not in style, "Genres square regression")

# WooCommerce markup and hooks.
check("do_action( 'woocommerce_before_shop_loop_item' );" in product, "Missing product extension hook")
check(".card-media > .woocommerce-loop-product__link" in wc_css, "Product cover link is not a full card-media target")
check("woocommerce_template_loop_product_link_open();\n\t\twoocommerce_template_loop_product_title();\n\t\twoocommerce_template_loop_product_link_close();" in product, "Product title is not linked")
check("remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );" in wc, "Default unlinked title remains hooked")
check("remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );" in wc, "Nested category-link opener remains")
check("remove_action( 'woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10 );" in wc, "Nested category-link closer remains")
check("remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );" in wc, "Default category title remains hooked")
check("do_action( 'woocommerce_shop_loop_subcategory_title', $category );" in category, "Category title extension hook is missing")
check("echo '<ul class=\"products products-row\">';" in text(THEME / "inc/template-tags.php"), "Front-page products are not a list")
check('<ul class="products products-row author-products-row">' in text(THEME / "taxonomy-book_author.php"), "Author products are not a responsive list")
check("add_action( 'woocommerce_no_products_found', 'inkwell_no_products_found', 10 );" in wc, "No-products action is not wired")
check("woocommerce_quantity_input_args" not in wc, "Theme still overrides product quantity business rules")
check("max-width: 460px" in wc_css and "justify-self: center" in wc_css, "Single-product gallery size regression")
check("add_action( 'woocommerce_after_single_product_summary', 'inkwell_author_box', 12 );" in wc, "Author box is not placed before recommendations")

# Search query safety and product UX.
check('name="post_type" value="product"' in search_form, "Book search is not scoped to products")
check("OR ' . $author_clause" in books and "preg_replace( '/^AND\\s+/i'" in books, "Author search is not safely grouped")
check("woocommerce_product_loop_start" in search_template, "Product search does not use product cards")

# Configuration and merchant claims.
check("#2e6b52" in text(THEME / "inc/setup.php"), "Runtime accent is not emerald")
check("--ink-faint: #626b78" in style, "Muted text contrast has regressed")
check(theme_json["settings"]["typography"]["fontFamilies"][0]["name"] == "Display (Newsreader)", "theme.json font is stale")
check(theme_json["settings"]["color"]["palette"][3]["color"] == "#2e6b52", "theme.json palette is stale")
theme_pot = text(THEME / "languages/inkwell.pot")
check('msgctxt "Color name"' in theme_pot and 'msgid "Emerald"' in theme_pot, "theme.json colors are missing from POT")
check('msgctxt "Font family name"' in theme_pot and 'msgid "Display (Newsreader)"' in theme_pot, "theme.json fonts are missing from POT")
check("hero-background" in text(THEME / "template-parts/hero.php"), "Hero image control is unused")
check(".header-dark .site-header" in style, "Dark-header control has no CSS")
header = text(THEME / "header.php")
check(header.index("wp_body_open();") < header.index("inkwell-js"), "wp_body_open is not the first body hook")
check("html:not(.inkwell-js) .mobile-menu { display: block; }" in style, "Mobile navigation has no no-JS fallback")
check("html:not(.inkwell-js) .search-panel { display: block; }" in style, "Search has no no-JS fallback")
check((THEME / "rtl.css").is_file() and "inkwell-rtl" in text(THEME / "inc/setup.php"), "RTL support is missing")
for production_file in [THEME / "inc/woocommerce.php", THEME / "template-parts/hero.php", THEME / "template-parts/section-valueprops.php", THEME / "footer.php"]:
    source = text(production_file)
    for claim in ("€25", "30-day returns", "ships within 24 hours", "Apple&nbsp;Pay"):
        check(claim not in source, f"Hard-coded merchant claim remains in {production_file}: {claim}")

# Newsletter safety and accessibility.
check("inkwell_newsletter_rate_limit" in newsletter, "Newsletter rate limiting missing")
check("inkwell_newsletter_unsubscribe" in newsletter, "Newsletter unsubscribe missing")
check("wp_privacy_personal_data_exporters" in newsletter and "wp_privacy_personal_data_erasers" in newsletter, "Newsletter privacy hooks missing")
check("static $instance = 0" in newsletter, "Newsletter form IDs are not unique")
check("inkwell_newsletter_local_limit" in newsletter, "Local newsletter storage is unbounded")
check("data-inkwell-newsletter novalidate" not in newsletter, "No-JS newsletter validation is disabled")
check("data.append('consent', '1')" in js, "AJAX consent is not submitted")
check("window.jQuery(document.body).on('added_to_cart'" in js, "WooCommerce add-to-cart event is not handled")
check("aria-controls=\"inkwell-mini-cart\"" in text(THEME / "inc/template-tags.php"), "Mini-cart disclosure ARIA missing")

# Import safety.
check("if ( ! class_exists( 'WooCommerce' ) )" in importer, "Importer lacks WooCommerce guard")
check("Inkwell Demo Menu" in importer and "wp_get_nav_menu_object( 'Main Menu' )" not in importer, "Importer can overwrite Main Menu")
check("wp_delete_post( $item->ID, true )" not in importer, "Importer still deletes existing menu items")
check("_inkwell_demo_product" in importer and "_inkwell_demo_asset" in importer, "Importer lacks ownership markers")
check("'' === trim( (string) get_post_field( 'post_content'" in importer, "Importer can overwrite cart/checkout content")
check(importer.count("if ( $apply_site_setup )") >= 2, "Pages and site settings are not both opt-in")
check("$legacy_import && $book['isbn']" in importer, "Legacy demo detection can claim unrelated products")
check("wc_get_page_id( 'shop' )" in importer and "<= 0" in importer, "Missing WooCommerce page IDs are not handled")
check("[woocommerce_my_account]" in importer, "My Account page is missing its shortcode")
check("woocommerce_enable_myaccount_registration', 'yes'" in importer, "Demo customer registration is not enabled")
check("inkwell_demo_imported" in importer and "inkwell_account_repaired_205" in importer, "Existing demo account pages are not repaired safely")
check("inkwell_demo_catalog_detected" in importer and "pride-prejudice" in importer, "Legacy WP-CLI demo sites are not detected for account repair")

# Packaging, licensing, fonts, metadata and repository hygiene.
check((THEME / "screenshot.png").is_file(), "Theme screenshot.png is not at theme root")
check((THEME / "screenshot.png").stat().st_size < 600_000, "Theme screenshot is too large")
check(not (THEME / "screenshot.jpg").exists() and not (THEME / "assets/screenshot.png").exists(), "Duplicate screenshot remains")
check(not (THEME / "assets/hero.jpg").exists(), "Unused legacy hero image remains")
check((ROOT / "LICENSE").stat().st_size > 10000 and (THEME / "LICENSE").is_file(), "GPL license missing")
check((THEME / "assets/fonts/OFL-Inter.txt").is_file() and (THEME / "assets/fonts/OFL-Newsreader.txt").is_file(), "Font licenses missing")
fonts = sorted((THEME / "assets/fonts").glob("*.woff2"))
check(len(fonts) == 6, f"Expected 6 variable-font subsets, found {len(fonts)}")
check(len({hashlib.sha256(path.read_bytes()).digest() for path in fonts}) == 6, "Duplicate font binaries remain")
for junk in (ROOT / ".wp-cli", ROOT / ".sudo_as_admin_successful", ROOT / "uploads", ROOT / "demo-content"):
    check(not junk.exists(), f"Repository junk/duplicate remains: {junk.name}")

versions = {
    "single-product.php": "1.6.4",
    "taxonomy-product-attribute.php": "7.3.0",
    "taxonomy-product-cat.php": "4.7.0",
}
for relative, expected in versions.items():
    check(f"@version {expected} (adapted)" in text(THEME / "woocommerce" / relative), f"Incorrect upstream version for {relative}")

# Companion plugin remains independently installable and synchronized.
plugin_header = text(PLUGIN / "inkwell-books.php")
check("Plugin Name: Inkwell Books" in plugin_header, "Companion plugin header missing")
check("Text Domain: inkwell-books" in plugin_header, "Companion plugin text domain does not match its slug")
check("Requires Plugins: woocommerce" in plugin_header, "Companion plugin does not declare WooCommerce")
check("register_activation_hook" in plugin_header and "flush_rewrite_rules" in plugin_header, "Companion plugin activation does not refresh author rewrites")
for module in ("books.php", "newsletter.php"):
    theme_module = text(THEME / "inc" / module)
    plugin_module = text(PLUGIN / "includes" / module).replace("'inkwell-books'", "'inkwell'")
    check(theme_module == plugin_module, f"Plugin {module} module drift")
check((PLUGIN / "languages/inkwell-books.pot").is_file(), "Companion plugin POT is missing")

# Gross structural checks.
json.loads(text(THEME / "demo/books.json"))
check(style.count("{") == style.count("}"), "Unbalanced theme CSS braces")
check(wc_css.count("{") == wc_css.count("}"), "Unbalanced WooCommerce CSS braces")

if "--archives" in sys.argv:
    for directory, archive_name in ((THEME, "inkwell.zip"), (PLUGIN, "inkwell-books.zip")):
        expected = {
            path.relative_to(ROOT).as_posix(): path.read_bytes()
            for path in directory.rglob("*")
            if path.is_file()
        }
        with ZipFile(ROOT / archive_name) as archive:
            file_infos = [info for info in archive.infolist() if not info.is_dir()]
            actual = {info.filename: archive.read(info.filename) for info in file_infos}
            check(all(info.date_time == (2026, 1, 1, 0, 0, 0) for info in file_infos), f"{archive_name} has non-deterministic timestamps")
        check(actual == expected, f"{archive_name} does not exactly match its source directory")

print("PASS: Inkwell static audit")
