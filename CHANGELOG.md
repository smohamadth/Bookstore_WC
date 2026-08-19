# Changelog

## 2.0.8 — 2026-08-19

### Changed

- Replaced the retired real-book demo catalog with 33 entirely fictional Sorani Kurdish books by 27 fictional Kurdish authors.
- Added original Kurdish descriptions, author bios, publishers, category copy, metadata and 14 explicitly fictional Kurdish reviews.
- Regenerated every demo cover with correctly shaped Sorani typography using bundled OFL cover-generation fonts.
- Added a guarded migration that deletes only products proven to belong to the retired Inkwell demo catalog.
- Updated catalog tests, documentation, translation templates and release metadata to 2.0.8.

## 2.0.7 — 2026-08-17

### Changed

- Added a prominent catalog search to the home hero and refined section-heading hierarchy.
- Redesigned the shop with an editorial discovery header, product search and scrollable genre chips.
- Added an accessible mobile filter drawer with focus management, Escape handling and RTL motion.
- Refined product-card alignment, hover treatment, shop toolbar and genre-tile details.
- Updated all runtime, package and translation release metadata to 2.0.7.

## 2.0.6 — 2026-08-17

### Changed

- Upgraded Persian typography to Estedad headings with Vazirmatn body text.
- Upgraded Sorani typography to Noto Kufi Arabic headings with Vazirmatn body text.
- Replaced the compact language pills with a professional dropdown showing
  native names, language codes, active state and a clear globe affordance.
- Added keyboard focus, arrow-key navigation, Escape/outside-click handling,
  mobile presentation and a no-JavaScript language-list fallback.
- Centralized the theme/runtime/package/translation release version at 2.0.6.

## 2.0.5 — 2026-08-15

### Fixed

- Removed WooCommerce clearfix pseudo-elements from the Inkwell CSS Grid.
- Removed the conflicting Genres-menu pseudo-element.
- Restored valid, linked and extension-friendly product/category loop markup.
- Corrected product/author search scoping and SQL grouping.
- Preserved WooCommerce quantity rules and readonly controls.
- Corrected no-products action handling and WooCommerce template metadata.
- Fixed responsive author grids, duplicate form/menu IDs, dark-header styling,
  hero background images and mini-cart event/focus behavior.
- Reduced the single-product gallery proportionally without cropping and moved
  author context before upsells and related recommendations.
- Created a functional My Account shortcode page, enabled demo customer
  registration, and added a one-time repair for earlier admin and WP-CLI demo imports.
- Restored an optimized root-level `screenshot.png` so WordPress reliably shows
  the theme preview card in Appearance → Themes.

### Security and privacy

- Added newsletter consent, throttling, unsubscribe, CSV management, and
  WordPress privacy exporter/eraser support.
- Made the demo importer opt-in for site-wide settings, ownership-aware,
  media-idempotent and non-destructive to existing menus, widgets and blocks.

### Localization

- Added complete Persian (`fa_IR`) and Sorani Kurdish (`ckb`) theme and
  companion-plugin language packs, including compiled MO catalogs.
- Added **Appearance → Inkwell Languages**, an administrator setup notice, and
  a visible visitor language switcher with Polylang/WPML support.
- Added a bundled-catalog fallback when core packs are unavailable and localized
  importer-owned menus, widgets, policy text and product-category chrome.
- Bundled Vazirmatn and Noto Naskh Arabic variable fonts, improved mixed LTR/RTL
  commerce values and icons, removed Latin-style capitalization/letter spacing,
  and documented interface-only and full multilingual WooCommerce setup.

### Changed

- Added configurable merchant policy, payment and social messages; unverified
  store promises are hidden by default.
- Added the optional Inkwell Books companion plugin for portable data features.
- Consolidated twenty duplicate font files into six variable-font subsets and
  removed an unused legacy hero image.
- Preserved category-title extension hooks and made homepage product queries
  request-cached with only the likely LCP cover loaded eagerly.
- Made pages and journal content part of explicit full demo setup, tightened
  legacy product ownership detection, and bounded local newsletter storage.
- Added a matching companion-plugin text domain, plugin translation template,
  WooCommerce dependency metadata, RTL support, deterministic ZIP metadata,
  licenses, notices, an optimized root screenshot, static regressions and
  release tooling.
