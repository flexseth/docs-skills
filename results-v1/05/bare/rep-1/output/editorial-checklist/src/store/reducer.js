const DEFAULT_STATE = {
	items: [],
	error: null,
};

/**
 * Applies a `done` value to every item, returning the original array when
 * nothing changed so subscribers do not re-render needlessly.
 *
 * @param {Array}    items  Current items.
 * @param {Function} getDone Callback returning the new done value for an item.
 * @return {Array} Items.
 */
function mapDone( items, getDone ) {
	let changed = false;
	const next = items.map( ( item ) => {
		const done = getDone( item );
		if ( done === item.done ) {
			return item;
		}
		changed = true;
		return { ...item, done };
	} );
	return changed ? next : items;
}

export default function reducer( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case 'RECEIVE_ITEMS': {
			const previouslyDone = new Set(
				state.items.filter( ( item ) => item.done ).map( ( item ) => item.id )
			);
			return {
				...state,
				error: null,
				items: action.items.map( ( item ) => ( {
					id: String( item.id ),
					label: String( item.label ),
					required: Boolean( item.required ),
					done: previouslyDone.has( String( item.id ) ),
				} ) ),
			};
		}

		case 'TOGGLE_ITEM': {
			const items = mapDone( state.items, ( item ) => {
				if ( item.id !== action.id ) {
					return item.done;
				}
				return typeof action.done === 'boolean' ? action.done : ! item.done;
			} );
			return items === state.items ? state : { ...state, items };
		}

		case 'SET_DONE_ITEMS': {
			const ids = new Set( action.ids );
			const items = mapDone( state.items, ( item ) => ids.has( item.id ) );
			return items === state.items ? state : { ...state, items };
		}

		case 'RESET_ITEMS': {
			const items = mapDone( state.items, () => false );
			return items === state.items ? state : { ...state, items };
		}

		case 'RECEIVE_ERROR':
			return { ...state, error: action.error };
	}

	return state;
}
