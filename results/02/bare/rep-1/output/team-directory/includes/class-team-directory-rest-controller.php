<?php
/**
 * REST API controller for team members.
 *
 * Routes (namespace team-directory/v1):
 * - GET            /members             List published members (public, paginated, filterable by role).
 * - POST|PUT|PATCH /members/{id}/role   Change a member's role (requires edit_post on that member).
 * - GET            /roles               List distinct roles in use (public).
 *
 * @package TeamDirectory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Team Directory REST controller.
 */
class Team_Directory_REST_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
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
			'/' . $this->rest_base . '/(?P<id>[\d]+)/role',
			array(
				'args'   => array(
					'id' => array(
						'description'       => __( 'Unique identifier for the team member.', 'team-directory' ),
						'type'              => 'integer',
						'minimum'           => 1,
						'validate_callback' => 'rest_validate_request_arg',
						'sanitize_callback' => 'absint',
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_role' ),
					'permission_callback' => array( $this, 'update_role_permissions_check' ),
					'args'                => array(
						'role' => array(
							'description'       => __( 'New role for the team member.', 'team-directory' ),
							'type'              => 'string',
							'required'          => true,
							'maxLength'         => Team_Directory_Members::ROLE_MAX_LENGTH,
							'validate_callback' => array( $this, 'validate_role' ),
							'sanitize_callback' => 'sanitize_text_field',
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
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * The member list is public.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Lists members.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$page     = (int) $request['page'];
		$per_page = (int) $request['per_page'];

		$query = Team_Directory_Members::query(
			array(
				'page'     => $page,
				'per_page' => $per_page,
				'role'     => (string) $request['role'],
			)
		);

		$total     = (int) $query->found_posts;
		$max_pages = (int) ceil( $total / $per_page );

		if ( $page > $max_pages && $total > 0 ) {
			return new WP_Error(
				'rest_invalid_page_number',
				sprintf(
					/* translators: %d: Total number of pages. */
					__( 'The page number requested is larger than the number of pages available (%d).', 'team-directory' ),
					$max_pages
				),
				array( 'status' => 400 )
			);
		}

		$items = array();
		foreach ( $query->posts as $post ) {
			$response = $this->prepare_item_for_response( $post, $request );
			$items[]  = $this->prepare_response_for_collection( $response );
		}

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $max_pages );

		$base = add_query_arg(
			urlencode_deep( array_filter( $request->get_query_params(), 'is_scalar' ) ),
			rest_url( $this->namespace . '/' . $this->rest_base )
		);

		if ( $page > 1 ) {
			$response->link_header( 'prev', add_query_arg( 'page', $page - 1, $base ) );
		}
		if ( $page < $max_pages ) {
			$response->link_header( 'next', add_query_arg( 'page', $page + 1, $base ) );
		}

		return $response;
	}

	/**
	 * Checks that the member exists and the current user may edit it.
	 *
	 * @param WP_REST_Request $request Request.
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
	 * Updates a member's role.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_role( $request ) {
		$post = $this->get_member( (int) $request['id'] );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$role = Team_Directory_Post_Type::sanitize_role( $request['role'] );

		// update_post_meta() returns false when the value is unchanged, which is not an error.
		update_post_meta( $post->ID, Team_Directory_Members::META_ROLE, $role );

		if ( get_post_meta( $post->ID, Team_Directory_Members::META_ROLE, true ) !== $role ) {
			return new WP_Error(
				'rest_member_update_failed',
				__( 'The role could not be saved.', 'team-directory' ),
				array( 'status' => 500 )
			);
		}

		return $this->prepare_item_for_response( get_post( $post->ID ), $request );
	}

	/**
	 * Validates the role argument.
	 *
	 * @param mixed           $value   Value.
	 * @param WP_REST_Request $request Request.
	 * @param string          $param   Parameter name.
	 * @return true|WP_Error
	 */
	public function validate_role( $value, $request, $param ) {
		$valid = rest_validate_request_arg( $value, $request, $param );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( '' === sanitize_text_field( $value ) ) {
			return new WP_Error(
				'rest_invalid_param',
				/* translators: %s: Parameter name. */
				sprintf( __( '%s must be a non-empty string of plain text.', 'team-directory' ), $param ),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Lists the distinct roles in use.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_roles( $request ) {
		return rest_ensure_response( Team_Directory_Members::get_roles() );
	}

	/**
	 * Prepares a single member for the response.
	 *
	 * @param WP_Post         $item    Member post.
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data     = Team_Directory_Members::prepare( $item );
		$response = rest_ensure_response( $data );

		$response->add_link( 'alternate', $data['link'], array( 'type' => 'text/html' ) );

		/**
		 * Filters a team member returned from the REST API.
		 *
		 * @param WP_REST_Response $response Response.
		 * @param WP_Post          $item     Member post.
		 * @param WP_REST_Request  $request  Request.
		 */
		return apply_filters( 'team_directory_rest_prepare_member', $response, $item, $request );
	}

	/**
	 * Collection parameters.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'page'     => array(
				'description'       => __( 'Current page of the collection.', 'team-directory' ),
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'description'       => __( 'Maximum number of members per page.', 'team-directory' ),
				'type'              => 'integer',
				'default'           => Team_Directory_Members::DEFAULT_PER_PAGE,
				'minimum'           => 1,
				'maximum'           => Team_Directory_Members::MAX_PER_PAGE,
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'absint',
			),
			'role'     => array(
				'description'       => __( 'Only return members with this role.', 'team-directory' ),
				'type'              => 'string',
				'default'           => '',
				'maxLength'         => Team_Directory_Members::ROLE_MAX_LENGTH,
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * Member schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'team-member',
			'type'       => 'object',
			'properties' => array(
				'id'         => array(
					'description' => __( 'Unique identifier for the member.', 'team-directory' ),
					'type'        => 'integer',
					'readonly'    => true,
				),
				'name'       => array(
					'description' => __( 'Name of the member.', 'team-directory' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'bio'        => array(
					'description' => __( 'Plain text biography.', 'team-directory' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'role'       => array(
					'description' => __( 'Role of the member.', 'team-directory' ),
					'type'        => 'string',
				),
				'start_year' => array(
					'description' => __( 'Year the member started, or null.', 'team-directory' ),
					'type'        => array( 'integer', 'null' ),
					'readonly'    => true,
				),
				'photo'      => array(
					'description' => __( 'Photo of the member, or null.', 'team-directory' ),
					'type'        => array( 'object', 'null' ),
					'readonly'    => true,
					'properties'  => array(
						'id'     => array( 'type' => 'integer' ),
						'url'    => array(
							'type'   => 'string',
							'format' => 'uri',
						),
						'width'  => array( 'type' => 'integer' ),
						'height' => array( 'type' => 'integer' ),
						'alt'    => array( 'type' => 'string' ),
					),
				),
				'link'       => array(
					'description' => __( 'URL of the member page.', 'team-directory' ),
					'type'        => 'string',
					'format'      => 'uri',
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $this->schema );
	}

	/**
	 * Gets a member post by ID.
	 *
	 * @param int $id Post ID.
	 * @return WP_Post|WP_Error
	 */
	protected function get_member( $id ) {
		$post = $id > 0 ? get_post( $id ) : null;

		if ( ! $post || Team_Directory_Members::POST_TYPE !== $post->post_type ) {
			return new WP_Error(
				'rest_member_invalid_id',
				__( 'Invalid team member ID.', 'team-directory' ),
				array( 'status' => 404 )
			);
		}

		return $post;
	}
}
