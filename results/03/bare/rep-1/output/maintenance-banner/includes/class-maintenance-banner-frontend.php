<?php
/**
 * Front-end banner output.
 *
 * @package MaintenanceBanner
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints the banner at the top of front-end pages when it should be visible.
 */
final class Maintenance_Banner_Frontend {

	/**
	 * Cached visibility result for the current request.
	 *
	 * @var bool|null
	 */
	private static $visible = null;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_body_open', array( __CLASS__, 'render' ), 1 );
	}

	/**
	 * Whether the banner should be shown to the current visitor right now.
	 *
	 * @return bool
	 */
	public static function should_display() {
		if ( null !== self::$visible ) {
			return self::$visible;
		}

		self::$visible = false;

		if ( is_admin() ) {
			return false;
		}

		$settings = Maintenance_Banner_Settings::get();

		if ( empty( $settings['enabled'] ) || '' === trim( wp_strip_all_tags( $settings['message'] ) ) ) {
			return false;
		}

		// current_datetime() is in the site timezone, as are the stored values.
		$now   = current_datetime();
		$start = Maintenance_Banner_Settings::parse_datetime( $settings['start'] );
		$end   = Maintenance_Banner_Settings::parse_datetime( $settings['end'] );

		if ( $start && $now < $start ) {
			return false;
		}
		if ( $end && $now >= $end ) {
			return false;
		}

		if ( is_user_logged_in() && ! empty( $settings['hidden_roles'] ) ) {
			$user = wp_get_current_user();
			if ( array_intersect( (array) $user->roles, $settings['hidden_roles'] ) ) {
				return false;
			}
		}

		self::$visible = true;
		return true;
	}

	/**
	 * Pick a readable text colour for a hex background.
	 *
	 * @param string $hex Hex colour.
	 * @return string
	 */
	private static function text_color( $hex ) {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		$luminance = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;

		return $luminance > 0.6 ? '#1e1e1e' : '#ffffff';
	}

	/**
	 * Enqueue banner styles only when needed.
	 */
	public static function enqueue() {
		if ( ! self::should_display() ) {
			return;
		}

		$settings = Maintenance_Banner_Settings::get();
		$bg       = sanitize_hex_color( $settings['background_color'] );
		if ( ! $bg ) {
			$bg = Maintenance_Banner_Settings::defaults()['background_color'];
		}

		wp_enqueue_style(
			'maintenance-banner',
			MAINTENANCE_BANNER_URL . 'assets/css/banner.css',
			array(),
			MAINTENANCE_BANNER_VERSION
		);

		wp_add_inline_style(
			'maintenance-banner',
			sprintf(
				'.maintenance-banner{background-color:%1$s;color:%2$s;}',
				$bg,
				self::text_color( $bg )
			)
		);
	}

	/**
	 * Output the banner markup.
	 */
	public static function render() {
		if ( ! self::should_display() ) {
			return;
		}

		$settings = Maintenance_Banner_Settings::get();
		?>
		<div class="maintenance-banner" role="region" aria-label="<?php esc_attr_e( 'Site notice', 'maintenance-banner' ); ?>">
			<div class="maintenance-banner__message">
				<?php echo wp_kses( $settings['message'], Maintenance_Banner_Settings::allowed_html() ); ?>
			</div>
		</div>
		<?php
	}
}
