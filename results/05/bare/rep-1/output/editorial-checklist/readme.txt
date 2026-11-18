=== Editorial Checklist ===
Contributors:      editorialchecklist
Tags:              editorial, checklist, publishing, workflow
Requires at least: 6.7
Tested up to:      6.9
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Adds a pre-publish editorial checklist to the block editor.

== Description ==

* A sidebar ("Editorial Checklist") shows every item as a checkbox, with a progress bar.
* The pre-publish panel lists unfinished required items, and you can check them off there.
* While any required item is unchecked, saving and publishing are locked and an editor notice explains why.
* Checked items are saved per post (post meta `_editorial_checklist_checked`) as soon as they change.
* The server also refuses REST requests that would publish a post with unchecked required items.

== Build ==

    npm install
    npm run build

== Developers ==

= Add or change items (PHP) =

    add_filter( 'editorial_checklist_items', function ( $items, $post_type ) {
        $items['seo-title'] = array(
            'label'       => __( 'SEO title is set', 'my-plugin' ),
            'required'    => true,
            'description' => __( 'Check the snippet preview.', 'my-plugin' ),
        );
        $items['excerpt']['required'] = true; // Make a built-in item required.
        unset( $items['taxonomy'] );           // Remove a built-in item.
        return $items;
    }, 10, 2 );

Other filters:

* `editorial_checklist_post_types` – post types the checklist runs on (default `post`, `page`).
* `editorial_checklist_enforce_on_publish` – return false to turn off the server-side publish check.
* Action `editorial_checklist_updated( $post_id, $checked )` – fires after a post's checklist state is saved.

= Read progress (JavaScript) =

    const progress = wp.data.select( 'editorial-checklist' ).getProgress();
    // { total, checked, required, requiredChecked, percent, isComplete, isReady }

Other selectors: `getItems()`, `getCheckedItemIds()`, `isItemChecked( id )`,
`getRequiredItems()`, `getUncheckedRequiredItems()`, `isComplete()`, `isReady()`.

Or subscribe to changes:

    wp.hooks.addAction( 'editorialChecklist.progressChanged', 'my-plugin', ( progress ) => {} );

= REST API =

* `GET  /wp-json/editorial-checklist/v1/items?post_type=post` – needs `edit_posts`.
* `GET  /wp-json/editorial-checklist/v1/posts/<id>` – needs `edit_post` for that post.
* `POST /wp-json/editorial-checklist/v1/posts/<id>` with `{ "checked": [ "id", ... ] }` – needs `edit_post`.
