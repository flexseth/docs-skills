/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { PluginPrePublishPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as checklistStore } from '../store';

/**
 * Pre-publish panel listing unfinished required items.
 *
 * @return {Element|null} Element.
 */
export default function PrePublishPanel() {
	const { isReady, missing } = useSelect( ( select ) => {
		const checklist = select( checklistStore );
		return {
			isReady: checklist.isReady(),
			missing: checklist.getIncompleteRequiredItems(),
		};
	}, [] );

	if ( ! isReady ) {
		return null;
	}

	const hasMissing = missing.length > 0;

	return (
		<PluginPrePublishPanel
			className="editorial-checklist-pre-publish"
			title={
				hasMissing
					? __( 'Editorial checklist: incomplete', 'editorial-checklist' )
					: __( 'Editorial checklist: complete', 'editorial-checklist' )
			}
			initialOpen={ hasMissing }
		>
			{ hasMissing ? (
				<>
					<p>
						{ __(
							'Complete these required items in the Editorial Checklist sidebar before publishing:',
							'editorial-checklist'
						) }
					</p>
					<ul className="editorial-checklist-pre-publish__list">
						{ missing.map( ( item ) => (
							<li key={ item.id }>{ item.label }</li>
						) ) }
					</ul>
				</>
			) : (
				<p>
					{ __(
						'All required checklist items are complete.',
						'editorial-checklist'
					) }
				</p>
			) }
		</PluginPrePublishPanel>
	);
}
