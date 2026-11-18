<?php
/**
 * Setting registration, defaults, validation and sanitization.
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default settings.
 *
 * @return array
 */
function maintenance_banner_get_defaults() {
	return array(
		'enabled'          => false,
		'message'          => '',
		'background_color' => '#d63638',
		'start'            => '',
		'end'              => '',
		'hidden_roles'     => array(),
	);
}

/**
 * HTML allowed inside the banner message.
 *
 * @return array
 */
function maintenance_banner_allowed_html() {
	return array(
		'a'      => array(
			'href'   => true,
			'title'  => true,
			'target' => true,
			'rel'    => true,
		),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'i'      => array(),
		'br'     => array(),
	);
}

/**
 * Current settings merged with defaults.
 *
 * @return array
 */
function maintenance_banner_get_settings() {
	$value = get_option( MAINTENANCE_BANNER_OPTION, array() );

	if ( ! is_array( $value ) ) {
		$value = array();
	}

	return array_intersect_key(
		wp_parse_args( $value, maintenance_banner_get_defaults() ),
		maintenance_banner_get_defaults()
	);
}

/**
 * Parses a stored date/time string in the site's timezone.
 *
 * @param string $value Value in MAINTENANCE_BANNER_DATE_FORMAT.
 * @return DateTimeImmutable|null|false Date object, null when empty, false when invalid.
 */
function maintenance_banner_parse_datetime( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';

	if ( '' === $value ) {
		return null;
	}

	// The leading "!" resets fields not in the format (seconds) to zero.
	$date = DateTimeImmutable::createFromFormat( '!' . MAINTENANCE_BANNER_DATE_FORMAT, $value, wp_timezone() );

	if ( ! $date || $date->format( MAINTENANCE_BANNER_DATE_FORMAT ) !== $value ) {
		return false;
	}

	return $date;
}

/**
 * Validates a start/end pair.
 *
 * @param string $start Start date/time.
 * @param string $end   End date/time.
 * @return true|WP_Error
 */
function maintenance_banner_validate_range( $start, $end ) {
	$start_date = maintenance_banner_parse_datetime( $start );
	$end_date   = maintenance_banner_parse_datetime( $end );

	if ( false === $start_date ) {
		return new WP_Error( 'maintenance_banner_invalid_start', __( 'The start date and time is not valid.', 'maintenance-banner' ) );
	}

	if ( false === $end_date ) {
		return new WP_Error( 'maintenance_banner_invalid_end', __( 'The end date and time is not valid.', 'maintenance-banner' ) );
	}

	if ( $start_date && $end_date && $end_date <= $start_date ) {
		return new WP_Error( 'maintenance_banner_invalid_range', __( 'The end date and time must be after the start date and time. The schedule was not changed.', 'maintenance-banner' ) );
	}

	return true;
}

/**
 * Adds a settings error once (the sanitize callback can run twice when the option is first created).
 *
 * @param string $code    Error code.
 * @param string $message Error message.
 */
function maintenance_banner_add_settings_error( $code, $message ) {
	foreach ( get_settings_errors( MAINTENANCE_BANNER_OPTION ) as $error ) {
		if ( $error['code'] === $code ) {
			return;
		}
	}

	add_settings_error( MAINTENANCE_BANNER_OPTION, $code, $message, 'error' );
}

/**
 * Sanitizes and validates the settings array before it is stored.
 *
 * Runs for the Settings page, the REST API and any update_option() call.
 *
 * @param mixed $input Raw value.
 * @return array
 */
function maintenance_banner_sanitize_settings( $input ) {
	$defaults = maintenance_banner_get_defaults();
	$current  = maintenance_banner_get_settings();

	if ( ! is_array( $input ) ) {
		return $current;
	}

	// Missing keys mean "off/empty" (unchecked checkboxes are not submitted).
	$input  = wp_parse_args( $input, $defaults );
	$output = array();

	$output['enabled'] = rest_sanitize_boolean( $input['enabled'] );

	$output['message'] = trim( wp_kses( (string) $input['message'], maintenance_banner_allowed_html() ) );

	$color = sanitize_hex_color( (string) $input['background_color'] );
	if ( empty( $color ) ) {
		maintenance_banner_add_settings_error(
			'maintenance_banner_invalid_color',
			__( 'The background colour must be a hex colour such as #d63638. The previous colour was kept.', 'maintenance-banner' )
		);
		$color = $current['background_color'];
	}
	$output['background_color'] = $color;

	$start = is_string( $input['start'] ) ? sanitize_text_field( $input['start'] ) : '';
	$end   = is_string( $input['end'] ) ? sanitize_text_field( $input['end'] ) : '';
	$valid = maintenance_banner_validate_range( $start, $end );

	if ( is_wp_error( $valid ) ) {
		maintenance_banner_add_settings_error( $valid->get_error_code(), $valid->get_error_message() );
		// Do not store the bad schedule; keep the previously saved one.
		$output['start'] = $current['start'];
		$output['end']   = $current['end'];
	} else {
		$output['start'] = $start;
		$output['end']   = $end;
	}

	$roles = is_array( $input['hidden_roles'] ) ? $input['hidden_roles'] : array();
	$roles = array_map( 'sanitize_key', array_filter( $roles, 'is_string' ) );

	$output['hidden_roles'] = array_values( array_unique( array_intersect( $roles, array_keys( wp_roles()->get_names() ) ) ) );

	return $output;
}

/**
 * Registers the setting (on init so it is available to the REST API).
 */
function maintenance_banner_register_setting() {
	$date_schema = array(
		'type'    => 'string',
		'pattern' => '^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2})?$',
	);

	register_setting(
		MAINTENANCE_BANNER_GROUP,
		MAINTENANCE_BANNER_OPTION,
		array(
			'type'              => 'object',
			'label'             => __( 'Maintenance banner', 'maintenance-banner' ),
			'description'       => __( 'Site-wide maintenance banner settings.', 'maintenance-banner' ),
			'sanitize_callback' => 'maintenance_banner_sanitize_settings',
			'default'           => maintenance_banner_get_defaults(),
			'show_in_rest'      => array(
				'name'   => MAINTENANCE_BANNER_REST_NAME,
				'schema' => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'enabled'          => array(
							'type' => 'boolean',
						),
						'message'          => array(
							'type' => 'string',
						),
						'background_color' => array(
							'type'   => 'string',
							'format' => 'hex-color',
						),
						'start'            => $date_schema,
						'end'              => $date_schema,
						'hidden_roles'     => array(
							'type'        => 'array',
							'uniqueItems' => true,
							'items'       => array(
								'type' => 'string',
							),
						),
					),
				),
			),
		)
	);
}
add_action( 'init', 'maintenance_banner_register_setting' );
