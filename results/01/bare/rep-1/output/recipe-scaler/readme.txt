=== Recipe Scaler ===
Contributors:      seth
Tags:              block, recipe, interactivity
Requires at least: 6.6
Tested up to:      6.8
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

A recipe block whose ingredient quantities rescale live when visitors change the number of servings.

== Description ==

Adds the "Recipe Scaler" block. Enter a title, a base number of servings and a list of ingredients (quantity, unit, name).
On the front end, visitors use the − and + buttons to change the servings and every quantity rescales instantly
(rounded to two decimals) via the WordPress Interactivity API. Quantities may be decimals ("1.5") or fractions
("1/2", "1 1/2"); non-numeric quantities such as "a pinch" are shown unchanged.

== Installation ==

1. Run `npm install` and `npm run build` in the plugin folder.
2. Upload the plugin to `/wp-content/plugins/recipe-scaler` and activate it.

== Changelog ==

= 1.0.0 =
* Initial release.
