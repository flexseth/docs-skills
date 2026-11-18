/**
 * WordPress dependencies
 */
import { createSelector } from '@wordpress/data';

/**
 * Returns the ID of the post the checklist was loaded for.
 *
 * @param {Object} state Store state.
 * @return {number|null} Post ID.
 */
export function getPostId( state ) {
	return state.postId;
}

/**
 * Returns all checklist items.
 *
 * @param {Object} state Store state.
 * @return {Array<{id: string, label: string, required: boolean}>} Items.
 */
export function getItems( state ) {
	return state.items;
}

/**
 * Returns the IDs of checked items.
 *
 * @param {Object} state Store state.
 * @return {string[]} Checked item IDs.
 */
export function getCheckedIds( state ) {
	return state.checked;
}

/**
 * Whether an item is checked.
 *
 * @param {Object} state  Store state.
 * @param {string} itemId Item ID.
 * @return {boolean} Whether checked.
 */
export function isItemChecked( state, itemId ) {
	return state.checked.includes( itemId );
}

/**
 * Whether items and saved state have been loaded.
 *
 * @param {Object} state Store state.
 * @return {boolean} Whether ready.
 */
export function isReady( state ) {
	return state.status === 'ready';
}

/**
 * Whether the checklist is loading.
 *
 * @param {Object} state Store state.
 * @return {boolean} Whether loading.
 */
export function isLoading( state ) {
	return state.status === 'loading';
}

/**
 * Returns the load error message, if any.
 *
 * @param {Object} state Store state.
 * @return {string|null} Error message.
 */
export function getError( state ) {
	return state.error;
}

/**
 * Whether checklist changes are being saved to the server.
 *
 * @param {Object} state Store state.
 * @return {boolean} Whether saving.
 */
export function isSaving( state ) {
	return state.pendingSaves > 0;
}

/**
 * Returns the required items that are not checked.
 *
 * @param {Object} state Store state.
 * @return {Array<{id: string, label: string, required: boolean}>} Items.
 */
export const getIncompleteRequiredItems = createSelector(
	( state ) =>
		state.items.filter(
			( item ) => item.required && ! state.checked.includes( item.id )
		),
	( state ) => [ state.items, state.checked ]
);

/**
 * Whether every required item is checked. False until the checklist is loaded.
 *
 * @param {Object} state Store state.
 * @return {boolean} Whether complete.
 */
export function isComplete( state ) {
	return isReady( state ) && getIncompleteRequiredItems( state ).length === 0;
}

/**
 * Returns a summary of checklist progress.
 *
 * @param {Object} state Store state.
 * @return {{
 *   isReady: boolean,
 *   isComplete: boolean,
 *   total: number,
 *   completed: number,
 *   requiredTotal: number,
 *   requiredCompleted: number,
 *   percent: number,
 *   incompleteRequired: string[]
 * }} Progress summary.
 */
export const getProgress = createSelector(
	( state ) => {
		const total = state.items.length;
		const required = state.items.filter( ( item ) => item.required );
		const isChecked = ( item ) => state.checked.includes( item.id );
		const completed = state.items.filter( isChecked ).length;
		const requiredCompleted = required.filter( isChecked ).length;
		const ready = isReady( state );

		return {
			isReady: ready,
			isComplete: ready && requiredCompleted === required.length,
			total,
			completed,
			requiredTotal: required.length,
			requiredCompleted,
			percent: total ? Math.round( ( completed / total ) * 100 ) : 100,
			incompleteRequired: required
				.filter( ( item ) => ! isChecked( item ) )
				.map( ( item ) => item.id ),
		};
	},
	( state ) => [ state.items, state.checked, state.status ]
);
