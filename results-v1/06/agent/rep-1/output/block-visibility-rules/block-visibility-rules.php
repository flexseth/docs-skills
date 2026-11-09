<?php
/**
 * Plugin Name:       Block Visibility Rules
 * Description:       Adds a "Visibility" setting to every block: everyone, logged-in users, logged-out visitors or a date range.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Seth
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block-visibility-rules
 *
 * @package BlockVisibilityRules
 */

namespace BlockVisibilityRules;

use DateTimeImmutable;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VERSION = '1.0.0';

/**
 * Blocks that never receive the visibility attribute.
 *
 * @return string[]
 */
function excluded_blocks() {
	return array( 'core/freeform' );
}

/**
 * Whether a block type supports the visibility attribute.
 *
 * Mirrors the JS check: excluded blocks and blocks that opt out of
 * custom class names are skipped.
 *
 * @param string $block_name Block name.
 * @param array  $supports   Block supports array.
 * @return bool
 */
function block_supports_rule( $block_name, $supports ) {
	if ( in_array( $block_name, excluded_blocks(), true ) ) {
		return false;
	}

	if ( is_array( $supports ) && array_key_exists( 'customClassName', $supports ) ) {
		return (bool) $supports['customClassName'];
	}

	return true;
}

/**
 * Registers the `visibilityRule` attribute server-side for every block.
 *
 * @param array  $args       Block type arguments.
 * @param string $block_name Block name.
 * @return array
 */
function register_attribute( $args, $block_name ) {
	$supports = isset( $args['supports'] ) ? $args['supports'] : array();

	if ( ! block_supports_rule( $block_name, $supports ) ) {
		return $args;
	}

	if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}

	$args['attributes']['visibilityRule'] = array(
		'type'       => 'object',
		'properties' => array(
			// No enum: other plugins may add rule types via JS/PHP filters.
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
add_filter( 'register_block_type_args', __NAMESPACE__ . '\\register_attribute', 10, 2 );

/**
 * Parses a stored date string in the site timezone.
 *
 * Strings without an explicit offset (as produced by DateTimePicker) are
 * interpreted in the site timezone.
 *
 * @param mixed $value Raw value.
 * @return DateTimeImmutable|null
 */
function parse_date( $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return null;
	}

	try {
		return new DateTimeImmutable( sanitize_text_field( $value ), wp_timezone() );
	} catch ( Exception $e ) {
		return null;
	}
}

/**
 * Evaluates a rule against the current request.
 *
 * @param array $rule  Sanitized rule.
 * @param array $block Parsed block.
 * @return bool True when the block should be displayed.
 */
function rule_matches( array $rule, array $block ) {
	switch ( $rule['type'] ) {
		case 'logged-in':
			$matches = is_user_logged_in();
			break;

		case 'logged-out':
			$matches = ! is_user_logged_in();
			break;

		case 'date-range':
			$now   = current_datetime();
			$start = parse_date( $rule['start'] );
			$end   = parse_date( $rule['end'] );

			$matches = true;
			if ( $start && $now < $start ) {
				$matches = false;
			}
			if ( $end && $now > $end ) {
				$matches = false;
			}
			break;

		case 'all':
			$matches = true;
			break;

		default:
			// Unknown (third-party) types show by default; use the filter to decide.
			$matches = true;
			break;
	}

	/**
	 * Filters whether a block's visibility rule matches the current request.
	 *
	 * Use this to implement custom rule types added through the
	 * `blockVisibilityRules.ruleTypes` JS filter, or to override the
	 * built-in result.
	 *
	 * @since 1.0.0
	 *
	 * @param bool  $matches Whether the block should be rendered.
	 * @param array $rule    The rule: `type`, `start`, `end`.
	 * @param array $block   The parsed block.
	 */
	return (bool) apply_filters( 'block_visibility_rules_match', $matches, $rule, $block );
}

/**
 * Whether the current request is an editor preview (e.g. ServerSideRender)
 * made by a user who can edit content, in which case nothing is hidden.
 *
 * @return bool
 */
function is_editor_render_request() {
	$is_rest = function_exists( 'wp_is_serving_rest_request' )
		? wp_is_serving_rest_request()
		: ( defined( 'REST_REQUEST' ) && REST_REQUEST );

	if ( ! $is_rest || ! current_user_can( 'edit_posts' ) ) {
		return false;
	}

	// Read-only check of a REST query arg; no state is changed.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$context = isset( $_GET['context'] ) ? sanitize_key( wp_unslash( $_GET['context'] ) ) : '';

	return 'edit' === $context;
}

/**
 * Hides blocks whose rule does not match the current request.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function filter_render_block( $block_content, $block ) {
	if ( empty( $block['attrs']['visibilityRule'] ) || ! is_array( $block['attrs']['visibilityRule'] ) ) {
		return $block_content;
	}

	$raw  = $block['attrs']['visibilityRule'];
	$rule = array(
		'type'  => isset( $raw['type'] ) && is_string( $raw['type'] ) ? sanitize_key( $raw['type'] ) : 'all',
		'start' => isset( $raw['start'] ) && is_string( $raw['start'] ) ? $raw['start'] : null,
		'end'   => isset( $raw['end'] ) && is_string( $raw['end'] ) ? $raw['end'] : null,
	);

	if ( 'all' === $rule['type'] || is_editor_render_request() ) {
		return $block_content;
	}

	return rule_matches( $rule, $block ) ? $block_content : '';
}
add_filter( 'render_block', __NAMESPACE__ . '\\filter_render_block', 10, 2 );

/**
 * Enqueues the editor script and stylesheet.
 *
 * @return void
 */
function enqueue_editor_assets() {
	$asset_file = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = include $asset_file;

	wp_enqueue_script(
		'block-visibility-rules-editor',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_set_script_translations( 'block-visibility-rules-editor', 'block-visibility-rules' );

	if ( file_exists( plugin_dir_path( __FILE__ ) . 'build/index.css' ) ) {
		wp_enqueue_style(
			'block-visibility-rules-editor',
			plugins_url( 'build/index.css', __FILE__ ),
			array(),
			$asset['version']
		);
	}
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_editor_assets' );
