You are a WordPress developer. Build the plugin described below.

- Write every file under `./output/` (create it). The plugin folder goes directly inside it, e.g. `./output/my-plugin/`.
- Follow WordPress Coding Standards (PHP and JS), the `create-block` file layout for blocks, PHP 7.4+, and current WordPress, Gutenberg and React APIs.
- Use WordPress components (`@wordpress/*`) for all editor UI, functional React components only.
- Escape output, sanitize input, check capabilities and nonces where they apply.
- Write source files only. Do not run `npm install`, builds or any shell commands. Include a `package.json` with `@wordpress/scripts` so it could be built.
- Check documentation where you are not sure of an API. Do not guess signatures.
- When finished, reply with a short list of the files you wrote.

---

Build a plugin called "Maintenance Banner".

Add a settings page under Settings → Maintenance Banner built with the Settings API: `register_setting()` for a single array option `maintenance_banner_options` with a `sanitize_callback` and a `default`, `add_settings_section()`, and `add_settings_field()` for: enabled (checkbox), message (textarea, limited HTML via `wp_kses`), background colour (hex), start and end datetime, and which user roles should NOT see the banner (multi-checkbox built from `wp_roles()`). Show settings errors with `add_settings_error()` when the end time is before the start time.

Also register the option with `show_in_rest` and a full schema, so it can be read and saved through `/wp/v2/settings`.

Add a block-editor sidebar (`registerPlugin` + `PluginSidebar`) that lets admins toggle `enabled` and edit `message` without leaving the editor, reading and saving through `useEntityRecord( 'root', 'site' )` / `saveEditedEntityRecord`. Only show it to users with `manage_options` (check via `useSelect` on the core store `canUser`).

On the front end, print the banner with `wp_body_open` when enabled, inside the time window and not hidden for the user's role. Add an uninstall routine that deletes the option.
