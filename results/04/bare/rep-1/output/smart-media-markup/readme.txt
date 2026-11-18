=== Smart Media Markup ===
Contributors: smartmediamarkup
Tags: images, gallery, cover, accessibility, performance
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Improves the front-end HTML of the core Image, Gallery and Cover blocks without changing saved content.

== Description ==

When the Image, Gallery and Cover blocks are rendered for visitors, the plugin:

1. Adds `decoding="async"` to images that don't already set a decoding mode.
2. Adds the class `has-smart-media` to the block's outer wrapper.
3. Adds `rel="noopener"` to links that point to another site, keeping any existing `rel` values.
4. Adds a "Use caption as alt text" toggle to the Image block sidebar. When it is on and the image has no alt text, the caption's plain text is used as the alt text.

All changes use the WordPress HTML API (`WP_HTML_Tag_Processor`) in `render_block_{$name}` filters. Stored post content is never modified.

= Filters =

* `smart_media_markup_block_names` – change the list of processed blocks.
* `smart_media_markup_is_external_url` – override whether a link counts as external.

== Development ==

    npm install
    npm run build

== Changelog ==

= 1.0.0 =
* Initial release.
