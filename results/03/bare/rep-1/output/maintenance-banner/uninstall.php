<?php
/**
 * Uninstall routine: remove plugin data.
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	$maintenance_banner_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $maintenance_banner_site_ids as $maintenance_banner_site_id ) {
		switch_to_blog( $maintenance_banner_site_id );
		delete_option( 'maintenance_banner_options' );
		restore_current_blog();
	}
} else {
	delete_option( 'maintenance_banner_options' );
}
