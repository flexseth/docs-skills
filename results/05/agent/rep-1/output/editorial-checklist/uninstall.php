<?php
/**
 * Removes stored checklist state when the plugin is deleted.
 *
 * @package EditorialChecklist
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_post_meta_by_key( '_editorial_checklist_checked' );
