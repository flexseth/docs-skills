=== Block Visibility Rules ===
Contributors: blockvisibilityrules
Tags: blocks, visibility, gutenberg, conditional, schedule
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a "Visibility" setting to every block.

== Description ==

Every block that supports custom class names (except the Classic block) gets a "Visibility" panel in the block inspector:

* Everyone (default)
* Logged-in users only
* Logged-out visitors only
* Within a date range (site timezone; either end may be left open)

Restricted blocks show a dashed outline in the editor. Hidden blocks are removed on the server, so their markup never reaches the visitor.

Note: logged-in/logged-out and date rules are evaluated per request. Full-page caching can serve a cached version to the wrong audience; exclude affected pages from caching where needed.

= Developer hooks =

JavaScript (`@wordpress/hooks`):

* Filter `blockVisibilityRules.ruleTypes( types, blockName )` – add `{ label, value }` rule types.
* Action `blockVisibilityRules.ruleChanged( newRule, previousRule, { clientId, name } )`.

PHP:

* Filter `block_visibility_rules_match( $matches, $rule, $block, $block_content )` – override the result (and implement custom rule types; unknown types match by default).
* Filter `block_visibility_rules_excluded_blocks( $blocks )` – exclude more block names.

== Installation ==

1. Run `npm install && npm run build` in the plugin folder.
2. Upload the folder to `wp-content/plugins/` and activate it.

== Changelog ==

= 1.0.0 =
* Initial release.
