<?php
/**
 * Settings page (Settings → Maintenance Banner).
 *
 * @package MaintenanceBanner
 */

namespace MaintenanceBanner;

defined( 'ABSPATH' ) || exit;

const PAGE_SLUG = 'maintenance-banner';

/**
 * Add the options page.
 */
function add_settings_page() {
	add_options_page(
		__( 'Maintenance Banner', 'maintenance-banner' ),
		__( 'Maintenance Banner', 'maintenance-banner' ),
		'manage_options',
		PAGE_SLUG,
		__NAMESPACE__ . '\render_settings_page'
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\add_settings_page' );

/**
 * Register the section and fields.
 */
function register_settings_fields() {
	$section = 'maintenance_banner_main';

	add_settings_section(
		$section,
		__( 'Banner settings', 'maintenance-banner' ),
		__NAMESPACE__ . '\render_section_intro',
		PAGE_SLUG
	);

	$fields = array(
		'enabled'          => array( __( 'Enable banner', 'maintenance-banner' ), 'render_enabled_field' ),
		'message'          => array( __( 'Message', 'maintenance-banner' ), 'render_message_field' ),
		'background_color' => array( __( 'Background colour', 'maintenance-banner' ), 'render_color_field' ),
		'start'            => array( __( 'Start', 'maintenance-banner' ), 'render_datetime_field' ),
		'end'              => array( __( 'End', 'maintenance-banner' ), 'render_datetime_field' ),
		'hidden_roles'     => array( __( 'Hide banner for roles', 'maintenance-banner' ), 'render_roles_field' ),
	);

	foreach ( $fields as $key => $field ) {
		$args = array( 'key' => $key );

		// Fieldsets provide their own legend; other fields get a <label for>.
		if ( 'hidden_roles' !== $key ) {
			$args['label_for'] = 'maintenance-banner-' . $key;
		}

		add_settings_field(
			'maintenance_banner_' . $key,
			$field[0],
			__NAMESPACE__ . '\\' . $field[1],
			PAGE_SLUG,
			$section,
			$args
		);
	}
}
add_action( 'admin_init', __NAMESPACE__ . '\register_settings_fields' );

/**
 * Build a field name attribute.
 *
 * @param string $key Option key.
 * @return string
 */
function field_name( $key ) {
	return MAINTENANCE_BANNER_OPTION . '[' . $key . ']';
}

/**
 * Section intro.
 */
function render_section_intro() {
	printf(
		'<p>%s</p>',
		esc_html(
			sprintf(
				/* translators: %s: site timezone string. */
				__( 'Times are in the site timezone (%s). Leave start or end empty for no limit.', 'maintenance-banner' ),
				wp_timezone_string()
			)
		)
	);
}

/**
 * Enabled checkbox.
 *
 * @param array $args Field args.
 */
function render_enabled_field( $args ) {
	$options = get_options();
	printf(
		'<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
		esc_attr( $args['label_for'] ),
		esc_attr( field_name( 'enabled' ) ),
		checked( (bool) $options['enabled'], true, false ),
		esc_html__( 'Show the maintenance banner on the front end', 'maintenance-banner' )
	);
}

/**
 * Message textarea.
 *
 * @param array $args Field args.
 */
function render_message_field( $args ) {
	$options = get_options();
	$tags    = '<' . implode( '>, <', array_keys( get_allowed_html() ) ) . '>';

	printf(
		'<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>',
		esc_attr( $args['label_for'] ),
		esc_attr( field_name( 'message' ) ),
		esc_textarea( $options['message'] )
	);
	printf(
		'<p class="description">%s</p>',
		esc_html(
			sprintf(
				/* translators: %s: list of allowed HTML tags. */
				__( 'Allowed HTML: %s', 'maintenance-banner' ),
				$tags
			)
		)
	);
}

/**
 * Background colour input.
 *
 * @param array $args Field args.
 */
function render_color_field( $args ) {
	$options = get_options();
	printf(
		'<input type="color" id="%1$s" name="%2$s" value="%3$s" />',
		esc_attr( $args['label_for'] ),
		esc_attr( field_name( 'background_color' ) ),
		esc_attr( $options['background_color'] )
	);
}

/**
 * Datetime input (start / end).
 *
 * @param array $args Field args.
 */
function render_datetime_field( $args ) {
	$options = get_options();
	$key     = $args['key'];
	printf(
		'<input type="datetime-local" id="%1$s" name="%2$s" value="%3$s" />',
		esc_attr( $args['label_for'] ),
		esc_attr( field_name( $key ) ),
		esc_attr( $options[ $key ] )
	);
}

/**
 * Roles multi-checkbox.
 */
function render_roles_field() {
	$options = get_options();
	$hidden  = (array) $options['hidden_roles'];
	$roles   = wp_roles()->get_names();

	echo '<fieldset><legend class="screen-reader-text">' . esc_html__( 'Hide banner for roles', 'maintenance-banner' ) . '</legend>';

	foreach ( $roles as $role => $name ) {
		printf(
			'<label><input type="checkbox" name="%1$s[]" value="%2$s" %3$s /> %4$s</label><br />',
			esc_attr( field_name( 'hidden_roles' ) ),
			esc_attr( $role ),
			checked( in_array( $role, $hidden, true ), true, false ),
			esc_html( translate_user_role( $name ) )
		);
	}

	echo '<p class="description">' . esc_html__( 'Logged-in users with any of the selected roles will not see the banner.', 'maintenance-banner' ) . '</p>';
	echo '</fieldset>';
}

/**
 * Render the settings page.
 *
 * Settings errors are printed automatically by options-head.php for pages
 * under Settings, so settings_errors() is not called here to avoid duplicates.
 */
function render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'maintenance_banner' );
			do_settings_sections( PAGE_SLUG );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
