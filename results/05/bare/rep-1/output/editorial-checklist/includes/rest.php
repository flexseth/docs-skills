<?php
/**
 * REST API endpoint for checklist item definitions.
 *
 * @package EditorialChecklist
 */

namespace EditorialChecklist;

use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers GET /editorial-checklist/v1/items.
 */
function register_routes() {
	register_rest_route(
		'editorial-checklist/v1',
		'/items',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => __NAMESPACE__ . '\\rest_get_items',
				'permission_callback' => __NAMESPACE__ . '\\rest_permissions_check',
			),
			'schema' => __NAMESPACE__ . '\\rest_get_schema',
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_routes' );

/**
 * Permission check: the user must be able to edit posts.
 *
 * @return true|\WP_Error
 */
function rest_permissions_check() {
	if ( current_user_can( 'edit_posts' ) ) {
		return true;
	}

	return new \WP_Error(
		'rest_forbidden',
		__( 'Sorry, you are not allowed to view the editorial checklist.', 'editorial-checklist' ),
		array( 'status' => rest_authorization_required_code() )
	);
}

/**
 * Returns the checklist items.
 *
 * @return \WP_REST_Response
 */
function rest_get_items() {
	return rest_ensure_response( get_items() );
}

/**
 * JSON schema for the endpoint response.
 *
 * @return array
 */
function rest_get_schema() {
	return array(
		'$schema' => 'http://json-schema.org/draft-04/schema#',
		'title'   => 'editorial-checklist-items',
		'type'    => 'array',
		'items'   => array(
			'type'       => 'object',
			'properties' => array(
				'id'       => array(
					'type'        => 'string',
					'description' => __( 'Unique item identifier.', 'editorial-checklist' ),
				),
				'label'    => array(
					'type'        => 'string',
					'description' => __( 'Human-readable label.', 'editorial-checklist' ),
				),
				'required' => array(
					'type'        => 'boolean',
					'description' => __( 'Whether the item must be done before publishing.', 'editorial-checklist' ),
				),
			),
		),
	);
}
