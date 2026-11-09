=== Team Directory ===
Contributors: teamdirectory
Tags: team, staff, directory, block
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A team member post type with REST routes, a directory block and an editor panel.

== Description ==

* `team_member` post type with `role` (string) and `start_year` (integer) meta, available in REST.
* `GET /wp-json/team-directory/v1/members` with `role`, `page` and `per_page` (max 50) args. Sends `X-WP-Total` and `X-WP-TotalPages` headers.
* `POST /wp-json/team-directory/v1/members/<id>/role` with a `role` arg. Needs the `edit_post` capability on that post. Cookie-authenticated requests must send a `X-WP-Nonce` header (`wp_rest`); `apiFetch` adds it automatically.
* `GET /wp-json/team-directory/v1/roles` returns the distinct roles in use, for the block's filter.
* "Team Directory" block (`team-directory/list`), rendered on the server.
* "Member details" panel in the document settings sidebar on team member posts.

== Development ==

Run `npm install`, then `npm run build`.
