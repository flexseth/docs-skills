<?php
/**
 * Plugin Name:       Maintenance Banner
 * Description:       Displays a scheduled maintenance banner on the front end, configurable from Settings or the block editor sidebar.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Maintenance Banner Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       maintenance-banner
 *
 * @package MaintenanceBanner
 */

namespace MaintenanceBanner;

defined( 'ABSPATH' ) || exit;

define( 'MAINTENANCE_BANNER_VERSION', '1.0.0' );
define( 'MAINTENANCE_BANNER_FILE', __FILE__ );
define( 'MAINTENANCE_BANNER_PATH', plugin_dir_path( __FILE__ ) );
define( 'MAINTENANCE_BANNER_OPTION', 'maintenance_banner_options' );

require_once MAINTENANCE_BANNER_PATH . 'includes/options.php';
require_once MAINTENANCE_BANNER_PATH . 'includes/settings.php';
require_once MAINTENANCE_BANNER_PATH . 'includes/editor.php';
require_once MAINTENANCE_BANNER_PATH . 'includes/frontend.php';
