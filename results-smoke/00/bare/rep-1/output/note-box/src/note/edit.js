/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextareaControl } from '@wordpress/components';

/**
 * Editor component for the Note Box block.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Attribute setter.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { note } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Note', 'note-box' ) }>
					<TextareaControl
						label={ __( 'Note text', 'note-box' ) }
						help={ __(
							'This text is shown in the block.',
							'note-box'
						) }
						value={ note }
						onChange={ ( value ) =>
							setAttributes( { note: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<p { ...useBlockProps() }>
				{ note ||
					__( 'Add a note in the block settings.', 'note-box' ) }
			</p>
		</>
	);
}
