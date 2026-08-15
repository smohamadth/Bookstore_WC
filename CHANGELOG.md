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
- Reduced the single-product gallery proportionally without cropping and moved
  author context before upsells and related recommendations.

### Security and privacy

- Added newsletter consent, throttling, unsubscribe, CSV management, and
  WordPress privacy exporter/eraser support.
- Made the demo importer opt-in for site-wide settings, ownership-aware,
  media-idempotent and non-destructive to existing menus, widgets and blocks.

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
