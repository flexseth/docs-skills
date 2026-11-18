<?php
/**
 * Settings registration, sanitization and validation.
 *
 * @package MaintenanceBanner
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin option with the Settings API and the REST API.
 */
final class Maintenance_Banner_Settings {

	/**
	 * Option name (also the key exposed at /wp/v2/settings).
	 */
	const OPTION = 'maintenance_banner_settings';

	/**
	 * Settings group used by the settings page form.
	 */
	const GROUP = 'maintenance_banner';

	/**
	 * Storage format for start/end values (site-local time, matches <input type="datetime-local">).
	 */
	const DATETIME_FORMAT = 'Y-m-d\TH:i';

	/**
	 * Error codes already reported during this request (sanitize callbacks can run twice).
	 *
	 * @var string[]
	 */
	private static $reported_errors = array();

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'rest_request_before_callbacks', array( __CLASS__, 'validate_rest_request' ), 10, 3 );
	}

	/**
	 * Default values.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'enabled'          => false,
			'message'          => '',
			'background_color' => '#ffcc00',
			'start'            => '',
			'end'              => '',
			'hidden_roles'     => array(),
		);
	}

	/**
	 * HTML allowed in the banner message.
	 *
	 * @return array
	 */
	public static function allowed_html() {
		$allowed = array(
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
			'p'      => array(),
		);

		/**
		 * Filters the HTML tags allowed in the banner message.
		 *
		 * @param array $allowed Allowed tags in wp_kses() format.
		 */
		return apply_filters( 'maintenance_banner_allowed_html', $allowed );
	}

	/**
	 * Current settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get() {
		$stored = get_option( self::OPTION, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return array_merge( self::defaults(), array_intersect_key( $stored, self::defaults() ) );
	}

	/**
	 * Register the setting.
	 */
	public static function register() {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'object',
				'description'       => __( 'Maintenance banner settings.', 'maintenance-banner' ),
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
				'show_in_rest'      => array(
					'schema' => self::rest_schema(),
				),
			)
		);
	}

	/**
	 * JSON schema used by the REST API settings endpoint.
	 *
	 * @return array
	 */
	public static function rest_schema() {
		$datetime_pattern = '^$|^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}$';

		return array(
			'type'                 => 'object',
			'additionalProperties' => false,
			'properties'           => array(
				'enabled'          => array(
					'type'        => 'boolean',
					'description' => __( 'Whether the banner is enabled.', 'maintenance-banner' ),
				),
				'message'          => array(
					'type'        => 'string',
					'description' => __( 'Banner message. Basic HTML (bold, italic, links) is allowed.', 'maintenance-banner' ),
				),
				'background_color' => array(
					'type'        => 'string',
					'description' => __( 'Banner background colour as a hex value.', 'maintenance-banner' ),
					'pattern'     => '^#([A-Fa-f0-9]{3}){1,2}$',
				),
				'start'            => array(
					'type'        => 'string',
					'description' => __( 'Start date and time (YYYY-MM-DDTHH:MM) in the site timezone. Empty for no start limit.', 'maintenance-banner' ),
					'pattern'     => $datetime_pattern,
				),
				'end'              => array(
					'type'        => 'string',
					'description' => __( 'End date and time (YYYY-MM-DDTHH:MM) in the site timezone. Empty for no end limit.', 'maintenance-banner' ),
					'pattern'     => $datetime_pattern,
				),
				'hidden_roles'     => array(
					'type'        => 'array',
					'description' => __( 'User roles that should not see the banner.', 'maintenance-banner' ),
					'items'       => array(
						'type' => 'string',
					),
				),
			),
		);
	}

	/**
	 * Parse a stored/submitted date-time string in the site timezone.
	 *
	 * @param string $value Date-time string in DATETIME_FORMAT.
	 * @return DateTimeImmutable|null Null when empty or invalid.
	 */
	public static function parse_datetime( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return null;
		}

		$date = DateTimeImmutable::createFromFormat( '!' . self::DATETIME_FORMAT, $value, wp_timezone() );

		if ( false === $date || $date->format( self::DATETIME_FORMAT ) !== $value ) {
			return null;
		}

		return $date;
	}

	/**
	 * Clean a submitted date-time value.
	 *
	 * @param mixed $value Raw value.
	 * @return string|null Normalized string, '' for empty, or null if invalid.
	 */
	private static function sanitize_datetime( $value ) {
		if ( ! is_scalar( $value ) ) {
			return null;
		}

		$value = trim( sanitize_text_field( (string) $value ) );

		if ( '' === $value ) {
			return '';
		}

		// Accept values with seconds (e.g. "2026-01-01T10:00:00") by trimming them.
		if ( preg_match( '/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2})(:\d{2})?$/', $value, $matches ) ) {
			$value = $matches[1];
		}

		return null === self::parse_datetime( $value ) ? null : $value;
	}

	/**
	 * Sanitize a list of role slugs against the registered roles.
	 *
	 * @param mixed $roles Raw value.
	 * @return string[]
	 */
	private static function sanitize_roles( $roles ) {
		if ( ! is_array( $roles ) ) {
			return array();
		}

		$valid = array_keys( wp_roles()->get_names() );
		$clean = array();

		foreach ( $roles as $role ) {
			if ( ! is_scalar( $role ) ) {
				continue;
			}
			$role = sanitize_key( (string) $role );
			if ( in_array( $role, $valid, true ) ) {
				$clean[] = $role;
			}
		}

		return array_values( array_unique( $clean ) );
	}

	/**
	 * Report a validation error on the settings page (no-op outside wp-admin).
	 *
	 * @param string $code    Error code.
	 * @param string $message Message.
	 */
	private static function report_error( $code, $message ) {
		if ( in_array( $code, self::$reported_errors, true ) || ! function_exists( 'add_settings_error' ) ) {
			return;
		}
		self::$reported_errors[] = $code;
		add_settings_error( self::OPTION, $code, $message, 'error' );
	}

	/**
	 * Resolve submitted start/end values against the current values.
	 *
	 * @param array $input   Submitted values (possibly partial).
	 * @param array $current Current stored values.
	 * @return array {
	 *     @type string|null $start Clean start, or null if invalid.
	 *     @type string|null $end   Clean end, or null if invalid.
	 *     @type bool        $order_ok Whether end is not before start.
	 * }
	 */
	private static function resolve_dates( array $input, array $current ) {
		$start = array_key_exists( 'start', $input ) ? self::sanitize_datetime( $input['start'] ) : $current['start'];
		$end   = array_key_exists( 'end', $input ) ? self::sanitize_datetime( $input['end'] ) : $current['end'];

		$order_ok = true;
		if ( null !== $start && null !== $end && '' !== $start && '' !== $end ) {
			$order_ok = self::parse_datetime( $end ) >= self::parse_datetime( $start );
		}

		return array(
			'start'    => $start,
			'end'      => $end,
			'order_ok' => $order_ok,
		);
	}

	/**
	 * Sanitize callback for the option. Missing keys keep their current value.
	 *
	 * @param mixed $input Raw submitted value.
	 * @return array Clean value.
	 */
	public static function sanitize( $input ) {
		$current = self::get();

		if ( ! is_array( $input ) ) {
			return $current;
		}

		$clean = $current;

		if ( array_key_exists( 'enabled', $input ) ) {
			$clean['enabled'] = rest_sanitize_boolean( $input['enabled'] );
		}

		if ( array_key_exists( 'message', $input ) && is_scalar( $input['message'] ) ) {
			$clean['message'] = trim( wp_kses( (string) $input['message'], self::allowed_html() ) );
		}

		if ( array_key_exists( 'background_color', $input ) ) {
			$color = is_scalar( $input['background_color'] ) ? sanitize_hex_color( (string) $input['background_color'] ) : null;
			if ( $color ) {
				$clean['background_color'] = $color;
			} else {
				self::report_error( 'invalid_color', __( 'The background colour must be a valid hex colour. The previous colour was kept.', 'maintenance-banner' ) );
			}
		}

		if ( array_key_exists( 'hidden_roles', $input ) ) {
			$clean['hidden_roles'] = self::sanitize_roles( $input['hidden_roles'] );
		}

		$dates = self::resolve_dates( $input, $current );

		if ( null === $dates['start'] ) {
			self::report_error( 'invalid_start', __( 'The start date and time is invalid. The previous value was kept.', 'maintenance-banner' ) );
		}
		if ( null === $dates['end'] ) {
			self::report_error( 'invalid_end', __( 'The end date and time is invalid. The previous value was kept.', 'maintenance-banner' ) );
		}

		if ( ! $dates['order_ok'] ) {
			self::report_error( 'end_before_start', __( 'The end date and time cannot be before the start date and time. The schedule was not changed.', 'maintenance-banner' ) );
		} else {
			if ( null !== $dates['start'] ) {
				$clean['start'] = $dates['start'];
			}
			if ( null !== $dates['end'] ) {
				$clean['end'] = $dates['end'];
			}
		}

		// Final safety net: never store an inverted window.
		$start_dt = self::parse_datetime( $clean['start'] );
		$end_dt   = self::parse_datetime( $clean['end'] );
		if ( $start_dt && $end_dt && $end_dt < $start_dt ) {
			$clean['start'] = $current['start'];
			$clean['end']   = $current['end'];
		}

		return $clean;
	}

	/**
	 * Reject REST updates with invalid dates or an end before the start.
	 *
	 * @param WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response Result to send to the client.
	 * @param array                                            $handler  Route handler.
	 * @param WP_REST_Request                                  $request  Request.
	 * @return mixed
	 */
	public static function validate_rest_request( $response, $handler, $request ) {
		if ( is_wp_error( $response ) || ! $request instanceof WP_REST_Request ) {
			return $response;
		}

		if ( '/wp/v2/settings' !== $request->get_route() || 'GET' === $request->get_method() ) {
			return $response;
		}

		$input = $request->get_param( self::OPTION );
		if ( ! is_array( $input ) ) {
			return $response;
		}

		$dates = self::resolve_dates( $input, self::get() );

		if ( null === $dates['start'] || null === $dates['end'] ) {
			return new WP_Error(
				'maintenance_banner_invalid_datetime',
				__( 'The start or end date and time is not a valid date.', 'maintenance-banner' ),
				array( 'status' => 400 )
			);
		}

		if ( ! $dates['order_ok'] ) {
			return new WP_Error(
				'maintenance_banner_end_before_start',
				__( 'The end date and time cannot be before the start date and time.', 'maintenance-banner' ),
				array( 'status' => 400 )
			);
		}

		return $response;
	}
}
