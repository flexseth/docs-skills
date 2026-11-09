<?php
/**
 * Settings → Maintenance Banner admin page (Settings API).
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin settings page.
 */
final class Maintenance_Banner_Settings_Page {

	/**
	 * Menu / page slug.
	 *
	 * @var string
	 */
	const PAGE = 'maintenance-banner';

	/**
	 * Section ID.
	 *
	 * @var string
	 */
	const SECTION = 'maintenance_banner_main';

	/**
	 * Hook into WordPress.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'add_fields' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( MAINTENANCE_BANNER_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Register the options page.
	 */
	public static function add_page() {
		add_options_page(
			__( 'Maintenance Banner', 'maintenance-banner' ),
			__( 'Maintenance Banner', 'maintenance-banner' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Add a "Settings" link on the Plugins screen.
	 *
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=' . self::PAGE );

		array_unshift(
			$links,
			sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Settings', 'maintenance-banner' ) )
		);

		return $links;
	}

	/**
	 * Register section and fields.
	 */
	public static function add_fields() {
		add_settings_section(
			self::SECTION,
			__( 'Banner settings', 'maintenance-banner' ),
			array( __CLASS__, 'render_section' ),
			self::PAGE
		);

		$fields = array(
			'enabled'          => array( __( 'Enable banner', 'maintenance-banner' ), 'render_enabled', true ),
			'message'          => array( __( 'Message', 'maintenance-banner' ), 'render_message', true ),
			'background_color' => array( __( 'Background colour', 'maintenance-banner' ), 'render_background_color', true ),
			'start_datetime'   => array( __( 'Start', 'maintenance-banner' ), 'render_start', true ),
			'end_datetime'     => array( __( 'End', 'maintenance-banner' ), 'render_end', true ),
			'hidden_roles'     => array( __( 'Hide banner for roles', 'maintenance-banner' ), 'render_hidden_roles', false ),
		);

		foreach ( $fields as $key => $field ) {
			$args = array();
			if ( $field[2] ) {
				$args['label_for'] = self::field_id( $key );
			}

			add_settings_field(
				'maintenance_banner_' . $key,
				$field[0],
				array( __CLASS__, $field[1] ),
				self::PAGE,
				self::SECTION,
				$args
			);
		}
	}

	/**
	 * HTML id for a field.
	 *
	 * @param string $key Option key.
	 * @return string
	 */
	private static function field_id( $key ) {
		return 'maintenance-banner-' . str_replace( '_', '-', $key );
	}

	/**
	 * HTML name for a field.
	 *
	 * @param string $key Option key.
	 * @return string
	 */
	private static function field_name( $key ) {
		return Maintenance_Banner_Options::OPTION . '[' . $key . ']';
	}

	/**
	 * Render the page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Settings errors / "Settings saved." are printed automatically by
		// wp-admin/options-head.php for pages under Settings.
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( Maintenance_Banner_Options::GROUP );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Section intro.
	 */
	public static function render_section() {
		printf(
			'<p>%s</p>',
			esc_html(
				sprintf(
					/* translators: %s: site timezone, e.g. "Europe/London" or "UTC+2". */
					__( 'Show a notice at the top of every front-end page. Times use the site timezone (%s).', 'maintenance-banner' ),
					wp_timezone_string()
				)
			)
		);
	}

	/**
	 * Enabled checkbox.
	 */
	public static function render_enabled() {
		$options = Maintenance_Banner_Options::get();
		?>
		<input
			type="checkbox"
			id="<?php echo esc_attr( self::field_id( 'enabled' ) ); ?>"
			name="<?php echo esc_attr( self::field_name( 'enabled' ) ); ?>"
			value="1"
			<?php checked( ! empty( $options['enabled'] ) ); ?>
		/>
		<span class="description"><?php esc_html_e( 'Display the banner on the front end.', 'maintenance-banner' ); ?></span>
		<?php
	}

	/**
	 * Message textarea.
	 */
	public static function render_message() {
		$options = Maintenance_Banner_Options::get();
		$tags    = array_map(
			static function ( $tag ) {
				return '<' . $tag . '>';
			},
			array_keys( Maintenance_Banner_Options::allowed_html() )
		);
		?>
		<textarea
			id="<?php echo esc_attr( self::field_id( 'message' ) ); ?>"
			name="<?php echo esc_attr( self::field_name( 'message' ) ); ?>"
			rows="4"
			class="large-text"
			aria-describedby="<?php echo esc_attr( self::field_id( 'message' ) ); ?>-description"
		><?php echo esc_textarea( $options['message'] ); ?></textarea>
		<p class="description" id="<?php echo esc_attr( self::field_id( 'message' ) ); ?>-description">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: comma-separated list of allowed HTML tags. */
					__( 'Allowed HTML: %s', 'maintenance-banner' ),
					implode( ', ', $tags )
				)
			);
			?>
		</p>
		<?php
	}

	/**
	 * Background colour input.
	 */
	public static function render_background_color() {
		$options = Maintenance_Banner_Options::get();
		$color   = sanitize_hex_color( $options['background_color'] );

		// <input type="color"> only accepts the 6-digit form.
		if ( $color && 4 === strlen( $color ) ) {
			$color = '#' . $color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3];
		}
		?>
		<input
			type="color"
			id="<?php echo esc_attr( self::field_id( 'background_color' ) ); ?>"
			name="<?php echo esc_attr( self::field_name( 'background_color' ) ); ?>"
			value="<?php echo esc_attr( $color ? $color : '#ffcc00' ); ?>"
		/>
		<?php
	}

	/**
	 * Start datetime.
	 */
	public static function render_start() {
		self::render_datetime( 'start_datetime' );
	}

	/**
	 * End datetime.
	 */
	public static function render_end() {
		self::render_datetime( 'end_datetime' );
	}

	/**
	 * Shared datetime-local input.
	 *
	 * @param string $key Option key.
	 */
	private static function render_datetime( $key ) {
		$options = Maintenance_Banner_Options::get();
		?>
		<input
			type="datetime-local"
			id="<?php echo esc_attr( self::field_id( $key ) ); ?>"
			name="<?php echo esc_attr( self::field_name( $key ) ); ?>"
			value="<?php echo esc_attr( $options[ $key ] ); ?>"
		/>
		<p class="description">
			<?php
			if ( 'start_datetime' === $key ) {
				esc_html_e( 'Leave empty to show the banner immediately.', 'maintenance-banner' );
			} else {
				esc_html_e( 'Leave empty to show the banner until it is disabled.', 'maintenance-banner' );
			}
			?>
		</p>
		<?php
	}

	/**
	 * Hidden roles multi-checkbox.
	 */
	public static function render_hidden_roles() {
		$options = Maintenance_Banner_Options::get();
		$hidden  = (array) $options['hidden_roles'];
		?>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Hide banner for roles', 'maintenance-banner' ); ?></legend>
			<?php foreach ( wp_roles()->get_names() as $role => $name ) : ?>
				<label>
					<input
						type="checkbox"
						name="<?php echo esc_attr( self::field_name( 'hidden_roles' ) ); ?>[]"
						value="<?php echo esc_attr( $role ); ?>"
						<?php checked( in_array( $role, $hidden, true ) ); ?>
					/>
					<?php echo esc_html( translate_user_role( $name ) ); ?>
				</label><br />
			<?php endforeach; ?>
			<p class="description"><?php esc_html_e( 'Logged-in users with any checked role will not see the banner. Visitors who are not logged in always see it.', 'maintenance-banner' ); ?></p>
		</fieldset>
		<?php
	}
}
