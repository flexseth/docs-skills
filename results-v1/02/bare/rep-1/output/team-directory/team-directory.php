<?php
/**
 * Plugin Name:       Team Directory
 * Description:       A team member post type, REST API routes, a directory block and an editor panel for member details.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Team Directory Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       team-directory
 *
 * @package TeamDirectory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TEAM_DIRECTORY_VERSION', '1.0.0' );
define( 'TEAM_DIRECTORY_PATH', plugin_dir_path( __FILE__ ) );
define( 'TEAM_DIRECTORY_URL', plugin_dir_url( __FILE__ ) );
define( 'TEAM_DIRECTORY_POST_TYPE', 'team_member' );

require_once TEAM_DIRECTORY_PATH . 'includes/class-team-directory-rest-controller.php';

/**
 * Registers the team_member post type and its meta fields.
 *
 * @return void
 */
function team_directory_register_post_type() {
	$labels = array(
		'name'               => _x( 'Team Members', 'post type general name', 'team-directory' ),
		'singular_name'      => _x( 'Team Member', 'post type singular name', 'team-directory' ),
		'menu_name'          => _x( 'Team', 'admin menu', 'team-directory' ),
		'add_new'            => __( 'Add New', 'team-directory' ),
		'add_new_item'       => __( 'Add New Team Member', 'team-directory' ),
		'edit_item'          => __( 'Edit Team Member', 'team-directory' ),
		'new_item'           => __( 'New Team Member', 'team-directory' ),
		'view_item'          => __( 'View Team Member', 'team-directory' ),
		'all_items'          => __( 'All Team Members', 'team-directory' ),
		'search_items'       => __( 'Search Team Members', 'team-directory' ),
		'not_found'          => __( 'No team members found.', 'team-directory' ),
		'not_found_in_trash' => __( 'No team members found in Trash.', 'team-directory' ),
	);

	register_post_type(
		TEAM_DIRECTORY_POST_TYPE,
		array(
			'labels'       => $labels,
			'public'       => true,
			'has_archive'  => true,
			'menu_icon'    => 'dashicons-groups',
			'show_in_rest' => true,
			'rest_base'    => 'team-members',
			'rewrite'      => array( 'slug' => 'team' ),
			// 'custom-fields' is required for registered meta to appear in the REST API.
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		)
	);

	$auth_callback = static function ( $allowed, $meta_key, $object_id ) {
		return current_user_can( 'edit_post', $object_id );
	};

	register_post_meta(
		TEAM_DIRECTORY_POST_TYPE,
		'role',
		array(
			'type'              => 'string',
			'description'       => __( 'The team member role.', 'team-directory' ),
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		TEAM_DIRECTORY_POST_TYPE,
		'start_year',
		array(
			'type'              => 'integer',
			'description'       => __( 'The year the team member started.', 'team-directory' ),
			'single'            => true,
			'default'           => 0,
			'show_in_rest'      => array(
				'schema' => array(
					'type'    => 'integer',
					'minimum' => 0,
					'maximum' => 9999,
				),
			),
			'sanitize_callback' => 'absint',
			'auth_callback'     => $auth_callback,
		)
	);
}
add_action( 'init', 'team_directory_register_post_type' );

/**
 * Registers the directory block from its compiled metadata.
 *
 * @return void
 */
function team_directory_register_blocks() {
	register_block_type( TEAM_DIRECTORY_PATH . 'build/list' );
}
add_action( 'init', 'team_directory_register_blocks' );

/**
 * Registers the custom REST routes.
 *
 * @return void
 */
function team_directory_register_rest_routes() {
	$controller = new Team_Directory_REST_Controller();
	$controller->register_routes();
}
add_action( 'rest_api_init', 'team_directory_register_rest_routes' );

/**
 * Enqueues the document settings panel script on team_member edit screens.
 *
 * @return void
 */
function team_directory_enqueue_editor_assets() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || TEAM_DIRECTORY_POST_TYPE !== $screen->post_type ) {
		return;
	}

	$asset_file = TEAM_DIRECTORY_PATH . 'build/sidebar.asset.php';
	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'team-directory-sidebar',
		TEAM_DIRECTORY_URL . 'build/sidebar.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_set_script_translations( 'team-directory-sidebar', 'team-directory' );
}
add_action( 'enqueue_block_editor_assets', 'team_directory_enqueue_editor_assets' );

/**
 * Flushes rewrite rules on activation so the CPT permalinks work.
 *
 * @return void
 */
function team_directory_activate() {
	team_directory_register_post_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'team_directory_activate' );

/**
 * Flushes rewrite rules on deactivation.
 *
 * @return void
 */
function team_directory_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'team_directory_deactivate' );
