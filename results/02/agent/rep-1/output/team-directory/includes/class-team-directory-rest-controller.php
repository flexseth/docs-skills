<?php
/**
 * REST controller for team-directory/v1.
 *
 * @package TeamDirectory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the members REST routes.
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
							'description'       => __( 'Team member post ID.', 'team-directory' ),
							'type'              => 'integer',
							'required'          => true,
							'minimum'           => 1,
							'sanitize_callback' => 'absint',
							'validate_callback' => 'rest_validate_request_arg',
						),
						'role' => array(
							'description'       => __( 'New role for the team member.', 'team-directory' ),
							'type'              => 'string',
							'required'          => true,
							'maxLength'         => 100,
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => 'rest_validate_request_arg',
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Collection query params.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'role'     => array(
				'description'       => __( 'Limit results to members with this role.', 'team-directory' ),
				'type'              => 'string',
				'default'           => '',
				'maxLength'         => 100,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
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
				'description'       => __( 'Maximum number of items to return.', 'team-directory' ),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 50,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);
	}

	/**
	 * Public read access.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Returns a page of members.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$page     = (int) $request['page'];
		$per_page = (int) $request['per_page'];
		$query    = team_directory_query_members( (string) $request['role'], $page, $per_page );

		$total       = (int) $query->found_posts;
		$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 0;

		if ( $page > 1 && $page > $total_pages ) {
			return new WP_Error(
				'rest_post_invalid_page_number',
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
	 * Checks the user can edit the target post.
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
				'rest_forbidden',
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

		update_post_meta( $post->ID, 'role', $request['role'] );

		return $this->prepare_item_for_response( get_post( $post->ID ), $request );
	}

	/**
	 * Prepares one member for output.
	 *
	 * @param WP_Post         $item    Post object.
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array(
			'id'         => $item->ID,
			'title'      => get_the_title( $item ),
			'link'       => get_permalink( $item ),
			'role'       => (string) get_post_meta( $item->ID, 'role', true ),
			'start_year' => (int) get_post_meta( $item->ID, 'start_year', true ),
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		return rest_ensure_response( $data );
	}

	/**
	 * Item JSON schema.
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
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'title'      => array(
					'description' => __( 'Member name.', 'team-directory' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'link'       => array(
					'description' => __( 'URL to the member.', 'team-directory' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'role'       => array(
					'description' => __( 'Member role.', 'team-directory' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'start_year' => array(
					'description' => __( 'Year the member started.', 'team-directory' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $this->schema );
	}

	/**
	 * Fetches a team_member post or returns an error.
	 *
	 * @param int $id Post ID.
	 * @return WP_Post|WP_Error
	 */
	protected function get_member( $id ) {
		$post = get_post( $id );
		if ( ! $post || 'team_member' !== $post->post_type ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid team member ID.', 'team-directory' ),
				array( 'status' => 404 )
			);
		}
		return $post;
	}
}
