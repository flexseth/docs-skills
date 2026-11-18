=== Smart Media Markup ===
Contributors: smartmediamarkup
Tags: images, gallery, cover, performance, accessibility
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Improves the front-end HTML of the core Image, Gallery and Cover blocks.

== Description ==

Only the HTML sent to visitors changes; saved post content is never modified.
All changes use the WordPress HTML API (WP_HTML_Tag_Processor).

* Images get `decoding="async"` unless they already set a decoding mode.
* The block wrapper element (the outer figure; for Cover, its outer div) gets the class `has-smart-media`.
* Links to other sites get `rel="noopener"`, keeping any existing `rel` values.
* Image block: "Use caption as alt text" toggle. When on and the image has no alt text, the caption's plain text is used as alt text.

Filter `smart_media_markup_is_external_url( bool $external, string $url )` changes how links are classified.

== Development ==

`npm install && npm run build` builds the editor script into `build/`.
Tests: `tests/fixtures/` holds input/expected HTML pairs; `tests/test-smart-media-markup.php` runs them with the WordPress PHPUnit test suite.
