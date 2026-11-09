=== Smart Media Markup ===
Contributors: smartmediamarkup
Tags: images, gallery, cover, performance, accessibility
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Improves the front-end HTML of core Image, Gallery and Cover blocks without changing saved content.

== Description ==

On render (via `render_block_core/image`, `render_block_core/gallery` and `render_block_core/cover`), using the WordPress HTML API:

* Adds `decoding="async"` to every `<img>` that lacks a `decoding` attribute.
* Image block option "Use caption as alt text": when the image's `alt` is empty, the plain text of its `<figcaption>` is used as `alt`.
* Adds the `has-smart-media` class to the block's outer `<figure>` (Image and Gallery; the Cover block's wrapper is a `<div>` and is left alone).
* Adds `rel="noopener"` to links pointing at another host, preserving existing `rel` values.

Post content in the database is never modified.

= Filters =

* `smart_media_markup_site_host` — host treated as internal (default: host of `home_url()`).
* `smart_media_markup_is_external_link` — override the external-link decision per URL.

== Development ==

    npm install
    npm run build

Tests: `phpunit` with the standard WordPress test suite (`WP_TESTS_DIR`).
