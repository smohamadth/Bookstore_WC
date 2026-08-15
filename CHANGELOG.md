# Changelog

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

### Security and privacy

- Added newsletter consent, throttling, unsubscribe, CSV management, and
  WordPress privacy exporter/eraser support.
- Made the demo importer opt-in for site-wide settings, ownership-aware,
  media-idempotent and non-destructive to existing menus, widgets and blocks.

### Changed

- Added configurable merchant policy, payment and social messages; unverified
  store promises are hidden by default.
- Added the optional Inkwell Books companion plugin for portable data features.
- Consolidated twenty duplicate font files into six variable-font subsets.
- Added licenses, notices, root theme screenshot, generated translations,
  static regressions, release tooling and a PHP 7.4–8.4 CI matrix.
