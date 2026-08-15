# 📖 Inkwell — Boutique Bookstore Theme for WooCommerce
## Master Implementation Plan

**Status:** Reference document — implementation follows this plan section by section.
**Target stack:** WordPress ≥ 6.0 · WooCommerce ≥ 8.0 (tested against WC 11.0.1) · PHP ≥ 7.4 (tested on 8.4)
**License:** GPL-2.0-or-later

---

## 1. Vision

A complete, polished, self-contained **bookstore experience** on top of WooCommerce:
install the theme, import the demo content, and you have a real shop with
book-specific features — no page builder, no paid plugins, no bloat.

Design direction: **"modern literary"** — warm paper tones, deep ink navy, burnt
terracotta accent, elegant serif display type (Fraunces) paired with a clean
humanist sans (Inter). Quiet, premium, readable.

---

## 2. Design System

| Token | Value | Usage |
|---|---|---|
| `--ink` | `#23272f` | Body text, headings |
| `--paper` | `#faf7f0` | Page background |
| `--card` | `#ffffff` | Cards, surfaces |
| `--line` | `#e8e0d2` | Borders, dividers |
| `--accent` | `#b4532a` | Buttons, links, badges |
| `--accent-dark` | `#8f3d1e` | Hover states |
| `--gold` | `#d9a441` | Star ratings |
| `--ok` | `#2f6b4f` | Success, stock-in |
| `--danger` | `#b3261e` | Errors, sale |

- **Display:** Fraunces (Google Fonts, `display=swap`, serif fallbacks for offline)
- **Body:** Inter (humanist sans, system fallback)
- **Spacing scale:** 4px base · section rhythm 96px desktop / 64px mobile
- **Radii:** 4px (controls) · 12px (cards, images)
- **Shadow:** soft `0 8px 24px rgba(35,39,47,.08)`
- **Grid:** max-width 1200px · shop grid 3-up (4-up ≥1440px) · 2-up mobile

---

## 3. Architecture Decisions

1. **Classic PHP theme** (not block theme): maximum WooCommerce compatibility,
   works on every host, familiar to customize, no site-editor dependency.
2. **WooCommerce template overrides** copied from the exact installed WC version
   (11.0.1) and modified — upstream template versions are retained so future
   WooCommerce status checks remain meaningful.
3. **Book domain modeled the WooCommerce way:**
   - `book_author` — custom *taxonomy* → author archive pages with bio (term description).
   - **Book Details** product-data panel — meta fields: ISBN, Publisher, Year, Pages, Format, Language.
4. **Hooks over template edits** where possible (wrappers, tabs, badges, fragments).
5. **No page builder / no premium plugins** — everything is theme code + core WooCommerce.
6. **Accessibility:** skip link, ARIA toggles, focus-visible styles, contrast-safe palette.

---

## 4. Feature List

### Storefront (theme)
- [x] Sticky header: logo · search toggle · account · live cart counter
- [x] Announcement bar (customizer text)
- [x] Mega-friendly primary menu + 2 footer menus
- [x] Mobile drawer menu with focus trapping
- [x] Footer: about, shop links, help links, newsletter form, payment badges

### Front page (all sections toggleable in Customizer)
- [x] Hero (headline, subline, 2 CTAs, image)
- [x] Value-props strip (free shipping, hand-picked, secure checkout, returns)
- [x] Featured categories (4 tiles, pickable in Customizer)
- [x] Bestsellers = WooCommerce *Featured* products
- [x] New arrivals (latest products)
- [x] Quote band (customizer text)
- [x] Testimonials (3 slots)
- [x] Latest journal posts
- [x] Newsletter band (AJAX, nonce-protected)

### Shop & products
- [x] Shop page with sidebar (categories, price filter, on-sale)
- [x] Product cards: cover, sale badge (−%), author, rating, price, hover add-to-cart
- [x] Single product: gallery, author link, book meta, quantity stepper, tabs
- [x] "Book Details" tab (ISBN, publisher, year, pages, format, language)
- [x] Author box under the product (bio from taxonomy term)
- [x] Related products (4), author archive pages
- [x] Quantity +/− steppers on shop cards, product page, cart
- [x] Cart / Checkout / My Account fully styled
- [x] Styled review stars & review form

### Book domain (inc/books.php)
- [x] `book_author` taxonomy + term bio + admin columns (books per author)
- [x] "Book Details" product-data tab with sanitized saving
- [x] Author column in product admin list
- [x] Live shop search searches author names too

### Content & extras
- [x] Blog: cards, sidebar, pagination
- [x] Newsletter shortcode `[inkwell_newsletter]` + AJAX handler
- [x] 404 page with search, SEO-ready markup, `theme.json`, print styles
- [x] `inkwell.pot` translation template (text domain `inkwell`)

### Demo content (inkwell/demo/ + tools/)
- [x] 6 genres, 14 books with real meta, sale prices, featured flags
- [x] Typographic cover art generated locally (no copyrighted imagery)
- [x] Pages (Home/Shop/About/Contact/Journal), menus, blog posts, reviews
- [x] One-command importer via WP-CLI

---

## 5. File Map

```
inkwell/                        ← the theme (installable as inkwell.zip)
├── style.css                   theme header + core styles
├── css/woocommerce.css         shop/product/cart/checkout styles
├── js/main.js                  menu, steppers, newsletter AJAX, scroll FX
├── functions.php               bootloader
├── inc/setup.php               supports, menus, images, widgets
├── inc/customizer.php          all theme options
├── inc/template-tags.php       helpers (logo, icons, section headers…)
├── inc/books.php               book_author taxonomy + Book Details meta
├── inc/woocommerce.php         WC hooks: wrappers, badges, tabs, author box
├── inc/newsletter.php          shortcode + AJAX endpoint
├── header.php footer.php sidebar.php searchform.php
├── index.php front-page.php page.php single.php archive.php search.php
├── taxonomy-book_author.php comments.php 404.php woocommerce.php
├── template-parts/             content, hero, 8 front-page sections
├── woocommerce/                overrides cloned from WC 11.0.1 + edits
├── screenshot.jpg + assets/fonts/ and cover-fallback.png
├── theme.json  languages/inkwell.pot
```

---

## 6. Testing Strategy (real environment)

1. `php -l` lint every file
2. Install WP + WooCommerce 11.0.1 in sandbox; activate theme
3. Import demo content; `WP_DEBUG` on; crawl every route with curl:
   home, shop, genre, author, product, cart, checkout, account, blog, post, search, 404
4. Fail on any `Warning|Notice|Fatal|Deprecated` in output or debug.log
5. Verify WooCommerce template-override freshness report is clean
6. Verify cart/checkout happy path (add item → cart → checkout → order created)
7. JS syntax check with node; `wc` product lookup table refresh for ratings

---

## 7. Deliverables

1. `inkwell/` theme source + `inkwell.zip` (ready for Appearance → Themes → Upload)
2. `inkwell/demo/` — catalog data and generated covers
3. `README.md` — overview, quick start, structure
4. `SETUP.md` — full install, customizer tour, translation, troubleshooting
5. This plan document

---

## 7.5. V2 — Design Overhaul & Catalog Expansion

Executed 2026-08-14:
- **Design direction:** "modern literary" → "modern library": Newsreader editorial
  serif (self-hosted, replaces Fraunces) + Inter; parchment/ink/emerald/gold palette
  (replaces terracotta accent).
- **Hero:** dark split with floating cover collage (3 featured products) + staff-picks badge.
- **Product cards:** book-spine edge, hover lift, quick-add overlay (AJAX), ranked
  badges 01–08 on bestsellers, "New" chips (45-day window), valid HTML (no nested anchors).
- **Header:** mini-cart dropdown (WC fragments-powered), animated nav underlines.
- **Single product:** sticky summary, in-stock chip, trust chips, "More by this author"
  strip (same-taxonomy query).
- **Checkout:** two-column layout via CSS (billing | order review), cart 2-col grid.
- **Catalog:** 17 → **33 books**, new **Poetry** genre, 31 authors, 14 reviews;
  staggered post dates (9-day gaps, newest last); band-style covers for variety;
  reviews dedupe + deterministic dates → importer fully idempotent.

## 8. Build Order

1. ✅ Environment: PHP 8.4 + MariaDB + WP + WC 11.0.1 installed
2. Design tokens + base CSS skeleton
3. Core templates (header/footer/front page/pages/blog)
4. `inc/` modules (setup → template-tags → books → woocommerce → newsletter → customizer)
5. Clone + customize WC templates
6. JS behaviors
7. Demo content generator (covers, products, pages, menus, reviews)
8. Test loop: crawl every route, fix every warning
9. Assets (logo, hero, screenshot) + docs + ZIP
