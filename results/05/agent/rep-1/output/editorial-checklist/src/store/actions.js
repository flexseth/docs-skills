/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import { store as noticesStore } from '@wordpress/notices';

const API_BASE = '/editorial-checklist/v1';

// Saves run one after another so the server always ends with the latest state.
let saveQueue = Promise.resolve();

/**
 * Loads the checklist items and the saved state for a post.
 *
 * @param {number} postId   Post ID.
 * @param {string} postType Post type.
 */
export const loadChecklist =
	( postId, postType ) =>
	async ( { dispatch } ) => {
		dispatch( { type: 'START_LOADING', postId } );

		try {
			const [ items, state ] = await Promise.all( [
				apiFetch( {
					path: addQueryArgs( `${ API_BASE }/items`, {
						post_type: postType,
					} ),
				} ),
				apiFetch( { path: `${ API_BASE }/posts/${ postId }` } ),
			] );

			dispatch( {
				type: 'RECEIVE_CHECKLIST',
				postId,
				items: Array.isArray( items ) ? items : [],
				checked: Array.isArray( state?.checked ) ? state.checked : [],
			} );
		} catch ( error ) {
			dispatch( {
				type: 'LOAD_FAILED',
				postId,
				error:
					error?.message ||
					__(
						'The editorial checklist could not be loaded.',
						'editorial-checklist'
					),
			} );
		}
	};

/**
 * Saves the current checked state to the server.
 */
export const saveChecklist =
	() =>
	( { dispatch, select, registry } ) => {
		const postId = select.getPostId();

		if ( ! postId ) {
			return Promise.resolve();
		}

		dispatch( { type: 'SAVE_START' } );

		const run = async () => {
			try {
				await apiFetch( {
					path: `${ API_BASE }/posts/${ postId }`,
					method: 'POST',
					// Read at run time so queued saves send the newest state.
					data: { checked: select.getCheckedIds() },
				} );
			} catch ( error ) {
				registry
					.dispatch( noticesStore )
					.createErrorNotice(
						error?.message ||
							__(
								'Checklist progress could not be saved.',
								'editorial-checklist'
							),
						{
							id: 'editorial-checklist-save-error',
							type: 'snackbar',
						}
					);
			} finally {
				dispatch( { type: 'SAVE_FINISH' } );
			}
		};

		saveQueue = saveQueue.then( run, run );

		return saveQueue;
	};

/**
 * Checks or unchecks an item and saves the change.
 *
 * @param {string}  itemId    Item ID.
 * @param {boolean} isChecked Whether the item is checked.
 */
export const setItemChecked =
	( itemId, isChecked ) =>
	async ( { dispatch, select } ) => {
		const current = select.getCheckedIds();
		const alreadyChecked = current.includes( itemId );

		if ( isChecked === alreadyChecked ) {
			return;
		}

		const checked = isChecked
			? [ ...current, itemId ]
			: current.filter( ( id ) => id !== itemId );

		dispatch( { type: 'SET_CHECKED', checked } );

		await dispatch( saveChecklist() );
	};
