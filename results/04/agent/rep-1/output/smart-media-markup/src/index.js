/**
 * Smart Media Markup – editor integration.
 *
 * Registers the `smartMediaCaptionAsAlt` attribute on core/image and adds a
 * toggle for it to the block's inspector sidebar.
 */
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ATTRIBUTE = 'smartMediaCaptionAsAlt';
const BLOCK_NAME = 'core/image';

/**
 * Adds the attribute to the core/image block settings.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Filtered settings.
 */
function addCaptionAsAltAttribute( settings, name ) {
	if ( BLOCK_NAME !== name ) {
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
 * Adds the inspector toggle to the image block edit component.
 */
const withCaptionAsAltControl = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		if ( BLOCK_NAME !== props.name ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes, isSelected } = props;
		const enabled = !! attributes[ ATTRIBUTE ];

		return (
			<>
				<BlockEdit { ...props } />
				{ isSelected && (
					<InspectorControls>
						<PanelBody
							title={ __( 'Smart Media', 'smart-media-markup' ) }
							initialOpen={ false }
						>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Use caption as alt text',
									'smart-media-markup'
								) }
								help={
									enabled
										? __(
												'If the alternative text is empty, the caption is used as alt text on the front end.',
												'smart-media-markup'
										  )
										: __(
												'Alternative text is left as entered.',
												'smart-media-markup'
										  )
								}
								checked={ enabled }
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
	'smart-media-markup/with-caption-as-alt-control',
	withCaptionAsAltControl
);
