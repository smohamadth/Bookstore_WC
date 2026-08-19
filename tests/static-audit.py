#!/usr/bin/env python3
"""Fast dependency-free release regressions for the Inkwell theme."""
from __future__ import annotations

import gettext
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
languages = text(THEME / "inc/languages.php")
demo_localization = text(THEME / "inc/demo-localization.php")
product = text(THEME / "woocommerce/content-product.php")
category = text(THEME / "woocommerce/content-product-cat.php")
search_form = text(THEME / "searchform.php")
search_template = text(THEME / "search.php")
importer = text(THEME / "inc/demo-import.php")
js = text(THEME / "js/main.js")
theme_json = json.loads(text(THEME / "theme.json"))

# Release and original grid/menu regressions.
release_version = "2.0.8"
check(re.search(rf"^Version:\s*{re.escape(release_version)}\s*$", style, re.M) is not None, "Theme header version mismatch")
check(f"define( 'INKWELL_VERSION', '{release_version}' );" in functions, "Runtime version mismatch")
check(json.loads(text(ROOT / "package.json"))["version"] == release_version, "Package version mismatch")
check(f"Stable tag: {release_version}" in text(THEME / "readme.txt"), "Theme readme version mismatch")
check(f"Project-Id-Version: Inkwell {release_version}" in text(THEME / "languages/inkwell.pot"), "POT version mismatch")
check(f"package: 'Inkwell {release_version}'" in text(ROOT / "tools/make-pot.js"), "Translation build version mismatch")
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
check("genre-tile__number" in text(THEME / "template-parts/section-categories.php"), "Home genre tile details are missing")
check("add_action( 'woocommerce_no_products_found', 'inkwell_no_products_found', 10 );" in wc, "No-products action is not wired")
check("woocommerce_quantity_input_args" not in wc, "Theme still overrides product quantity business rules")
check("hero-search" in text(THEME / "template-parts/hero.php"), "Home hero catalog search is missing")
check("section-head__copy" in text(THEME / "inc/template-tags.php"), "Home section hierarchy regression")
check("shop-archive-header" in wc and "shop-category-chips" in wc, "Shop discovery header is missing")
check("data-shop-filter-toggle" in wc and "data-shop-filter-close" in text(THEME / "sidebar.php"), "Shop filter drawer controls are missing")
check("initShopFilters" in js and "shop-filters-open" in js, "Shop filter drawer behavior is missing")
check("shop-filter-backdrop" in wc_css and "shop-archive-header" in wc_css, "Shop redesign styles are missing")
check("'woocommerce'" not in text(THEME / "woocommerce/global/quantity-input.php"), "Quantity override bypasses bundled translations")
check("'woocommerce'" not in text(THEME / "woocommerce/single-product/meta.php"), "Product meta override bypasses bundled translations")
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
rtl_css = text(THEME / "rtl.css")
check((THEME / "rtl.css").is_file() and "inkwell-rtl" in text(THEME / "inc/setup.php"), "RTL support is missing")
check("html:lang(fa)" in rtl_css and "html:lang(ckb)" in rtl_css, "Persian/Sorani typography support is missing")
check("estedad-persian.woff2" in rtl_css and "noto-kufi-sorani.woff2" in rtl_css and "vazirmatn-arabic.woff2" in rtl_css, "Language-specific Persian/Sorani fonts are not loaded")
check("unicode-bidi: plaintext" in rtl_css and ".woocommerce-Price-amount" in rtl_css, "Mixed-script commerce layout is not bidi-safe")
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
check("$is_owned || $is_match" in importer and "_inkwell_demo_product" in importer, "Legacy demo replacement is not ownership guarded")
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
font_licenses = ("OFL-Inter.txt", "OFL-Newsreader.txt", "OFL-Vazirmatn.txt", "OFL-Estedad.txt", "OFL-NotoKufiArabic.txt")
check(all((THEME / "assets/fonts" / name).is_file() for name in font_licenses), "Font licenses missing")
fonts = sorted((THEME / "assets/fonts").glob("*.woff2"))
check(len(fonts) == 9, f"Expected 9 variable-font files, found {len(fonts)}")
check(len({hashlib.sha256(path.read_bytes()).digest() for path in fonts}) == 9, "Duplicate font binaries remain")
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

# Bundled Persian and Sorani translations must be visible and operational.
check("/inc/languages.php" in functions, "Language module is not loaded")
check(header.count("inkwell_language_switcher();") == 2, "Desktop/mobile language switchers are not both rendered")
check("inkwell_languages_admin_page" in languages and "wp_download_language_pack" in languages, "Dashboard language installer is missing")
check("switch_to_locale" in languages and "inkwell_language" in languages, "Visitor language preference is not applied")
check("inkwell_load_bundled_interface_translation" in languages and "language_attributes" in languages, "Bundled-locale fallback is missing")
check("inkwell_interface_is_rtl" in languages and "inkwell_interface_is_rtl" in text(THEME / "inc/setup.php"), "Fallback locale does not load RTL layout")
check("$wp_locale->text_direction = 'rtl'" in languages, "Core/WooCommerce RTL direction is not forced for fallback locales")
check(".language-selector__toggle" in style and ".language-selector__menu" in style and ".mobile-language-switcher" in style, "Professional language selector styling is missing")
check("initLanguageSelectors" in js and "ArrowDown" in js and "Escape" in js, "Language selector keyboard behavior is incomplete")
check("data-language-toggle" in languages and "data-language-menu" in languages, "Language selector disclosure markup is missing")
check("demo-localization.php" in functions and "nav_menu_item_title" in demo_localization, "Imported demo chrome is not localized")
check("product_cat" in demo_localization and "Science Fiction & Fantasy" in demo_localization, "Imported demo categories are not localized")
check("sanitize_key( wp_unslash( $_GET['inkwell_lang']" not in languages, "Locale case would be destroyed by sanitize_key")
check("id=\"inkwell-mini-cart\"" in header, "Mini-cart ARIA target is missing")

# Bundled Persian and Sorani translations must be complete, compiled and safe.
placeholder_pattern = re.compile(r"%(?:\d+\$)?[sd]")
for locale in ("fa_IR", "ckb"):
    for directory, filename_prefix, pot_path in (
        (THEME / "languages", "", THEME / "languages/inkwell.pot"),
        (PLUGIN / "languages", "inkwell-books-", PLUGIN / "languages/inkwell-books.pot"),
    ):
        pot_source = text(pot_path)
        expected_entries = sum(line.startswith("msgid ") for line in pot_source.splitlines()) - 1
        plural_entries = sum(line.startswith("msgid_plural ") for line in pot_source.splitlines())
        po_path = directory / f"{filename_prefix}{locale}.po"
        mo_path = directory / f"{filename_prefix}{locale}.mo"
        check(po_path.is_file() and mo_path.is_file(), f"Missing {locale} language files in {directory}")
        with mo_path.open("rb") as stream:
            translations = gettext.GNUTranslations(stream)
        catalog = translations._catalog
        info = translations.info()
        check(info.get("language") == locale, f"Wrong locale metadata in {mo_path.name}")
        check(info.get("x-inkwell-template-sha256") == hashlib.sha256(pot_path.read_bytes()).hexdigest(), f"Stale translations in {mo_path.name}")
        check(len(catalog) == 1 + expected_entries + plural_entries, f"Incomplete catalog in {mo_path.name}")
        if directory == THEME / "languages":
            expected_shop = "فروشگاه" if locale == "fa_IR" else "فرۆشگا"
            check(translations.gettext("Shop") == expected_shop, f"Core storefront translation failed in {mo_path.name}")
            check(translations.ngettext("%d book", "%d books", 2) != "%d books", f"Plural translation failed in {mo_path.name}")
        for source_key, translated in catalog.items():
            if source_key == "":
                continue
            source = source_key[0] if isinstance(source_key, tuple) else source_key
            source = source.split("\x04", 1)[-1]
            check(bool(translated), f"Empty translation for {source!r} in {mo_path.name}")
            check(placeholder_pattern.findall(source) == placeholder_pattern.findall(translated), f"Placeholder mismatch for {source!r} in {mo_path.name}")
        keeping = catalog.get("Books worth <em>keeping</em>")
        if keeping:
            check("<em>" in keeping and "</em>" in keeping, f"Hero markup missing in {mo_path.name}")

# Fictional Kurdish demo catalog and generated covers.
demo_books = json.loads(text(THEME / "demo/books.json"))
check(len(demo_books) == 33 and len({book["sku"] for book in demo_books}) == 33, "Demo catalog must contain 33 unique books")
check(len({book["author"] for book in demo_books}) == 27, "Demo catalog author count mismatch")
check(all(book.get("language") == "کوردی (سۆرانی)" and book.get("author_bio") for book in demo_books), "Demo books are not complete Sorani fiction records")
check(all(re.search(r"[\u0600-\u06ff]", book["title"]) for book in demo_books), "Non-Kurdish demo title found")
check(not {"pride-prejudice", "dune", "sapiens"} & {book["sku"] for book in demo_books}, "Retired real-book products remain in demo data")
retired_names = ("Pride and Prejudice", "George Orwell", "Agatha Christie", "Roald Dahl", "Where the Crawdads Sing")
check(not any(name in text(THEME / "demo/books.json") for name in retired_names), "Real book or author remains in demo catalog")
check(not any(name in importer for name in retired_names), "Real book or author remains in importer content")
cover_names = {path.stem for path in (THEME / "demo/covers").glob("*.png")}
check(cover_names == {book["sku"] for book in demo_books}, "Demo covers do not exactly match the Kurdish catalog")
check("inkwell_demo_remove_legacy_products" in importer and "_inkwell_demo_catalog_version" in importer, "Legacy demo migration is missing")
check("retired demo catalog detected" in importer and "Replace demo catalog" in importer, "Legacy catalog replacement notice is missing")
check("fictional Kurdish demo reviews" in importer, "Demo reviews are not clearly fictional Kurdish content")
check((ROOT / "tools/fonts/Vazirmatn.ttf").is_file() and (ROOT / "tools/fonts/NotoKufiArabic.ttf").is_file(), "Kurdish cover-generation fonts are missing")
check((ROOT / "tools/requirements.txt").is_file() and "arabic-reshaper" in text(ROOT / "tools/requirements.txt"), "Kurdish cover shaping dependencies are missing")
check(style.count("{") == style.count("}"), "Unbalanced theme CSS braces")
check(wc_css.count("{") == wc_css.count("}"), "Unbalanced WooCommerce CSS braces")
check(rtl_css.count("{") == rtl_css.count("}"), "Unbalanced RTL CSS braces")

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
