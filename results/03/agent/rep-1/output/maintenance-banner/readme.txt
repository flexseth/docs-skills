=== Maintenance Banner ===
Contributors:      maintenancebanner
Tags:              maintenance, banner, notice
Requires at least: 6.7
Tested up to:      6.9
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Show a scheduled maintenance banner at the top of every front-end page.

== Description ==

* Settings → Maintenance Banner: enable, message (limited HTML), background colour, start/end time (site timezone) and roles that should not see the banner.
* Block editor sidebar for administrators to toggle the banner and edit the message.
* Option `maintenance_banner_options` is exposed via `/wp/v2/settings`.
* The banner is printed via `wp_body_open`, so the theme must call `wp_body_open()`.

Note: role-based hiding relies on per-user rendering; full-page caches may serve cached output to logged-in users.

== Installation ==

1. Run `npm install && npm run build` in the plugin folder.
2. Activate the plugin and configure it under Settings → Maintenance Banner.

== Changelog ==

= 1.0.0 =
* Initial release.
