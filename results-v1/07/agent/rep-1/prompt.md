You are a WordPress developer. Build the plugin described below.

- Write every file under `./output/` (create it). The plugin folder goes directly inside it, e.g. `./output/my-plugin/`.
- Follow WordPress Coding Standards (PHP and JS), the `create-block` file layout for blocks, PHP 7.4+, and current WordPress, Gutenberg and React APIs.
- Use WordPress components (`@wordpress/*`) for all editor UI, functional React components only.
- Escape output, sanitize input, check capabilities and nonces where they apply.
- Write source files only. Do not run `npm install`, builds or any shell commands. Include a `package.json` with `@wordpress/scripts` so it could be built.
- Check documentation where you are not sure of an API. Do not guess signatures.
- When finished, reply with a short list of the files you wrote.

---

Build a plugin called "Event RSVP".

Add a block `event-rsvp/form` that renders an RSVP form on the front end (name, email, number of guests 1–5). Submitting it must:
- POST to `admin-ajax.php` with action `event_rsvp_submit` for both logged-in and logged-out users (`wp_ajax_` and `wp_ajax_nopriv_`).
- Be protected by a nonce created with `wp_create_nonce()`, passed to the script with `wp_add_inline_script()` (or `wp_localize_script()`), and verified with `check_ajax_referer()`.
- Sanitize and validate every field (`sanitize_text_field`, `sanitize_email`, `is_email`, `absint`), and reply with `wp_send_json_success()` / `wp_send_json_error()`.
- Store RSVPs in a custom table created on activation with `dbDelta()`, inserted with `$wpdb->insert()` and proper formats.

Add an admin page under Tools → Event RSVPs listing submissions with a `WP_List_Table` subclass, with a bulk "delete" action protected by a nonce (`wp_nonce_field` / `check_admin_referer`) and the `manage_options` capability. Add a "Download CSV" button that goes through `admin-post.php` with its own nonce.

Also register a Block Bindings source `event-rsvp/count` with `register_block_bindings_source()` that returns the number of RSVPs for the current post, so a Paragraph block's content can be bound to it.
