=== Recipe Scaler ===
Contributors:      recipescaler
Tags:              block, recipe, ingredients, servings
Requires at least: 6.6
Tested up to:      6.8
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

A recipe block whose ingredient quantities rescale when visitors change the servings.

== Description ==

Adds a "Recipe" block. Authors enter a title, base servings (block sidebar) and a list of
ingredients (quantity, unit, name). On the front end, visitors use the − and + buttons to
change servings and every quantity rescales instantly, rounded to two decimals.

Built with the WordPress Interactivity API and server-side rendered, so the correct
quantities are shown before JavaScript loads. Each block scales independently.

== Installation ==

1. Run `npm install` and `npm run build` in the plugin folder.
2. Upload the plugin to `/wp-content/plugins/` and activate it.

== Changelog ==

= 1.0.0 =
* Initial release.
