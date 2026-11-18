/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import './store';
import EditorialChecklistPlugin from './components/plugin';
import './editor.scss';

registerPlugin( 'editorial-checklist', {
	render: EditorialChecklistPlugin,
} );
