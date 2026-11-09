You are a WordPress developer. Build the plugin described below.

- Write every file under `./output/` (create it). The plugin folder goes directly inside it, e.g. `./output/my-plugin/`.
- Follow WordPress Coding Standards (PHP and JS), the `create-block` file layout for blocks, PHP 7.4+, and current WordPress, Gutenberg and React APIs.
- Use WordPress components (`@wordpress/*`) for all editor UI, functional React components only.
- Escape output, sanitize input, check capabilities and nonces where they apply.
- Write source files only. Do not run `npm install`, builds or any shell commands. Include a `package.json` with `@wordpress/scripts` so it could be built.
- Check documentation where you are not sure of an API. Do not guess signatures.
- When finished, reply with a short list of the files you wrote.

---

Build a plugin called "Recipe Scaler" with a dynamic block `recipe-scaler/recipe`.

In the editor, the author adds a recipe title, a base number of servings (NumberControl), and a list of ingredients, each with a quantity, unit and name. Editing happens in the block canvas with RichText for the title and in an InspectorControls panel for servings.

On the front end, visitors get "−" and "+" buttons that change the servings, and every ingredient quantity rescales live without a page reload. Use the Interactivity API for this: a store in `view.js`, per-block context for the base servings and ingredients, and directives (`data-wp-interactive`, `data-wp-context`, `data-wp-on--click`, `data-wp-text`, `data-wp-bind`) in `render.php`. Quantities should round to two decimals and the "−" button must be disabled at 1 serving.

Use `block.json` (apiVersion 3) with `viewScriptModule` and `supports.interactivity`. Server-render the initial state with `wp_interactivity_state()` so the page is correct before JavaScript loads.
