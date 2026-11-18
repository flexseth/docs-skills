/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';

/**
 * Renders checklist items as checkboxes.
 *
 * @param {Object}   props       Component props.
 * @param {Object[]} props.items Items to render.
 */
export default function ChecklistItems( { items } ) {
	const checked = useSelect(
		( select ) => select( checklistStore ).getCheckedItemIds(),
		[]
	);
	const { setItemChecked } = useDispatch( checklistStore );

	return (
		<ul className="editorial-checklist__items">
			{ items.map( ( item ) => (
				<li
					key={ item.id }
					className={
						item.required
							? 'editorial-checklist__item is-required'
							: 'editorial-checklist__item'
					}
				>
					<CheckboxControl
						__nextHasNoMarginBottom
						label={
							item.required
								? sprintf(
										/* translators: %s: checklist item label. */
										__( '%s (required)', 'editorial-checklist' ),
										item.label
								  )
								: item.label
						}
						help={ item.description || undefined }
						checked={ checked.includes( item.id ) }
						onChange={ ( isChecked ) =>
							setItemChecked( item.id, isChecked )
						}
					/>
				</li>
			) ) }
		</ul>
	);
}
