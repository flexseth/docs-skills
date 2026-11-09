/**
 * WordPress dependencies
 */
import {
	Button,
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
import useChecklist from '../hooks/use-checklist';

export default function ChecklistSidebar() {
	const { items, progress, error, hasLoaded, toggle, reset } = useChecklist();

	if ( ! hasLoaded ) {
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
				<p>{ __( 'No checklist items are defined.', 'editorial-checklist' ) }</p>
			</PanelBody>
		);
	}

	const doneCount = items.filter( ( item ) => item.done ).length;

	return (
		<PanelBody className="editorial-checklist-sidebar">
			<div className="editorial-checklist-sidebar__progress">
				<ProgressBar
					value={ progress }
					aria-label={ __( 'Checklist progress', 'editorial-checklist' ) }
				/>
				<p className="editorial-checklist-sidebar__progress-label">
					{ sprintf(
						/* translators: 1: number of done items, 2: total items, 3: percentage. */
						__( '%1$d of %2$d done (%3$d%%)', 'editorial-checklist' ),
						doneCount,
						items.length,
						progress
					) }
				</p>
			</div>

			<ul className="editorial-checklist-sidebar__list">
				{ items.map( ( item ) => (
					<li key={ item.id }>
						<CheckboxControl
							__nextHasNoMarginBottom
							label={ item.label }
							help={
								item.required
									? __( 'Required', 'editorial-checklist' )
									: undefined
							}
							checked={ item.done }
							onChange={ ( checked ) => toggle( item.id, checked ) }
						/>
					</li>
				) ) }
			</ul>

			<Button
				variant="secondary"
				isDestructive
				onClick={ reset }
				disabled={ doneCount === 0 }
				accessibleWhenDisabled
			>
				{ __( 'Reset checklist', 'editorial-checklist' ) }
			</Button>
		</PanelBody>
	);
}
