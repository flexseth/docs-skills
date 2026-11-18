export const DEFAULT_STATE = {
	postId: null,
	items: [],
	checked: [],
	status: 'idle',
	error: null,
	pendingSaves: 0,
};

/**
 * Checklist store reducer.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 * @return {Object} Next state.
 */
export default function reducer( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case 'START_LOADING':
			return {
				...DEFAULT_STATE,
				postId: action.postId,
				status: 'loading',
			};

		case 'RECEIVE_CHECKLIST':
			if ( action.postId !== state.postId ) {
				return state;
			}
			return {
				...state,
				items: action.items,
				checked: action.checked,
				status: 'ready',
				error: null,
			};

		case 'LOAD_FAILED':
			if ( action.postId !== state.postId ) {
				return state;
			}
			return {
				...state,
				status: 'error',
				error: action.error,
			};

		case 'SET_CHECKED':
			return {
				...state,
				checked: action.checked,
			};

		case 'SAVE_START':
			return {
				...state,
				pendingSaves: state.pendingSaves + 1,
			};

		case 'SAVE_FINISH':
			return {
				...state,
				pendingSaves: Math.max( 0, state.pendingSaves - 1 ),
			};
	}

	return state;
}
