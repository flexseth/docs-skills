/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { doAction } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';

export const PROGRESS_HOOK = 'editorialChecklist.progressChanged';

/**
 * Fires a `@wordpress/hooks` action whenever checklist progress changes, so
 * plugins can react without subscribing to the data store themselves:
 *
 *     wp.hooks.addAction( 'editorialChecklist.progressChanged', 'my-plugin', ( progress ) => {} );
 *
 * @return {null} Renders nothing.
 */
export default function ProgressBroadcaster() {
	const progress = useSelect(
		( select ) => select( checklistStore ).getProgress(),
		[]
	);

	useEffect( () => {
		if ( progress.isReady ) {
			doAction( PROGRESS_HOOK, progress );
		}
	}, [ progress ] );

	return null;
}
