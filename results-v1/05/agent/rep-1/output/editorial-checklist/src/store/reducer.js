const DEFAULT_STATE = {
	items: [],
	error: null,
};

/**
 * Checklist reducer.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 * @return {Object} Next state.
 */
export default function reducer( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case 'RECEIVE_ITEMS': {
			// Preserve done-state for items that already exist.
			const doneIds = new Set(
				state.items.filter( ( item ) => item.done ).map( ( item ) => item.id )
			);
			return {
				...state,
				error: null,
				items: action.items.map( ( item ) => ( {
					id: String( item.id ),
					label: String( item.label ),
					required: Boolean( item.required ),
					done: doneIds.has( String( item.id ) ),
				} ) ),
			};
		}

		case 'RECEIVE_ERROR':
			return { ...state, error: action.error };

		case 'TOGGLE_ITEM':
			return {
				...state,
				items: state.items.map( ( item ) =>
					item.id === action.id
						? {
								...item,
								done:
									typeof action.done === 'boolean'
										? action.done
										: ! item.done,
						  }
						: item
				),
			};

		case 'SET_DONE_ITEMS': {
			const ids = new Set( action.ids );
			const changed = state.items.some(
				( item ) => item.done !== ids.has( item.id )
			);
			if ( ! changed ) {
				return state;
			}
			return {
				...state,
				items: state.items.map( ( item ) => ( {
					...item,
					done: ids.has( item.id ),
				} ) ),
			};
		}

		case 'RESET_ITEMS':
			if ( ! state.items.some( ( item ) => item.done ) ) {
				return state;
			}
			return {
				...state,
				items: state.items.map( ( item ) => ( {
					...item,
					done: false,
				} ) ),
			};
	}

	return state;
}
