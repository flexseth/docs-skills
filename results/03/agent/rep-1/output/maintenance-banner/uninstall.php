<?php
/**
 * Removes all plugin data on uninstall.
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids', 'number' => 0 ) ) as $maintenance_banner_site_id ) {
		switch_to_blog( $maintenance_banner_site_id );
		delete_option( 'maintenance_banner_settings' );
		restore_current_blog();
	}
} else {
	delete_option( 'maintenance_banner_settings' );
}
