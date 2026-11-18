<?php
/**
 * Settings > Maintenance Banner page.
 *
 * @package MaintenanceBanner
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the settings page using the Settings API.
 */
final class Maintenance_Banner_Admin {

	/**
	 * Page slug.
	 */
	const PAGE = 'maintenance-banner';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'add_fields' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( MAINTENANCE_BANNER_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Add the page under Settings.
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
		$section = 'maintenance_banner_main';

		add_settings_section( $section, '', '__return_false', self::PAGE );

		$fields = array(
			'enabled'          => __( 'Banner status', 'maintenance-banner' ),
			'message'          => __( 'Message', 'maintenance-banner' ),
			'background_color' => __( 'Background colour', 'maintenance-banner' ),
			'start'            => __( 'Start', 'maintenance-banner' ),
			'end'              => __( 'End', 'maintenance-banner' ),
			'hidden_roles'     => __( 'Hide for roles', 'maintenance-banner' ),
		);

		foreach ( $fields as $key => $label ) {
			$args = array( 'key' => $key );
			if ( in_array( $key, array( 'enabled', 'background_color', 'start', 'end' ), true ) ) {
				$args['label_for'] = 'maintenance-banner-' . str_replace( '_', '-', $key );
			}
			add_settings_field(
				'maintenance_banner_' . $key,
				$label,
				array( __CLASS__, 'render_field' ),
				self::PAGE,
				$section,
				$args
			);
		}
	}

	/**
	 * Input name for a key.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	private static function name( $key ) {
		return Maintenance_Banner_Settings::OPTION . '[' . $key . ']';
	}

	/**
	 * Render a single field.
	 *
	 * @param array $args Field args.
	 */
	public static function render_field( $args ) {
		$settings = Maintenance_Banner_Settings::get();
		$key      = $args['key'];

		switch ( $key ) {
			case 'enabled':
				?>
				<input type="hidden" name="<?php echo esc_attr( self::name( 'enabled' ) ); ?>" value="0" />
				<label>
					<input type="checkbox" id="maintenance-banner-enabled" name="<?php echo esc_attr( self::name( 'enabled' ) ); ?>" value="1" <?php checked( $settings['enabled'] ); ?> />
					<?php esc_html_e( 'Show the banner', 'maintenance-banner' ); ?>
				</label>
				<?php
				break;

			case 'message':
				wp_editor(
					$settings['message'],
					'maintenance-banner-message',
					array(
						'textarea_name' => self::name( 'message' ),
						'textarea_rows' => 4,
						'media_buttons' => false,
						'teeny'         => true,
						'wpautop'       => false,
						'tinymce'       => array(
							'toolbar1' => 'bold,italic,link,unlink,undo,redo',
							'toolbar2' => '',
						),
						'quicktags'     => array(
							'buttons' => 'strong,em,link',
						),
					)
				);
				?>
				<p class="description"><?php esc_html_e( 'Bold, italic and links are allowed. Other HTML is removed.', 'maintenance-banner' ); ?></p>
				<?php
				break;

			case 'background_color':
				?>
				<input type="color" id="maintenance-banner-background-color" name="<?php echo esc_attr( self::name( 'background_color' ) ); ?>" value="<?php echo esc_attr( $settings['background_color'] ); ?>" />
				<?php
				break;

			case 'start':
			case 'end':
				?>
				<input type="datetime-local" id="<?php echo esc_attr( 'maintenance-banner-' . $key ); ?>" name="<?php echo esc_attr( self::name( $key ) ); ?>" value="<?php echo esc_attr( $settings[ $key ] ); ?>" />
				<p class="description">
					<?php
					printf(
						/* translators: %s: site timezone, e.g. "Europe/London" or "+02:00". */
						esc_html__( 'Site timezone: %s. Leave empty for no limit.', 'maintenance-banner' ),
						'<code>' . esc_html( wp_timezone_string() ) . '</code>'
					);
					?>
				</p>
				<?php
				break;

			case 'hidden_roles':
				?>
				<fieldset>
					<legend class="screen-reader-text"><?php esc_html_e( 'Hide for roles', 'maintenance-banner' ); ?></legend>
					<input type="hidden" name="<?php echo esc_attr( self::name( 'hidden_roles' ) ); ?>[]" value="" />
					<?php foreach ( wp_roles()->get_names() as $role => $role_name ) : ?>
						<label style="display:block;">
							<input type="checkbox" name="<?php echo esc_attr( self::name( 'hidden_roles' ) ); ?>[]" value="<?php echo esc_attr( $role ); ?>" <?php checked( in_array( $role, $settings['hidden_roles'], true ) ); ?> />
							<?php echo esc_html( translate_user_role( $role_name ) ); ?>
						</label>
					<?php endforeach; ?>
				</fieldset>
				<p class="description"><?php esc_html_e( 'Logged-in users with any of these roles will not see the banner.', 'maintenance-banner' ); ?></p>
				<?php
				break;
		}
	}

	/**
	 * Render the page. Nonce and capability checks are handled by options.php via settings_fields().
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
				<?php
				settings_fields( Maintenance_Banner_Settings::GROUP );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
