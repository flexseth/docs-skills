/**
 * WordPress dependencies.
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Saved markup for the Note Box block.
 *
 * The note is rendered as a React text child, so it is escaped automatically.
 *
 * @param {Object} props            Block props.
 * @param {Object} props.attributes Block attributes.
 * @return {Element|null} Element to render, or null when there is no note.
 */
export default function save( { attributes } ) {
	const { note } = attributes;

	if ( ! note ) {
		return null;
	}

	return <p { ...useBlockProps.save() }>{ note }</p>;
}
