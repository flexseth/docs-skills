<?php
/**
 * WordPress hook registrations for Editorial Checklist.
 *
 * @package EditorialChecklist
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loads translations.
 *
 * @return void
 */
function editorial_checklist_load_textdomain() {
	load_plugin_textdomain( 'editorial-checklist', false, dirname( plugin_basename( EDITORIAL_CHECKLIST_FILE ) ) . '/languages' );
}
add_action( 'init', 'editorial_checklist_load_textdomain' );

/**
 * Registers the post meta that stores checked items.
 *
 * The meta is protected (underscore prefix) and not exposed through the core
 * posts endpoint; it is read and written through the plugin's own REST routes
 * so progress is saved even while post saving is locked.
 *
 * @return void
 */
function editorial_checklist_register_meta() {
	foreach ( editorial_checklist_get_post_types() as $post_type ) {
		register_post_meta(
			$post_type,
			EDITORIAL_CHECKLIST_META_KEY,
			array(
				'type'              => 'array',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => 'editorial_checklist_sanitize_checked',
				'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}
}
add_action( 'init', 'editorial_checklist_register_meta', 20 );

/**
 * Registers REST routes and server-side publish enforcement.
 *
 * @return void
 */
function editorial_checklist_rest_api_init() {
	$controller = new Editorial_Checklist_REST_Controller();
	$controller->register_routes();

	foreach ( editorial_checklist_get_post_types() as $post_type ) {
		add_filter(
			"rest_pre_insert_{$post_type}",
			static function ( $prepared_post, $request ) use ( $post_type ) {
				return editorial_checklist_enforce_on_rest_insert( $prepared_post, $request, $post_type );
			},
			10,
			2
		);
	}
}
add_action( 'rest_api_init', 'editorial_checklist_rest_api_init' );

/**
 * Rejects REST requests that would publish a post with unchecked required items.
 *
 * This backs up the editor-side save lock so the rule cannot be bypassed by
 * calling the REST API directly.
 *
 * @param stdClass|WP_Error $prepared_post Post object prepared for insertion.
 * @param WP_REST_Request   $request       Request.
 * @param string            $post_type     Post type.
 * @return stdClass|WP_Error
 */
function editorial_checklist_enforce_on_rest_insert( $prepared_post, $request, $post_type ) {
	if ( is_wp_error( $prepared_post ) ) {
		return $prepared_post;
	}

	/**
	 * Filters whether the checklist is enforced on the server for REST saves.
	 *
	 * @param bool            $enforce       Whether to enforce. Default true.
	 * @param stdClass        $prepared_post Post prepared for insertion.
	 * @param WP_REST_Request $request       Request.
	 */
	if ( ! apply_filters( 'editorial_checklist_enforce', true, $prepared_post, $request ) ) {
		return $prepared_post;
	}

	$post_id = isset( $prepared_post->ID ) ? (int) $prepared_post->ID : 0;

	if ( isset( $prepared_post->post_status ) ) {
		$status = $prepared_post->post_status;
	} elseif ( $post_id ) {
		$status = get_post_status( $post_id );
	} else {
		return $prepared_post;
	}

	/**
	 * Filters the post statuses that require a complete checklist.
	 *
	 * @param string[] $statuses Post statuses. Default array( 'publish', 'future' ).
	 */
	$guarded_statuses = (array) apply_filters( 'editorial_checklist_guarded_statuses', array( 'publish', 'future' ) );

	if ( ! in_array( $status, $guarded_statuses, true ) ) {
		return $prepared_post;
	}

	$missing = editorial_checklist_get_missing_required( $post_id, $post_type );

	if ( empty( $missing ) ) {
		return $prepared_post;
	}

	return new WP_Error(
		'editorial_checklist_incomplete',
		sprintf(
			/* translators: %s: Comma-separated list of checklist item labels. */
			__( 'This post cannot be published until the required editorial checklist items are complete: %s.', 'editorial-checklist' ),
			wp_sprintf( '%l', wp_list_pluck( $missing, 'label' ) )
		),
		array(
			'status'  => 403,
			'missing' => wp_list_pluck( $missing, 'id' ),
		)
	);
}

/**
 * Preloads the checklist REST responses so the editor does not wait on extra requests.
 *
 * @param array                   $paths   REST paths to preload.
 * @param WP_Block_Editor_Context $context Block editor context.
 * @return array
 */
function editorial_checklist_preload_paths( $paths, $context ) {
	if ( empty( $context->post ) || ! editorial_checklist_is_enabled_for( $context->post->post_type ) ) {
		return $paths;
	}

	$base    = '/' . EDITORIAL_CHECKLIST_REST_NAMESPACE;
	$paths[] = add_query_arg( 'post_type', $context->post->post_type, $base . '/items' );
	$paths[] = $base . '/posts/' . (int) $context->post->ID;

	return $paths;
}
add_filter( 'block_editor_rest_api_preload_paths', 'editorial_checklist_preload_paths', 10, 2 );

/**
 * Enqueues the editor script and style for enabled post types.
 *
 * @return void
 */
function editorial_checklist_enqueue_editor_assets() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || ! $screen->is_block_editor() || ! editorial_checklist_is_enabled_for( $screen->post_type ) ) {
		return;
	}

	$post_type_object = get_post_type_object( $screen->post_type );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
		return;
	}

	$asset_path = EDITORIAL_CHECKLIST_DIR . 'build/index.asset.php';
	if ( ! file_exists( $asset_path ) ) {
		return;
	}

	$asset = require $asset_path;

	wp_enqueue_script(
		'editorial-checklist-editor',
		EDITORIAL_CHECKLIST_URL . 'build/index.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_set_script_translations( 'editorial-checklist-editor', 'editorial-checklist', EDITORIAL_CHECKLIST_DIR . 'languages' );

	if ( file_exists( EDITORIAL_CHECKLIST_DIR . 'build/index.css' ) ) {
		wp_enqueue_style(
			'editorial-checklist-editor',
			EDITORIAL_CHECKLIST_URL . 'build/index.css',
			array( 'wp-components' ),
			$asset['version']
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'editorial_checklist_enqueue_editor_assets' );
