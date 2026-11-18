<?php
/**
 * Plugin Name:       Maintenance Banner
 * Description:       Displays a configurable, scheduled, site-wide banner at the top of every front-end page.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Maintenance Banner Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       maintenance-banner
 * Domain Path:       /languages
 *
 * @package MaintenanceBanner
 */

defined( 'ABSPATH' ) || exit;

define( 'MAINTENANCE_BANNER_VERSION', '1.0.0' );
define( 'MAINTENANCE_BANNER_FILE', __FILE__ );
define( 'MAINTENANCE_BANNER_DIR', plugin_dir_path( __FILE__ ) );
define( 'MAINTENANCE_BANNER_URL', plugin_dir_url( __FILE__ ) );

require_once MAINTENANCE_BANNER_DIR . 'includes/class-maintenance-banner-settings.php';
require_once MAINTENANCE_BANNER_DIR . 'includes/class-maintenance-banner-admin.php';
require_once MAINTENANCE_BANNER_DIR . 'includes/class-maintenance-banner-editor.php';
require_once MAINTENANCE_BANNER_DIR . 'includes/class-maintenance-banner-frontend.php';

Maintenance_Banner_Settings::init();
Maintenance_Banner_Admin::init();
Maintenance_Banner_Editor::init();
Maintenance_Banner_Frontend::init();
