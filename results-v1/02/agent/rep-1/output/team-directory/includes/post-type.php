<?php
/**
 * Post type, meta registration and shared query helper.
 *
 * @package TeamDirectory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the team_member post type.
 */
function team_directory_register_post_type() {
	$labels = array(
		'name'               => _x( 'Team Members', 'post type general name', 'team-directory' ),
		'singular_name'      => _x( 'Team Member', 'post type singular name', 'team-directory' ),
		'add_new_item'       => __( 'Add New Team Member', 'team-directory' ),
		'edit_item'          => __( 'Edit Team Member', 'team-directory' ),
		'new_item'           => __( 'New Team Member', 'team-directory' ),
		'view_item'          => __( 'View Team Member', 'team-directory' ),
		'search_items'       => __( 'Search Team Members', 'team-directory' ),
		'not_found'          => __( 'No team members found.', 'team-directory' ),
		'not_found_in_trash' => __( 'No team members found in Trash.', 'team-directory' ),
		'all_items'          => __( 'All Team Members', 'team-directory' ),
		'menu_name'          => __( 'Team', 'team-directory' ),
	);

	register_post_type(
		'team_member',
		array(
			'labels'       => $labels,
			'public'       => true,
			'show_in_rest' => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'team' ),
			'menu_icon'    => 'dashicons-groups',
			// custom-fields support is required for meta to appear in the REST response.
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		)
	);
}
add_action( 'init', 'team_directory_register_post_type' );

/**
 * Auth callback for the plugin's meta keys.
 *
 * @param bool   $allowed  Whether the user can edit the meta.
 * @param string $meta_key Meta key.
 * @param int    $post_id  Post ID.
 * @return bool
 */
function team_directory_meta_auth( $allowed, $meta_key, $post_id ) {
	return current_user_can( 'edit_post', $post_id );
}

/**
 * Registers the role and start_year post meta.
 */
function team_directory_register_meta() {
	register_post_meta(
		'team_member',
		'role',
		array(
			'type'              => 'string',
			'description'       => __( 'Role or job title of the team member.', 'team-directory' ),
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'team_directory_meta_auth',
		)
	);

	register_post_meta(
		'team_member',
		'start_year',
		array(
			'type'              => 'integer',
			'description'       => __( 'Year the team member started.', 'team-directory' ),
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
			'auth_callback'     => 'team_directory_meta_auth',
		)
	);
}
add_action( 'init', 'team_directory_register_meta' );

/**
 * Queries published team members.
 *
 * @param string $role     Optional role to filter by. Empty for all.
 * @param int    $page     Page number (1-based).
 * @param int    $per_page Items per page.
 * @return WP_Query
 */
function team_directory_query_members( $role = '', $page = 1, $per_page = 10 ) {
	$args = array(
		'post_type'      => 'team_member',
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, min( 50, (int) $per_page ) ),
		'paged'          => max( 1, (int) $page ),
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	if ( '' !== $role ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Filtering by role is the feature.
		$args['meta_query'] = array(
			array(
				'key'   => 'role',
				'value' => $role,
			),
		);
	}

	return new WP_Query( $args );
}
