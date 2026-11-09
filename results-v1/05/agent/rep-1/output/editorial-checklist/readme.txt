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
* Pre-publish panel listing incomplete required items.
* Saving and publishing are locked while required items are incomplete.
* Per-post done-state stored in the `_editorial_checklist` post meta.

= Filters =

`editorial_checklist_items` - modify the checklist item definitions:

    add_filter( 'editorial_checklist_items', function ( $items ) {
        $items[] = array(
            'id'       => 'legal-review',
            'label'    => 'Legal review complete',
            'required' => true,
        );
        return $items;
    } );

`editorial_checklist_post_types` - change the post types the checklist applies to (default: post, page).

== Installation ==

1. Run `npm install && npm run build` in the plugin folder.
2. Activate the plugin.
