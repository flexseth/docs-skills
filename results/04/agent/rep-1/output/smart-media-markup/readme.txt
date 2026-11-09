=== Smart Media Markup ===
Contributors: smartmediamarkup
Tags: images, gallery, cover, accessibility, performance
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Improves the front-end HTML of core Image, Gallery and Cover blocks without changing saved content.

== Description ==

On render (`render_block_core/image`, `render_block_core/gallery`, `render_block_core/cover`), using `WP_HTML_Tag_Processor`:

1. Adds `decoding="async"` to every `<img>` that has no `decoding` attribute.
2. Image blocks with "Use caption as alt text" enabled: if the `<img>` alt is empty, the `<figcaption>` text is copied into `alt` (read with `next_token()`/`get_modifiable_text()`, applied via a bookmark).
3. Adds the `has-smart-media` class to the block's outer `<figure>` (for Cover, its outer `<div>` wrapper).
4. Adds `rel="noopener"` to links pointing to another host, keeping existing `rel` values.

Filter `smart_media_markup_is_external_url` to change how links are classified.

== Development ==

`npm install && npm run build` builds `src/index.js` into `build/`.
Tests: `phpunit` with the WordPress test suite (`WP_TESTS_DIR`).
