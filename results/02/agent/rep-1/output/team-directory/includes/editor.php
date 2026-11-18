<?php
/**
 * Editor assets for the team member post type.
 *
 * @package TeamDirectory
 */

namespace Team_Directory;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the document sidebar panel (role and start year) when editing a team member.
 *
 * @return void
 */
function enqueue_member_sidebar() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || POST_TYPE !== $screen->post_type ) {
		return;
	}

	$asset_file = TEAM_DIRECTORY_DIR . 'build/member-sidebar/index.asset.php';
	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'team-directory-member-sidebar',
		TEAM_DIRECTORY_URL . 'build/member-sidebar/index.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_add_inline_script(
		'team-directory-member-sidebar',
		'window.teamDirectoryMember = ' . wp_json_encode(
			array(
				'minYear' => min_start_year(),
				'maxYear' => max_start_year(),
			)
		) . ';',
		'before'
	);

	wp_set_script_translations( 'team-directory-member-sidebar', 'team-directory' );
}
