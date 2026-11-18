/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import { select } from '@wordpress/data';
import { addFilter } from '@wordpress/hooks';
import { __, _n, sprintf } from '@wordpress/i18n';
import { check } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { store as checklistStore } from './store';
import ChecklistSidebar from './components/sidebar';
import ChecklistPrePublishPanel from './components/pre-publish-panel';
import ChecklistController from './components/checklist-controller';
import './editor.scss';

const SIDEBAR_NAME = 'editorial-checklist-sidebar';

function EditorialChecklistPlugin() {
	const title = __( 'Editorial Checklist', 'editorial-checklist' );

	return (
		<>
			<ChecklistController />
			<PluginSidebarMoreMenuItem target={ SIDEBAR_NAME } icon={ check }>
				{ title }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar name={ SIDEBAR_NAME } title={ title } icon={ check }>
				<ChecklistSidebar />
			</PluginSidebar>
			<ChecklistPrePublishPanel />
		</>
	);
}

registerPlugin( 'editorial-checklist', {
	render: EditorialChecklistPlugin,
} );

/*
 * Backstop for code paths that call savePost() directly (e.g. keyboard shortcuts
 * or other plugins) and don't check the saving lock. Autosaves and previews are
 * already stopped by the autosave lock.
 */
addFilter(
	'editor.preSavePost',
	'editorial-checklist/require-checklist',
	( edits, options ) => {
		if ( options?.isAutosave || options?.isPreview ) {
			return edits;
		}

		const missing = select( checklistStore ).getUncheckedRequiredItems();
		if ( select( checklistStore ).isReady() && missing.length ) {
			throw new Error(
				sprintf(
					/* translators: %d: number of unfinished required checklist items. */
					_n(
						'Complete %d required editorial checklist item before saving.',
						'Complete %d required editorial checklist items before saving.',
						missing.length,
						'editorial-checklist'
					),
					missing.length
				)
			);
		}

		return edits;
	}
);
