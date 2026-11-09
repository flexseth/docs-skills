/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';
import { sprintf, _n } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';
import useChecklist from '../hooks/use-checklist';

const LOCK_NAME = 'editorial-checklist';
const NOTICE_ID = 'editorial-checklist-incomplete';

/**
 * Renders nothing. Hydrates the store from post meta and locks saving
 * (and therefore publishing) while required items are incomplete.
 *
 * @return {null} Nothing.
 */
export default function ChecklistSync() {
	const { hasLoaded, incomplete, doneIds } = useChecklist();
	const { setDoneItems } = useDispatch( checklistStore );
	const { lockPostSaving, unlockPostSaving } = useDispatch( editorStore );
	const { createNotice, removeNotice } = useDispatch( noticesStore );

	// Post meta -> store. Runs once items are loaded and whenever meta changes
	// (e.g. undo/redo or a reload of the post).
	useEffect( () => {
		if ( hasLoaded ) {
			setDoneItems( doneIds );
		}
	}, [ hasLoaded, doneIds, setDoneItems ] );

	const incompleteCount = incomplete.length;

	useEffect( () => {
		if ( ! hasLoaded ) {
			return;
		}

		if ( incompleteCount > 0 ) {
			lockPostSaving( LOCK_NAME );
			createNotice(
				'warning',
				sprintf(
					/* translators: %d: number of incomplete required checklist items. */
					_n(
						'%d required editorial checklist item is incomplete. Saving and publishing are locked until it is done.',
						'%d required editorial checklist items are incomplete. Saving and publishing are locked until they are done.',
						incompleteCount,
						'editorial-checklist'
					),
					incompleteCount
				),
				{
					id: NOTICE_ID,
					isDismissible: false,
				}
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
		createNotice,
		removeNotice,
	] );

	// Release the lock if the plugin is unmounted.
	useEffect(
		() => () => {
			unlockPostSaving( LOCK_NAME );
			removeNotice( NOTICE_ID );
		},
		[ unlockPostSaving, removeNotice ]
	);

	return null;
}
