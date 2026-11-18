<?php
/**
 * Plugin Name:       Smart Media Markup
 * Description:       Improves the front-end HTML of the core Image, Gallery and Cover blocks: async image decoding, safe external links, a marker class and optional caption-as-alt-text. Saved post content is never modified.
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

defined( 'ABSPATH' ) || exit;

define( 'SMART_MEDIA_MARKUP_VERSION', '1.0.0' );
define( 'SMART_MEDIA_MARKUP_FILE', __FILE__ );
define( 'SMART_MEDIA_MARKUP_DIR', plugin_dir_path( __FILE__ ) );

require_once SMART_MEDIA_MARKUP_DIR . 'includes/class-markup-processor.php';
require_once SMART_MEDIA_MARKUP_DIR . 'includes/class-plugin.php';

\Smart_Media_Markup\Plugin::init();
