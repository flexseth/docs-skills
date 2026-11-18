/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';

/**
 * Scales a base quantity and rounds it to two decimals.
 *
 * Mirrors `recipe_scaler_format_quantity()` in recipe-scaler.php so the
 * client renders exactly what the server rendered.
 *
 * @param {number} baseQuantity Quantity for the base servings.
 * @param {number} servings     Current number of servings.
 * @param {number} baseServings Number of servings the recipe was written for.
 * @return {string} Formatted quantity, without trailing zeros.
 */
const formatQuantity = ( baseQuantity, servings, baseServings ) => {
	const scaled =
		( Number( baseQuantity ) * servings ) / Math.max( 1, baseServings );
	const rounded = Math.round( ( scaled + Number.EPSILON ) * 100 ) / 100;

	// String() drops trailing zeros: 2.5 -> "2.5", 3 -> "3".
	return String( rounded === 0 ? 0 : rounded );
};

store( 'recipe-scaler', {
	state: {
		/**
		 * Quantity of the current ingredient for the current servings.
		 *
		 * The ingredient's context (`quantity`) inherits `servings` and
		 * `baseServings` from the enclosing recipe block's context.
		 *
		 * @return {string} Scaled quantity.
		 */
		get scaledQuantity() {
			const context = getContext();

			if ( typeof context.quantity !== 'number' ) {
				return '';
			}

			return formatQuantity(
				context.quantity,
				context.servings,
				context.baseServings
			);
		},

		/**
		 * Whether servings are at the minimum (1).
		 *
		 * @return {boolean} True when the "−" button must be disabled.
		 */
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
