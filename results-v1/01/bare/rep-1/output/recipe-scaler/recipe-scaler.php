<?php
/**
 * Plugin Name:       Recipe Scaler
 * Description:       A recipe block whose ingredient quantities rescale live when visitors change the number of servings.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Seth
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
 * Registers the block using the metadata loaded from the block.json file.
 *
 * @return void
 */
function recipe_scaler_block_init() {
	register_block_type( __DIR__ . '/build/recipe' );
}
add_action( 'init', 'recipe_scaler_block_init' );

/**
 * Parses an author-entered quantity into a float.
 *
 * Accepts decimals ("1.5", "1,5"), simple fractions ("1/2") and mixed
 * numbers ("1 1/2"). Returns null when the value is not numeric so it can be
 * displayed verbatim (e.g. "a pinch").
 *
 * @param string $raw Raw quantity string.
 * @return float|null
 */
function recipe_scaler_parse_quantity( $raw ) {
	$raw = trim( str_replace( ',', '.', (string) $raw ) );

	if ( '' === $raw ) {
		return null;
	}

	if ( is_numeric( $raw ) ) {
		return (float) $raw;
	}

	if ( preg_match( '/^(?:(\d+)\s+)?(\d+)\s*\/\s*(\d+)$/', $raw, $matches ) ) {
		$denominator = (int) $matches[3];
		if ( 0 === $denominator ) {
			return null;
		}
		$whole = '' !== $matches[1] ? (int) $matches[1] : 0;
		return $whole + ( (int) $matches[2] / $denominator );
	}

	return null;
}

/**
 * Formats a quantity rounded to two decimals, trimming trailing zeros.
 *
 * Mirrors the JavaScript output of String( Math.round( n * 100 ) / 100 ).
 *
 * @param float $value Quantity.
 * @return string
 */
function recipe_scaler_format_quantity( $value ) {
	$formatted = number_format( round( (float) $value, 2 ), 2, '.', '' );
	$formatted = rtrim( rtrim( $formatted, '0' ), '.' );

	return '-0' === $formatted ? '0' : $formatted;
}
