/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit';
import metadata from './block.json';
import './style.scss';
import './editor.scss';

/**
 * Dynamic block: rendered on the server by render.php, so save returns null.
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
