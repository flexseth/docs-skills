/**
 * Adds a "Use caption as alt text" toggle to the core Image block.
 *
 * The setting is stored as a block attribute only; the block's saved HTML is
 * unchanged. The alt text is applied on the front end by PHP.
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

const BLOCK_NAME = 'core/image';
const ATTRIBUTE = 'smartMediaCaptionAlt';

/**
 * Registers the attribute on the Image block.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Updated settings.
 */
function addCaptionAltAttribute( settings, name ) {
	if ( name !== BLOCK_NAME ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			[ ATTRIBUTE ]: {
				type: 'boolean',
				default: false,
			},
		},
	};
}

addFilter(
	'blocks.registerBlockType',
	'smart-media-markup/caption-alt-attribute',
	addCaptionAltAttribute
);

/**
 * Sidebar panel with the toggle.
 *
 * @param {Object}   props               Component props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Attribute setter.
 * @return {Element} Inspector controls.
 */
function CaptionAltControl( { attributes, setAttributes } ) {
	const enabled = !! attributes[ ATTRIBUTE ];
	const hasAlt = !! ( attributes.alt && attributes.alt.trim() );

	let help = __(
		"When the image has no alt text, the caption's plain text is used as alt text on the front end.",
		'smart-media-markup'
	);
	if ( enabled && hasAlt ) {
		help = __(
			'This image already has alt text, so the caption will not be used.',
			'smart-media-markup'
		);
	}

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Smart Media', 'smart-media-markup' ) }>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Use caption as alt text', 'smart-media-markup' ) }
					help={ help }
					checked={ enabled }
					onChange={ ( value ) =>
						setAttributes( { [ ATTRIBUTE ]: value } )
					}
				/>
			</PanelBody>
		</InspectorControls>
	);
}

const withCaptionAltControl = createHigherOrderComponent(
	( BlockEdit ) =>
		function SmartMediaBlockEdit( props ) {
			if ( props.name !== BLOCK_NAME ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<>
					<BlockEdit { ...props } />
					{ props.isSelected && (
						<CaptionAltControl
							attributes={ props.attributes }
							setAttributes={ props.setAttributes }
						/>
					) }
				</>
			);
		},
	'withCaptionAltControl'
);

addFilter(
	'editor.BlockEdit',
	'smart-media-markup/caption-alt-control',
	withCaptionAltControl
);
