/**
 * Stores checklist item definitions loaded from the REST API.
 *
 * @param {Array} items Item definitions ({ id, label, required }).
 * @return {Object} Action.
 */
export function receiveItems( items ) {
	return {
		type: 'RECEIVE_ITEMS',
		items: Array.isArray( items ) ? items : [],
	};
}

/**
 * Toggles an item. Pass `done` to set an explicit value.
 *
 * @param {string}  id     Item id.
 * @param {boolean} [done] Explicit done value.
 * @return {Object} Action.
 */
export function toggleItem( id, done ) {
	return {
		type: 'TOGGLE_ITEM',
		id,
		done,
	};
}

/**
 * Marks exactly the given ids as done (used to hydrate from post meta).
 *
 * @param {string[]} ids Done item ids.
 * @return {Object} Action.
 */
export function setDoneItems( ids ) {
	return {
		type: 'SET_DONE_ITEMS',
		ids: Array.isArray( ids ) ? ids : [],
	};
}

/**
 * Marks every item as not done.
 *
 * @return {Object} Action.
 */
export function resetItems() {
	return {
		type: 'RESET_ITEMS',
	};
}

/**
 * Stores an error message from loading items.
 *
 * @param {string} error Error message.
 * @return {Object} Action.
 */
export function receiveError( error ) {
	return {
		type: 'RECEIVE_ERROR',
		error,
	};
}
