<?php
/**
 * REST controller for the Team Directory routes.
 *
 * @package TeamDirectory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the team-directory/v1 routes.
 */
class Team_Directory_REST_Controller extends WP_REST_Controller {

	/**
	 * Largest allowed per_page value.
	 */
	const MAX_PER_PAGE = 50;

	/**
	 * Longest allowed role string.
	 */
	const MAX_ROLE_LENGTH = 100;

	/**
	 * Sets up the namespace and base.
	 */
	public function __construct() {
		$this->namespace = 'team-directory/v1';
		$this->rest_base = 'members';
	}

	/**
	 * Registers the routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/role',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_role' ),
					'permission_callback' => array( $this, 'update_role_permissions_check' ),
					'args'                => array(
						'id'   => array(
							'description'       => __( 'Unique identifier for the team member.', 'team-directory' ),
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
							'validate_callback' => 'rest_validate_request_arg',
						),
						'role' => array(
							'description'       => __( 'The new role for the team member.', 'team-directory' ),
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => array( $this, 'sanitize_role' ),
							'validate_callback' => array( $this, 'validate_role' ),
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/roles',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_roles' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => array( $this, 'get_roles_schema' ),
			)
		);
	}

	/**
	 * Members are public information, so anyone may read them.
	 *
	 * @param WP_REST_Request $request Full request data.
	 * @return true
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Checks that the member exists and the current user can edit it.
	 *
	 * @param WP_REST_Request $request Full request data.
	 * @return true|WP_Error
	 */
	public function update_role_permissions_check( $request ) {
		$post = $this->get_member( (int) $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return new WP_Error(
				'rest_cannot_edit',
				__( 'Sorry, you are not allowed to edit this team member.', 'team-directory' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Returns a paginated list of published members.
	 *
	 * @param WP_REST_Request $request Full request data.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$page     = (int) $request['page'];
		$per_page = (int) $request['per_page'];
		$query    = self::query_members( (string) $request['role'], $page, $per_page );

		$total       = (int) $query->found_posts;
		$total_pages = (int) $query->max_num_pages;

		if ( $total > 0 && $page > $total_pages ) {
			return new WP_Error(
				'rest_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.', 'team-directory' ),
				array( 'status' => 400 )
			);
		}

		$items = array();
		foreach ( $query->posts as $post ) {
			$data    = $this->prepare_item_for_response( $post, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $total_pages );

		return $response;
	}

	/**
	 * Updates the role of a single member.
	 *
	 * @param WP_REST_Request $request Full request data.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_role( $request ) {
		$post = $this->get_member( (int) $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		update_post_meta( $post->ID, 'role', $request['role'] );
		wp_cache_delete( 'team_directory_roles', 'team-directory' );

		return $this->prepare_item_for_response( get_post( $post->ID ), $request );
	}

	/**
	 * Returns the distinct roles used by published members.
	 *
	 * @param WP_REST_Request $request Full request data.
	 * @return WP_REST_Response
	 */
	public function get_roles( $request ) {
		global $wpdb;

		$cache_key = 'team_directory_roles';
		$roles     = wp_cache_get( $cache_key, 'team-directory' );

		if ( false === $roles ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Distinct meta values are not available through WP_Query; result is cached.
			$roles = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT pm.meta_value
					FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
					WHERE pm.meta_key = %s
					AND p.post_type = %s
					AND p.post_status = 'publish'
					AND pm.meta_value <> ''
					ORDER BY pm.meta_value ASC",
					'role',
					TEAM_DIRECTORY_POST_TYPE
				)
			);
			wp_cache_set( $cache_key, $roles, 'team-directory', MINUTE_IN_SECONDS );
		}

		return rest_ensure_response( array_values( array_map( 'strval', (array) $roles ) ) );
	}

	/**
	 * Runs the member query. Shared by the REST route and the block's render.php.
	 *
	 * @param string $role     Optional role to filter by. Empty for all.
	 * @param int    $page     1-based page number.
	 * @param int    $per_page Items per page, capped at MAX_PER_PAGE.
	 * @return WP_Query
	 */
	public static function query_members( $role = '', $page = 1, $per_page = 10 ) {
		$args = array(
			'post_type'           => TEAM_DIRECTORY_POST_TYPE,
			'post_status'         => 'publish',
			'posts_per_page'      => min( max( 1, (int) $per_page ), self::MAX_PER_PAGE ),
			'paged'               => max( 1, (int) $page ),
			'orderby'             => 'title',
			'order'               => 'ASC',
			'ignore_sticky_posts' => true,
		);

		if ( '' !== $role ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Filtering by role is the purpose of the query.
			$args['meta_query'] = array(
				array(
					'key'   => 'role',
					'value' => $role,
				),
			);
		}

		return new WP_Query( $args );
	}

	/**
	 * Prepares a single member for the response.
	 *
	 * @param WP_Post         $item    Post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array(
			'id'         => (int) $item->ID,
			'name'       => html_entity_decode( get_the_title( $item ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'role'       => (string) get_post_meta( $item->ID, 'role', true ),
			'start_year' => (int) get_post_meta( $item->ID, 'start_year', true ),
			'link'       => get_permalink( $item ),
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		return rest_ensure_response( $data );
	}

	/**
	 * Returns the member JSON schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'team-directory-member',
			'type'       => 'object',
			'properties' => array(
				'id'         => array(
					'description' => __( 'Unique identifier for the team member.', 'team-directory' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed' ),
					'readonly'    => true,
				),
				'name'       => array(
					'description' => __( 'The team member name.', 'team-directory' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed' ),
					'readonly'    => true,
				),
				'role'       => array(
					'description' => __( 'The team member role.', 'team-directory' ),
					'type'        => 'string',
					'maxLength'   => self::MAX_ROLE_LENGTH,
					'context'     => array( 'view', 'embed' ),
				),
				'start_year' => array(
					'description' => __( 'The year the team member started.', 'team-directory' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed' ),
					'readonly'    => true,
				),
				'link'       => array(
					'description' => __( 'URL of the team member page.', 'team-directory' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'embed' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $this->schema );
	}

	/**
	 * Returns the schema for the roles route.
	 *
	 * @return array
	 */
	public function get_roles_schema() {
		return array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'team-directory-roles',
			'type'    => 'array',
			'items'   => array(
				'type' => 'string',
			),
		);
	}

	/**
	 * Returns the query params for the members collection.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context'  => array(
				'description'       => __( 'Scope under which the request is made.', 'team-directory' ),
				'type'              => 'string',
				'enum'              => array( 'view', 'embed' ),
				'default'           => 'view',
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'role'     => array(
				'description'       => __( 'Limit results to members with this role.', 'team-directory' ),
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_role' ),
				'validate_callback' => array( $this, 'validate_role' ),
			),
			'page'     => array(
				'description'       => __( 'Current page of the collection.', 'team-directory' ),
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'per_page' => array(
				'description'       => __( 'Maximum number of items to be returned in result set.', 'team-directory' ),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => self::MAX_PER_PAGE,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);
	}

	/**
	 * Sanitizes a role value.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public function sanitize_role( $value ) {
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Validates a role value.
	 *
	 * @param mixed           $value   Raw value.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param   Parameter name.
	 * @return true|WP_Error
	 */
	public function validate_role( $value, $request, $param ) {
		$valid = rest_validate_request_arg( $value, $request, $param );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( mb_strlen( (string) $value ) > self::MAX_ROLE_LENGTH ) {
			return new WP_Error(
				'rest_invalid_param',
				/* translators: 1: Parameter name, 2: Maximum length. */
				sprintf( __( '%1$s must be at most %2$d characters long.', 'team-directory' ), $param, self::MAX_ROLE_LENGTH ),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Fetches a team member post or returns an error.
	 *
	 * @param int $id Post ID.
	 * @return WP_Post|WP_Error
	 */
	protected function get_member( $id ) {
		$post = $id > 0 ? get_post( $id ) : null;

		if ( ! $post || TEAM_DIRECTORY_POST_TYPE !== $post->post_type ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid team member ID.', 'team-directory' ),
				array( 'status' => 404 )
			);
		}

		return $post;
	}
}
