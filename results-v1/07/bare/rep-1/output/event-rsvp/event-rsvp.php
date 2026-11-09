<?php
/**
 * Plugin Name:       Event RSVP
 * Description:       An RSVP form block, an admin list of submissions with CSV export, and a block bindings source for the RSVP count.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Event RSVP Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       event-rsvp
 *
 * @package EventRsvp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EVENT_RSVP_VERSION', '1.0.0' );
define( 'EVENT_RSVP_DB_VERSION', '1.0.0' );
define( 'EVENT_RSVP_FILE', __FILE__ );
define( 'EVENT_RSVP_DIR', plugin_dir_path( __FILE__ ) );

require_once EVENT_RSVP_DIR . 'includes/class-event-rsvp-db.php';
require_once EVENT_RSVP_DIR . 'includes/class-event-rsvp-ajax.php';
require_once EVENT_RSVP_DIR . 'includes/class-event-rsvp-bindings.php';

if ( is_admin() ) {
	require_once EVENT_RSVP_DIR . 'includes/class-event-rsvp-admin.php';
}

register_activation_hook( __FILE__, array( 'Event_RSVP_DB', 'install' ) );

/**
 * Runs the table installer if the stored schema version is out of date
 * (e.g. after a plugin update without re-activation).
 */
function event_rsvp_maybe_upgrade() {
	if ( get_option( 'event_rsvp_db_version' ) !== EVENT_RSVP_DB_VERSION ) {
		Event_RSVP_DB::install();
	}
}
add_action( 'plugins_loaded', 'event_rsvp_maybe_upgrade' );

/**
 * Registers the RSVP form block from its built block.json.
 */
function event_rsvp_register_block() {
	register_block_type( EVENT_RSVP_DIR . 'build/form' );
}
add_action( 'init', 'event_rsvp_register_block' );

/**
 * Passes the AJAX URL, nonce and strings to the block's front-end view script.
 *
 * Called from render.php so the data is only printed on pages that contain the
 * block. Runs once per request.
 */
function event_rsvp_add_view_script_data() {
	static $done = false;

	if ( $done ) {
		return;
	}
	$done = true;

	$handle = generate_block_asset_handle( 'event-rsvp/form', 'viewScript' );

	$data = array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'action'  => 'event_rsvp_submit',
		'nonce'   => wp_create_nonce( Event_RSVP_Ajax::NONCE_ACTION ),
		'i18n'    => array(
			'sending' => __( 'Sending…', 'event-rsvp' ),
			'error'   => __( 'Something went wrong. Please try again.', 'event-rsvp' ),
		),
	);

	wp_add_inline_script(
		$handle,
		'window.eventRsvp = ' . wp_json_encode( $data ) . ';',
		'before'
	);
}

Event_RSVP_Ajax::init();
Event_RSVP_Bindings::init();

if ( is_admin() ) {
	Event_RSVP_Admin::init();
}
