/**
 * WordPress dependencies
 */
import { PluginPrePublishPanel } from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { __, _n, sprintf } from '@wordpress/i18n';
import { check, caution } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';
import ChecklistItems from './checklist-items';
import { ChecklistState } from './sidebar';

/**
 * Pre-publish step listing unfinished required items. They can be checked off right here.
 */
export default function ChecklistPrePublishPanel() {
	const { missing, uncheckedOptional, isComplete } = useSelect(
		( select ) => {
			const checklist = select( checklistStore );
			const progress = checklist.getProgress();
			return {
				missing: checklist.getUncheckedRequiredItems(),
				uncheckedOptional:
					progress.total -
					progress.checked -
					( progress.required - progress.requiredChecked ),
				isComplete: progress.isComplete,
			};
		},
		[]
	);

	return (
		<PluginPrePublishPanel
			className="editorial-checklist-pre-publish"
			title={ __( 'Editorial checklist', 'editorial-checklist' ) }
			icon={ isComplete ? check : caution }
			initialOpen
		>
			<ChecklistState>
				{ missing.length > 0 ? (
					<>
						<p className="editorial-checklist__warning">
							{ sprintf(
								/* translators: %d: number of unfinished required items. */
								_n(
									'%d required item must be completed before you can publish:',
									'%d required items must be completed before you can publish:',
									missing.length,
									'editorial-checklist'
								),
								missing.length
							) }
						</p>
						<ChecklistItems items={ missing } />
					</>
				) : (
					<p>
						{ uncheckedOptional > 0
							? sprintf(
									/* translators: %d: number of unchecked optional items. */
									_n(
										'All required items are complete. %d optional item is still unchecked.',
										'All required items are complete. %d optional items are still unchecked.',
										uncheckedOptional,
										'editorial-checklist'
									),
									uncheckedOptional
							  )
							: __(
									'All checklist items are complete.',
									'editorial-checklist'
							  ) }
					</p>
				) }
			</ChecklistState>
		</PluginPrePublishPanel>
	);
}
