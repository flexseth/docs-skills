/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Thunk resolver: loads item definitions from the REST API.
 *
 * @return {Function} Thunk.
 */
export const getItems =
	() =>
	async ( { dispatch } ) => {
		try {
			const items = await apiFetch( {
				path: '/editorial-checklist/v1/items',
			} );
			dispatch.receiveItems( Array.isArray( items ) ? items : [] );
		} catch ( error ) {
			dispatch.receiveError(
				error?.message ||
					__( 'Could not load the editorial checklist.', 'editorial-checklist' )
			);
		}
	};
