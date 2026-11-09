/**
 * Stores item definitions fetched from the REST API.
 *
 * @param {Array} items Item definitions.
 * @return {Object} Action.
 */
export function receiveItems( items ) {
	return { type: 'RECEIVE_ITEMS', items };
}

/**
 * Stores a loading error.
 *
 * @param {string} error Error message.
 * @return {Object} Action.
 */
export function receiveError( error ) {
	return { type: 'RECEIVE_ERROR', error };
}

/**
 * Toggles an item's done-state.
 *
 * @param {string}  id     Item ID.
 * @param {boolean} [done] Explicit state; toggles when omitted.
 * @return {Object} Action.
 */
export function toggleItem( id, done ) {
	return { type: 'TOGGLE_ITEM', id, done };
}

/**
 * Marks exactly the given item IDs as done (used to hydrate from post meta).
 *
 * @param {string[]} ids Completed item IDs.
 * @return {Object} Action.
 */
export function setDoneItems( ids ) {
	return { type: 'SET_DONE_ITEMS', ids: Array.isArray( ids ) ? ids : [] };
}

/**
 * Marks every item as not done.
 *
 * @return {Object} Action.
 */
export function resetItems() {
	return { type: 'RESET_ITEMS' };
}
