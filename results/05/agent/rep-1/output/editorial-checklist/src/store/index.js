/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import reducer from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';

/**
 * Public store name. Other plugins can read progress with:
 *
 *     wp.data.select( 'editorial-checklist' ).getProgress();
 */
export const STORE_NAME = 'editorial-checklist';

export const store = createReduxStore( STORE_NAME, {
	reducer,
	actions,
	selectors,
} );

register( store );
