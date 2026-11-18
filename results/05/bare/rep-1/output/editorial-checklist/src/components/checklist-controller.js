/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useDispatch, useSelect, useRegistry } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';
import { doAction } from '@wordpress/hooks';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';

export const LOCK_NAME = 'editorial-checklist';
export const NOTICE_ID = 'editorial-checklist-incomplete';

/**
 * Loads the checklist, locks saving while required items are unchecked,
 * shows the explanatory notice and broadcasts progress changes.
 *
 * Renders nothing.
 */
export default function ChecklistController() {
	const registry = useRegistry();
	const { postId, postType, isLoading, isSaving, missingCount, progress } =
		useSelect( ( select ) => {
			const editor = select( editorStore );
			const checklist = select( checklistStore );
			return {
				postId: editor.getCurrentPostId(),
				postType: editor.getCurrentPostType(),
				isLoading: checklist.isLoading(),
				isSaving: checklist.isSaving(),
				missingCount: checklist.isReady()
					? checklist.getUncheckedRequiredItems().length
					: 0,
				progress: checklist.getProgress(),
			};
		}, [] );

	const { loadChecklist } = useDispatch( checklistStore );
	const {
		lockPostSaving,
		unlockPostSaving,
		lockPostAutosaving,
		unlockPostAutosaving,
		openPublishSidebar,
	} = useDispatch( editorStore );
	const { createWarningNotice, removeNotice } = useDispatch( noticesStore );

	// Load items and stored state for the current post.
	useEffect( () => {
		if ( postId && postType ) {
			loadChecklist( postId, postType );
		}
	}, [ postId, postType, loadChecklist ] );

	/*
	 * Block saving while the checklist loads, while required items are unchecked,
	 * and while a checklist change is being written to the server (so the server-side
	 * publish check never sees stale state).
	 */
	const isIncomplete = missingCount > 0;
	const isLocked = isLoading || isIncomplete || isSaving;

	useEffect( () => {
		if ( ! isLocked ) {
			return;
		}
		lockPostSaving( LOCK_NAME );
		lockPostAutosaving( LOCK_NAME );
		return () => {
			unlockPostSaving( LOCK_NAME );
			unlockPostAutosaving( LOCK_NAME );
		};
	}, [
		isLocked,
		lockPostSaving,
		unlockPostSaving,
		lockPostAutosaving,
		unlockPostAutosaving,
	] );

	// Explain the lock in an editor notice.
	useEffect( () => {
		if ( ! isIncomplete ) {
			removeNotice( NOTICE_ID );
			return;
		}
		createWarningNotice(
			sprintf(
				/* translators: %d: number of unfinished required checklist items. */
				_n(
					'Saving and publishing are disabled until %d required editorial checklist item is complete. Use the Editorial Checklist sidebar to check it off.',
					'Saving and publishing are disabled until %d required editorial checklist items are complete. Use the Editorial Checklist sidebar to check them off.',
					missingCount,
					'editorial-checklist'
				),
				missingCount
			),
			{ id: NOTICE_ID, isDismissible: false }
		);
	}, [ isIncomplete, missingCount, createWarningNotice, removeNotice ] );

	useEffect( () => () => removeNotice( NOTICE_ID ), [ removeNotice ] );

	/*
	 * The saving lock also disables the "Publish" toggle, so a click on it would
	 * do nothing. Open the pre-publish panel instead so the author sees which
	 * required items are unfinished (the final Publish button stays disabled).
	 */
	useEffect( () => {
		if ( ! isIncomplete ) {
			return;
		}
		const onClick = ( event ) => {
			const target = event.target;
			if (
				! target?.closest ||
				! target.closest( '.editor-post-publish-button__button' )
			) {
				return;
			}
			const editor = registry.select( editorStore );
			if (
				! editor.isCurrentPostPublished() &&
				! editor.isPublishSidebarOpened()
			) {
				openPublishSidebar();
			}
		};
		document.addEventListener( 'click', onClick );
		return () => document.removeEventListener( 'click', onClick );
	}, [ isIncomplete, registry, openPublishSidebar ] );

	// Let other plugins react to progress without polling the store.
	useEffect( () => {
		if ( progress.isReady ) {
			doAction( 'editorialChecklist.progressChanged', progress );
		}
	}, [ progress ] );

	return null;
}
