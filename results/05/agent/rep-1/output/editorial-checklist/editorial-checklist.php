<?php
/**
 * Plugin Name:       Editorial Checklist
 * Description:       Adds a pre-publish editorial checklist to the block editor and locks publishing until required items are done.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Editorial Checklist Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       editorial-checklist
 *
 * @package EditorialChecklist
 */

defined( 'ABSPATH' ) || exit;

define( 'EDITORIAL_CHECKLIST_META_KEY', '_editorial_checklist' );

/**
 * Returns the post types the checklist applies to.
 *
 * @return string[]
 */
function editorial_checklist_post_types() {
	/**
	 * Filters the post types that use the editorial checklist.
	 *
	 * @param string[] $post_types Post type slugs.
	 */
	$post_types = apply_filters( 'editorial_checklist_post_types', array( 'post', 'page' ) );

	return array_values( array_filter( array_map( 'sanitize_key', (array) $post_types ), 'post_type_exists' ) );
}

/**
 * Returns the sanitized checklist item definitions.
 *
 * @return array[] List of items with `id`, `label` and `required` keys.
 */
function editorial_checklist_get_items() {
	$defaults = array(
		array(
			'id'       => 'title-reviewed',
			'label'    => __( 'Headline reviewed and approved', 'editorial-checklist' ),
			'required' => true,
		),
		array(
			'id'       => 'featured-image',
			'label'    => __( 'Featured image set with alt text', 'editorial-checklist' ),
			'required' => true,
		),
		array(
			'id'       => 'links-checked',
			'label'    => __( 'All links checked', 'editorial-checklist' ),
			'required' => true,
		),
		array(
			'id'       => 'categories-tags',
			'label'    => __( 'Categories and tags assigned', 'editorial-checklist' ),
			'required' => false,
		),
		array(
			'id'       => 'excerpt',
			'label'    => __( 'Excerpt written', 'editorial-checklist' ),
			'required' => false,
		),
	);

	/**
	 * Filters the editorial checklist items.
	 *
	 * Each item is an array with:
	 * - `id`       (string) Unique key.
	 * - `label`    (string) Human-readable label.
	 * - `required` (bool)   Whether the item blocks publishing.
	 *
	 * @param array[] $items Checklist item definitions.
	 */
	$items = apply_filters( 'editorial_checklist_items', $defaults );

	$sanitized = array();
	$seen      = array();

	foreach ( (array) $items as $item ) {
		if ( ! is_array( $item ) || empty( $item['id'] ) || empty( $item['label'] ) ) {
			continue;
		}

		$id = sanitize_key( $item['id'] );

		if ( '' === $id || isset( $seen[ $id ] ) ) {
			continue;
		}

		$seen[ $id ] = true;
		$sanitized[] = array(
			'id'       => $id,
			'label'    => sanitize_text_field( $item['label'] ),
			'required' => ! empty( $item['required'] ),
		);
	}

	return $sanitized;
}

/**
 * Sanitizes the stored list of completed item IDs.
 *
 * @param mixed $value Raw meta value.
 * @return string[]
 */
function editorial_checklist_sanitize_meta( $value ) {
	if ( ! is_array( $value ) ) {
		return array();
	}

	return array_values( array_unique( array_filter( array_map( 'sanitize_key', $value ) ) ) );
}

/**
 * Registers the post meta used to persist done-state.
 */
function editorial_checklist_register_meta() {
	foreach ( editorial_checklist_post_types() as $post_type ) {
		// Meta is only exposed in the REST API for post types supporting custom fields.
		if ( ! post_type_supports( $post_type, 'custom-fields' ) ) {
			add_post_type_support( $post_type, 'custom-fields' );
		}

		register_post_meta(
			$post_type,
			EDITORIAL_CHECKLIST_META_KEY,
			array(
				'type'              => 'array',
				'description'       => __( 'IDs of completed editorial checklist items.', 'editorial-checklist' ),
				'single'            => true,
				'default'           => array(),
				'sanitize_callback' => 'editorial_checklist_sanitize_meta',
				'auth_callback'     => static function ( $allowed, $meta_key, $object_id ) {
					return current_user_can( 'edit_post', (int) $object_id );
				},
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
						),
					),
				),
			)
		);
	}
}
add_action( 'init', 'editorial_checklist_register_meta' );

/**
 * Registers the REST route that serves the checklist item definitions.
 */
function editorial_checklist_register_routes() {
	register_rest_route(
		'editorial-checklist/v1',
		'/items',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function () {
				return rest_ensure_response( editorial_checklist_get_items() );
			},
			'permission_callback' => static function () {
				return current_user_can( 'edit_posts' );
			},
			'schema'              => static function () {
				return array(
					'$schema' => 'http://json-schema.org/draft-04/schema#',
					'title'   => 'editorial-checklist-items',
					'type'    => 'array',
					'items'   => array(
						'type'       => 'object',
						'properties' => array(
							'id'       => array( 'type' => 'string' ),
							'label'    => array( 'type' => 'string' ),
							'required' => array( 'type' => 'boolean' ),
						),
					),
				);
			},
		)
	);
}
add_action( 'rest_api_init', 'editorial_checklist_register_routes' );

/**
 * Enqueues the editor script on supported post types.
 */
function editorial_checklist_enqueue_editor_assets() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || ! in_array( $screen->post_type, editorial_checklist_post_types(), true ) ) {
		return;
	}

	$asset_file = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = include $asset_file;

	wp_enqueue_script(
		'editorial-checklist',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_set_script_translations( 'editorial-checklist', 'editorial-checklist' );

	if ( file_exists( plugin_dir_path( __FILE__ ) . 'build/index.css' ) ) {
		wp_enqueue_style(
			'editorial-checklist',
			plugins_url( 'build/index.css', __FILE__ ),
			array(),
			$asset['version']
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'editorial_checklist_enqueue_editor_assets' );
