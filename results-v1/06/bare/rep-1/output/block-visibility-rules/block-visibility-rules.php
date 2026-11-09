<?php
/**
 * Plugin Name:       Block Visibility Rules
 * Description:       Adds a "Visibility" setting to every block: show it to everyone, logged-in users, logged-out visitors, or only within a date range.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Block Visibility Rules contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block-visibility-rules
 *
 * @package BlockVisibilityRules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BLOCK_VISIBILITY_RULES_VERSION', '1.0.0' );
define( 'BLOCK_VISIBILITY_RULES_FILE', __FILE__ );
define( 'BLOCK_VISIBILITY_RULES_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Blocks that never receive the visibility attribute.
 *
 * @return string[] Block names.
 */
function block_visibility_rules_excluded_blocks() {
	/**
	 * Filters the list of block names that are excluded from visibility rules.
	 *
	 * @param string[] $blocks Excluded block names.
	 */
	return (array) apply_filters( 'block_visibility_rules_excluded_blocks', array( 'core/freeform' ) );
}

/**
 * Registers the `visibilityRule` attribute on every eligible server-side block
 * so it is validated by WP_Block_Type::prepare_attributes_for_render().
 *
 * @param array  $args       Block type registration arguments.
 * @param string $block_type Block type name.
 * @return array Filtered arguments.
 */
function block_visibility_rules_register_attribute( $args, $block_type ) {
	if ( in_array( $block_type, block_visibility_rules_excluded_blocks(), true ) ) {
		return $args;
	}

	// Mirror the JS check: skip blocks that opt out of custom class names.
	if (
		isset( $args['supports'] ) &&
		is_array( $args['supports'] ) &&
		array_key_exists( 'customClassName', $args['supports'] ) &&
		false === $args['supports']['customClassName']
	) {
		return $args;
	}

	if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}

	$args['attributes']['visibilityRule'] = array(
		'type'       => 'object',
		'properties' => array(
			// Not limited to an enum: other plugins may add rule types.
			'type'  => array(
				'type' => 'string',
			),
			'start' => array(
				'type' => array( 'string', 'null' ),
			),
			'end'   => array(
				'type' => array( 'string', 'null' ),
			),
		),
	);

	return $args;
}
add_filter( 'register_block_type_args', 'block_visibility_rules_register_attribute', 10, 2 );

/**
 * Normalizes a raw rule array from block attributes.
 *
 * @param mixed $rule Raw rule.
 * @return array{type:string,start:string,end:string}|null Sanitized rule, or null when absent/invalid.
 */
function block_visibility_rules_sanitize_rule( $rule ) {
	if ( ! is_array( $rule ) ) {
		return null;
	}

	$sanitized = $rule;

	$sanitized['type']  = isset( $rule['type'] ) && is_string( $rule['type'] ) ? sanitize_key( $rule['type'] ) : 'all';
	$sanitized['start'] = isset( $rule['start'] ) && is_string( $rule['start'] ) ? sanitize_text_field( $rule['start'] ) : '';
	$sanitized['end']   = isset( $rule['end'] ) && is_string( $rule['end'] ) ? sanitize_text_field( $rule['end'] ) : '';

	if ( '' === $sanitized['type'] ) {
		$sanitized['type'] = 'all';
	}

	return $sanitized;
}

/**
 * Parses a stored date string in the site timezone.
 *
 * Values saved by the DateTimePicker have no offset (e.g. "2026-11-09T10:00:00")
 * and are interpreted as site-local time. Values with an explicit offset keep it.
 *
 * @param string $value Date string.
 * @return DateTimeImmutable|null Date, or null if empty/invalid.
 */
function block_visibility_rules_parse_date( $value ) {
	if ( '' === $value ) {
		return null;
	}

	try {
		return new DateTimeImmutable( $value, wp_timezone() );
	} catch ( Exception $e ) {
		return null;
	}
}

/**
 * Evaluates a rule against the current request.
 *
 * Unknown rule types (e.g. added via the JS `blockVisibilityRules.ruleTypes`
 * filter) match by default; use the `block_visibility_rules_match` filter
 * to implement them.
 *
 * @param array $rule Sanitized rule.
 * @return bool Whether the block should be displayed.
 */
function block_visibility_rules_evaluate( array $rule ) {
	switch ( $rule['type'] ) {
		case 'all':
			return true;

		case 'logged-in':
			return is_user_logged_in();

		case 'logged-out':
			return ! is_user_logged_in();

		case 'date-range':
			$now   = current_datetime();
			$start = block_visibility_rules_parse_date( $rule['start'] );
			$end   = block_visibility_rules_parse_date( $rule['end'] );

			if ( $start && $now < $start ) {
				return false;
			}

			if ( $end && $now > $end ) {
				return false;
			}

			return true;

		default:
			return true;
	}
}

/**
 * Hides blocks whose visibility rule does not match the current request.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Parsed block.
 * @return string Block content, or an empty string when hidden.
 */
function block_visibility_rules_render_block( $block_content, $block ) {
	// Never hide content inside wp-admin (e.g. previews rendered in the editor).
	if ( is_admin() ) {
		return $block_content;
	}

	if ( empty( $block['blockName'] ) || empty( $block['attrs']['visibilityRule'] ) ) {
		return $block_content;
	}

	if ( in_array( $block['blockName'], block_visibility_rules_excluded_blocks(), true ) ) {
		return $block_content;
	}

	$rule = block_visibility_rules_sanitize_rule( $block['attrs']['visibilityRule'] );

	if ( null === $rule ) {
		return $block_content;
	}

	$matches = block_visibility_rules_evaluate( $rule );

	/**
	 * Filters whether a block's visibility rule matches the current request.
	 *
	 * @param bool   $matches       Whether the block should be displayed.
	 * @param array  $rule          Sanitized rule: `type`, `start`, `end` plus any custom keys.
	 * @param array  $block         Parsed block.
	 * @param string $block_content Rendered block content.
	 */
	$matches = (bool) apply_filters( 'block_visibility_rules_match', $matches, $rule, $block, $block_content );

	return $matches ? $block_content : '';
}
add_filter( 'render_block', 'block_visibility_rules_render_block', 10, 2 );

/**
 * Registers the editor script and style from the generated asset file.
 *
 * @return bool True when the build exists and assets were registered.
 */
function block_visibility_rules_register_assets() {
	if ( wp_script_is( 'block-visibility-rules-editor', 'registered' ) ) {
		return true;
	}

	$asset_file = BLOCK_VISIBILITY_RULES_DIR . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return false;
	}

	$asset = include $asset_file;

	wp_register_script(
		'block-visibility-rules-editor',
		plugins_url( 'build/index.js', BLOCK_VISIBILITY_RULES_FILE ),
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_set_script_translations( 'block-visibility-rules-editor', 'block-visibility-rules' );

	if ( file_exists( BLOCK_VISIBILITY_RULES_DIR . 'build/index.css' ) ) {
		wp_register_style(
			'block-visibility-rules-editor',
			plugins_url( 'build/index.css', BLOCK_VISIBILITY_RULES_FILE ),
			array(),
			$asset['version']
		);
	}

	return true;
}

/**
 * Enqueues the editor script (and panel styles) in the block editor.
 */
function block_visibility_rules_enqueue_editor_assets() {
	if ( ! block_visibility_rules_register_assets() ) {
		return;
	}

	wp_enqueue_script( 'block-visibility-rules-editor' );
	wp_enqueue_style( 'block-visibility-rules-editor' );
}
add_action( 'enqueue_block_editor_assets', 'block_visibility_rules_enqueue_editor_assets' );

/**
 * Enqueues the outline styles inside the iframed editor canvas.
 *
 * Styles added on `enqueue_block_editor_assets` do not reach the iframe, so the
 * canvas styles are also loaded on `enqueue_block_assets` (admin only).
 */
function block_visibility_rules_enqueue_canvas_styles() {
	if ( ! is_admin() || ! block_visibility_rules_register_assets() ) {
		return;
	}

	wp_enqueue_style( 'block-visibility-rules-editor' );
}
add_action( 'enqueue_block_assets', 'block_visibility_rules_enqueue_canvas_styles' );
