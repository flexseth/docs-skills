You are a WordPress developer. Build the plugin described below.

- Write every file under `./output/` (create it). The plugin folder goes directly inside it, e.g. `./output/my-plugin/`.
- Follow WordPress Coding Standards (PHP and JS), the `create-block` file layout for blocks, PHP 7.4+, and current WordPress, Gutenberg and React APIs.
- Use WordPress components (`@wordpress/*`) for all editor UI, functional React components only.
- Escape output, sanitize input, check capabilities and nonces where they apply.
- Write source files only. Do not run `npm install`, builds or any shell commands. Include a `package.json` with `@wordpress/scripts` so it could be built.
- Check documentation where you are not sure of an API. Do not guess signatures.
- When finished, reply with a short list of the files you wrote.

---

Build a plugin called "Block Visibility Rules". It adds a Visibility setting to every existing block (except the Classic block). Don't register any new blocks.

In each block's sidebar, the author chooses who sees the block:
- everyone
- logged-in visitors only
- logged-out visitors only
- only between a start and end date and time (in the site's timezone)

In the editor, blocks with a rule get a dashed outline. On the front end, a block whose rule doesn't match isn't output at all. Hiding it with CSS isn't enough.

Other plugins must be able to:
- add their own rule types to the editor's choices
- run their own JavaScript when a block's rule changes
- override the front-end show/hide decision from PHP
