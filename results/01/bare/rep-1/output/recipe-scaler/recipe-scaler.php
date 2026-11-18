<?php
/**
 * Plugin Name:       Recipe Scaler
 * Description:       A recipe block whose ingredient quantities rescale instantly when visitors change the number of servings.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       recipe-scaler
 *
 * @package RecipeScaler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from the built block.json.
 */
function recipe_scaler_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'recipe_scaler_block_init' );

/**
 * Parses a quantity typed by an author into a number.
 *
 * Accepts decimals ("1.5", "1,5"), fractions ("1/2") and mixed numbers ("1 1/2").
 *
 * @param mixed $raw Raw quantity value.
 * @return float|null The numeric quantity, or null when it is not numeric.
 */
function recipe_scaler_parse_quantity( $raw ) {
	if ( is_int( $raw ) || is_float( $raw ) ) {
		return (float) $raw;
	}

	$raw = trim( str_replace( ',', '.', (string) $raw ) );

	if ( '' === $raw ) {
		return null;
	}

	if ( is_numeric( $raw ) ) {
		return (float) $raw;
	}

	if ( preg_match( '/^(?:(\d+(?:\.\d+)?)\s+)?(\d+)\s*\/\s*(\d+)$/', $raw, $matches ) ) {
		$denominator = (int) $matches[3];
		if ( 0 === $denominator ) {
			return null;
		}
		$whole = '' !== $matches[1] ? (float) $matches[1] : 0.0;
		return $whole + ( (int) $matches[2] / $denominator );
	}

	return null;
}

/**
 * Scales a base quantity and rounds it to two decimals.
 *
 * Mirrors `formatQuantity()` in src/view.js so the server and the client
 * always render the same string.
 *
 * @param float $base_quantity Quantity for the base servings.
 * @param int   $servings      Current number of servings.
 * @param int   $base_servings Number of servings the recipe was written for.
 * @return string
 */
function recipe_scaler_format_quantity( $base_quantity, $servings, $base_servings ) {
	$base_servings = max( 1, (int) $base_servings );
	$scaled        = round( (float) $base_quantity * (int) $servings / $base_servings, 2 );

	// Drop trailing zeros: 2.50 -> "2.5", 3.00 -> "3".
	$formatted = rtrim( rtrim( number_format( $scaled, 2, '.', '' ), '0' ), '.' );

	return '' === $formatted || '-0' === $formatted ? '0' : $formatted;
}
