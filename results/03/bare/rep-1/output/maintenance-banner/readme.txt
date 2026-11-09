=== Maintenance Banner ===
Contributors: seth
Tags: maintenance, banner, notice
Requires at least: 6.7
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows a scheduled maintenance banner at the top of the front end.

== Description ==

* Settings → Maintenance Banner: enable, message (limited HTML), background colour, start/end time and roles that should not see the banner.
* The `maintenance_banner_options` option is exposed via `/wp/v2/settings` with a full schema.
* A block editor sidebar lets administrators toggle the banner and edit the message without leaving the editor.
* The banner is printed on `wp_body_open`, so the theme must call `wp_body_open()`.

== Development ==

    npm install
    npm run build

== Changelog ==

= 1.0.0 =
* Initial release.
