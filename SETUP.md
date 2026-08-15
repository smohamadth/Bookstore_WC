# 🔧 Inkwell — Setup Guide

Complete walkthrough: install, demo import, configuration, translation and troubleshooting.

---

## 1. Requirements

| Thing | Version |
|---|---|
| WordPress | ≥ 6.0 (tested 6.8) |
| WooCommerce | ≥ 8.0 (tested **11.0.1**) |
| PHP | ≥ 7.4 (tested 8.4) |
| Extensions | standard — no PHP extensions beyond WP/WC defaults |

## 2. Install the theme

1. In wp-admin go to **Appearance → Themes → Add New → Upload Theme**.
2. Choose `inkwell.zip` → **Install Now** → **Activate**.
3. WooCommerce will be detected automatically. If you activate WooCommerce
   *after* the theme, just re-save **Settings → Permalinks** once.

## 3. Import the demo content (recommended — one click, no WP-CLI)

After activating the theme:

1. Open **Appearance → Import Demo Content** in wp-admin (or click the
   **"Import demo content"** button in the dashboard notice that appears while
   your shop is empty).
2. Click **"Import demo content now"** and wait ~5 seconds.
3. Done — visit **Shop** to see the catalog.

The importer is bundled *inside the theme* (`inkwell/demo/`), so it works on any
host without extra tools. It is **idempotent** — safe to re-run, it updates by
SKU instead of duplicating.

**What it creates:**

- **7 genres** (Fiction, Sci-Fi & Fantasy, Mystery & Thriller, Non-Fiction, History & Biography, Children's Books, Poetry)
- **33 books** with real bibliographic data, sale prices, featured flags, typographic covers (staggered publication dates so "New arrivals" and "New" chips look natural)
- **19 authors** (the `book_author` taxonomy) with bios → beautiful author archive pages
- **Pages:** Home, Shop, About, Contact, Privacy Policy, The Journal
- **Menus:** main menu (with genre dropdown), footer Shop & Help menus
- **Journal:** 3 sample posts with images and categories
- **Reviews:** 14 product reviews with star ratings
- **Widgets:** shop sidebar (Genres, Price filter, Top rated) + blog sidebar
- **Settings:** front page, EUR currency, pretty permalinks, announcement bar, genre tiles, quote & testimonials

> **WP-CLI alternative:** `WP_CLI="php wp-cli.phar" WP_SITE=/path/to/your/wordpress ./tools/import-demo.sh`

### Currency
The importer sets EUR. To change it: **WooCommerce → Settings → General → Currency**.

### Cart & Checkout — classic vs blocks
WooCommerce 11 ships **block-based** cart/checkout pages. This theme is built on
the classic templates, so the importer pins the Cart & Checkout pages to the
classic shortcodes (`[woocommerce_cart]`, `[woocommerce_checkout]`) — this keeps
every theme feature (trust badges, quantity steppers, styling) fully active.
If you *prefer* the block versions, edit the Cart/Checkout pages in the block
editor and replace the shortcode with the Cart/Checkout blocks — the theme
ships fallback styles for the blocks too.

## 4. Configure (Appearance → Customize)

### Colors
- **Accent color** — buttons, links, badges (live preview; defaults `#b4532a`)
- **Accent (hover)** — dark variant
- **Dark header** — swaps the header for a dark style

### Header & Announcement Bar
- **Announcement bar text** — e.g. `Free shipping on orders over €25 — every book, everywhere.` Leave empty to hide.

### Hero Section
- Eyebrow, headline, subheadline, two buttons (label + URL), background image.
- Empty fields fall back to built-in defaults, so the hero always looks complete.

### Home Page Sections
- Show/hide: genre tiles, bestsellers, new arrivals, quote, testimonials, journal, newsletter.
- **Genre tile 1–4**: pick which categories appear as tiles.
- **Quote / testimonials**: your own text.
- **Newsletter**: title + text.

### Footer
- About text, copyright line. (Menus: **Appearance → Menus → Manage Locations**.)

## 5. Daily operations

| Task | How |
|---|---|
| Add a book | Products → Add New → title, description, cover (product image), price → **Book Details** tab → ISBN/publisher/year/pages/format/language → **Authors** box → assign author |
| Mark a bestseller | Products → edit → **Product data → General → Featured** (star icon) |
| Reviews | WooCommerce → Settings → Products → enable “Enable reviews”; customers rate books on the product page |
| Newsletter subscribers | Theme option `inkwell_subscribers` (see §7 for Mailchimp) |

## 6. Translation

1. Generate/refresh the template: `wp i18n make-pot inkwell languages/inkwell.pot --domain=inkwell`
2. Create `inkwell-xx_XX.po` (e.g. with [Poedit](https://poedit.net)) from the pot.
3. Compile to `inkwell-xx_XX.mo` and place both in `inkwell/languages/`.
4. Set the site language under **Settings → General**.

## 7. Connecting a real newsletter service (Mailchimp / Brevo / Kit)

The theme stores subscribers locally and fires an action you can hook:

```php
add_action( 'inkwell_newsletter_subscribed', function ( $email ) {
    // POST $email to your ESP's API…
} );
```

Drop that in a small must-use plugin (`wp-content/mu-plugins/esp-bridge.php`) so
it survives theme updates.

## 8. Troubleshooting

| Symptom | Fix |
|---|---|
| Shop grid looks unstyled | Re-save **Settings → Permalinks** once (rewrite flush) |
| No cart counter update | Ensure `jquery` is loaded (default) — the counter uses WC cart fragments |
| Gallery has no zoom/slider | Theme declares gallery support; check a product has multiple images |
| “Outdated WooCommerce templates” in Status | Should never appear (scanned clean vs WC 11.0.1); if a future WC bumps a version, copy the new file from the plugin into `inkwell/woocommerce/` |
| Front page redirects to `/home/` | The static front page slug must not be `home` — rename it |
| Want the block cart/checkout | Swap the shortcodes for the blocks on those pages (see §3) |

## 9. Development

```
inkwell/
├── style.css              theme header + core styles
├── css/woocommerce.css    shop/product/cart/checkout styles
├── js/main.js             vanilla JS behaviors
├── functions.php          bootstrap
├── inc/
│   ├── setup.php          supports, menus, images, widgets
│   ├── customizer.php     all theme options
│   ├── template-tags.php  icons, logo, cart, product rows
│   ├── books.php          book_author taxonomy + Book Details meta
│   ├── woocommerce.php    WC hooks, layout, badges, tabs, author box
│   └── newsletter.php     shortcode + AJAX endpoint
├── woocommerce/           WC template overrides (WC 11.0.1 compatible)
├── template-parts/        hero + 8 front-page sections + cards
├── assets/                logo, hero, screenshot
├── theme.json  languages/
```

Demo data: `demo-content/books.json` is the single source of truth —
edit it, re-run `python3 tools/make-covers.py` and the importer, done.
