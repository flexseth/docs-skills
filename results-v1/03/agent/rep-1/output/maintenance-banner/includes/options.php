<?php
/**
 * Option helpers: defaults, schema, sanitization and registration.
 *
 * @package MaintenanceBanner
 */

namespace MaintenanceBanner;

defined( 'ABSPATH' ) || exit;

/**
 * Datetime format used for storage (matches the HTML datetime-local input).
 */
const DATETIME_FORMAT = 'Y-m-d\TH:i';

/**
 * Default option values.
 *
 * @return array
 */
function get_defaults() {
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
 * Stored options merged with defaults.
 *
 * @return array
 */
function get_options() {
	$options = get_option( MAINTENANCE_BANNER_OPTION, get_defaults() );

	if ( ! is_array( $options ) ) {
		$options = array();
	}

	return wp_parse_args( $options, get_defaults() );
}

/**
 * HTML allowed in the banner message.
 *
 * @return array
 */
function get_allowed_html() {
	return array(
		'a'      => array(
			'href'   => true,
			'title'  => true,
			'target' => true,
			'rel'    => true,
		),
		'strong' => array(),
		'em'     => array(),
		'b'      => array(),
		'i'      => array(),
		'br'     => array(),
		'code'   => array(),
		'span'   => array(
			'class' => true,
		),
	);
}

/**
 * REST schema for the option.
 *
 * @return array
 */
function get_schema() {
	$datetime_pattern = '^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2})?$';

	return array(
		'type'                 => 'object',
		'additionalProperties' => false,
		'properties'           => array(
			'enabled'          => array(
				'description' => __( 'Whether the banner is enabled.', 'maintenance-banner' ),
				'type'        => 'boolean',
			),
			'message'          => array(
				'description' => __( 'Banner message. Limited HTML is allowed.', 'maintenance-banner' ),
				'type'        => 'string',
			),
			'background_color' => array(
				'description' => __( 'Banner background colour as a hex value.', 'maintenance-banner' ),
				'type'        => 'string',
				'format'      => 'hex-color',
			),
			'start'            => array(
				'description' => __( 'Start of the display window (site timezone, Y-m-d\TH:i), or empty.', 'maintenance-banner' ),
				'type'        => 'string',
				'pattern'     => $datetime_pattern,
			),
			'end'              => array(
				'description' => __( 'End of the display window (site timezone, Y-m-d\TH:i), or empty.', 'maintenance-banner' ),
				'type'        => 'string',
				'pattern'     => $datetime_pattern,
			),
			'hidden_roles'     => array(
				'description' => __( 'User roles that should not see the banner.', 'maintenance-banner' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'string',
				),
				'uniqueItems' => true,
			),
		),
	);
}

/**
 * Register the option for the Settings API and REST API.
 *
 * Hooked to `init` so it is available on admin_init (options.php) and rest_api_init.
 */
function register_option() {
	register_setting(
		'maintenance_banner',
		MAINTENANCE_BANNER_OPTION,
		array(
			'type'              => 'object',
			'label'             => __( 'Maintenance Banner', 'maintenance-banner' ),
			'description'       => __( 'Maintenance banner settings.', 'maintenance-banner' ),
			'sanitize_callback' => __NAMESPACE__ . '\sanitize_options',
			'default'           => get_defaults(),
			'show_in_rest'      => array(
				'schema' => get_schema(),
			),
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\register_option' );

/**
 * Parse a stored datetime string in the site timezone.
 *
 * @param string $value Datetime string.
 * @return \DateTimeImmutable|null
 */
function parse_datetime( $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return null;
	}

	$date = \DateTimeImmutable::createFromFormat( '!' . DATETIME_FORMAT, $value, wp_timezone() );

	if ( false === $date || $date->format( DATETIME_FORMAT ) !== $value ) {
		return null;
	}

	return $date;
}

/**
 * Sanitize a datetime value to DATETIME_FORMAT or an empty string.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function sanitize_datetime( $value ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}

	$value = trim( (string) $value );

	// Accept values with seconds (e.g. from some browsers) by truncating.
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $value, $matches ) ) {
		$value = $matches[0];
	}

	return parse_datetime( $value ) ? $value : '';
}

/**
 * Sanitize callback for the option.
 *
 * Used by options.php (Settings page) and by the REST settings endpoint,
 * since both go through update_option() -> sanitize_option().
 *
 * @param mixed $input Raw input.
 * @return array
 */
function sanitize_options( $input ) {
	// sanitize_option can run twice when the option is first added; avoid duplicate notices.
	static $errors_reported = array();

	$old = get_options();

	if ( ! is_array( $input ) ) {
		return $old;
	}

	$defaults = get_defaults();
	$output   = array();

	$output['enabled'] = isset( $input['enabled'] ) ? (bool) rest_sanitize_boolean( $input['enabled'] ) : false;

	$output['message'] = isset( $input['message'] ) && is_string( $input['message'] )
		? trim( wp_kses( $input['message'], get_allowed_html() ) )
		: '';

	$color                      = isset( $input['background_color'] ) && is_string( $input['background_color'] )
		? sanitize_hex_color( $input['background_color'] )
		: '';
	$output['background_color'] = $color ? $color : $defaults['background_color'];

	$output['start'] = isset( $input['start'] ) ? sanitize_datetime( $input['start'] ) : '';
	$output['end']   = isset( $input['end'] ) ? sanitize_datetime( $input['end'] ) : '';

	$start = parse_datetime( $output['start'] );
	$end   = parse_datetime( $output['end'] );

	if ( $start && $end && $end < $start ) {
		// add_settings_error() lives in wp-admin and is not loaded for REST requests.
		if ( empty( $errors_reported['window'] ) && function_exists( 'add_settings_error' ) ) {
			add_settings_error(
				MAINTENANCE_BANNER_OPTION,
				'maintenance_banner_invalid_window',
				__( 'The end time must be after the start time. The schedule was not changed.', 'maintenance-banner' ),
				'error'
			);
			$errors_reported['window'] = true;
		}

		$output['start'] = $old['start'];
		$output['end']   = $old['end'];
	}

	$valid_roles = array_keys( wp_roles()->get_names() );
	$roles       = isset( $input['hidden_roles'] ) && is_array( $input['hidden_roles'] )
		? array_map( 'sanitize_key', array_filter( $input['hidden_roles'], 'is_string' ) )
		: array();

	$output['hidden_roles'] = array_values( array_unique( array_intersect( $roles, $valid_roles ) ) );

	return $output;
}
