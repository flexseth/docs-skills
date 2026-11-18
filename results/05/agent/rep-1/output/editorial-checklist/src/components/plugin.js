/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	PluginSidebar,
	PluginSidebarMoreMenuItem,
	store as editorStore,
} from '@wordpress/editor';
import { check } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';
import ChecklistPanel from './checklist-panel';
import PrePublishPanel from './pre-publish-panel';
import ProgressBroadcaster from './progress-broadcaster';
import SaveLock from './save-lock';

const SIDEBAR_NAME = 'editorial-checklist-sidebar';

/**
 * Root component of the editor plugin.
 *
 * @return {Element} Element.
 */
export default function EditorialChecklistPlugin() {
	const { postId, postType } = useSelect( ( select ) => {
		const editor = select( editorStore );
		return {
			postId: editor.getCurrentPostId(),
			postType: editor.getCurrentPostType(),
		};
	}, [] );
	const { loadChecklist } = useDispatch( checklistStore );

	useEffect( () => {
		if ( postId && postType ) {
			loadChecklist( postId, postType );
		}
	}, [ postId, postType, loadChecklist ] );

	const title = __( 'Editorial Checklist', 'editorial-checklist' );

	return (
		<>
			<SaveLock />
			<ProgressBroadcaster />
			<PluginSidebarMoreMenuItem target={ SIDEBAR_NAME } icon={ check }>
				{ title }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar name={ SIDEBAR_NAME } title={ title } icon={ check }>
				<ChecklistPanel />
			</PluginSidebar>
			<PrePublishPanel />
		</>
	);
}
