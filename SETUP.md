# 🔧 Inkwell Setup Guide

## 1. Requirements

| Component | Requirement |
|---|---|
| WordPress | 6.0+ (theme metadata tested through 7.0) |
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

Local records are stored in the non-autoloaded `inkwell_subscribers` option.
For a production marketing list, connect a specialist email provider:

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

## 8. Translation

```bash
npm ci
npm run make-pot
```

Create `inkwell-xx_XX.po`, compile it to `inkwell-xx_XX.mo`, and place both in
`inkwell/languages/`.

## 9. Development and releases

```bash
npm ci
npm test
npm run lint:js
./tools/build-release.sh
```

The build produces and validates `inkwell.zip` and `inkwell-books.zip`.
GitHub Actions syntax-checks PHP on 7.4, 8.1 and 8.4 and runs the release
regression suite.

## 10. Troubleshooting

| Symptom | Resolution |
|---|---|
| Import page says WooCommerce is required | Install and activate WooCommerce first |
| Shop sidebar is empty | Add WooCommerce widgets, or run complete demo setup on an empty sidebar |
| No store-benefit messages appear | Configure truthful messages under Store Benefits & Policies |
| Mini-cart does not update | Confirm WooCommerce frontend scripts are not disabled by an optimization plugin |
| Gallery lacks zoom/slider | Add more than one product image and ensure gallery scripts are enabled |
| Author fields disappear after changing themes | Install and activate the Inkwell Books companion plugin |
| Need block cart/checkout | Keep the existing blocks; the importer will not overwrite them |
