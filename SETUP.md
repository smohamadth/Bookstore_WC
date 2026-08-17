# 🔧 Inkwell Setup Guide

## 1. Requirements

| Component | Requirement |
|---|---|
| WordPress | 6.0+ (theme metadata tested through 6.8) |
| WooCommerce | 8.0+ (theme metadata tested through 11.0) |
| PHP | 7.4+ |
| Browser | Current evergreen browser; JavaScript enhances but is not required for core forms |

No PHP extension beyond normal WordPress/WooCommerce hosting is required.

## 2. Install

1. Install and activate WooCommerce.
2. Go to **Appearance → Themes → Add New → Upload Theme**.
3. Upload `inkwell.zip`, install and activate it.
4. Recommended: go to **Plugins → Add New → Upload Plugin**, upload
   `inkwell-books.zip`, and activate it. This companion plugin keeps author,
   bibliographic and newsletter data interfaces available if themes change.

The theme retains compatible fallback modules, so activating the plugin later
does not interrupt an existing Inkwell site.

## 3. Demo content

Open **Appearance → Import Demo Content**.

The catalog contains:

- 33 books with typographic covers and bibliographic data
- 7 product genres
- 31 authors with bios
- 14 approved sample reviews
- 3 sample journal posts
- Optional pages, menu, sidebars and theme settings

### Safe behavior

The catalog importer:

- marks every demo product and media attachment it owns;
- reuses marked products and media on reruns;
- skips an unrelated product that happens to have a demo SKU;
- creates a dedicated **Inkwell Demo Menu**, never deleting a menu named “Main Menu”;
- fills only empty theme sidebars without replacing existing widgets;
- initializes cart/checkout shortcodes only when those pages are genuinely empty;
- creates a working My Account shortcode page and enables customer registration during complete demo setup;
- preserves existing block-based cart and checkout content.

The **Also apply the complete demo-site setup** option is unchecked by default.
Enable it only for a new/demo site when you want EUR currency, the demo front
page, demo menu and empty sidebars assigned automatically.

## 4. Customize

### Inkwell Colors

- Accent color (default emerald `#2e6b52`)
- Darker hover color
- Optional dark header

### Header

- Announcement bar text; leave blank to hide it

### Store Benefits & Policies

- Shipping benefit
- Fulfillment promise
- Returns summary
- Accepted payment labels

These values are blank on a normal installation. Add only claims that match the
store’s WooCommerce configuration and legal policies.

### Hero

- Hide/show the hero
- Eyebrow, headline and subheadline
- Two button labels and URLs
- Optional background image

### Home Page Sections

- Hide/show the benefits, genre, bestseller, new-arrival, quote, testimonial,
  journal and newsletter sections
- Select four featured genres
- Configure quote, testimonials and newsletter copy

### Footer

- About and copyright text
- Facebook, Instagram and X/Twitter URLs

Blank social URLs and payment labels are not rendered.

## 5. Book management

1. Open **Products → Add New**.
2. Add title, description, cover and WooCommerce price/stock information.
3. Use **Product data → Book Details** for ISBN, publisher, year, pages, format and language.
4. Assign one or more terms in the **Authors** box.
5. Mark a product Featured to include it in the bestseller row.

## 6. Reading list and privacy

The built-in form requires explicit consent and provides request throttling,
local unsubscribe, WordPress personal-data export, and erasure integration.

Shortcodes:

```text
[inkwell_newsletter]
[inkwell_newsletter_unsubscribe]
```

Local records are stored in the non-autoloaded `inkwell_subscribers` option and
are capped at 1,000 addresses by default. Use the
`inkwell_newsletter_local_limit` filter only for a deliberately sized local
list. For a production marketing list, connect a specialist email provider:

```php
add_action( 'inkwell_newsletter_subscribed', function ( $email ) {
    // Send the address to the provider's double-opt-in API.
} );
```

Document the provider in the privacy policy. The bundled demo privacy page is a
placeholder and must be replaced with the merchant’s real policy.

## 7. Classic and block cart/checkout

The theme has detailed classic-template styling and baseline block styling.
The importer does not replace existing Cart or Checkout blocks. A fresh empty
page can be initialized with the classic shortcodes during complete demo setup.

## 8. Persian, Sorani Kurdish and other translations

Bundled RTL interface packs:

- **فارسی (Persian):** theme `fa_IR.po` / `fa_IR.mo`; plugin `inkwell-books-fa_IR.po` / `inkwell-books-fa_IR.mo`
- **کوردی (سۆرانی):** theme `ckb.po` / `ckb.mo`; plugin `inkwell-books-ckb.po` / `inkwell-books-ckb.mo`

Open **Appearance → Inkwell Languages** to install either WordPress core pack
and activate a site-wide language. WordPress will load the bundled translations,
RTL stylesheet and available WooCommerce language pack. Persian uses Estedad
headings with Vazirmatn text; Sorani uses Noto Kufi Arabic headings with
Vazirmatn text. The accessible dropdown selector is enabled by default; hide it
under **Customize → Header & Announcement Bar** if needed.

Without a multilingual plugin, the switcher changes theme strings and known
imported menu/widget/policy/genre labels per visitor using the essential
`inkwell_language` cookie. The bundled catalog provides a fallback before core
packs are installed; install the core packs to translate WordPress and
WooCommerce too. Configure full-page caches to vary by the language cookie.
Products and pages are not duplicated.

For translated content and language-specific URLs, install Polylang for
WooCommerce, WPML/WooCommerce Multilingual, or TranslatePress. Inkwell detects
Polylang/WPML and uses its content-aware language links. Translate products,
categories, author terms, menus, pages, checkout policies and email content in
the plugin. Bundled demo catalog content remains English until translated by
the store owner.

To refresh translation templates after changing source strings:

```bash
npm ci
npm run make-pot
```

Theme translation files use the locale-only WordPress naming convention
(`<locale>.po` / `<locale>.mo`). Companion-plugin files use
`inkwell-books-<locale>.po` / `.mo`.

## 9. Development and releases

```bash
npm ci
npm test
npm run lint:js
python3 tools/sync-plugin-modules.py
./tools/build-release.sh
```

The build produces deterministic, validated `inkwell.zip` and
`inkwell-books.zip` archives. The ready-to-install
`tools/github-quality-workflow.yml` workflow syntax-checks PHP on 7.4, 8.1 and
8.4 and runs the release regression suite once copied into `.github/workflows/`.

## 10. Troubleshooting

| Symptom | Resolution |
|---|---|
| Import page says WooCommerce is required | Install and activate WooCommerce first |
| Shop sidebar is empty | Add WooCommerce widgets, or run complete demo setup on an empty sidebar |
| No store-benefit messages appear | Configure truthful messages under Store Benefits & Policies |
| Mini-cart does not update | Confirm WooCommerce frontend scripts are not disabled by an optimization plugin |
| Gallery lacks zoom/slider | Add more than one product image and ensure gallery scripts are enabled |
| Author fields disappear after changing themes | Install and activate the Inkwell Books companion plugin |
| My Account shows login but no registration form | Enable “Allow customers to create an account on the My account page” under WooCommerce → Settings → Accounts & Privacy; complete demo setup enables it automatically |
| Need block cart/checkout | Keep the existing blocks; the importer will not overwrite them |
