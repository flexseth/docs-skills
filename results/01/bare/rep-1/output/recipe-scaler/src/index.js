/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import './style.scss';
import './editor.scss';
import Edit from './edit';
import metadata from './block.json';

/**
 * Dynamic block: the markup is produced by render.php, so no save function.
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
