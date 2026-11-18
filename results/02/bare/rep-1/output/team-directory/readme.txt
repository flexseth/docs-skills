=== Team Directory ===
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage team members, expose them via a public REST endpoint, and list them with a block.

== Description ==

* "Team Members" post type: title = name, content = bio, featured image = photo.
* Role and start year are edited in the "Member details" panel of the document sidebar.
* "Team List" block: live preview in the editor, rendered on the server on the front end.

= REST API =

`GET /wp-json/team-directory/v1/members`

* `page` (int, default 1)
* `per_page` (int, 1-50, default 10)
* `role` (string, optional exact role match)

The response is an array of members. Totals are in the `X-WP-Total` and
`X-WP-TotalPages` headers, and `Link` headers point to the previous and next pages.

`POST|PUT|PATCH /wp-json/team-directory/v1/members/{id}/role` with body `{ "role": "Designer" }`

The user must be able to edit that member (`edit_post`). With cookie authentication, send an `X-WP-Nonce` header (`wp_rest` nonce). An empty or missing role returns `400 rest_invalid_param`. An unknown ID returns `404 rest_member_invalid_id`, and a user without permission gets `401`/`403 rest_cannot_edit`.

`GET /wp-json/team-directory/v1/roles` returns the roles currently in use.

== Development ==

    npm install
    npm run build
