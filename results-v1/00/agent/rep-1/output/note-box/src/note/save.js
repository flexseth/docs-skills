/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Saved markup for the Note Box block.
 *
 * React escapes the note text, so no manual escaping is needed.
 *
 * @param {Object} props            Block props.
 * @param {Object} props.attributes Block attributes.
 * @return {Element} Element to render.
 */
export default function save( { attributes } ) {
	return <p { ...useBlockProps.save() }>{ attributes.note }</p>;
}
