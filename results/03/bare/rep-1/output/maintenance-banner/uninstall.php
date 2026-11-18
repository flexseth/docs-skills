<?php
/**
 * Removes all plugin data when the plugin is deleted.
 *
 * @package MaintenanceBanner
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$maintenance_banner_option = 'maintenance_banner_settings';

if ( is_multisite() ) {
	$maintenance_banner_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $maintenance_banner_site_ids as $maintenance_banner_site_id ) {
		switch_to_blog( $maintenance_banner_site_id );
		delete_option( $maintenance_banner_option );
		restore_current_blog();
	}
} else {
	delete_option( $maintenance_banner_option );
}
