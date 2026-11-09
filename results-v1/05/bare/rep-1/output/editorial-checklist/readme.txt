=== Editorial Checklist ===
Contributors: editorialchecklist
Tags: editorial, checklist, publishing, block editor
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a pre-publish editorial checklist to the block editor.

== Description ==

* Sidebar with checkboxes and a progress bar.
* Pre-publish panel that lists incomplete required items.
* Saving and publishing are locked while required items are incomplete.
* Each post's done-state is stored in the `_editorial_checklist` post meta.

= Filters =

`editorial_checklist_items` — change the checklist items:

    add_filter( 'editorial_checklist_items', function ( $items ) {
        $items[] = array(
            'id'       => 'seo',
            'label'    => 'SEO title and description set',
            'required' => true,
        );
        return $items;
    } );

`editorial_checklist_post_types` — post types that get the checklist (default: `post`, `page`). Post types must be shown in REST and support `custom-fields`.

== Development ==

    npm install
    npm run build
