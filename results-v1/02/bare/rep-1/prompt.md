You are a WordPress developer. Build the plugin described below.

- Write every file under `./output/` (create it). The plugin folder goes directly inside it, e.g. `./output/my-plugin/`.
- Follow WordPress Coding Standards (PHP and JS), the `create-block` file layout for blocks, PHP 7.4+, and current WordPress, Gutenberg and React APIs.
- Use WordPress components (`@wordpress/*`) for all editor UI, functional React components only.
- Escape output, sanitize input, check capabilities and nonces where they apply.
- Write source files only. Do not run `npm install`, builds or any shell commands. Include a `package.json` with `@wordpress/scripts` so it could be built.
- Check documentation where you are not sure of an API. Do not guess signatures.
- When finished, reply with a short list of the files you wrote.

---

Build a plugin called "Team Directory".

Register a custom post type `team_member` (shown in REST) with post meta `role` (string) and `start_year` (integer), both registered with `register_post_meta()` so they are exposed in REST and editable in the editor.

Add a custom REST route `team-directory/v1/members` (GET) that returns members filtered by an optional `role` query arg and paginated with `page` and `per_page` (max 50). Define the route's `args` with types, sanitization and validation callbacks, give the response a JSON schema, set `X-WP-Total` and `X-WP-TotalPages` headers, and use a `permission_callback` that allows public reads. Add a second route `team-directory/v1/members/(?P<id>\d+)/role` (POST) that updates a member's role and requires the `edit_post` capability for that post.

Add a block `team-directory/list` whose editor fetches members with `apiFetch` from the custom route, offers a SelectControl to filter by role, and shows a Spinner while loading. The front end is server-rendered in `render.php`.

Add a document settings panel (`PluginDocumentSettingPanel`) on `team_member` posts that edits `role` and `start_year` through `useEntityProp`.
