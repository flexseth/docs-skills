/**
 * Editor integration for Smart Media Markup.
 *
 * Registers the `smartMediaCaptionAsAlt` attribute on core/image and adds a
 * toggle for it to the block's Settings sidebar.
 */

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ATTRIBUTE = 'smartMediaCaptionAsAlt';

/**
 * Adds the attribute to the core/image block settings.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Filtered settings.
 */
function addCaptionAsAltAttribute( settings, name ) {
	if ( 'core/image' !== name ) {
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
	'smart-media-markup/caption-as-alt-attribute',
	addCaptionAsAltAttribute
);

/**
 * Adds the inspector toggle to core/image.
 */
const withCaptionAsAltControl = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		const { name, attributes, setAttributes, isSelected } = props;

		if ( 'core/image' !== name ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<>
				<BlockEdit { ...props } />
				{ isSelected && (
					<InspectorControls>
						<PanelBody
							title={ __(
								'Smart Media Markup',
								'smart-media-markup'
							) }
							initialOpen={ false }
						>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Use caption as alt text',
									'smart-media-markup'
								) }
								help={ __(
									'When the alternative text is empty, the caption text is used as alt text on the front end.',
									'smart-media-markup'
								) }
								checked={ !! attributes[ ATTRIBUTE ] }
								onChange={ ( value ) =>
									setAttributes( { [ ATTRIBUTE ]: value } )
								}
							/>
						</PanelBody>
					</InspectorControls>
				) }
			</>
		);
	},
	'withCaptionAsAltControl'
);

addFilter(
	'editor.BlockEdit',
	'smart-media-markup/caption-as-alt-control',
	withCaptionAsAltControl
);
