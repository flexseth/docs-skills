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

* A sidebar ("Editorial Checklist") lists the checklist items as checkboxes, with a progress bar.
* While any required item is unchecked, saving, autosaving and publishing are locked and a warning notice explains why. Checking the last required item removes the lock and the notice.
* The pre-publish panel lists unfinished required items.
* Checked items are saved per post as soon as they change (in the `_editorial_checklist_checked` post meta), so progress is kept even while post saving is locked.
* The server also rejects REST requests that would publish a post with unchecked required items.

== Building ==

    npm install
    npm run build

== PHP API ==

Add, change or remove items:

    add_filter( 'editorial_checklist_items', function ( $items, $post_type ) {
        $items['seo-description'] = array(
            'label'    => __( 'SEO description written', 'my-plugin' ),
            'required' => true,
        );
        $items['featured-image']['required'] = true;
        unset( $items['links-checked'] );
        return $items;
    }, 10, 2 );

Other filters:

* `editorial_checklist_post_types` – post types that use the checklist (default `post`, `page`).
* `editorial_checklist_enforce` – return false to turn off server-side publish enforcement.
* `editorial_checklist_guarded_statuses` – statuses that need a complete checklist (default `publish`, `future`).

Action: `editorial_checklist_saved( $post_id, $checked )`.

Functions: `editorial_checklist_get_items( $post_type )`, `editorial_checklist_get_checked( $post_id )`, `editorial_checklist_set_checked( $post_id, $ids )`, `editorial_checklist_get_missing_required( $post_id )`.

REST routes (users must be able to edit posts of that type, or edit that post):

* `GET  /wp-json/editorial-checklist/v1/items?post_type=post`
* `GET  /wp-json/editorial-checklist/v1/posts/<id>`
* `POST /wp-json/editorial-checklist/v1/posts/<id>` with `{ "checked": [ "item-id" ] }`

== JavaScript API ==

Read progress from the `editorial-checklist` data store:

    const progress = wp.data.select( 'editorial-checklist' ).getProgress();
    // { isReady, isComplete, total, completed, requiredTotal, requiredCompleted, percent, incompleteRequired }

Other selectors: `getItems()`, `getCheckedIds()`, `isItemChecked( id )`, `getIncompleteRequiredItems()`, `isComplete()`, `isReady()`, `isLoading()`, `isSaving()`, `getError()`.

Subscribe in a component:

    const isComplete = wp.data.useSelect( ( select ) => select( 'editorial-checklist' ).isComplete(), [] );

Or listen to the hook fired on every change:

    wp.hooks.addAction( 'editorialChecklist.progressChanged', 'my-plugin', ( progress ) => {} );

== Changelog ==

= 1.0.0 =
* First release.
