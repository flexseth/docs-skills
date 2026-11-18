<?php
/**
 * Settings > Maintenance Banner page.
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the page under the Settings menu.
 */
function maintenance_banner_add_settings_page() {
	add_options_page(
		__( 'Maintenance Banner', 'maintenance-banner' ),
		__( 'Maintenance Banner', 'maintenance-banner' ),
		'manage_options',
		MAINTENANCE_BANNER_GROUP,
		'maintenance_banner_render_settings_page'
	);
}
add_action( 'admin_menu', 'maintenance_banner_add_settings_page' );

/**
 * Registers the settings section and fields.
 */
function maintenance_banner_register_fields() {
	$page = MAINTENANCE_BANNER_GROUP;

	add_settings_section(
		'maintenance_banner_main',
		'',
		'__return_false',
		$page
	);

	$fields = array(
		'enabled'          => __( 'Banner', 'maintenance-banner' ),
		'message'          => __( 'Message', 'maintenance-banner' ),
		'background_color' => __( 'Background colour', 'maintenance-banner' ),
		'start'            => __( 'Start', 'maintenance-banner' ),
		'end'              => __( 'End', 'maintenance-banner' ),
		'hidden_roles'     => __( 'Hide from roles', 'maintenance-banner' ),
	);

	foreach ( $fields as $key => $label ) {
		$args = array( 'key' => $key );

		if ( in_array( $key, array( 'message', 'background_color', 'start', 'end' ), true ) ) {
			$args['label_for'] = 'maintenance-banner-' . str_replace( '_', '-', $key );
		}

		add_settings_field(
			'maintenance_banner_' . $key,
			$label,
			'maintenance_banner_render_field',
			$page,
			'maintenance_banner_main',
			$args
		);
	}
}
add_action( 'admin_init', 'maintenance_banner_register_fields' );

/**
 * Renders a single field.
 *
 * @param array $args Field arguments.
 */
function maintenance_banner_render_field( $args ) {
	$settings = maintenance_banner_get_settings();
	$key      = $args['key'];
	$name     = MAINTENANCE_BANNER_OPTION . '[' . $key . ']';
	$id       = 'maintenance-banner-' . str_replace( '_', '-', $key );

	switch ( $key ) {
		case 'enabled':
			printf(
				'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( $settings['enabled'], true, false ),
				esc_html__( 'Show the banner', 'maintenance-banner' )
			);
			break;

		case 'message':
			wp_editor(
				$settings['message'],
				$id,
				array(
					'textarea_name' => $name,
					'textarea_rows' => 4,
					'media_buttons' => false,
					'tinymce'       => false,
					'quicktags'     => array( 'buttons' => 'strong,em,link' ),
				)
			);
			echo '<p class="description">' . esc_html__( 'Allowed formatting: bold, italic, links and line breaks.', 'maintenance-banner' ) . '</p>';
			break;

		case 'background_color':
			printf(
				'<input type="color" id="%1$s" name="%2$s" value="%3$s" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $settings['background_color'] )
			);
			break;

		case 'start':
		case 'end':
			printf(
				'<input type="datetime-local" id="%1$s" name="%2$s" value="%3$s" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $settings[ $key ] )
			);
			echo '<p class="description">';
			printf(
				/* translators: %s: site timezone, e.g. "Europe/London" or "+02:00". */
				esc_html__( 'Site timezone: %s. Leave empty for no limit.', 'maintenance-banner' ),
				esc_html( wp_timezone_string() )
			);
			echo '</p>';
			break;

		case 'hidden_roles':
			echo '<fieldset><legend class="screen-reader-text">' . esc_html__( 'Hide from roles', 'maintenance-banner' ) . '</legend>';
			foreach ( wp_roles()->get_names() as $role => $role_name ) {
				printf(
					'<label><input type="checkbox" name="%1$s[]" value="%2$s" %3$s /> %4$s</label><br />',
					esc_attr( $name ),
					esc_attr( $role ),
					checked( in_array( $role, $settings['hidden_roles'], true ), true, false ),
					esc_html( translate_user_role( $role_name ) )
				);
			}
			echo '<p class="description">' . esc_html__( 'Logged-in users with any of these roles will not see the banner.', 'maintenance-banner' ) . '</p>';
			echo '</fieldset>';
			break;
	}
}

/**
 * Renders the settings page.
 *
 * Settings errors are printed automatically by options-head.php for pages under Settings.
 */
function maintenance_banner_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
			<?php
			settings_fields( MAINTENANCE_BANNER_GROUP );
			do_settings_sections( MAINTENANCE_BANNER_GROUP );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Adds a "Settings" link on the Plugins screen.
 *
 * @param array $links Action links.
 * @return array
 */
function maintenance_banner_action_links( $links ) {
	$links[] = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'options-general.php?page=' . MAINTENANCE_BANNER_GROUP ) ),
		esc_html__( 'Settings', 'maintenance-banner' )
	);

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( MAINTENANCE_BANNER_FILE ), 'maintenance_banner_action_links' );
