/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginPrePublishPanel,
	PluginSidebar,
	PluginSidebarMoreMenuItem,
} from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { check } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import './store';
import ChecklistSync from './components/checklist-sync';
import ChecklistSidebar from './components/checklist-sidebar';
import PrePublishChecklist from './components/pre-publish-panel';
import './editor.scss';

const SIDEBAR_NAME = 'editorial-checklist-sidebar';

function EditorialChecklistPlugin() {
	const title = __( 'Editorial Checklist', 'editorial-checklist' );

	return (
		<>
			<ChecklistSync />
			<PluginSidebarMoreMenuItem target={ SIDEBAR_NAME } icon={ check }>
				{ title }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar name={ SIDEBAR_NAME } title={ title } icon={ check }>
				<ChecklistSidebar />
			</PluginSidebar>
			<PluginPrePublishPanel
				title={ title }
				initialOpen
				icon={ check }
				className="editorial-checklist-prepublish-panel"
			>
				<PrePublishChecklist />
			</PluginPrePublishPanel>
		</>
	);
}

registerPlugin( 'editorial-checklist', {
	render: EditorialChecklistPlugin,
} );
