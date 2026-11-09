=== Event RSVP ===
Contributors: eventrsvp
Tags: rsvp, event, form, block
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An RSVP form block with an admin list, CSV export and a Block Bindings source for the RSVP count.

== Description ==

* Block `event-rsvp/form`: name, email and number of guests (1–5), submitted via admin-ajax.php.
* Tools → Event RSVPs: list, search, sort, bulk delete and CSV download.
* Block Bindings source `event-rsvp/count`: shows the RSVP count for the current post.

= Binding a Paragraph to the RSVP count =

Add this in the Code Editor:

`<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"event-rsvp/count"}}}} -->
<p></p>
<!-- /wp:paragraph -->`

Pass `"args":{"postId":123}` to show the count for a specific post.

== Development ==

`npm install && npm run build`
