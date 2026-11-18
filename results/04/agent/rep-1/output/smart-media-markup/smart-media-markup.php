<?php
/**
 * Plugin Name:       Smart Media Markup
 * Description:       Improves the front-end HTML of the core Image, Gallery and Cover blocks without changing saved post content.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Smart Media Markup Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smart-media-markup
 *
 * @package SmartMediaMarkup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SMART_MEDIA_MARKUP_FILE', __FILE__ );
define( 'SMART_MEDIA_MARKUP_DIR', plugin_dir_path( __FILE__ ) );

require_once SMART_MEDIA_MARKUP_DIR . 'includes/class-smart-media-markup.php';

Smart_Media_Markup::init();
