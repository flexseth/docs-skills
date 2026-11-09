<?php
/**
 * Plugin Name:       Maintenance Banner
 * Description:       Displays a configurable, scheduled maintenance banner at the top of the front end, editable from Settings or the block editor sidebar.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Seth
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       maintenance-banner
 * Domain Path:       /languages
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAINTENANCE_BANNER_VERSION', '1.0.0' );
define( 'MAINTENANCE_BANNER_FILE', __FILE__ );
define( 'MAINTENANCE_BANNER_PATH', plugin_dir_path( __FILE__ ) );
define( 'MAINTENANCE_BANNER_URL', plugin_dir_url( __FILE__ ) );

require_once MAINTENANCE_BANNER_PATH . 'includes/class-maintenance-banner-options.php';
require_once MAINTENANCE_BANNER_PATH . 'includes/class-maintenance-banner-settings-page.php';
require_once MAINTENANCE_BANNER_PATH . 'includes/class-maintenance-banner-editor.php';
require_once MAINTENANCE_BANNER_PATH . 'includes/class-maintenance-banner-frontend.php';

Maintenance_Banner_Options::init();
Maintenance_Banner_Settings_Page::init();
Maintenance_Banner_Editor::init();
Maintenance_Banner_Frontend::init();
