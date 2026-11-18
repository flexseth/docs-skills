=== Maintenance Banner ===
Contributors: maintenancebanner
Tags: banner, notice, maintenance, announcement
Requires at least: 6.7
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show a scheduled, site-wide banner at the top of every front-end page.

== Description ==

Configure the banner under Settings > Maintenance Banner:

* Turn it on or off
* Message with basic formatting (bold, italic, links)
* Background colour (text colour is chosen automatically for contrast)
* Start and end date/time, in the site timezone
* User roles that should not see the banner

Administrators can also toggle the banner and edit its message from the "Maintenance Banner" panel in the block editor's document sidebar.

The settings are exposed through the core REST API settings endpoint (`/wp/v2/settings`) as `maintenance_banner_settings`. Requests where the end is before the start are rejected with a 400 error.

The banner is printed on the `wp_body_open` hook, so your theme must call `wp_body_open()` (all block themes and most modern classic themes do).

Deleting the plugin removes its stored option.

== Development ==

Run `npm install` and `npm run build` to build the editor script into `build/`.
