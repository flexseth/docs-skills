<?php
/**
 * Plugin Name:       Event RSVP
 * Description:       An RSVP form block with an admin list, CSV export and a Block Bindings source for the RSVP count.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Event RSVP
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       event-rsvp
 *
 * @package EventRSVP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EVENT_RSVP_VERSION', '1.0.0' );
define( 'EVENT_RSVP_DB_VERSION', '1.0.0' );
define( 'EVENT_RSVP_FILE', __FILE__ );
define( 'EVENT_RSVP_DIR', plugin_dir_path( __FILE__ ) );
define( 'EVENT_RSVP_MAX_GUESTS', 5 );

require_once EVENT_RSVP_DIR . 'includes/class-event-rsvp-db.php';
require_once EVENT_RSVP_DIR . 'includes/class-event-rsvp-ajax.php';
require_once EVENT_RSVP_DIR . 'includes/class-event-rsvp-bindings.php';

if ( is_admin() ) {
	require_once EVENT_RSVP_DIR . 'includes/class-event-rsvp-admin.php';
}

register_activation_hook( __FILE__, array( 'Event_RSVP_DB', 'install' ) );

/**
 * Registers the block and attaches the AJAX config to its view script.
 *
 * @return void
 */
function event_rsvp_register_block() {
	$block_type = register_block_type( EVENT_RSVP_DIR . 'build/form' );

	if ( ! $block_type instanceof WP_Block_Type || empty( $block_type->view_script_handles ) ) {
		return;
	}

	$config = array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'action'  => 'event_rsvp_submit',
		'nonce'   => wp_create_nonce( 'event_rsvp_submit' ),
		'i18n'    => array(
			'sending' => __( 'Sending…', 'event-rsvp' ),
			'error'   => __( 'Something went wrong. Please try again.', 'event-rsvp' ),
		),
	);

	foreach ( $block_type->view_script_handles as $handle ) {
		wp_add_inline_script(
			$handle,
			'window.eventRsvp = ' . wp_json_encode( $config ) . ';',
			'before'
		);
	}
}
add_action( 'init', 'event_rsvp_register_block' );

/**
 * Upgrades the table schema when the stored DB version is outdated.
 *
 * @return void
 */
function event_rsvp_maybe_upgrade() {
	if ( get_option( 'event_rsvp_db_version' ) !== EVENT_RSVP_DB_VERSION ) {
		Event_RSVP_DB::install();
	}
}
add_action( 'plugins_loaded', 'event_rsvp_maybe_upgrade' );

Event_RSVP_Ajax::init();
Event_RSVP_Bindings::init();
