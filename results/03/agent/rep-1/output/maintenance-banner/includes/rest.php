<?php
/**
 * REST API support for /wp/v2/settings.
 *
 * The setting itself is exposed via register_setting( 'show_in_rest' ). This file
 * allows partial updates (e.g. only "enabled") and returns a proper 400 error for an
 * invalid schedule instead of silently ignoring it.
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates and merges maintenance banner values sent to the settings endpoint.
 *
 * @param WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response Result to send.
 * @param array                                            $handler  Route handler.
 * @param WP_REST_Request                                  $request  Request.
 * @return WP_REST_Response|WP_HTTP_Response|WP_Error|mixed
 */
function maintenance_banner_rest_before_callbacks( $response, $handler, $request ) {
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	if ( '/wp/v2/settings' !== untrailingslashit( $request->get_route() ) || 'GET' === $request->get_method() ) {
		return $response;
	}

	$value = $request->get_param( MAINTENANCE_BANNER_REST_NAME );

	if ( ! is_array( $value ) ) {
		return $response;
	}

	// Partial updates: keep values that were not sent.
	$merged = array_merge( maintenance_banner_get_settings(), $value );
	$valid  = maintenance_banner_validate_range( $merged['start'], $merged['end'] );

	if ( is_wp_error( $valid ) ) {
		$valid->add_data( array( 'status' => 400 ) );
		return $valid;
	}

	$request->set_param( MAINTENANCE_BANNER_REST_NAME, $merged );

	return $response;
}
add_filter( 'rest_request_before_callbacks', 'maintenance_banner_rest_before_callbacks', 10, 3 );
