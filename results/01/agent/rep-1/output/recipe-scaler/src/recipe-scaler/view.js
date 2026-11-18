/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';

/**
 * Scales a quantity and rounds it to two decimals.
 *
 * Mirrors recipe_scaler_format_quantity() in PHP.
 *
 * @param {number|null} quantity     Base quantity.
 * @param {number}      servings     Current servings.
 * @param {number}      baseServings Base servings.
 * @return {string} Formatted quantity.
 */
const formatQuantity = ( quantity, servings, baseServings ) => {
	if ( null === quantity || undefined === quantity || '' === quantity ) {
		return '';
	}
	const value = Number( quantity );
	if ( ! Number.isFinite( value ) ) {
		return '';
	}
	const base = Math.max( 1, baseServings );
	const scaled = ( value * Math.max( 1, servings ) ) / base;
	const rounded = Math.round( ( scaled + Number.EPSILON ) * 100 ) / 100;
	return String( rounded );
};

store( 'recipe-scaler', {
	state: {
		get scaledQuantity() {
			const { quantity, servings, baseServings } = getContext();
			return formatQuantity( quantity, servings, baseServings );
		},
		get isMinimum() {
			return getContext().servings <= 1;
		},
	},
	actions: {
		increment() {
			const context = getContext();
			context.servings += 1;
		},
		decrement() {
			const context = getContext();
			context.servings = Math.max( 1, context.servings - 1 );
		},
	},
} );
