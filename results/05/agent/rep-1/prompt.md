You are a WordPress developer. Build the plugin described below.

- Write every file under `./output/` (create it). The plugin folder goes directly inside it, e.g. `./output/my-plugin/`.
- Follow WordPress Coding Standards (PHP and JS), the `create-block` file layout for blocks, PHP 7.4+, and current WordPress, Gutenberg and React APIs.
- Use WordPress components (`@wordpress/*`) for all editor UI, functional React components only.
- Escape output, sanitize input, check capabilities and nonces where they apply.
- Write source files only. Do not run `npm install`, builds or any shell commands. Include a `package.json` with `@wordpress/scripts` so it could be built.
- Check documentation where you are not sure of an API. Do not guess signatures.
- When finished, reply with a short list of the files you wrote.

---

Build a plugin called "Editorial Checklist" that adds a pre-publish checklist to the block editor.

- Checklist items come from the server. Each has a label and is either required or optional. Other plugins must be able to add or change items from PHP. Only users who can edit posts may load them.
- A sidebar shows the items as checkboxes with a progress indicator.
- When the author clicks Publish, the pre-publish step lists any unfinished required items.
- While any required item is unchecked, the post can't be published or saved, and an editor notice explains why. Checking the last required item lifts the block and removes the notice.
- Which items are checked is saved per post and restored when the post is reopened.
- Other editor plugins must be able to read the checklist's progress from JavaScript.
