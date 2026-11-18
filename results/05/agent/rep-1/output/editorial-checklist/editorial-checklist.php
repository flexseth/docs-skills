<?php
/**
 * Plugin Name:       Editorial Checklist
 * Description:       Adds a pre-publish editorial checklist to the block editor. Required items must be checked before a post can be saved or published.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Editorial Checklist Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       editorial-checklist
 * Domain Path:       /languages
 *
 * @package EditorialChecklist
 */

defined( 'ABSPATH' ) || exit;

define( 'EDITORIAL_CHECKLIST_VERSION', '1.0.0' );
define( 'EDITORIAL_CHECKLIST_FILE', __FILE__ );
define( 'EDITORIAL_CHECKLIST_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDITORIAL_CHECKLIST_URL', plugin_dir_url( __FILE__ ) );
define( 'EDITORIAL_CHECKLIST_META_KEY', '_editorial_checklist_checked' );
define( 'EDITORIAL_CHECKLIST_REST_NAMESPACE', 'editorial-checklist/v1' );

require_once EDITORIAL_CHECKLIST_DIR . 'includes/functions.php';
require_once EDITORIAL_CHECKLIST_DIR . 'includes/class-editorial-checklist-rest-controller.php';
require_once EDITORIAL_CHECKLIST_DIR . 'includes/hooks.php';
