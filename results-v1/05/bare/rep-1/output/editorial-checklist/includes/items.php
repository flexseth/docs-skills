<?php
/**
 * Checklist item definitions and post type helpers.
 *
 * @package EditorialChecklist
 */

namespace EditorialChecklist;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default checklist items.
 *
 * @return array[] List of items with `id`, `label` and `required` keys.
 */
function get_default_items() {
	return array(
		array(
			'id'       => 'headline',
			'label'    => __( 'Headline has been proofread', 'editorial-checklist' ),
			'required' => true,
		),
		array(
			'id'       => 'featured-image',
			'label'    => __( 'Featured image is set and credited', 'editorial-checklist' ),
			'required' => true,
		),
		array(
			'id'       => 'links',
			'label'    => __( 'All links have been checked', 'editorial-checklist' ),
			'required' => true,
		),
		array(
			'id'       => 'categories',
			'label'    => __( 'Categories and tags are assigned', 'editorial-checklist' ),
			'required' => false,
		),
		array(
			'id'       => 'excerpt',
			'label'    => __( 'Excerpt has been written', 'editorial-checklist' ),
			'required' => false,
		),
	);
}

/**
 * Returns the filtered, normalized checklist items.
 *
 * Items with a missing/duplicate id or an empty label are dropped.
 *
 * @return array[] List of items with `id` (string), `label` (string) and `required` (bool).
 */
function get_items() {
	/**
	 * Filters the editorial checklist items.
	 *
	 * @param array[] $items Items, each an array with `id`, `label` and optional `required` keys.
	 */
	$items = apply_filters( 'editorial_checklist_items', get_default_items() );

	if ( ! is_array( $items ) ) {
		return array();
	}

	$normalized = array();
	$seen       = array();

	foreach ( $items as $item ) {
		if ( ! is_array( $item ) || empty( $item['id'] ) || empty( $item['label'] ) ) {
			continue;
		}

		$id    = sanitize_key( (string) $item['id'] );
		$label = sanitize_text_field( (string) $item['label'] );

		if ( '' === $id || '' === $label || isset( $seen[ $id ] ) ) {
			continue;
		}

		$seen[ $id ]  = true;
		$normalized[] = array(
			'id'       => $id,
			'label'    => $label,
			'required' => ! empty( $item['required'] ),
		);
	}

	return $normalized;
}

/**
 * Post types the checklist is enabled for.
 *
 * Only post types that are shown in REST and support custom fields are kept,
 * because the block editor can only read/write meta for those.
 *
 * @return string[]
 */
function get_post_types() {
	/**
	 * Filters the post types that get the editorial checklist.
	 *
	 * @param string[] $post_types Post type slugs.
	 */
	$post_types = (array) apply_filters( 'editorial_checklist_post_types', array( 'post', 'page' ) );

	return array_values(
		array_filter(
			array_map( 'sanitize_key', $post_types ),
			static function ( $post_type ) {
				$object = get_post_type_object( $post_type );

				return $object
					&& ! empty( $object->show_in_rest )
					&& post_type_supports( $post_type, 'custom-fields' );
			}
		)
	);
}
