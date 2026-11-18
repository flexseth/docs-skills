/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import {
	CheckboxControl,
	Notice,
	PanelBody,
	ProgressBar,
	Spinner,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';

/**
 * Progress summary with a progress bar.
 *
 * @param {Object} props          Component props.
 * @param {Object} props.progress Progress from the `getProgress` selector.
 * @return {Element} Element.
 */
function ChecklistProgress( { progress } ) {
	return (
		<div className="editorial-checklist__progress">
			<ProgressBar
				className="editorial-checklist__progress-bar"
				value={ progress.percent }
			/>
			<p className="editorial-checklist__progress-text">
				{ sprintf(
					/* translators: 1: Number of checked items, 2: Total number of items. */
					__( '%1$d of %2$d items complete', 'editorial-checklist' ),
					progress.completed,
					progress.total
				) }
			</p>
			{ progress.requiredTotal > 0 && (
				<p
					className={
						progress.isComplete
							? 'editorial-checklist__required is-complete'
							: 'editorial-checklist__required'
					}
				>
					{ sprintf(
						/* translators: 1: Number of checked required items, 2: Total number of required items. */
						__(
							'%1$d of %2$d required items complete',
							'editorial-checklist'
						),
						progress.requiredCompleted,
						progress.requiredTotal
					) }
				</p>
			) }
		</div>
	);
}

/**
 * Sidebar content: progress and the checklist checkboxes.
 *
 * @return {Element} Element.
 */
export default function ChecklistPanel() {
	const { items, checked, progress, isLoading, error } = useSelect(
		( select ) => {
			const checklist = select( checklistStore );
			return {
				items: checklist.getItems(),
				checked: checklist.getCheckedIds(),
				progress: checklist.getProgress(),
				isLoading: checklist.isLoading(),
				error: checklist.getError(),
			};
		},
		[]
	);
	const { setItemChecked } = useDispatch( checklistStore );

	if ( isLoading ) {
		return (
			<PanelBody>
				<Spinner />
			</PanelBody>
		);
	}

	if ( error ) {
		return (
			<PanelBody>
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			</PanelBody>
		);
	}

	if ( ! items.length ) {
		return (
			<PanelBody>
				<p>
					{ __(
						'There are no checklist items for this post.',
						'editorial-checklist'
					) }
				</p>
			</PanelBody>
		);
	}

	return (
		<PanelBody className="editorial-checklist">
			<ChecklistProgress progress={ progress } />
			<ul className="editorial-checklist__items">
				{ items.map( ( item ) => (
					<li key={ item.id } className="editorial-checklist__item">
						<CheckboxControl
							__nextHasNoMarginBottom
							label={ item.label }
							help={
								item.required
									? __( 'Required', 'editorial-checklist' )
									: __( 'Optional', 'editorial-checklist' )
							}
							checked={ checked.includes( item.id ) }
							onChange={ ( isChecked ) =>
								setItemChecked( item.id, isChecked )
							}
						/>
					</li>
				) ) }
			</ul>
		</PanelBody>
	);
}
