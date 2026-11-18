<?php
/**
 * Shared data access for team members, used by the REST API and the block.
 *
 * @package TeamDirectory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Queries and formats team members.
 */
class Team_Directory_Members {

	/**
	 * Post type slug.
	 */
	const POST_TYPE = 'tdir_member';

	/**
	 * Meta key for the member role.
	 */
	const META_ROLE = '_tdir_role';

	/**
	 * Meta key for the member start year.
	 */
	const META_START_YEAR = '_tdir_start_year';

	/**
	 * Maximum number of members per page.
	 */
	const MAX_PER_PAGE = 50;

	/**
	 * Default number of members per page.
	 */
	const DEFAULT_PER_PAGE = 10;

	/**
	 * Maximum length of a role.
	 */
	const ROLE_MAX_LENGTH = 100;

	/**
	 * Runs a query for published team members.
	 *
	 * @param array $args {
	 *     Query arguments.
	 *
	 *     @type int    $page     Page number, 1-based.
	 *     @type int    $per_page Members per page (1-50).
	 *     @type string $role     Optional role to filter by.
	 * }
	 * @return WP_Query
	 */
	public static function query( array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'page'     => 1,
				'per_page' => self::DEFAULT_PER_PAGE,
				'role'     => '',
			)
		);

		$per_page = min( max( 1, (int) $args['per_page'] ), self::MAX_PER_PAGE );
		$page     = max( 1, (int) $args['page'] );
		$role     = sanitize_text_field( (string) $args['role'] );

		$query_args = array(
			'post_type'           => self::POST_TYPE,
			'post_status'         => 'publish',
			'posts_per_page'      => $per_page,
			'paged'               => $page,
			'orderby'             => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'ignore_sticky_posts' => true,
		);

		if ( '' !== $role ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Filtering by role is a core feature.
			$query_args['meta_query'] = array(
				array(
					'key'     => self::META_ROLE,
					'value'   => $role,
					'compare' => '=',
				),
			);
		}

		return new WP_Query( $query_args );
	}

	/**
	 * Returns the distinct roles used by published members.
	 *
	 * @return string[]
	 */
	public static function get_roles() {
		global $wpdb;

		$cache_key = 'roles:' . wp_cache_get_last_changed( 'posts' );
		$roles     = wp_cache_get( $cache_key, 'team_directory' );

		if ( false === $roles ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- No core API for distinct meta values; cached below.
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
					self::META_ROLE,
					self::POST_TYPE
				)
			);
			wp_cache_set( $cache_key, $roles, 'team_directory' );
		}

		return array_values( array_map( 'strval', (array) $roles ) );
	}

	/**
	 * Converts a member post into a plain array.
	 *
	 * @param WP_Post $post Member post.
	 * @return array
	 */
	public static function prepare( WP_Post $post ) {
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

		$start_year = (int) get_post_meta( $post->ID, self::META_START_YEAR, true );

		return array(
			'id'         => $post->ID,
			'name'       => html_entity_decode( get_the_title( $post ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'bio'        => self::get_bio( $post ),
			'role'       => (string) get_post_meta( $post->ID, self::META_ROLE, true ),
			'start_year' => $start_year > 0 ? $start_year : null,
			'photo'      => $photo,
			'link'       => get_permalink( $post ),
		);
	}

	/**
	 * Returns the member bio as plain text.
	 *
	 * Only basic text blocks are rendered, which prevents recursion if a
	 * member bio contains a Team List block.
	 *
	 * @param WP_Post $post Member post.
	 * @return string
	 */
	public static function get_bio( WP_Post $post ) {
		$content = excerpt_remove_blocks( strip_shortcodes( $post->post_content ) );
		$content = wp_strip_all_tags( $content );
		$content = html_entity_decode( $content, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$content = preg_replace( "/\n{3,}/", "\n\n", str_replace( "\r", '', $content ) );

		return trim( (string) $content );
	}
}
