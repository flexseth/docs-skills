=== Recipe Scaler ===
Contributors:      wordpressdotorg
Tags:              block, recipe, ingredients, servings
Requires at least: 6.7
Tested up to:      6.8
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

A recipe block whose ingredient quantities rescale when visitors change the number of servings.

== Description ==

Adds a "Recipe" block. Authors enter a title, the base number of servings (in the block sidebar) and a list of ingredients (quantity, unit, name).

On the front end, visitors use the "−" and "+" buttons to change servings. All quantities rescale instantly, rounded to two decimals. Servings cannot go below 1.

Built with the WordPress Interactivity API: quantities are rendered on the server, so they are correct before JavaScript loads, and each recipe block on a page scales independently.

Quantities accept decimals (1.5), fractions (1/2) and mixed numbers (1 1/2). Non-numeric quantities (e.g. "a pinch") are shown as typed and are not scaled.

== Installation ==

1. Run `npm install` and `npm run build` in the plugin folder.
2. Upload the plugin folder to `/wp-content/plugins/` and activate it.

== Changelog ==

= 1.0.0 =
* Initial release.
