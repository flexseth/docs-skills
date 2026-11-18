<?php
/**
 * Plugin Name:       Maintenance Banner
 * Description:       Displays a configurable, scheduled site-wide banner at the top of every front-end page.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAINTENANCE_BANNER_VERSION', '1.0.0' );
define( 'MAINTENANCE_BANNER_FILE', __FILE__ );
define( 'MAINTENANCE_BANNER_DIR', plugin_dir_path( __FILE__ ) );
define( 'MAINTENANCE_BANNER_URL', plugin_dir_url( __FILE__ ) );

/** Option name used in the database. */
define( 'MAINTENANCE_BANNER_OPTION', 'maintenance_banner_settings' );

/** Settings group / page slug. */
define( 'MAINTENANCE_BANNER_GROUP', 'maintenance_banner' );

/** Name of the setting in the /wp/v2/settings REST endpoint. */
define( 'MAINTENANCE_BANNER_REST_NAME', 'maintenance_banner' );

/** Storage format of the start/end values (matches <input type="datetime-local">). */
define( 'MAINTENANCE_BANNER_DATE_FORMAT', 'Y-m-d\TH:i' );

require_once MAINTENANCE_BANNER_DIR . 'includes/settings.php';
require_once MAINTENANCE_BANNER_DIR . 'includes/admin-page.php';
require_once MAINTENANCE_BANNER_DIR . 'includes/rest.php';
require_once MAINTENANCE_BANNER_DIR . 'includes/editor.php';
require_once MAINTENANCE_BANNER_DIR . 'includes/frontend.php';
