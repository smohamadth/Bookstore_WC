# 📖 Inkwell — Boutique Bookstore Theme for WooCommerce

A complete, self-contained bookstore experience on top of WooCommerce.
Install the theme, import the demo content, and you have a real shop with
book-specific features — no page builder, no paid plugins, no bloat.

**Version 2 — "modern library" redesign:** editorial Newsreader serif + emerald
palette, dark split hero with floating book covers, book-spine product cards
with quick-add, ranked bestsellers, mini-cart dropdown, sticky product summary,
"More by this author" strips, dark quote band, two-column checkout.

**Tested against:** WordPress 6.x · WooCommerce **11.0.1** · PHP 8.4 (PHP 7.4+ required)
**License:** GPL-2.0-or-later · **Text domain:** `inkwell`

---

## ✨ Highlights

| Area | What you get |
|---|---|
| 🏠 **Front page** | **Dark split hero** with floating cover collage + staff-picks badge, value props, genre tiles, **ranked Bestsellers (01–08)**, **New arrivals** with "New" chips, dark quote band, testimonials, journal preview, newsletter band — every section toggleable in the Customizer |
| 📚 **Book domain** | `book_author` taxonomy with **author archive pages** & bios · **Book Details** (ISBN-13, publisher, year, pages, format, language) on every product · **"More by this author"** cover strips |
| 🛍️ **Shop** | Sidebar (genres, price filter, top rated), 3-up book grid, book-**spine** covers, −% sale badges, author lines, **quick-add overlay** with AJAX + live header **mini-cart dropdown** |
| 📄 **Product page** | Gallery with zoom/lightbox, **sticky summary**, in-stock chip, trust chips, **Book Details tab**, author box, related books, styled reviews |
| 🛒 **Checkout** | Styled classic cart (2-column) & **two-column checkout** with sticky order review, trust badges, styled account area |
| 📝 **Journal** | Blog with category chips, cards, sidebar, pagination, comments |
| 🎨 **Design** | **"Modern library" v2**: Newsreader + Inter, parchment/ink/emerald/gold palette, book-spine cards, hover micro-interactions, fully responsive, accessible (skip links, ARIA, focus states, reduced-motion support) |
| 🌍 **i18n** | `inkwell.pot` included, all strings translatable |
| ⚡ **Performance** | Vanilla JS (no jQuery dependency), lazy images, no frameworks — fonts are **self-hosted** (zero external requests, works fully offline) |

## 📦 What's in the box

```
inkwell/            ← the theme (also packaged as inkwell.zip)
demo-content/       ← books.json, generated covers, one-command importer
tools/              ← cover generator + import script
docs/ PLAN.md       ← the build plan (architecture & decisions)
```

## 🚀 Quick start

1. **WordPress + WooCommerce** installed (any host).
2. **Appearance → Themes → Add New → Upload Theme** → upload `inkwell.zip` → Activate.
3. **Import the sample catalog in one click** — *no WP-CLI needed*:
   - An **"Import Demo Content"** page appears under **Appearance** in wp-admin, and
   - when your shop is empty you'll see a **"your shop is empty" notice** on the dashboard with an import button.
   
   Click it and in ~5 seconds you have **33 books** (covers, prices, book details),
   **7 genres**, **19 authors** with bios, **14 reviews**, menus, pages, widgets and
   default settings. Safe to re-run — it updates by SKU, never duplicates.

   > Prefer WP-CLI? `WP_CLI="php wp-cli.phar" WP_SITE=/path/to/wordpress ./tools/import-demo.sh`

4. Done — browse your shop.

## 🎨 Customize (Appearance → Customize)

- **Inkwell Colors** — accent color, hover accent, dark header
- **Header & Announcement Bar** — announcement text
- **Hero Section** — headline, subheadline, buttons, background image
- **Home Page Sections** — show/hide every section, pick the 4 genre tiles, set the quote & testimonials
- **Footer** — about text, copyright line

## 🛠️ Extending

- Hooks: `inkwell_newsletter_subscribed` (fires on new subscribers — plug in Mailchimp/Brevo here)
- Shortcode: `[inkwell_newsletter]` for the newsletter form anywhere
- Template overrides live in `woocommerce/` (cloned from WC 11.0.1, zero outdated-template warnings)
- Book meta keys: `_inkwell_isbn`, `_inkwell_publisher`, `_inkwell_year`, `_inkwell_pages`, `_inkwell_format`, `_inkwell_language`

## 🧪 Test results (this build)

- ✅ Every route (home, shop, genres, product, author, cart, checkout, account, journal, post, search, 404) renders with **zero PHP warnings/notices** (`WP_DEBUG` on)
- ✅ End-to-end purchase: AJAX add-to-cart → cart → checkout → **order placed & stored**
- ✅ WooCommerce template scan: **no outdated templates**
- ✅ Author search: “austen” finds *Pride and Prejudice* via the author taxonomy
- ✅ Newsletter AJAX works; invalid nonces rejected (403)
