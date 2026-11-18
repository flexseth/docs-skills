<?php
/**
 * Public API functions for Editorial Checklist.
 *
 * @package EditorialChecklist
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the post types the checklist is enabled for.
 *
 * @return string[] Post type slugs.
 */
function editorial_checklist_get_post_types() {
	/**
	 * Filters the post types that use the editorial checklist.
	 *
	 * Post types must use the block editor for the sidebar to appear.
	 *
	 * @param string[] $post_types Post type slugs. Default array( 'post', 'page' ).
	 */
	$post_types = apply_filters( 'editorial_checklist_post_types', array( 'post', 'page' ) );

	$post_types = array_map( 'sanitize_key', (array) $post_types );

	return array_values( array_unique( array_filter( $post_types, 'post_type_exists' ) ) );
}

/**
 * Whether the checklist is enabled for a post type.
 *
 * @param string $post_type Post type slug.
 * @return bool
 */
function editorial_checklist_is_enabled_for( $post_type ) {
	return is_string( $post_type ) && in_array( $post_type, editorial_checklist_get_post_types(), true );
}

/**
 * Default checklist items, keyed by item ID.
 *
 * @return array<string, array{label: string, required: bool}>
 */
function editorial_checklist_default_items() {
	return array(
		'headline-reviewed' => array(
			'label'    => __( 'Headline has been reviewed', 'editorial-checklist' ),
			'required' => true,
		),
		'copy-proofread'    => array(
			'label'    => __( 'Copy has been proofread', 'editorial-checklist' ),
			'required' => true,
		),
		'links-checked'     => array(
			'label'    => __( 'All links have been checked', 'editorial-checklist' ),
			'required' => true,
		),
		'featured-image'    => array(
			'label'    => __( 'Featured image is set', 'editorial-checklist' ),
			'required' => false,
		),
		'taxonomy-assigned' => array(
			'label'    => __( 'Categories and tags are assigned', 'editorial-checklist' ),
			'required' => false,
		),
	);
}

/**
 * Returns the normalized checklist items for a post type.
 *
 * Other plugins add, change or remove items with the `editorial_checklist_items` filter.
 *
 * @param string $post_type Optional. Post type slug. Default empty string.
 * @return array<int, array{id: string, label: string, required: bool}>
 */
function editorial_checklist_get_items( $post_type = '' ) {
	/**
	 * Filters the editorial checklist items.
	 *
	 * Items are keyed by a unique ID. Each item is an array with:
	 * - `label`    (string) Text shown next to the checkbox. Required.
	 * - `required` (bool)   Whether the item must be checked before publishing. Default false.
	 *
	 * Example:
	 *
	 *     add_filter( 'editorial_checklist_items', function ( $items, $post_type ) {
	 *         $items['seo-description'] = array(
	 *             'label'    => __( 'SEO description written', 'my-plugin' ),
	 *             'required' => true,
	 *         );
	 *         $items['featured-image']['required'] = true;
	 *         unset( $items['links-checked'] );
	 *         return $items;
	 *     }, 10, 2 );
	 *
	 * @param array  $items     Checklist items keyed by ID.
	 * @param string $post_type Post type the checklist is for. May be empty.
	 */
	$items = apply_filters( 'editorial_checklist_items', editorial_checklist_default_items(), (string) $post_type );

	$normalized = array();

	foreach ( (array) $items as $key => $item ) {
		if ( is_string( $item ) ) {
			$item = array( 'label' => $item );
		}

		if ( ! is_array( $item ) ) {
			continue;
		}

		$id    = isset( $item['id'] ) ? $item['id'] : $key;
		$id    = is_scalar( $id ) ? sanitize_key( (string) $id ) : '';
		$label = isset( $item['label'] ) && is_scalar( $item['label'] ) ? trim( wp_strip_all_tags( (string) $item['label'] ) ) : '';

		if ( '' === $id || '' === $label || isset( $normalized[ $id ] ) ) {
			continue;
		}

		$normalized[ $id ] = array(
			'id'       => $id,
			'label'    => $label,
			'required' => ! empty( $item['required'] ),
		);
	}

	return array_values( $normalized );
}

/**
 * Returns the IDs of the checked items stored for a post.
 *
 * @param int $post_id Post ID.
 * @return string[] Checked item IDs.
 */
function editorial_checklist_get_checked( $post_id ) {
	$value = get_post_meta( (int) $post_id, EDITORIAL_CHECKLIST_META_KEY, true );

	return editorial_checklist_sanitize_checked( $value );
}

/**
 * Sanitizes a list of checked item IDs.
 *
 * @param mixed $value Raw value.
 * @return string[] Unique, sanitized item IDs.
 */
function editorial_checklist_sanitize_checked( $value ) {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$ids = array();
	foreach ( $value as $id ) {
		if ( is_scalar( $id ) ) {
			$id = sanitize_key( (string) $id );
			if ( '' !== $id ) {
				$ids[] = $id;
			}
		}
	}

	return array_values( array_unique( $ids ) );
}

/**
 * Stores the checked item IDs for a post.
 *
 * IDs that do not match a current checklist item are discarded.
 *
 * @param int      $post_id Post ID.
 * @param string[] $checked Checked item IDs.
 * @return string[] The IDs that were stored.
 */
function editorial_checklist_set_checked( $post_id, $checked ) {
	$post_id = (int) $post_id;
	$valid   = wp_list_pluck( editorial_checklist_get_items( (string) get_post_type( $post_id ) ), 'id' );
	$checked = array_values( array_intersect( editorial_checklist_sanitize_checked( $checked ), $valid ) );

	update_post_meta( $post_id, EDITORIAL_CHECKLIST_META_KEY, $checked );

	/**
	 * Fires after the checklist state of a post has been saved.
	 *
	 * @param int      $post_id Post ID.
	 * @param string[] $checked Checked item IDs.
	 */
	do_action( 'editorial_checklist_saved', $post_id, $checked );

	return $checked;
}

/**
 * Returns the required items that are not yet checked for a post.
 *
 * @param int    $post_id   Post ID. Pass 0 for a post that does not exist yet.
 * @param string $post_type Optional. Post type. Defaults to the post's type.
 * @return array<int, array{id: string, label: string, required: bool}>
 */
function editorial_checklist_get_missing_required( $post_id, $post_type = '' ) {
	$post_id = (int) $post_id;

	if ( '' === $post_type && $post_id ) {
		$post_type = (string) get_post_type( $post_id );
	}

	$checked = $post_id ? editorial_checklist_get_checked( $post_id ) : array();
	$missing = array();

	foreach ( editorial_checklist_get_items( $post_type ) as $item ) {
		if ( $item['required'] && ! in_array( $item['id'], $checked, true ) ) {
			$missing[] = $item;
		}
	}

	return $missing;
}
