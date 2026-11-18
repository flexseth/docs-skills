/**
 * WordPress dependencies
 */
import { ProgressBar } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';

/**
 * Progress bar with a text summary.
 */
export default function ChecklistProgress() {
	const progress = useSelect(
		( select ) => select( checklistStore ).getProgress(),
		[]
	);

	const summary = sprintf(
		/* translators: 1: number of checked items, 2: total number of items. */
		__( '%1$d of %2$d items complete', 'editorial-checklist' ),
		progress.checked,
		progress.total
	);

	return (
		<div className="editorial-checklist__progress">
			<ProgressBar
				className="editorial-checklist__progress-bar"
				value={ progress.percent }
				aria-label={ summary }
			/>
			<p className="editorial-checklist__progress-text">{ summary }</p>
			{ progress.required > 0 && (
				<p
					className={
						progress.isComplete
							? 'editorial-checklist__required is-complete'
							: 'editorial-checklist__required'
					}
				>
					{ sprintf(
						/* translators: 1: checked required items, 2: total required items. */
						__(
							'Required: %1$d of %2$d',
							'editorial-checklist'
						),
						progress.requiredChecked,
						progress.required
					) }
				</p>
			) }
		</div>
	);
}
