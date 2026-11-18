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

Site owners manage team members as their own content type in the block editor. Each member has a name, bio, photo, role and start year. Role and start year are edited in the editor's document sidebar and saved with the member.

Other apps need a public JSON endpoint at `/wp-json/team-directory/v1/members` that lists members, can filter by role, and paginates (at most 50 per page). Responses must tell clients the total number of members and pages. A second endpoint lets someone change a single member's role, but only if they are allowed to edit that member. Invalid input must be rejected with a clear error.

Add a block that lists team members. In the editor it shows a live preview loaded from the endpoint, with a role filter and a loading indicator. On the front end, the list is rendered on the server.
