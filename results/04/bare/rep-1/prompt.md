You are a WordPress developer. Build the plugin described below.

- Write every file under `./output/` (create it). The plugin folder goes directly inside it, e.g. `./output/my-plugin/`.
- Follow WordPress Coding Standards (PHP and JS), the `create-block` file layout for blocks, PHP 7.4+, and current WordPress, Gutenberg and React APIs.
- Use WordPress components (`@wordpress/*`) for all editor UI, functional React components only.
- Escape output, sanitize input, check capabilities and nonces where they apply.
- Write source files only. Do not run `npm install`, builds or any shell commands. Include a `package.json` with `@wordpress/scripts` so it could be built.
- Check documentation where you are not sure of an API. Do not guess signatures.
- When finished, reply with a short list of the files you wrote.

---

Build a plugin called "Smart Media Markup" that improves the front-end HTML of the core Image, Gallery and Cover blocks. Saved post content must not change; only the HTML sent to visitors.

1. Images load with asynchronous decoding, unless they already set a decoding mode.
2. The block's outer figure gets the class `has-smart-media`.
3. Links inside those blocks that point to another site open safely (no access to the opening page), keeping any `rel` values they already have.
4. The Image block gets a "Use caption as alt text" toggle in its sidebar. When it's on and the image has no alt text, the caption's plain text becomes the alt text on the front end.

The HTML must be changed with a proper HTML parser that ships with WordPress. No regular expressions, no DOMDocument.

Include a `tests/` folder with example input and expected output for each change.
