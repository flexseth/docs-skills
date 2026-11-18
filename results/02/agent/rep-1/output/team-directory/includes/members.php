<?php
/**
 * Shared helpers for querying and formatting team members.
 *
 * Used by both the REST endpoint and the block's server-side render.
 *
 * @package TeamDirectory
 */

namespace Team_Directory;

defined( 'ABSPATH' ) || exit;

/**
 * Query published team members.
 *
 * @param array $args {
 *     Optional. Query arguments.
 *
 *     @type string $role     Exact role to filter by. Empty for all.
 *     @type int    $per_page Items per page (1–50).
 *     @type int    $page     Page number (1-based).
 * }
 * @return \WP_Query
 */
function query_members( array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'role'     => '',
			'per_page' => 10,
			'page'     => 1,
		)
	);

	$per_page = min( MAX_PER_PAGE, max( 1, (int) $args['per_page'] ) );
	$page     = max( 1, (int) $args['page'] );

	$query_args = array(
		'post_type'           => POST_TYPE,
		'post_status'         => 'publish',
		'posts_per_page'      => $per_page,
		'paged'               => $page,
		'orderby'             => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => false,
	);

	$role = sanitize_role( $args['role'] );
	if ( '' !== $role ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Filtering by role is a core feature.
		$query_args['meta_query'] = array(
			array(
				'key'     => ROLE_META,
				'value'   => $role,
				'compare' => '=',
			),
		);
	}

	return new \WP_Query( $query_args );
}

/**
 * Format a team member for output.
 *
 * @param \WP_Post $post Team member post.
 * @return array
 */
function format_member( \WP_Post $post ) {
	$photo    = null;
	$thumb_id = (int) get_post_thumbnail_id( $post );

	if ( $thumb_id ) {
		$src = wp_get_attachment_image_src( $thumb_id, 'medium' );
		if ( $src ) {
			$photo = array(
				'id'     => $thumb_id,
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ),
			);
		}
	}

	$start_year = (int) get_post_meta( $post->ID, START_YEAR_META, true );

	return array(
		'id'         => $post->ID,
		'name'       => html_entity_decode( get_the_title( $post ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
		'bio'        => get_member_bio( $post ),
		'photo'      => $photo,
		'role'       => (string) get_post_meta( $post->ID, ROLE_META, true ),
		'start_year' => $start_year > 0 ? $start_year : null,
		'link'       => get_permalink( $post ),
	);
}

/**
 * Get the plain-text bio of a team member.
 *
 * Uses the manual excerpt when present, otherwise the post content.
 *
 * @param \WP_Post $post Team member post.
 * @return string
 */
function get_member_bio( \WP_Post $post ) {
	if ( has_excerpt( $post ) ) {
		$bio = $post->post_excerpt;
	} else {
		$bio = excerpt_remove_blocks( strip_shortcodes( $post->post_content ) );
	}

	$bio = wp_strip_all_tags( $bio, true );

	return html_entity_decode( trim( $bio ), ENT_QUOTES, get_bloginfo( 'charset' ) );
}

/**
 * Get the distinct roles used by published team members.
 *
 * @return string[]
 */
function get_member_roles() {
	global $wpdb;

	$cache_key = 'roles:' . wp_cache_get_last_changed( 'posts' );
	$roles     = wp_cache_get( $cache_key, 'team_directory' );

	if ( false === $roles ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Distinct meta values have no core API; result is cached.
		$roles = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT pm.meta_value
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = %s
					AND pm.meta_value <> ''
					AND p.post_type = %s
					AND p.post_status = 'publish'
				ORDER BY pm.meta_value ASC",
				ROLE_META,
				POST_TYPE
			)
		);
		wp_cache_set( $cache_key, $roles, 'team_directory' );
	}

	return array_map( 'strval', (array) $roles );
}

/**
 * Bust the roles cache when a member's role changes.
 *
 * @param int    $meta_id   Meta ID.
 * @param int    $object_id Post ID.
 * @param string $meta_key  Meta key.
 * @return void
 */
function bust_roles_cache( $meta_id, $object_id, $meta_key ) {
	if ( ROLE_META === $meta_key ) {
		wp_cache_set_posts_last_changed();
	}
}
add_action( 'added_post_meta', __NAMESPACE__ . '\\bust_roles_cache', 10, 3 );
add_action( 'updated_post_meta', __NAMESPACE__ . '\\bust_roles_cache', 10, 3 );
add_action( 'deleted_post_meta', __NAMESPACE__ . '\\bust_roles_cache', 10, 3 );
