<?php
/**
 * REST API controller for checklist items and per-post state.
 *
 * @package EditorialChecklist
 */

defined( 'ABSPATH' ) || exit;

/**
 * Routes:
 * - GET  /editorial-checklist/v1/items?post_type=post
 * - GET  /editorial-checklist/v1/posts/<id>
 * - POST /editorial-checklist/v1/posts/<id>   { "checked": [ "item-id", ... ] }
 *
 * Cookie-authenticated requests need the `wp_rest` nonce, which `@wordpress/api-fetch` sends in the editor.
 */
class Editorial_Checklist_REST_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = EDITORIAL_CHECKLIST_REST_NAMESPACE;
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
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'post_type' => array(
							'description'       => __( 'Post type the checklist is for.', 'editorial-checklist' ),
							'type'              => 'string',
							'default'           => 'post',
							'sanitize_callback' => 'sanitize_key',
							'validate_callback' => array( $this, 'validate_post_type' ),
						),
					),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/posts/(?P<id>[\d]+)',
			array(
				'args' => array(
					'id' => array(
						'description' => __( 'Post ID.', 'editorial-checklist' ),
						'type'        => 'integer',
						'minimum'     => 1,
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_post_state' ),
					'permission_callback' => array( $this, 'post_state_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_post_state' ),
					'permission_callback' => array( $this, 'post_state_permissions_check' ),
					'args'                => array(
						'checked' => array(
							'description'       => __( 'IDs of the checked items.', 'editorial-checklist' ),
							'type'              => 'array',
							'items'             => array( 'type' => 'string' ),
							'required'          => true,
							'sanitize_callback' => 'editorial_checklist_sanitize_checked',
						),
					),
				),
			)
		);
	}

	/**
	 * Only users who can edit posts may load the checklist items.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		if ( current_user_can( 'edit_posts' ) ) {
			return true;
		}

		return new WP_Error(
			'rest_forbidden',
			__( 'Sorry, you are not allowed to view the editorial checklist.', 'editorial-checklist' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * Validates the post_type argument.
	 *
	 * @param mixed $value Value.
	 * @return true|WP_Error
	 */
	public function validate_post_type( $value ) {
		if ( is_string( $value ) && editorial_checklist_is_supported_post_type( sanitize_key( $value ) ) ) {
			return true;
		}

		return new WP_Error(
			'rest_invalid_param',
			__( 'The editorial checklist is not enabled for this post type.', 'editorial-checklist' ),
			array( 'status' => 400 )
		);
	}

	/**
	 * Returns the checklist items.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$items = array_map(
			function ( $item ) use ( $request ) {
				return $this->prepare_response_for_collection( $this->prepare_item_for_response( $item, $request ) );
			},
			editorial_checklist_get_items( $request['post_type'] )
		);

		return rest_ensure_response( $items );
	}

	/**
	 * Prepares a single item for the response.
	 *
	 * @param array           $item    Item.
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		return rest_ensure_response(
			array(
				'id'          => (string) $item['id'],
				'label'       => (string) $item['label'],
				'required'    => (bool) $item['required'],
				'description' => (string) $item['description'],
			)
		);
	}

	/**
	 * The user must be able to edit the specific post.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true|WP_Error
	 */
	public function post_state_permissions_check( $request ) {
		$post = get_post( (int) $request['id'] );

		if ( ! $post || ! editorial_checklist_is_supported_post_type( $post->post_type ) ) {
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
	 * Returns the checklist state for a post.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_post_state( $request ) {
		return $this->prepare_post_state( (int) $request['id'] );
	}

	/**
	 * Saves the checklist state for a post.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function update_post_state( $request ) {
		$post_id = (int) $request['id'];
		editorial_checklist_set_checked( $post_id, (array) $request['checked'] );

		return $this->prepare_post_state( $post_id );
	}

	/**
	 * Builds the post state response.
	 *
	 * @param int $post_id Post ID.
	 * @return WP_REST_Response
	 */
	protected function prepare_post_state( $post_id ) {
		return rest_ensure_response(
			array(
				'post_id'          => $post_id,
				'checked'          => editorial_checklist_get_checked( $post_id ),
				'missing_required' => wp_list_pluck( editorial_checklist_get_missing_required( $post_id ), 'id' ),
			)
		);
	}

	/**
	 * Schema for a checklist item.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'editorial-checklist-item',
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'Unique item ID.', 'editorial-checklist' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'label'       => array(
					'description' => __( 'Item label.', 'editorial-checklist' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'required'    => array(
					'description' => __( 'Whether the item must be checked before publishing.', 'editorial-checklist' ),
					'type'        => 'boolean',
					'readonly'    => true,
				),
				'description' => array(
					'description' => __( 'Optional help text.', 'editorial-checklist' ),
					'type'        => 'string',
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $this->schema );
	}
}
