=== Block Visibility Rules ===
Contributors: seth
Tags: blocks, visibility, gutenberg
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later

Adds a "Visibility" setting to every block.

== Description ==

Each block that supports custom class names (except Classic) gets a
"Visibility" panel in the block sidebar:

* Everyone
* Logged-in users
* Logged-out visitors
* Date range (start and/or end, in the site timezone)

Restricted blocks get a dashed outline in the editor and aren't rendered on
the front end when the rule doesn't match.

Note: with full-page caching, logged-in/logged-out and date rules apply when
the page is generated, not each time it is viewed.

== Developer hooks ==

JS action, fires when a rule changes:

    wp.hooks.addAction( 'blockVisibilityRules.ruleChanged', 'my-plugin',
        ( newRule, oldRule, clientId, blockName ) => {} );

JS filter, adds rule types:

    wp.hooks.addFilter( 'blockVisibilityRules.ruleTypes', 'my-plugin',
        ( types ) => [ ...types, { label: 'Admins', value: 'admins' } ] );

PHP filter, decides or overrides the result:

    add_filter( 'block_visibility_rules_match', function ( $matches, $rule, $block ) {
        if ( 'admins' === $rule['type'] ) {
            return current_user_can( 'manage_options' );
        }
        return $matches;
    }, 10, 3 );

== Development ==

    npm install
    npm run build
