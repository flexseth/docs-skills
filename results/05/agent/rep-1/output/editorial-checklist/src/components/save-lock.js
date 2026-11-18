/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';
import { _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';

export const LOCK_NAME = 'editorial-checklist';
export const NOTICE_ID = 'editorial-checklist-incomplete';

/**
 * Locks saving and autosaving and shows a notice while required items are unchecked.
 *
 * @return {null} Renders nothing.
 */
export default function SaveLock() {
	const { isReady, missingCount, isSaving } = useSelect( ( select ) => {
		const checklist = select( checklistStore );
		return {
			isReady: checklist.isReady(),
			missingCount: checklist.getIncompleteRequiredItems().length,
			isSaving: checklist.isSaving(),
		};
	}, [] );

	const {
		lockPostSaving,
		unlockPostSaving,
		lockPostAutosaving,
		unlockPostAutosaving,
	} = useDispatch( editorStore );
	const { createWarningNotice, removeNotice } = useDispatch( noticesStore );

	const isIncomplete = isReady && missingCount > 0;
	// Stay locked while the final check is still being stored on the server,
	// so a publish request cannot race ahead of it.
	const shouldLock = isIncomplete || ( isReady && isSaving );

	useEffect( () => {
		if ( shouldLock ) {
			lockPostSaving( LOCK_NAME );
			lockPostAutosaving( LOCK_NAME );
		} else {
			unlockPostSaving( LOCK_NAME );
			unlockPostAutosaving( LOCK_NAME );
		}
	}, [
		shouldLock,
		lockPostSaving,
		unlockPostSaving,
		lockPostAutosaving,
		unlockPostAutosaving,
	] );

	useEffect( () => {
		if ( ! isIncomplete ) {
			removeNotice( NOTICE_ID );
			return;
		}

		createWarningNotice(
			sprintf(
				/* translators: %d: Number of unchecked required checklist items. */
				_n(
					'Saving and publishing are disabled: %d required editorial checklist item is not complete. Check it in the Editorial Checklist sidebar.',
					'Saving and publishing are disabled: %d required editorial checklist items are not complete. Check them in the Editorial Checklist sidebar.',
					missingCount,
					'editorial-checklist'
				),
				missingCount
			),
			{
				id: NOTICE_ID,
				isDismissible: false,
			}
		);
	}, [ isIncomplete, missingCount, createWarningNotice, removeNotice ] );

	// Release the lock and notice if the plugin is unmounted.
	useEffect(
		() => () => {
			unlockPostSaving( LOCK_NAME );
			unlockPostAutosaving( LOCK_NAME );
			removeNotice( NOTICE_ID );
		},
		[ unlockPostSaving, unlockPostAutosaving, removeNotice ]
	);

	return null;
}
