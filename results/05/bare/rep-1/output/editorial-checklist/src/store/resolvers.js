/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { API_PATH } from './constants';

/**
 * Thunk resolver: loads item definitions from the REST API the first time
 * `getItems` is selected.
 *
 * @return {Function} Thunk.
 */
export const getItems =
	() =>
	async ( { dispatch } ) => {
		try {
			const items = await apiFetch( { path: API_PATH } );
			dispatch.receiveItems( items );
		} catch ( error ) {
			dispatch.receiveError(
				error?.message ||
					__( 'The editorial checklist could not be loaded.', 'editorial-checklist' )
			);
		}
	};
