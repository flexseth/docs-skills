/**
 * WordPress dependencies
 */
import { BaseControl, useBaseControlProps } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Accessible progress bar built on a native <progress> element.
 *
 * @param {Object} props       Component props.
 * @param {number} props.value Percentage (0–100).
 * @return {Element} Progress indicator.
 */
export default function ProgressIndicator( { value } ) {
	const { baseControlProps, controlProps } = useBaseControlProps( {
		__nextHasNoMarginBottom: true,
		label: sprintf(
			/* translators: %d: completion percentage. */
			__( 'Progress: %d%%', 'editorial-checklist' ),
			value
		),
	} );

	return (
		<BaseControl { ...baseControlProps }>
			<progress
				{ ...controlProps }
				className="editorial-checklist__progress"
				max="100"
				value={ value }
			>
				{ `${ value }%` }
			</progress>
		</BaseControl>
	);
}
