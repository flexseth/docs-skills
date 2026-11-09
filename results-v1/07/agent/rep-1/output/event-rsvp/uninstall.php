<?php
/**
 * Removes plugin data on uninstall.
 *
 * @package EventRSVP
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$event_rsvp_table = $wpdb->prefix . 'event_rsvps';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$event_rsvp_table}" );

delete_option( 'event_rsvp_db_version' );
delete_metadata( 'user', 0, 'event_rsvp_per_page', '', true );
