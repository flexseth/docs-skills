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

Create a custom data store `editorial-checklist/store` with `createReduxStore` and `register` from `@wordpress/data`. It holds checklist items (id, label, required, done), with actions to toggle and reset items, selectors for all items, incomplete required items, and progress percentage, and a resolver that loads the item definitions from a REST endpoint `editorial-checklist/v1/items` (registered in PHP, requiring `edit_posts`) using a `controls`/thunk-based fetch with `apiFetch`.

UI:
- A `PluginSidebar` listing items as CheckboxControls with a progress bar.
- A `PluginPrePublishPanel` that shows incomplete required items.
- While any required item is incomplete, lock publishing with `lockPostSaving` / `unlockPostSaving` from the `core/editor` store, and show a notice via the `core/notices` store.

Persist each post's done-state in post meta `_editorial_checklist` (registered with `register_post_meta`, `show_in_rest`, an `auth_callback`, and an array schema) and sync the store with that meta through `useEntityProp`. Items should also be filterable in PHP with an `editorial_checklist_items` filter.
