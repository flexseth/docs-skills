=== Event RSVP ===
Contributors: eventrsvp
Tags: rsvp, events, form, block
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An RSVP form block with an admin list, CSV export and a block bindings source for the RSVP count.

== Description ==

* **Event RSVP Form** block (`event-rsvp/form`): name, email and number of guests (1–5), submitted via admin-ajax.php with nonce protection.
* **Tools → Event RSVPs**: list of submissions with bulk delete and a "Download CSV" button.
* **Block Bindings source** `event-rsvp/count`: the number of RSVPs for the current post.

= Binding a Paragraph to the RSVP count =

In the Code editor, add:

    <!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"event-rsvp/count"}}}} -->
    <p></p>
    <!-- /wp:paragraph -->

To show the total number of guests instead, pass `"args":{"key":"guests"}` in the binding.

== Installation ==

1. Run `npm install && npm run build` in the plugin folder.
2. Upload the folder to `wp-content/plugins/` and activate it. The table is created on activation.

== Changelog ==

= 1.0.0 =
* Initial release.
