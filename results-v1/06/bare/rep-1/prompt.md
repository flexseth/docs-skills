You are a WordPress developer. Build the plugin described below.

- Write every file under `./output/` (create it). The plugin folder goes directly inside it, e.g. `./output/my-plugin/`.
- Follow WordPress Coding Standards (PHP and JS), the `create-block` file layout for blocks, PHP 7.4+, and current WordPress, Gutenberg and React APIs.
- Use WordPress components (`@wordpress/*`) for all editor UI, functional React components only.
- Escape output, sanitize input, check capabilities and nonces where they apply.
- Write source files only. Do not run `npm install`, builds or any shell commands. Include a `package.json` with `@wordpress/scripts` so it could be built.
- Check documentation where you are not sure of an API. Do not guess signatures.
- When finished, reply with a short list of the files you wrote.

---

Build a plugin called "Block Visibility Rules" that adds a "Visibility" setting to every block, without registering new blocks.

JavaScript, using `@wordpress/hooks`:
- `blocks.registerBlockType` filter: add an attribute `visibilityRule` (object: `{ type: 'all' | 'logged-in' | 'logged-out' | 'date-range', start, end }`) to all blocks except `core/freeform` and blocks that don't support custom class names.
- `editor.BlockEdit` filter with `createHigherOrderComponent`: add an InspectorControls panel with a RadioControl for the type and DateTimePicker controls for the date range.
- `editor.BlockListBlock` filter: add a class `has-visibility-rule` in the editor so restricted blocks get a dashed outline (provide the editor stylesheet).
- Expose a JS action hook `blockVisibilityRules.ruleChanged` that fires with `doAction` whenever the rule changes, and a JS filter `blockVisibilityRules.ruleTypes` that lets other plugins add rule types.

PHP:
- Register the attribute server-side for all blocks via `register_block_type_args` so it validates.
- Use the `render_block` filter to return an empty string when the rule doesn't match the current request (dates compared in the site timezone with `wp_date()` / `current_datetime()`).
- Provide a PHP filter `block_visibility_rules_match` so the result can be overridden.

Enqueue editor assets with `enqueue_block_editor_assets` using the generated `index.asset.php`.
