<?php
/**
 * Post meta registration.
 *
 * @package EditorialChecklist
 */

namespace EditorialChecklist;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the `_editorial_checklist` post meta for each enabled post type.
 *
 * The value is an array of the ids of completed checklist items.
 *
 * Runs late on `init` so post types registered by other code are available.
 */
function register_meta() {
	foreach ( get_post_types() as $post_type ) {
		register_post_meta(
			$post_type,
			EDITORIAL_CHECKLIST_META_KEY,
			array(
				'type'              => 'array',
				'description'       => __( 'IDs of completed editorial checklist items.', 'editorial-checklist' ),
				'single'            => true,
				'default'           => array(),
				'sanitize_callback' => __NAMESPACE__ . '\\sanitize_meta',
				'auth_callback'     => __NAMESPACE__ . '\\auth_meta',
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'    => 'string',
							'pattern' => '^[a-z0-9_\-]+$',
						),
					),
				),
			)
		);
	}
}
add_action( 'init', __NAMESPACE__ . '\\register_meta', 99 );

/**
 * Sanitizes the meta value into a unique list of item ids.
 *
 * @param mixed $value Raw meta value.
 * @return string[]
 */
function sanitize_meta( $value ) {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$ids = array_map(
		static function ( $id ) {
			return is_scalar( $id ) ? sanitize_key( (string) $id ) : '';
		},
		$value
	);

	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Only users who can edit the post may change its checklist state.
 *
 * @param bool   $allowed  Whether the user can edit the meta. Unused.
 * @param string $meta_key Meta key. Unused.
 * @param int    $post_id  Post ID.
 * @return bool
 */
function auth_meta( $allowed, $meta_key, $post_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
	return current_user_can( 'edit_post', (int) $post_id );
}
