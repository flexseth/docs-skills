<?php
/**
 * Checklist item registry, post meta storage and publish enforcement.
 *
 * @package EditorialChecklist
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the built-in checklist items, keyed by item ID.
 *
 * @return array<string, array{label: string, required: bool, description?: string}>
 */
function editorial_checklist_default_items() {
	return array(
		'headline'       => array(
			'label'    => __( 'Headline is clear and accurate', 'editorial-checklist' ),
			'required' => true,
		),
		'proofread'      => array(
			'label'    => __( 'Copy has been proofread', 'editorial-checklist' ),
			'required' => true,
		),
		'links'          => array(
			'label'    => __( 'All links have been checked', 'editorial-checklist' ),
			'required' => true,
		),
		'featured-image' => array(
			'label'    => __( 'Featured image is set and has alt text', 'editorial-checklist' ),
			'required' => false,
		),
		'taxonomy'       => array(
			'label'    => __( 'Categories and tags are assigned', 'editorial-checklist' ),
			'required' => false,
		),
		'excerpt'        => array(
			'label'    => __( 'Excerpt has been written', 'editorial-checklist' ),
			'required' => false,
		),
	);
}

/**
 * Returns the post types the checklist is active on.
 *
 * @return string[]
 */
function editorial_checklist_get_post_types() {
	/**
	 * Filters the post types that use the editorial checklist.
	 *
	 * @param string[] $post_types Post type slugs. Default `post` and `page`.
	 */
	$post_types = apply_filters( 'editorial_checklist_post_types', array( 'post', 'page' ) );

	return array_values( array_filter( array_map( 'sanitize_key', (array) $post_types ) ) );
}

/**
 * Whether the checklist is active on a post type.
 *
 * @param string $post_type Post type slug.
 * @return bool
 */
function editorial_checklist_is_supported_post_type( $post_type ) {
	return is_string( $post_type ) && in_array( $post_type, editorial_checklist_get_post_types(), true );
}

/**
 * Returns the normalized checklist items.
 *
 * Other plugins add, change or remove items with the `editorial_checklist_items` filter:
 *
 *     add_filter( 'editorial_checklist_items', function ( $items, $post_type ) {
 *         $items['seo-title'] = array( 'label' => 'SEO title set', 'required' => true );
 *         $items['excerpt']['required'] = true;
 *         unset( $items['taxonomy'] );
 *         return $items;
 *     }, 10, 2 );
 *
 * @param string $post_type Optional. Post type the items are for.
 * @return array<int, array{id: string, label: string, required: bool, description: string}>
 */
function editorial_checklist_get_items( $post_type = '' ) {
	/**
	 * Filters the checklist items.
	 *
	 * @param array  $items     Items keyed by ID. Each item has `label` (string), `required` (bool)
	 *                          and optional `description` (string). An explicit `id` overrides the key.
	 * @param string $post_type Post type the items are requested for. May be empty.
	 */
	$raw_items = apply_filters( 'editorial_checklist_items', editorial_checklist_default_items(), (string) $post_type );

	if ( ! is_array( $raw_items ) ) {
		return array();
	}

	$items = array();
	foreach ( $raw_items as $key => $item ) {
		if ( ! is_array( $item ) || empty( $item['label'] ) ) {
			continue;
		}

		$label = sanitize_text_field( (string) $item['label'] );
		if ( isset( $item['id'] ) ) {
			$id = sanitize_key( (string) $item['id'] );
		} elseif ( is_string( $key ) ) {
			$id = sanitize_key( $key );
		} else {
			$id = sanitize_key( sanitize_title( $label ) );
		}

		if ( '' === $id || '' === $label || isset( $items[ $id ] ) ) {
			continue;
		}

		$items[ $id ] = array(
			'id'          => $id,
			'label'       => $label,
			'required'    => ! empty( $item['required'] ),
			'description' => isset( $item['description'] ) ? sanitize_text_field( (string) $item['description'] ) : '',
		);
	}

	return array_values( $items );
}

/**
 * Sanitizes a list of checked item IDs.
 *
 * @param mixed $value Raw value.
 * @return string[]
 */
function editorial_checklist_sanitize_checked( $value ) {
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
 * Registers the post meta that stores checked item IDs.
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
				'default'           => array(),
				'show_in_rest'      => false,
				'sanitize_callback' => 'editorial_checklist_sanitize_checked',
				'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}
}

/**
 * Returns the checked item IDs for a post, limited to items that currently exist.
 *
 * @param int $post_id Post ID.
 * @return string[]
 */
function editorial_checklist_get_checked( $post_id ) {
	$stored = editorial_checklist_sanitize_checked( get_post_meta( $post_id, EDITORIAL_CHECKLIST_META_KEY, true ) );
	$known  = wp_list_pluck( editorial_checklist_get_items( (string) get_post_type( $post_id ) ), 'id' );

	return array_values( array_intersect( $stored, $known ) );
}

/**
 * Saves the checked item IDs for a post. Unknown IDs are discarded.
 *
 * @param int   $post_id Post ID.
 * @param array $checked Checked item IDs.
 * @return string[] The stored IDs.
 */
function editorial_checklist_set_checked( $post_id, $checked ) {
	$known   = wp_list_pluck( editorial_checklist_get_items( (string) get_post_type( $post_id ) ), 'id' );
	$checked = array_values( array_intersect( editorial_checklist_sanitize_checked( $checked ), $known ) );

	update_post_meta( $post_id, EDITORIAL_CHECKLIST_META_KEY, $checked );

	/**
	 * Fires after the checklist state of a post is saved.
	 *
	 * @param int      $post_id Post ID.
	 * @param string[] $checked Checked item IDs.
	 */
	do_action( 'editorial_checklist_updated', $post_id, $checked );

	return $checked;
}

/**
 * Returns the required items that are not checked for a post.
 *
 * @param int    $post_id   Post ID. 0 for a post that does not exist yet.
 * @param string $post_type Optional. Post type, used when the post does not exist yet.
 * @return array[] Unchecked required items.
 */
function editorial_checklist_get_missing_required( $post_id, $post_type = '' ) {
	if ( '' === $post_type ) {
		$post_type = (string) get_post_type( $post_id );
	}

	$checked = $post_id ? editorial_checklist_get_checked( $post_id ) : array();

	return array_values(
		array_filter(
			editorial_checklist_get_items( $post_type ),
			static function ( $item ) use ( $checked ) {
				return $item['required'] && ! in_array( $item['id'], $checked, true );
			}
		)
	);
}

/**
 * Hooks server-side publish enforcement into the REST API for each supported post type.
 *
 * @return void
 */
function editorial_checklist_register_publish_enforcement() {
	foreach ( editorial_checklist_get_post_types() as $post_type ) {
		add_filter( "rest_pre_insert_{$post_type}", 'editorial_checklist_enforce_on_publish', 10, 2 );
	}
}

/**
 * Rejects REST requests that would publish a post with unchecked required items.
 *
 * This backs up the editor-side lock so the rule can't be bypassed by calling the API directly.
 *
 * @param stdClass|WP_Error $prepared_post Post prepared for the database.
 * @param WP_REST_Request   $request       Request object.
 * @return stdClass|WP_Error
 */
function editorial_checklist_enforce_on_publish( $prepared_post, $request ) {
	if ( is_wp_error( $prepared_post ) ) {
		return $prepared_post;
	}

	$post_id        = isset( $prepared_post->ID ) ? (int) $prepared_post->ID : 0;
	$current_status = $post_id ? get_post_status( $post_id ) : false;
	$new_status     = isset( $prepared_post->post_status ) ? $prepared_post->post_status : $current_status;
	$publishing     = array( 'publish', 'future' );

	// Only act on the transition into a published state.
	if ( ! in_array( $new_status, $publishing, true ) || in_array( $current_status, $publishing, true ) ) {
		return $prepared_post;
	}

	$post_type = isset( $prepared_post->post_type ) ? $prepared_post->post_type : (string) get_post_type( $post_id );
	$missing   = editorial_checklist_get_missing_required( $post_id, $post_type );

	/**
	 * Filters whether publishing is blocked on the server while required items are unchecked.
	 *
	 * @param bool            $enforce Whether to block. Default true when items are missing.
	 * @param int             $post_id Post ID, 0 for new posts.
	 * @param array[]         $missing Unchecked required items.
	 * @param WP_REST_Request $request Request object.
	 */
	if ( empty( $missing ) || ! apply_filters( 'editorial_checklist_enforce_on_publish', true, $post_id, $missing, $request ) ) {
		return $prepared_post;
	}

	return new WP_Error(
		'editorial_checklist_incomplete',
		sprintf(
			/* translators: %s: comma-separated list of checklist item labels. */
			__( 'This post cannot be published until the required editorial checklist items are complete: %s', 'editorial-checklist' ),
			implode( ', ', wp_list_pluck( $missing, 'label' ) )
		),
		array(
			'status'  => 400,
			'missing' => wp_list_pluck( $missing, 'id' ),
		)
	);
}
