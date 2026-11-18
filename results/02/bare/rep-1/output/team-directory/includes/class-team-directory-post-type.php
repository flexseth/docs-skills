<?php
/**
 * Team member post type and meta registration.
 *
 * @package TeamDirectory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the team member content type.
 */
class Team_Directory_Post_Type {

	/**
	 * Hooks into WordPress.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
	}

	/**
	 * Registers the post type.
	 *
	 * @return void
	 */
	public static function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Team Members', 'post type general name', 'team-directory' ),
			'singular_name'         => _x( 'Team Member', 'post type singular name', 'team-directory' ),
			'menu_name'             => _x( 'Team', 'admin menu', 'team-directory' ),
			'add_new'               => __( 'Add New Member', 'team-directory' ),
			'add_new_item'          => __( 'Add New Team Member', 'team-directory' ),
			'edit_item'             => __( 'Edit Team Member', 'team-directory' ),
			'new_item'              => __( 'New Team Member', 'team-directory' ),
			'view_item'             => __( 'View Team Member', 'team-directory' ),
			'all_items'             => __( 'All Team Members', 'team-directory' ),
			'search_items'          => __( 'Search Team Members', 'team-directory' ),
			'not_found'             => __( 'No team members found.', 'team-directory' ),
			'not_found_in_trash'    => __( 'No team members found in Trash.', 'team-directory' ),
			'featured_image'        => __( 'Photo', 'team-directory' ),
			'set_featured_image'    => __( 'Set photo', 'team-directory' ),
			'remove_featured_image' => __( 'Remove photo', 'team-directory' ),
			'use_featured_image'    => __( 'Use as photo', 'team-directory' ),
		);

		register_post_type(
			Team_Directory_Members::POST_TYPE,
			array(
				'labels'       => $labels,
				'public'       => true,
				'has_archive'  => false,
				'show_in_rest' => true,
				'rest_base'    => 'team-members',
				'menu_icon'    => 'dashicons-groups',
				'rewrite'      => array( 'slug' => 'team' ),
				'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes', 'revisions' ),
				'template'     => array(
					array(
						'core/paragraph',
						array( 'placeholder' => __( 'Write a short bio…', 'team-directory' ) ),
					),
				),
			)
		);
	}

	/**
	 * Registers role and start year meta so the editor can save them.
	 *
	 * @return void
	 */
	public static function register_meta() {
		register_post_meta(
			Team_Directory_Members::POST_TYPE,
			Team_Directory_Members::META_ROLE,
			array(
				'type'              => 'string',
				'description'       => __( 'Role of the team member.', 'team-directory' ),
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => array( __CLASS__, 'sanitize_role' ),
				'auth_callback'     => array( __CLASS__, 'can_edit_meta' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'      => 'string',
						'maxLength' => Team_Directory_Members::ROLE_MAX_LENGTH,
					),
				),
			)
		);

		register_post_meta(
			Team_Directory_Members::POST_TYPE,
			Team_Directory_Members::META_START_YEAR,
			array(
				'type'              => 'integer',
				'description'       => __( 'Year the team member started.', 'team-directory' ),
				'single'            => true,
				'default'           => 0,
				'sanitize_callback' => array( __CLASS__, 'sanitize_start_year' ),
				'auth_callback'     => array( __CLASS__, 'can_edit_meta' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'    => 'integer',
						'minimum' => 0,
						'maximum' => 9999,
					),
				),
			)
		);
	}

	/**
	 * Sanitizes a role value.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_role( $value ) {
		$value = sanitize_text_field( (string) $value );

		return mb_substr( $value, 0, Team_Directory_Members::ROLE_MAX_LENGTH );
	}

	/**
	 * Sanitizes a start year. Empty or out of range values become 0 (unset).
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_start_year( $value ) {
		$year = absint( $value );
		$max  = (int) gmdate( 'Y' ) + 1;

		if ( $year < 1900 || $year > $max ) {
			return 0;
		}

		return $year;
	}

	/**
	 * Only users who can edit the member may change its meta.
	 *
	 * @param bool   $allowed  Whether the user can add the meta. Unused.
	 * @param string $meta_key Meta key. Unused.
	 * @param int    $post_id  Post ID.
	 * @return bool
	 */
	public static function can_edit_meta( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', (int) $post_id );
	}

	/**
	 * Uses "Name" as the title placeholder for members.
	 *
	 * @param string  $text Placeholder text.
	 * @param WP_Post $post Current post.
	 * @return string
	 */
	public static function title_placeholder( $text, $post ) {
		if ( $post instanceof WP_Post && Team_Directory_Members::POST_TYPE === $post->post_type ) {
			return __( 'Name', 'team-directory' );
		}

		return $text;
	}
}
