<?php
/**
 * Plugin Name:       Note Box
 * Description:       A simple static block that displays a note entered in the block sidebar.
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            Seth
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       note-box
 *
 * @package NoteBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from the built block.json.
 */
function note_box_block_init() {
	register_block_type( __DIR__ . '/build/note' );
}
add_action( 'init', 'note_box_block_init' );
