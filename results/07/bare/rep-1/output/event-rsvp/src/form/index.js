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

// Dynamic block: markup is produced by render.php, so no save function.
registerBlockType( metadata.name, {
	edit: Edit,
} );
