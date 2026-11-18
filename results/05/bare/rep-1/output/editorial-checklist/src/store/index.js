/**
 * Editorial checklist data store.
 *
 * Public, read-only API for other editor plugins:
 *
 *     const progress = wp.data.select( 'editorial-checklist' ).getProgress();
 *     // { total, checked, required, requiredChecked, percent, isComplete, isReady }
 *
 * Inside a component use `useSelect( ( select ) => select( 'editorial-checklist' ).getProgress(), [] )`.
 * Progress changes are also broadcast with the `editorialChecklist.progressChanged` action from `@wordpress/hooks`.
 */

/**
 * WordPress dependencies
 */
import { createReduxStore, register, createSelector } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { store as noticesStore } from '@wordpress/notices';
import { __ } from '@wordpress/i18n';

export const STORE_NAME = 'editorial-checklist';

const API_ROOT = '/editorial-checklist/v1';

const DEFAULT_STATE = {
	postId: null,
	status: 'idle', // 'idle' | 'loading' | 'ready' | 'error'
	error: null,
	items: [],
	checked: [],
	pendingSaves: 0,
};

const EMPTY_ARRAY = [];

function reducer( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case 'LOAD_START':
			return {
				...DEFAULT_STATE,
				postId: action.postId,
				status: 'loading',
			};
		case 'LOAD_SUCCESS':
			return {
				...state,
				status: 'ready',
				error: null,
				items: action.items,
				checked: action.checked,
			};
		case 'LOAD_ERROR':
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

/*
 * Saves run one after another and always send the latest local state,
 * so rapid toggling can't leave an older state on the server.
 */
let saveQueue = Promise.resolve();

const actions = {
	/**
	 * Loads the checklist items and the stored state for a post.
	 *
	 * @param {number} postId   Post ID.
	 * @param {string} postType Post type.
	 */
	loadChecklist:
		( postId, postType ) =>
		async ( { dispatch, select } ) => {
			dispatch( { type: 'LOAD_START', postId } );

			try {
				const [ items, postState ] = await Promise.all( [
					apiFetch( {
						path: addQueryArgs( `${ API_ROOT }/items`, {
							post_type: postType,
						} ),
					} ),
					apiFetch( { path: `${ API_ROOT }/posts/${ postId }` } ),
				] );

				if ( select.getPostId() !== postId ) {
					return;
				}

				const knownIds = items.map( ( item ) => item.id );
				dispatch( {
					type: 'LOAD_SUCCESS',
					items,
					checked: ( postState.checked || [] ).filter( ( id ) =>
						knownIds.includes( id )
					),
				} );
			} catch ( error ) {
				if ( select.getPostId() === postId ) {
					dispatch( {
						type: 'LOAD_ERROR',
						error:
							error?.message ||
							__(
								'The editorial checklist could not be loaded.',
								'editorial-checklist'
							),
					} );
				}
			}
		},

	/**
	 * Checks or unchecks an item and saves the state for the current post.
	 *
	 * @param {string}  itemId    Item ID.
	 * @param {boolean} isChecked Whether the item is checked.
	 */
	setItemChecked:
		( itemId, isChecked ) =>
		async ( { dispatch, select, registry } ) => {
			const postId = select.getPostId();
			if ( ! postId || ! select.isReady() ) {
				return;
			}

			const current = select.getCheckedItemIds();
			const next = isChecked
				? Array.from( new Set( [ ...current, itemId ] ) )
				: current.filter( ( id ) => id !== itemId );

			dispatch( { type: 'SET_CHECKED', checked: next } );
			dispatch( { type: 'SAVE_START' } );

			const save = saveQueue.then( () => {
				if ( select.getPostId() !== postId ) {
					return null;
				}
				return apiFetch( {
					path: `${ API_ROOT }/posts/${ postId }`,
					method: 'POST',
					data: { checked: select.getCheckedItemIds() },
				} );
			} );
			// Keep the queue alive after a failure.
			saveQueue = save.catch( () => {} );

			try {
				await save;
			} catch ( error ) {
				// Roll back this change unless the user has changed something since.
				if (
					select.getPostId() === postId &&
					select.getCheckedItemIds() === next
				) {
					dispatch( { type: 'SET_CHECKED', checked: current } );
				}
				registry
					.dispatch( noticesStore )
					.createErrorNotice(
						error?.message ||
							__(
								'The checklist could not be saved.',
								'editorial-checklist'
							),
						{ id: 'editorial-checklist-save-error', type: 'snackbar' }
					);
			} finally {
				dispatch( { type: 'SAVE_FINISH' } );
			}
		},
};

const selectors = {
	getPostId: ( state ) => state.postId,
	getItems: ( state ) => state.items,
	getCheckedItemIds: ( state ) => state.checked,
	isItemChecked: ( state, itemId ) => state.checked.includes( itemId ),
	isLoading: ( state ) => state.status === 'loading',
	isReady: ( state ) => state.status === 'ready',
	getError: ( state ) => state.error,
	isSaving: ( state ) => state.pendingSaves > 0,

	getRequiredItems: createSelector(
		( state ) => state.items.filter( ( item ) => item.required ),
		( state ) => [ state.items ]
	),

	getUncheckedRequiredItems: createSelector(
		( state ) => {
			const missing = state.items.filter(
				( item ) => item.required && ! state.checked.includes( item.id )
			);
			return missing.length ? missing : EMPTY_ARRAY;
		},
		( state ) => [ state.items, state.checked ]
	),

	/**
	 * Returns the checklist progress.
	 *
	 * @return {{total:number, checked:number, required:number, requiredChecked:number, percent:number, isComplete:boolean, isReady:boolean}} Progress.
	 */
	getProgress: createSelector(
		( state ) => {
			const total = state.items.length;
			const checked = state.items.filter( ( item ) =>
				state.checked.includes( item.id )
			).length;
			const requiredItems = state.items.filter(
				( item ) => item.required
			);
			const requiredChecked = requiredItems.filter( ( item ) =>
				state.checked.includes( item.id )
			).length;

			return {
				total,
				checked,
				required: requiredItems.length,
				requiredChecked,
				percent: total ? Math.round( ( checked / total ) * 100 ) : 100,
				isComplete:
					state.status === 'ready' &&
					requiredChecked === requiredItems.length,
				isReady: state.status === 'ready',
			};
		},
		( state ) => [ state.items, state.checked, state.status ]
	),

	isComplete: ( state ) =>
		state.status === 'ready' &&
		! state.items.some(
			( item ) => item.required && ! state.checked.includes( item.id )
		),
};

export const store = createReduxStore( STORE_NAME, {
	reducer,
	actions,
	selectors,
} );

register( store );
