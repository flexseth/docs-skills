<?php
/**
 * Plugin Name:       Team Directory
 * Description:       Manage team members in the block editor, expose them through a public REST endpoint and list them with a block.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Team Directory Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       team-directory
 *
 * @package TeamDirectory
 */

namespace Team_Directory;

defined( 'ABSPATH' ) || exit;

const VERSION         = '1.0.0';
const POST_TYPE       = 'td_member';
const ROLE_META       = 'td_role';
const START_YEAR_META = 'td_start_year';
const MAX_PER_PAGE    = 50;
const ROLE_MAX_LENGTH = 100;

define( 'TEAM_DIRECTORY_DIR', plugin_dir_path( __FILE__ ) );
define( 'TEAM_DIRECTORY_URL', plugin_dir_url( __FILE__ ) );

require_once TEAM_DIRECTORY_DIR . 'includes/post-type.php';
require_once TEAM_DIRECTORY_DIR . 'includes/members.php';
require_once TEAM_DIRECTORY_DIR . 'includes/class-rest-controller.php';
require_once TEAM_DIRECTORY_DIR . 'includes/editor.php';

add_action( 'init', __NAMESPACE__ . '\\register_post_type_and_meta' );
add_action( 'init', __NAMESPACE__ . '\\register_blocks' );
add_action( 'rest_api_init', array( new REST_Controller(), 'register_routes' ) );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_member_sidebar' );
add_filter( 'enter_title_here', __NAMESPACE__ . '\\title_placeholder', 10, 2 );

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Register the post type on activation and flush rewrite rules.
 *
 * @return void
 */
function activate() {
	register_post_type_and_meta();
	flush_rewrite_rules();
}

/**
 * Register blocks from the build directory.
 *
 * @return void
 */
function register_blocks() {
	register_block_type( TEAM_DIRECTORY_DIR . 'build/team-list' );
}
