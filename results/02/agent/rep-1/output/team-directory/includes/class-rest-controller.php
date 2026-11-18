<?php
/**
 * REST API endpoints for team members.
 *
 * @package TeamDirectory
 */

namespace Team_Directory;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and handles the team-directory/v1 routes.
 */
class REST_Controller {

	/**
	 * Route namespace.
	 *
	 * @var string
	 */
	const ROUTE_NAMESPACE = 'team-directory/v1';

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'/members',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_members' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_args(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			'/members/(?P<id>\d+)/role',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_role' ),
					'permission_callback' => array( $this, 'update_role_permissions_check' ),
					'args'                => array(
						'id'   => array(
							'description'       => __( 'Team member ID.', 'team-directory' ),
							'type'              => 'integer',
							'minimum'           => 1,
							'required'          => true,
							'validate_callback' => 'rest_validate_request_arg',
							'sanitize_callback' => 'absint',
						),
						'role' => array(
							'description'       => __( 'New role for the team member.', 'team-directory' ),
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => array( $this, 'validate_role' ),
							'sanitize_callback' => __NAMESPACE__ . '\\sanitize_role',
						),
					),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
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
	 * Arguments for the members collection.
	 *
	 * @return array
	 */
	protected function get_collection_args() {
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
				'description'       => sprintf(
					/* translators: %d: maximum number of items per page. */
					__( 'Maximum number of items to return per page (1–%d).', 'team-directory' ),
					MAX_PER_PAGE
				),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => MAX_PER_PAGE,
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'absint',
			),
			'role'     => array(
				'description'       => __( 'Only return members with this exact role.', 'team-directory' ),
				'type'              => 'string',
				'default'           => '',
				'maxLength'         => ROLE_MAX_LENGTH,
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => __NAMESPACE__ . '\\sanitize_role',
			),
		);
	}

	/**
	 * GET /members.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_members( WP_REST_Request $request ) {
		$page  = (int) $request['page'];
		$query = query_members(
			array(
				'role'     => (string) $request['role'],
				'per_page' => (int) $request['per_page'],
				'page'     => $page,
			)
		);

		$total       = (int) $query->found_posts;
		$total_pages = (int) $query->max_num_pages;

		if ( $page > 1 && $page > $total_pages ) {
			return new WP_Error(
				'team_directory_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.', 'team-directory' ),
				array( 'status' => 400 )
			);
		}

		$items = array_map( __NAMESPACE__ . '\\format_member', $query->posts );

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $total_pages );

		$base = add_query_arg(
			urlencode_deep( array_filter( $request->get_query_params(), 'is_scalar' ) ),
			rest_url( self::ROUTE_NAMESPACE . '/members' )
		);
		if ( $page > 1 ) {
			$response->link_header( 'prev', add_query_arg( 'page', $page - 1, $base ) );
		}
		if ( $page < $total_pages ) {
			$response->link_header( 'next', add_query_arg( 'page', $page + 1, $base ) );
		}

		return $response;
	}

	/**
	 * GET /roles.
	 *
	 * @return WP_REST_Response
	 */
	public function get_roles() {
		return rest_ensure_response( get_member_roles() );
	}

	/**
	 * Permission check for updating a member's role.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true|WP_Error
	 */
	public function update_role_permissions_check( WP_REST_Request $request ) {
		$post = get_post( (int) $request['id'] );

		if ( ! $post || POST_TYPE !== $post->post_type ) {
			return new WP_Error(
				'team_directory_member_not_found',
				__( 'Team member not found.', 'team-directory' ),
				array( 'status' => 404 )
			);
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return new WP_Error(
				'team_directory_cannot_edit',
				__( 'Sorry, you are not allowed to edit this team member.', 'team-directory' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Validate the role argument.
	 *
	 * @param mixed           $value   Value.
	 * @param WP_REST_Request $request Request.
	 * @param string          $param   Parameter name.
	 * @return true|WP_Error
	 */
	public function validate_role( $value, $request, $param ) {
		if ( ! is_string( $value ) ) {
			return new WP_Error(
				'rest_invalid_param',
				/* translators: %s: parameter name. */
				sprintf( __( '%s must be a string.', 'team-directory' ), $param ),
				array( 'status' => 400 )
			);
		}

		$clean = sanitize_text_field( $value );

		if ( '' === $clean ) {
			return new WP_Error(
				'rest_invalid_param',
				/* translators: %s: parameter name. */
				sprintf( __( '%s must not be empty.', 'team-directory' ), $param ),
				array( 'status' => 400 )
			);
		}

		if ( mb_strlen( $clean ) > ROLE_MAX_LENGTH ) {
			return new WP_Error(
				'rest_invalid_param',
				sprintf(
					/* translators: 1: parameter name, 2: maximum length. */
					__( '%1$s must be at most %2$d characters long.', 'team-directory' ),
					$param,
					ROLE_MAX_LENGTH
				),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * POST/PUT/PATCH /members/{id}/role.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_role( WP_REST_Request $request ) {
		$post = get_post( (int) $request['id'] );
		$role = (string) $request['role'];

		if ( (string) get_post_meta( $post->ID, ROLE_META, true ) !== $role ) {
			$result = update_post_meta( $post->ID, ROLE_META, $role );

			if ( false === $result ) {
				return new WP_Error(
					'team_directory_update_failed',
					__( 'The role could not be saved.', 'team-directory' ),
					array( 'status' => 500 )
				);
			}
		}

		return rest_ensure_response( format_member( $post ) );
	}

	/**
	 * Schema for a team member item.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'team-member',
			'type'       => 'object',
			'properties' => array(
				'id'         => array( 'type' => 'integer' ),
				'name'       => array( 'type' => 'string' ),
				'bio'        => array( 'type' => 'string' ),
				'photo'      => array(
					'type'       => array( 'object', 'null' ),
					'properties' => array(
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
				'role'       => array( 'type' => 'string' ),
				'start_year' => array( 'type' => array( 'integer', 'null' ) ),
				'link'       => array(
					'type'   => 'string',
					'format' => 'uri',
				),
			),
		);
	}
}
