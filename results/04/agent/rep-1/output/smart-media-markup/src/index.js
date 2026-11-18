/**
 * Adds a "Use caption as alt text" toggle to the core Image block sidebar.
 */
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ATTRIBUTE = 'smartMediaCaptionAsAlt';

/**
 * Registers the attribute on the core Image block.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Filtered settings.
 */
function addCaptionAltAttribute( settings, name ) {
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
	'smart-media-markup/caption-alt-attribute',
	addCaptionAltAttribute
);

const withCaptionAltControl = createHigherOrderComponent(
	( BlockEdit ) =>
		function SmartMediaMarkupEdit( props ) {
			const { name, attributes, setAttributes, isSelected } = props;

			if ( 'core/image' !== name ) {
				return <BlockEdit { ...props } />;
			}

			const enabled = !! attributes[ ATTRIBUTE ];
			const hasAlt = !! ( attributes.alt && attributes.alt.trim() );

			let help = __(
				'When the image has no alt text, the caption text is used as alt text on the front end.',
				'smart-media-markup'
			);

			if ( enabled && hasAlt ) {
				help = __(
					'This image already has alt text, so the caption will not replace it.',
					'smart-media-markup'
				);
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
							>
								<ToggleControl
									__nextHasNoMarginBottom
									label={ __(
										'Use caption as alt text',
										'smart-media-markup'
									) }
									help={ help }
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
	'withCaptionAltControl'
);

addFilter(
	'editor.BlockEdit',
	'smart-media-markup/caption-alt-control',
	withCaptionAltControl
);
