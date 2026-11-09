/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';

const MAX_SERVINGS = 999;

/**
 * Rounds a quantity to two decimals and drops trailing zeros.
 *
 * Mirrors recipe_scaler_format_quantity() in PHP.
 *
 * @param {number} quantity Raw quantity.
 * @return {string} Formatted quantity, or an empty string for no quantity.
 */
const formatQuantity = ( quantity ) => {
	if ( ! Number.isFinite( quantity ) || quantity <= 0 ) {
		return '';
	}
	return String( Math.round( ( quantity + Number.EPSILON ) * 100 ) / 100 );
};

store( 'recipe-scaler', {
	state: {
		get isMinimum() {
			const context = getContext();
			return context.servings <= 1;
		},
		get scaledQuantity() {
			const context = getContext();
			const ingredient = context.ingredients?.[ context.index ];

			if ( ! ingredient || ! context.baseServings ) {
				return '';
			}

			return formatQuantity(
				( Number( ingredient.quantity ) * context.servings ) /
					context.baseServings
			);
		},
	},
	actions: {
		increase() {
			const context = getContext();
			if ( context.servings < MAX_SERVINGS ) {
				context.servings += 1;
			}
		},
		decrease() {
			const context = getContext();
			if ( context.servings > 1 ) {
				context.servings -= 1;
			}
		},
	},
} );
