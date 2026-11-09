/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';
import { _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';
import useChecklist from '../hooks/use-checklist';

const LOCK_NAME = 'editorial-checklist';
const NOTICE_ID = 'editorial-checklist-incomplete';

/**
 * Headless component: hydrates the store from post meta and locks saving
 * while required items are incomplete.
 *
 * @return {null} Renders nothing.
 */
export default function ChecklistController() {
	const { incomplete, hasLoaded, metaIds } = useChecklist();
	const { setDoneItems } = useDispatch( checklistStore );
	const { lockPostSaving, unlockPostSaving } = useDispatch( editorStore );
	const { createWarningNotice, removeNotice } = useDispatch( noticesStore );

	const metaKey = metaIds.join( ',' );

	// Sync post meta -> store (on load, undo/redo, and after items arrive).
	useEffect( () => {
		if ( hasLoaded ) {
			setDoneItems( metaIds );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ hasLoaded, metaKey, setDoneItems ] );

	const incompleteCount = incomplete.length;

	useEffect( () => {
		if ( ! hasLoaded ) {
			return;
		}

		if ( incompleteCount > 0 ) {
			lockPostSaving( LOCK_NAME );
			createWarningNotice(
				sprintf(
					/* translators: %d: number of incomplete required checklist items. */
					_n(
						'Publishing is locked: %d required editorial checklist item is incomplete.',
						'Publishing is locked: %d required editorial checklist items are incomplete.',
						incompleteCount,
						'editorial-checklist'
					),
					incompleteCount
				),
				{ id: NOTICE_ID, isDismissible: false }
			);
		} else {
			unlockPostSaving( LOCK_NAME );
			removeNotice( NOTICE_ID );
		}
	}, [
		hasLoaded,
		incompleteCount,
		lockPostSaving,
		unlockPostSaving,
		createWarningNotice,
		removeNotice,
	] );

	// Release the lock and notice if the plugin unmounts.
	useEffect(
		() => () => {
			unlockPostSaving( LOCK_NAME );
			removeNotice( NOTICE_ID );
		},
		[ unlockPostSaving, removeNotice ]
	);

	return null;
}
