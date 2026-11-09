/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { store as editorStore } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';

export const META_KEY = '_editorial_checklist';

const EMPTY = [];

/**
 * Reads the checklist store and the post meta, and returns handlers that
 * update both so the done-state is persisted with the post.
 *
 * @return {Object} Checklist state and handlers.
 */
export default function useChecklist() {
	const { postType, postId } = useSelect( ( select ) => {
		const editor = select( editorStore );
		return {
			postType: editor.getCurrentPostType(),
			postId: editor.getCurrentPostId(),
		};
	}, [] );

	const [ meta, setMeta ] = useEntityProp(
		'postType',
		postType,
		'meta',
		postId
	);

	const { items, incomplete, progress, hasLoaded, error } = useSelect(
		( select ) => {
			const checklist = select( checklistStore );
			return {
				items: checklist.getItems(),
				incomplete: checklist.getIncompleteRequiredItems(),
				progress: checklist.getProgress(),
				hasLoaded: checklist.hasFinishedResolution( 'getItems', [] ),
				error: checklist.getError(),
			};
		},
		[]
	);

	const { toggleItem, resetItems } = useDispatch( checklistStore );

	const metaIds = Array.isArray( meta?.[ META_KEY ] )
		? meta[ META_KEY ]
		: EMPTY;

	const toggle = useCallback(
		( id, checked ) => {
			toggleItem( id, checked );
			const next = checked
				? Array.from( new Set( [ ...metaIds, id ] ) )
				: metaIds.filter( ( doneId ) => doneId !== id );
			setMeta( { ...meta, [ META_KEY ]: next } );
		},
		[ toggleItem, setMeta, meta, metaIds ]
	);

	const reset = useCallback( () => {
		resetItems();
		setMeta( { ...meta, [ META_KEY ]: [] } );
	}, [ resetItems, setMeta, meta ] );

	return {
		items,
		incomplete,
		progress,
		hasLoaded,
		error,
		metaIds,
		toggle,
		reset,
	};
}
