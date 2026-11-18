/**
 * Team List block registration.
 */
import { registerBlockType } from '@wordpress/blocks';

import './style.scss';
import './editor.scss';

import Edit from './edit';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: Edit,
	// Rendered on the server via render.php.
	save: () => null,
} );
