/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import './store';
import ChecklistController from './components/checklist-controller';
import ChecklistSidebar from './components/checklist-sidebar';
import ChecklistPrePublishPanel from './components/pre-publish-panel';
import './editor.scss';

function EditorialChecklist() {
	return (
		<>
			<ChecklistController />
			<ChecklistSidebar />
			<ChecklistPrePublishPanel />
		</>
	);
}

registerPlugin( 'editorial-checklist', {
	render: EditorialChecklist,
	icon: 'yes-alt',
} );
