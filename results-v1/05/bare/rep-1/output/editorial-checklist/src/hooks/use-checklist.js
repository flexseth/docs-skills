/**
 * WordPress dependencies
 */
import { useCallback, useMemo } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { store as editorStore } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';
import { META_KEY } from '../store/constants';

const EMPTY_ARRAY = [];

/**
 * Reads/writes the done item ids in the current post's meta.
 *
 * @return {{doneIds: string[], setDoneIds: Function}} Meta accessors.
 */
export function useChecklistMeta() {
	const { postType, postId } = useSelect( ( select ) => {
		const { getCurrentPostType, getCurrentPostId } = select( editorStore );
		return {
			postType: getCurrentPostType(),
			postId: getCurrentPostId(),
		};
	}, [] );

	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta', postId );

	const doneIds = useMemo(
		() => ( Array.isArray( meta?.[ META_KEY ] ) ? meta[ META_KEY ] : EMPTY_ARRAY ),
		[ meta ]
	);

	const setDoneIds = useCallback(
		( ids ) => setMeta( { ...( meta || {} ), [ META_KEY ]: ids } ),
		[ meta, setMeta ]
	);

	return { doneIds, setDoneIds };
}

/**
 * Combines the checklist store with post meta. Every mutation is applied to
 * both the store and the meta so they stay in sync.
 *
 * @return {Object} Checklist state and actions.
 */
export default function useChecklist() {
	const { items, incomplete, progress, error, hasLoaded } = useSelect(
		( select ) => {
			const store = select( checklistStore );
			return {
				items: store.getItems(),
				incomplete: store.getIncompleteRequiredItems(),
				progress: store.getProgress(),
				error: store.getError(),
				hasLoaded: store.hasFinishedResolution( 'getItems', [] ),
			};
		},
		[]
	);

	const { toggleItem, resetItems } = useDispatch( checklistStore );
	const { doneIds, setDoneIds } = useChecklistMeta();

	const toggle = useCallback(
		( id, done ) => {
			toggleItem( id, done );
			const next = done
				? Array.from( new Set( [ ...doneIds, id ] ) )
				: doneIds.filter( ( doneId ) => doneId !== id );
			setDoneIds( next );
		},
		[ doneIds, setDoneIds, toggleItem ]
	);

	const reset = useCallback( () => {
		resetItems();
		setDoneIds( [] );
	}, [ resetItems, setDoneIds ] );

	return {
		items,
		incomplete,
		progress,
		error,
		hasLoaded,
		doneIds,
		toggle,
		reset,
	};
}
