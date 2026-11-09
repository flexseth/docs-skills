/**
 * WordPress dependencies
 */
import { createSelector } from '@wordpress/data';

/**
 * Returns all checklist items.
 *
 * @param {Object} state Store state.
 * @return {Array} Items.
 */
export function getItems( state ) {
	return state.items;
}

/**
 * Returns required items that are not done yet.
 */
export const getIncompleteRequiredItems = createSelector(
	( state ) => state.items.filter( ( item ) => item.required && ! item.done ),
	( state ) => [ state.items ]
);

/**
 * Returns the IDs of completed items.
 */
export const getDoneItemIds = createSelector(
	( state ) =>
		state.items.filter( ( item ) => item.done ).map( ( item ) => item.id ),
	( state ) => [ state.items ]
);

/**
 * Returns completion progress as an integer percentage (0–100).
 *
 * @param {Object} state Store state.
 * @return {number} Percentage.
 */
export function getProgress( state ) {
	const total = state.items.length;
	if ( ! total ) {
		return 0;
	}
	const done = state.items.filter( ( item ) => item.done ).length;
	return Math.round( ( done / total ) * 100 );
}

/**
 * Returns the last loading error, if any.
 *
 * @param {Object} state Store state.
 * @return {?string} Error message.
 */
export function getError( state ) {
	return state.error;
}
