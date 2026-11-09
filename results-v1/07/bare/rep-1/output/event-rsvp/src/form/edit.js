/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	SelectControl,
	TextControl,
} from '@wordpress/components';

const GUEST_OPTIONS = [ 1, 2, 3, 4, 5 ].map( ( n ) => ( {
	label: String( n ),
	value: String( n ),
} ) );

/**
 * Editor UI for the RSVP form block.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Attribute setter.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { heading, buttonText } = attributes;
	const blockProps = useBlockProps( { className: 'event-rsvp-form' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Form settings', 'event-rsvp' ) }>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Heading', 'event-rsvp' ) }
						value={ heading }
						placeholder={ __( 'RSVP', 'event-rsvp' ) }
						onChange={ ( value ) =>
							setAttributes( { heading: value } )
						}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Button text', 'event-rsvp' ) }
						value={ buttonText }
						placeholder={ __( 'Send RSVP', 'event-rsvp' ) }
						onChange={ ( value ) =>
							setAttributes( { buttonText: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<h3 className="event-rsvp-form__heading">
					{ heading || __( 'RSVP', 'event-rsvp' ) }
				</h3>
				<Disabled>
					<div className="event-rsvp-form__fields">
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label={ __( 'Name', 'event-rsvp' ) }
							value=""
							onChange={ () => {} }
						/>
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="email"
							label={ __( 'Email', 'event-rsvp' ) }
							value=""
							onChange={ () => {} }
						/>
						<SelectControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label={ __( 'Number of guests', 'event-rsvp' ) }
							value="1"
							options={ GUEST_OPTIONS }
							onChange={ () => {} }
						/>
						<button
							type="button"
							className="wp-element-button event-rsvp-form__submit"
						>
							{ buttonText || __( 'Send RSVP', 'event-rsvp' ) }
						</button>
					</div>
				</Disabled>
			</div>
		</>
	);
}
