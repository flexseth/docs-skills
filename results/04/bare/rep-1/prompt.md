You are a WordPress developer. Build the plugin described below.

- Write every file under `./output/` (create it). The plugin folder goes directly inside it, e.g. `./output/my-plugin/`.
- Follow WordPress Coding Standards (PHP and JS), the `create-block` file layout for blocks, PHP 7.4+, and current WordPress, Gutenberg and React APIs.
- Use WordPress components (`@wordpress/*`) for all editor UI, functional React components only.
- Escape output, sanitize input, check capabilities and nonces where they apply.
- Write source files only. Do not run `npm install`, builds or any shell commands. Include a `package.json` with `@wordpress/scripts` so it could be built.
- Check documentation where you are not sure of an API. Do not guess signatures.
- When finished, reply with a short list of the files you wrote.

---

Build a plugin called "Smart Media Markup" that improves the HTML of core blocks on the front end without changing saved content.

Using the `render_block_core/image`, `render_block_core/gallery` and `render_block_core/cover` filters and the WordPress HTML API (`WP_HTML_Tag_Processor`, not regex or DOMDocument):

1. Add `decoding="async"` to every `<img>` that doesn't have it.
2. If an image block has a custom attribute `smartMediaCaptionAsAlt` set to true and the `<img>` has an empty `alt`, copy the text of its `<figcaption>` into `alt`. Use `WP_HTML_Processor` (or bookmarks with the Tag Processor) to read the caption text correctly.
3. Add a CSS class `has-smart-media` to the outer `<figure>`.
4. For external links (`<a href>` to another host) inside those blocks, add `rel="noopener"` while keeping any existing `rel` values.

Register the `smartMediaCaptionAsAlt` boolean attribute on `core/image` on both sides: in PHP with the `register_block_type_args` filter and in JS with the `blocks.registerBlockType` filter. Add a ToggleControl for it in the image block's InspectorControls using `editor.BlockEdit` and `createHigherOrderComponent`.

Include PHPUnit-style example inputs/outputs in a `tests/` folder showing what each transform does.
