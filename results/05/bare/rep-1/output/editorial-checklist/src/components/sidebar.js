/**
 * WordPress dependencies
 */
import { PanelBody, Spinner, Notice } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';
import ChecklistItems from './checklist-items';
import ChecklistProgress from './progress';

/**
 * Loading, error and empty states shared by the sidebar and pre-publish panel.
 *
 * @param {Object}  props          Component props.
 * @param {Element} props.children Content to show once the checklist is ready.
 */
export function ChecklistState( { children } ) {
	const { isLoading, error, hasItems, postId, postType } = useSelect(
		( select ) => {
			const checklist = select( checklistStore );
			const editor = select( editorStore );
			return {
				isLoading: checklist.isLoading(),
				error: checklist.getError(),
				hasItems: checklist.getItems().length > 0,
				postId: editor.getCurrentPostId(),
				postType: editor.getCurrentPostType(),
			};
		},
		[]
	);
	const { loadChecklist } = useDispatch( checklistStore );

	if ( isLoading ) {
		return <Spinner />;
	}

	if ( error ) {
		return (
			<Notice
				status="error"
				isDismissible={ false }
				actions={ [
					{
						label: __( 'Retry', 'editorial-checklist' ),
						onClick: () => loadChecklist( postId, postType ),
					},
				] }
			>
				{ error }
			</Notice>
		);
	}

	if ( ! hasItems ) {
		return (
			<p>
				{ __(
					'There are no checklist items for this post.',
					'editorial-checklist'
				) }
			</p>
		);
	}

	return children;
}

/**
 * Sidebar body: progress indicator and every item as a checkbox.
 */
export default function ChecklistSidebar() {
	const items = useSelect(
		( select ) => select( checklistStore ).getItems(),
		[]
	);

	return (
		<PanelBody>
			<ChecklistState>
				<ChecklistProgress />
				<ChecklistItems items={ items } />
			</ChecklistState>
		</PanelBody>
	);
}
