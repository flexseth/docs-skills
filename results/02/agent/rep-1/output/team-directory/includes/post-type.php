<?php
/**
 * Team member post type and meta registration.
 *
 * @package TeamDirectory
 */

namespace Team_Directory;

defined( 'ABSPATH' ) || exit;

/**
 * Register the team member post type and its meta fields.
 *
 * Name = post title, bio = post content, photo = featured image.
 *
 * @return void
 */
function register_post_type_and_meta() {
	register_post_type(
		POST_TYPE,
		array(
			'labels'          => array(
				'name'                  => __( 'Team Members', 'team-directory' ),
				'singular_name'         => __( 'Team Member', 'team-directory' ),
				'menu_name'             => __( 'Team Directory', 'team-directory' ),
				'add_new'               => __( 'Add Member', 'team-directory' ),
				'add_new_item'          => __( 'Add New Team Member', 'team-directory' ),
				'edit_item'             => __( 'Edit Team Member', 'team-directory' ),
				'new_item'              => __( 'New Team Member', 'team-directory' ),
				'view_item'             => __( 'View Team Member', 'team-directory' ),
				'search_items'          => __( 'Search Team Members', 'team-directory' ),
				'not_found'             => __( 'No team members found.', 'team-directory' ),
				'not_found_in_trash'    => __( 'No team members found in Trash.', 'team-directory' ),
				'all_items'             => __( 'All Team Members', 'team-directory' ),
				'featured_image'        => __( 'Photo', 'team-directory' ),
				'set_featured_image'    => __( 'Set photo', 'team-directory' ),
				'remove_featured_image' => __( 'Remove photo', 'team-directory' ),
				'use_featured_image'    => __( 'Use as photo', 'team-directory' ),
			),
			'public'          => true,
			'has_archive'     => false,
			'rewrite'         => array( 'slug' => 'team' ),
			'menu_icon'       => 'dashicons-groups',
			'show_in_rest'    => true,
			'rest_base'       => 'team-members',
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
			'template'        => array(
				array(
					'core/paragraph',
					array( 'placeholder' => __( 'Write a short bio…', 'team-directory' ) ),
				),
			),
		)
	);

	$auth_callback = static function ( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	};

	register_post_meta(
		POST_TYPE,
		ROLE_META,
		array(
			'type'              => 'string',
			'description'       => __( 'Role or job title of the team member.', 'team-directory' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_role',
			'auth_callback'     => $auth_callback,
			'show_in_rest'      => array(
				'schema' => array(
					'type'      => 'string',
					'maxLength' => ROLE_MAX_LENGTH,
				),
			),
			'revisions_enabled' => true,
		)
	);

	register_post_meta(
		POST_TYPE,
		START_YEAR_META,
		array(
			'type'              => 'integer',
			'description'       => __( 'Year the team member started. 0 means not set.', 'team-directory' ),
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_start_year',
			'auth_callback'     => $auth_callback,
			'show_in_rest'      => array(
				'schema' => array(
					'type'    => 'integer',
					'minimum' => 0,
					'maximum' => max_start_year(),
				),
			),
			'revisions_enabled' => true,
		)
	);
}

/**
 * Sanitize a role value.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function sanitize_role( $value ) {
	$value = sanitize_text_field( (string) $value );

	return mb_substr( $value, 0, ROLE_MAX_LENGTH );
}

/**
 * Sanitize a start year. Values outside the allowed range become 0 (not set).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function sanitize_start_year( $value ) {
	$year = absint( $value );

	if ( $year < min_start_year() || $year > max_start_year() ) {
		return 0;
	}

	return $year;
}

/**
 * Earliest accepted start year.
 *
 * @return int
 */
function min_start_year() {
	return 1900;
}

/**
 * Latest accepted start year (next year, to allow announced hires).
 *
 * @return int
 */
function max_start_year() {
	return (int) gmdate( 'Y' ) + 1;
}

/**
 * Use "Member name" as the title placeholder for team members.
 *
 * @param string   $text Default placeholder.
 * @param \WP_Post $post Current post.
 * @return string
 */
function title_placeholder( $text, $post ) {
	if ( $post instanceof \WP_Post && POST_TYPE === $post->post_type ) {
		return __( 'Member name', 'team-directory' );
	}

	return $text;
}
