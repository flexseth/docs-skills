<?php
/**
 * Plugin Name:       Recipe Scaler
 * Description:       A recipe block whose ingredient quantities rescale on the front end when visitors change the number of servings.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Recipe Scaler Contributors
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
 *
 * @return void
 */
function recipe_scaler_register_block() {
	register_block_type( __DIR__ . '/build/recipe-scaler' );
}
add_action( 'init', 'recipe_scaler_register_block' );

/**
 * Scales a quantity and rounds it to two decimals.
 *
 * Mirrors the logic in view.js so server and client output match.
 *
 * @param float|null $quantity      Base quantity.
 * @param int        $servings      Current servings.
 * @param int        $base_servings Base servings.
 * @return string Formatted quantity, or an empty string when there is none.
 */
function recipe_scaler_format_quantity( $quantity, $servings, $base_servings ) {
	if ( null === $quantity || '' === $quantity || ! is_numeric( $quantity ) ) {
		return '';
	}

	$base_servings = max( 1, (int) $base_servings );
	$servings      = max( 1, (int) $servings );
	$scaled        = round( (float) $quantity * $servings / $base_servings, 2 );
	$formatted     = number_format( $scaled, 2, '.', '' );

	return rtrim( rtrim( $formatted, '0' ), '.' );
}
