=== Maintenance Banner ===
Contributors: maintenancebanner
Tags: banner, maintenance, notice
Requires at least: 6.7
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A scheduled, site-wide banner shown at the top of every front-end page.

== Description ==

* Turn the banner on or off and write a message (bold, italic, links allowed).
* Choose a background colour; text colour is picked automatically for contrast.
* Optional start and end date/time, in the site's timezone.
* Hide the banner from selected user roles.
* Settings > Maintenance Banner page, a panel in the block editor (users with `manage_options` only),
  and REST access through `/wp/v2/settings` (`maintenance_banner` field, partial updates supported).
* Uninstalling removes the stored option.

The banner is printed on the `wp_body_open` hook, which all modern themes support.

== Development ==

    npm install
    npm run build
