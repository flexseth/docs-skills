You are a WordPress developer. Build the plugin described below.

- Write every file under `./output/` (create it). The plugin folder goes directly inside it, e.g. `./output/my-plugin/`.
- Follow WordPress Coding Standards (PHP and JS), the `create-block` file layout for blocks, PHP 7.4+, and current WordPress, Gutenberg and React APIs.
- Use WordPress components (`@wordpress/*`) for all editor UI, functional React components only.
- Escape output, sanitize input, check capabilities and nonces where they apply.
- Write source files only. Do not run `npm install`, builds or any shell commands. Include a `package.json` with `@wordpress/scripts` so it could be built.
- Check documentation where you are not sure of an API. Do not guess signatures.
- When finished, reply with a short list of the files you wrote.

---

Build a plugin called "Recipe Scaler" that adds a recipe block.

In the editor, authors enter a recipe title, a base number of servings, and a list of ingredients (quantity, unit and name for each). The title is edited directly in the block; the servings setting lives in the block's sidebar.

On the front end, visitors see "−" and "+" buttons next to the servings. Changing the servings rescales every ingredient quantity instantly, without reloading the page. Quantities round to two decimals. Servings can't go below 1, and the "−" button is disabled at 1.

The page must show the correct quantities before any JavaScript loads, and two recipe blocks on the same page must scale independently. Use the approach WordPress currently recommends for front-end interactivity in blocks, not jQuery or a hand-written script.
