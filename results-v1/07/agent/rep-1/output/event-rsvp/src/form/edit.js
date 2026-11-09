/**
 * Editor UI for the Event RSVP form block.
 */
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

const MAX_GUESTS = 5;

/**
 * Edit component.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Attribute setter.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { heading, buttonText } = attributes;
	const blockProps = useBlockProps( { className: 'event-rsvp' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Form settings', 'event-rsvp' ) }>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Button text', 'event-rsvp' ) }
						value={ buttonText }
						onChange={ ( value ) =>
							setAttributes( { buttonText: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<RichText
					tagName="h3"
					className="event-rsvp__heading"
					value={ heading }
					onChange={ ( value ) => setAttributes( { heading: value } ) }
					placeholder={ __( 'RSVP heading…', 'event-rsvp' ) }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
				/>

				<div className="event-rsvp__form" aria-hidden="true">
					<p className="event-rsvp__field">
						<label>{ __( 'Name', 'event-rsvp' ) }</label>
						<input type="text" disabled />
					</p>
					<p className="event-rsvp__field">
						<label>{ __( 'Email', 'event-rsvp' ) }</label>
						<input type="email" disabled />
					</p>
					<p className="event-rsvp__field">
						<label>{ __( 'Number of guests', 'event-rsvp' ) }</label>
						<select disabled>
							{ Array.from(
								{ length: MAX_GUESTS },
								( _, i ) => i + 1
							).map( ( n ) => (
								<option key={ n } value={ n }>
									{ n }
								</option>
							) ) }
						</select>
					</p>
					<p>
						<button
							type="button"
							className="wp-element-button event-rsvp__submit"
							disabled
						>
							{ buttonText || __( 'Send RSVP', 'event-rsvp' ) }
						</button>
					</p>
				</div>
			</div>
		</>
	);
}
