<?php
/**
 * Option registration, schema and sanitization.
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the `maintenance_banner_options` option.
 */
final class Maintenance_Banner_Options {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION = 'maintenance_banner_options';

	/**
	 * Settings group used by the Settings API form.
	 *
	 * @var string
	 */
	const GROUP = 'maintenance_banner';

	/**
	 * Storage format for start/end values (matches `<input type="datetime-local">`).
	 *
	 * @var string
	 */
	const DATETIME_FORMAT = 'Y-m-d\TH:i';

	/**
	 * Hook into WordPress.
	 *
	 * The setting is registered on `init` so it is available both to the
	 * Settings API (options.php) and to the REST API (/wp/v2/settings).
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Default option values.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'enabled'          => false,
			'message'          => '',
			'background_color' => '#ffcc00',
			'start_datetime'   => '',
			'end_datetime'     => '',
			'hidden_roles'     => array(),
		);
	}

	/**
	 * Get the current option merged with defaults.
	 *
	 * @return array
	 */
	public static function get() {
		$value = get_option( self::OPTION, self::defaults() );

		if ( ! is_array( $value ) ) {
			$value = array();
		}

		return wp_parse_args( $value, self::defaults() );
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
			'span'   => array(
				'class' => true,
			),
		);

		/**
		 * Filters the HTML tags allowed in the maintenance banner message.
		 *
		 * @param array $allowed Allowed tags in wp_kses() format.
		 */
		return apply_filters( 'maintenance_banner_allowed_html', $allowed );
	}

	/**
	 * JSON schema for the option, used by the REST API.
	 *
	 * @return array
	 */
	public static function schema() {
		$defaults = self::defaults();

		return array(
			'type'                 => 'object',
			'properties'           => array(
				'enabled'          => array(
					'description' => __( 'Whether the banner is enabled.', 'maintenance-banner' ),
					'type'        => 'boolean',
					'default'     => $defaults['enabled'],
				),
				'message'          => array(
					'description' => __( 'Banner message. Limited HTML is allowed.', 'maintenance-banner' ),
					'type'        => 'string',
					'default'     => $defaults['message'],
				),
				'background_color' => array(
					'description' => __( 'Banner background colour as a hex value.', 'maintenance-banner' ),
					'type'        => 'string',
					'pattern'     => '^#([A-Fa-f0-9]{3}){1,2}$',
					'default'     => $defaults['background_color'],
				),
				'start_datetime'   => array(
					'description' => __( 'Start of the display window (site timezone, Y-m-d\TH:i). Empty for no start.', 'maintenance-banner' ),
					'type'        => 'string',
					'pattern'     => '^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2})?$',
					'default'     => $defaults['start_datetime'],
				),
				'end_datetime'     => array(
					'description' => __( 'End of the display window (site timezone, Y-m-d\TH:i). Empty for no end.', 'maintenance-banner' ),
					'type'        => 'string',
					'pattern'     => '^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2})?$',
					'default'     => $defaults['end_datetime'],
				),
				'hidden_roles'     => array(
					'description' => __( 'User roles that should not see the banner.', 'maintenance-banner' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
					),
					'uniqueItems' => true,
					'default'     => $defaults['hidden_roles'],
				),
			),
			'additionalProperties' => false,
		);
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
					'schema' => self::schema(),
				),
			)
		);
	}

	/**
	 * Sanitize the option. Runs for both the settings form and REST saves.
	 *
	 * @param mixed $input Raw value.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$previous = self::get();

		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$output = array();

		// Enabled: missing checkbox means off.
		$output['enabled'] = isset( $input['enabled'] ) && rest_sanitize_boolean( $input['enabled'] );

		// Message: limited HTML.
		$message           = isset( $input['message'] ) && is_scalar( $input['message'] ) ? (string) $input['message'] : '';
		$output['message'] = trim( wp_kses( $message, self::allowed_html() ) );

		// Background colour.
		$raw_color = isset( $input['background_color'] ) && is_string( $input['background_color'] ) ? trim( $input['background_color'] ) : '';
		$color     = sanitize_hex_color( $raw_color );
		if ( empty( $color ) ) {
			if ( '' !== $raw_color ) {
				self::report_error(
					'invalid_background_color',
					__( 'The background colour must be a valid hex colour such as #ffcc00. The previous value was kept.', 'maintenance-banner' )
				);
			}
			$color = sanitize_hex_color( $previous['background_color'] );
			$color = $color ? $color : $defaults['background_color'];
		}
		$output['background_color'] = $color;

		// Start / end.
		foreach ( array( 'start_datetime', 'end_datetime' ) as $key ) {
			$raw            = isset( $input[ $key ] ) && is_string( $input[ $key ] ) ? trim( $input[ $key ] ) : '';
			$output[ $key ] = self::sanitize_datetime( $raw );

			if ( '' !== $raw && '' === $output[ $key ] ) {
				self::report_error(
					'invalid_' . $key,
					'start_datetime' === $key
						? __( 'The start time is not a valid date and time and was cleared.', 'maintenance-banner' )
						: __( 'The end time is not a valid date and time and was cleared.', 'maintenance-banner' )
				);
			}
		}

		$start = self::to_datetime( $output['start_datetime'] );
		$end   = self::to_datetime( $output['end_datetime'] );

		if ( $start && $end && $end <= $start ) {
			self::report_error(
				'end_before_start',
				__( 'The end time must be later than the start time. The previous schedule was kept.', 'maintenance-banner' )
			);
			$output['start_datetime'] = self::sanitize_datetime( (string) $previous['start_datetime'] );
			$output['end_datetime']   = self::sanitize_datetime( (string) $previous['end_datetime'] );
		}

		// Hidden roles: only keep roles that exist.
		$valid_roles = array_keys( wp_roles()->get_names() );
		$roles       = isset( $input['hidden_roles'] ) ? (array) $input['hidden_roles'] : array();
		$roles       = array_filter( $roles, 'is_string' );
		$roles       = array_map( 'sanitize_key', $roles );

		$output['hidden_roles'] = array_values( array_unique( array_intersect( $roles, $valid_roles ) ) );

		return $output;
	}

	/**
	 * Normalise a datetime-local string, or return '' when invalid.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	public static function sanitize_datetime( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}

		foreach ( array( self::DATETIME_FORMAT, 'Y-m-d\TH:i:s' ) as $format ) {
			$date = DateTimeImmutable::createFromFormat( '!' . $format, $value, wp_timezone() );
			if ( $date && $date->format( $format ) === $value ) {
				return $date->format( self::DATETIME_FORMAT );
			}
		}

		return '';
	}

	/**
	 * Convert a stored value into a DateTimeImmutable in the site timezone.
	 *
	 * @param string $value Stored value.
	 * @return DateTimeImmutable|null
	 */
	public static function to_datetime( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return null;
		}

		$date = DateTimeImmutable::createFromFormat( '!' . self::DATETIME_FORMAT, $value, wp_timezone() );

		return $date ? $date : null;
	}

	/**
	 * Add a settings error once per request.
	 *
	 * The sanitize callback can run twice when the option is first created
	 * (update_option() falls through to add_option()), and add_settings_error()
	 * only exists in wp-admin, not in REST requests.
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 */
	private static function report_error( $code, $message ) {
		if ( ! function_exists( 'add_settings_error' ) || ! function_exists( 'get_settings_errors' ) ) {
			return;
		}

		foreach ( get_settings_errors( self::OPTION ) as $error ) {
			if ( isset( $error['code'] ) && $error['code'] === $code ) {
				return;
			}
		}

		add_settings_error( self::OPTION, $code, $message, 'error' );
	}
}
