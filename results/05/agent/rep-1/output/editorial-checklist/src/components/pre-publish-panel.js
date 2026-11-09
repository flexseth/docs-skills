/**
 * WordPress dependencies
 */
import { PluginPrePublishPanel } from '@wordpress/editor';
import { Notice, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import useChecklist from '../hooks/use-checklist';

/**
 * Pre-publish panel listing incomplete required items.
 *
 * @return {Element} Panel.
 */
export default function ChecklistPrePublishPanel() {
	const { incomplete, hasLoaded } = useChecklist();

	return (
		<PluginPrePublishPanel
			className="editorial-checklist-pre-publish"
			title={ __( 'Editorial Checklist', 'editorial-checklist' ) }
			initialOpen
			icon="yes-alt"
		>
			{ ! hasLoaded && <Spinner /> }

			{ hasLoaded && incomplete.length === 0 && (
				<p>
					{ __(
						'All required checklist items are complete.',
						'editorial-checklist'
					) }
				</p>
			) }

			{ hasLoaded && incomplete.length > 0 && (
				<Notice status="warning" isDismissible={ false }>
					<p>
						{ __(
							'Complete these required items before publishing:',
							'editorial-checklist'
						) }
					</p>
					<ul className="editorial-checklist-pre-publish__list">
						{ incomplete.map( ( item ) => (
							<li key={ item.id }>{ item.label }</li>
						) ) }
					</ul>
				</Notice>
			) }
		</PluginPrePublishPanel>
	);
}
