/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';

const MIN_SERVINGS = 1;
const MAX_SERVINGS = 999;

/**
 * Rounds to two decimals and drops trailing zeros ("1.50" -> "1.5").
 * Matches recipe_scaler_format_quantity() in PHP.
 *
 * @param {number} value Quantity.
 * @return {string} Formatted quantity.
 */
const formatQuantity = ( value ) =>
	String( Math.round( ( value + Number.EPSILON ) * 100 ) / 100 );

store( 'recipe-scaler', {
	state: {
		/**
		 * Quantity of the current ingredient scaled to the selected servings.
		 * Reads the block context (servings, baseServings, ingredients) merged
		 * with the ingredient's own context (index).
		 *
		 * @return {string} Scaled quantity.
		 */
		get scaledQuantity() {
			const { ingredients, index, servings, baseServings } = getContext();
			const item = ingredients?.[ index ];

			if ( ! item ) {
				return '';
			}

			if ( item.amount === null || item.amount === undefined ) {
				return item.original;
			}

			return formatQuantity(
				( item.amount * servings ) / Math.max( 1, baseServings )
			);
		},

		/**
		 * Whether the servings count is at the minimum (disables "−").
		 *
		 * @return {boolean} True at the minimum.
		 */
		get isMinServings() {
			return getContext().servings <= MIN_SERVINGS;
		},
	},
	actions: {
		decrement() {
			const context = getContext();
			context.servings = Math.max( MIN_SERVINGS, context.servings - 1 );
		},
		increment() {
			const context = getContext();
			context.servings = Math.min( MAX_SERVINGS, context.servings + 1 );
		},
	},
} );
