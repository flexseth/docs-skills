<?php
/**
 * Plugin Name:       Recipe Scaler
 * Description:       A recipe block whose ingredient quantities rescale live when visitors change the number of servings.
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
	register_block_type( __DIR__ . '/build/recipe' );
}
add_action( 'init', 'recipe_scaler_register_block' );

/**
 * Formats a quantity rounded to two decimals, without trailing zeros.
 *
 * Mirrors formatQuantity() in view.js so server and client output match.
 *
 * @param float $quantity Raw quantity.
 * @return string Formatted quantity, or an empty string for no quantity.
 */
function recipe_scaler_format_quantity( $quantity ) {
	$quantity = (float) $quantity;

	if ( $quantity <= 0 ) {
		return '';
	}

	$formatted = number_format( round( $quantity, 2 ), 2, '.', '' );

	return rtrim( rtrim( $formatted, '0' ), '.' );
}

/**
 * Sanitizes the ingredient list stored in the block attributes.
 *
 * @param mixed $ingredients Raw attribute value.
 * @return array[] List of ingredients with quantity, unit and name keys.
 */
function recipe_scaler_sanitize_ingredients( $ingredients ) {
	$clean = array();

	if ( ! is_array( $ingredients ) ) {
		return $clean;
	}

	foreach ( $ingredients as $ingredient ) {
		if ( ! is_array( $ingredient ) ) {
			continue;
		}

		$quantity = isset( $ingredient['quantity'] ) && is_numeric( $ingredient['quantity'] )
			? max( 0, (float) $ingredient['quantity'] )
			: 0;

		$clean[] = array(
			'quantity' => $quantity,
			'unit'     => isset( $ingredient['unit'] ) ? sanitize_text_field( (string) $ingredient['unit'] ) : '',
			'name'     => isset( $ingredient['name'] ) ? sanitize_text_field( (string) $ingredient['name'] ) : '',
		);
	}

	return $clean;
}
