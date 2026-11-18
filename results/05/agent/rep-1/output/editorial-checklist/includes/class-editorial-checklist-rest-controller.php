<?php
/**
 * REST API controller for Editorial Checklist.
 *
 * @package EditorialChecklist
 */

defined( 'ABSPATH' ) || exit;

/**
 * Exposes checklist items and per-post checklist state.
 *
 * Routes:
 * - GET  /editorial-checklist/v1/items?post_type=post
 * - GET  /editorial-checklist/v1/posts/<id>
 * - POST /editorial-checklist/v1/posts/<id>
 */
class Editorial_Checklist_REST_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = EDITORIAL_CHECKLIST_REST_NAMESPACE;
		$this->rest_base = 'posts';
	}

	/**
	 * Registers the routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/items',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_checklist_items' ),
					'permission_callback' => array( $this, 'get_checklist_items_permissions_check' ),
					'args'                => array(
						'post_type' => array(
							'description'       => __( 'Post type to load checklist items for.', 'editorial-checklist' ),
							'type'              => 'string',
							'default'           => 'post',
							'sanitize_callback' => 'sanitize_key',
							'validate_callback' => array( $this, 'validate_post_type' ),
						),
					),
				),
				'schema' => array( $this, 'get_items_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Post ID.', 'editorial-checklist' ),
						'type'        => 'integer',
						'minimum'     => 1,
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => array(
						'checked' => array(
							'description' => __( 'IDs of the checked items.', 'editorial-checklist' ),
							'type'        => 'array',
							'items'       => array( 'type' => 'string' ),
							'required'    => true,
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Validates the post_type parameter.
	 *
	 * @param mixed $value Parameter value.
	 * @return true|WP_Error
	 */
	public function validate_post_type( $value ) {
		if ( ! is_string( $value ) || ! editorial_checklist_is_enabled_for( sanitize_key( $value ) ) ) {
			return new WP_Error(
				'rest_invalid_param',
				__( 'The editorial checklist is not enabled for this post type.', 'editorial-checklist' ),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Only users who can edit posts of the requested type may load the items.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true|WP_Error
	 */
	public function get_checklist_items_permissions_check( $request ) {
		$post_type_object = get_post_type_object( (string) $request['post_type'] );
		$capability       = $post_type_object ? $post_type_object->cap->edit_posts : 'edit_posts';

		if ( ! current_user_can( $capability ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to view the editorial checklist.', 'editorial-checklist' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Returns the checklist items.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_checklist_items( $request ) {
		return rest_ensure_response( editorial_checklist_get_items( (string) $request['post_type'] ) );
	}

	/**
	 * Checks whether the current user may read the checklist state of a post.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		return $this->check_post_permission( (int) $request['id'] );
	}

	/**
	 * Checks whether the current user may update the checklist state of a post.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true|WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		return $this->check_post_permission( (int) $request['id'] );
	}

	/**
	 * Shared permission logic: post must exist, use the checklist and be editable by the user.
	 *
	 * @param int $post_id Post ID.
	 * @return true|WP_Error
	 */
	protected function check_post_permission( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || ! editorial_checklist_is_enabled_for( $post->post_type ) ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid post ID.', 'editorial-checklist' ),
				array( 'status' => 404 )
			);
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to edit the checklist for this post.', 'editorial-checklist' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Returns the checklist state of a post.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_item( $request ) {
		$post_id = (int) $request['id'];

		return $this->prepare_item_for_response( editorial_checklist_get_checked( $post_id ), $request );
	}

	/**
	 * Saves the checklist state of a post.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function update_item( $request ) {
		$post_id = (int) $request['id'];
		$checked = editorial_checklist_set_checked( $post_id, (array) $request['checked'] );

		return $this->prepare_item_for_response( $checked, $request );
	}

	/**
	 * Prepares the checklist state for the response.
	 *
	 * @param string[]        $item    Checked item IDs.
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$post_id = (int) $request['id'];
		$missing = editorial_checklist_get_missing_required( $post_id );

		return rest_ensure_response(
			array(
				'post_id'          => $post_id,
				'checked'          => array_values( $item ),
				'missing_required' => wp_list_pluck( $missing, 'id' ),
				'complete'         => empty( $missing ),
			)
		);
	}

	/**
	 * Schema for the per-post checklist state.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'editorial-checklist-state',
			'type'       => 'object',
			'properties' => array(
				'post_id'          => array(
					'type'     => 'integer',
					'readonly' => true,
				),
				'checked'          => array(
					'type'  => 'array',
					'items' => array( 'type' => 'string' ),
				),
				'missing_required' => array(
					'type'     => 'array',
					'items'    => array( 'type' => 'string' ),
					'readonly' => true,
				),
				'complete'         => array(
					'type'     => 'boolean',
					'readonly' => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $this->schema );
	}

	/**
	 * Schema for the items list.
	 *
	 * @return array
	 */
	public function get_items_schema() {
		return array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'editorial-checklist-items',
			'type'    => 'array',
			'items'   => array(
				'type'       => 'object',
				'properties' => array(
					'id'       => array( 'type' => 'string' ),
					'label'    => array( 'type' => 'string' ),
					'required' => array( 'type' => 'boolean' ),
				),
			),
		);
	}
}
