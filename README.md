# 📖 Inkwell — Boutique Bookstore Theme for WooCommerce

Inkwell is a classic WordPress theme for a curated online bookshop. It adds an
editorial Newsreader + Inter design, book-cover product cards, author browsing,
bibliographic product fields, curated home-page sections, and styled classic
WooCommerce shopping flows without a page builder.

- **Theme version:** 2.0.5
- **Requires:** WordPress 6.0+, WooCommerce 8.0+, PHP 7.4+
- **Tested metadata:** WordPress 6.8, WooCommerce 11.0
- **License:** GPL-2.0-or-later · **Text domain:** `inkwell`

## Highlights

- Responsive shop, product, author, blog, cart, checkout and account layouts
- `book_author` taxonomy and book details: ISBN, publisher, year, pages, format and language
- Linked product cards with AJAX quick-add, live cart count and accessible mini-cart disclosure
- Author-aware product search
- Configurable store benefits, fulfillment/returns messages, payment labels and social URLs
- Privacy-aware reading list with consent, throttling, unsubscribe, exporter and eraser support
- One-click demo catalog: 33 books, 7 genres, 31 authors and 14 reviews
- Self-hosted variable fonts with no external font requests
- Complete Persian (`fa_IR`) and Sorani Kurdish (`ckb`) interface translations
- RTL-aware and keyboard-friendly

## Repository layout

```text
inkwell/              Installable theme source
inkwell-books/        Optional companion plugin for portable book/newsletter data
inkwell.zip           Built theme archive
inkwell-books.zip     Built companion-plugin archive
tools/                Release, POT, cover and WP-CLI import tools
tests/                Dependency-free release regressions
```

`inkwell/demo/books.json` and `inkwell/demo/covers/` are the single source of
truth for bundled demo data.

## Installation

1. Install and activate WooCommerce.
2. Upload `inkwell.zip` under **Appearance → Themes → Add New → Upload Theme**.
3. Optionally upload and activate `inkwell-books.zip` under **Plugins → Add New**.
   The theme includes backward-compatible fallbacks, but the plugin keeps author,
   book-detail and reading-list interfaces available when changing themes.
4. Open **Appearance → Import Demo Content** if you want the sample catalog.

The importer always reuses content it owns and skips unrelated products with a
matching SKU. The optional **complete demo-site setup** checkbox is deliberately
unchecked by default; enable it only when you want the importer to assign its
own menu/empty sidebars, EUR currency, demo front page, and customer account
registration. Existing cart or checkout block content is never replaced.

## Configuration

Under **Appearance → Customize**:

- **Inkwell Colors:** emerald accent and optional dark header
- **Header & Announcement Bar:** announcement content
- **Store Benefits & Policies:** shipping, fulfillment, returns and payment labels
- **Hero Section:** copy, buttons, background image and visibility
- **Home Page Sections:** visibility, genres, quote, testimonials and newsletter copy
- **Footer:** about/copyright text and social URLs

Merchant promises are blank by default. Configure only shipping, return,
fulfillment and payment claims the store actually offers.

## Book and newsletter data

Book product metadata keys:

- `_inkwell_isbn`
- `_inkwell_publisher`
- `_inkwell_year`
- `_inkwell_pages`
- `_inkwell_format`
- `_inkwell_language`

Newsletter shortcodes:

- `[inkwell_newsletter]`
- `[inkwell_newsletter_unsubscribe]`

A new subscription fires `inkwell_newsletter_subscribed`; an unsubscribe fires
`inkwell_newsletter_unsubscribed`. Local storage is capped at 1,000 addresses by
default (filterable with `inkwell_newsletter_local_limit`). For a larger or
marketing-critical list, hook an email service such as Mailchimp or Brevo and
use its authenticated double-opt-in/unsubscribe workflow.

## Persian and Sorani Kurdish

The theme and companion plugin include compiled interface translations for:

- فارسی — Persian (`fa_IR`)
- کوردی (سۆرانی) — Sorani Kurdish (`ckb`)

Open **Appearance → Inkwell Languages** to install the Persian/Sorani WordPress
core packs and activate a site-wide language. The public header switcher is
visible by default and can be hidden in **Customize → Header & Announcement
Bar**.

Without a multilingual plugin, the switcher remembers each visitor’s interface
language in the essential `inkwell_language` cookie. It translates the theme,
companion plugin, WordPress and available WooCommerce strings, but it does not
duplicate products or pages.

For translated content and language-specific URLs, use a WooCommerce-compatible
multilingual plugin such as Polylang for WooCommerce, WPML/WooCommerce
Multilingual, or TranslatePress. Inkwell automatically uses Polylang/WPML’s
content-aware language links instead of its cookie links. Product titles,
descriptions, categories, authors, policies and demo content must be translated
through that workflow. Configure page caching to vary by the language cookie or
use the multilingual plugin’s cache integration.

## Development and release

```bash
npm ci
npm test
npm run lint:js
python3 tools/sync-plugin-modules.py
npm run make-pot
./tools/build-release.sh
```

The release script regenerates both translation templates, runs static
regressions, checks JavaScript, verifies the generated companion-plugin
modules, builds both ZIP files with normalized metadata, proves a second build
is byte-identical, tests archive integrity, and compares every packaged file
byte-for-byte with its source.

`tools/github-quality-workflow.yml` is a ready-to-install GitHub Actions workflow
that lints every PHP file on PHP 7.4, 8.1 and 8.4. Copy it to
`.github/workflows/quality.yml` when the repository's GitHub App has workflow
write permission.

To regenerate typographic demo covers:

```bash
python3 tools/make-covers.py
```

To run the safe importer through WP-CLI with the theme active:

```bash
WP_CLI="php /path/to/wp-cli.phar" WP_SITE=/path/to/wordpress ./tools/import-demo.sh
```

## Third-party assets

Inter and Newsreader are licensed under the SIL Open Font License 1.1. Their
license texts and attribution are bundled under `inkwell/assets/fonts/` and
`inkwell/THIRD-PARTY-NOTICES.md`.
