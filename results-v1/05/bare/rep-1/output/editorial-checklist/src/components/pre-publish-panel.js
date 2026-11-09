/**
 * WordPress dependencies
 */
import { CheckboxControl, Notice, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import useChecklist from '../hooks/use-checklist';

export default function PrePublishChecklist() {
	const { incomplete, error, hasLoaded, toggle } = useChecklist();

	if ( ! hasLoaded ) {
		return <Spinner />;
	}

	if ( error ) {
		return (
			<Notice status="error" isDismissible={ false }>
				{ error }
			</Notice>
		);
	}

	if ( ! incomplete.length ) {
		return (
			<p className="editorial-checklist-prepublish__done">
				{ __( 'All required checklist items are done.', 'editorial-checklist' ) }
			</p>
		);
	}

	return (
		<div className="editorial-checklist-prepublish">
			<p>
				{ __(
					'Complete these required items before publishing:',
					'editorial-checklist'
				) }
			</p>
			<ul className="editorial-checklist-prepublish__list">
				{ incomplete.map( ( item ) => (
					<li key={ item.id }>
						<CheckboxControl
							__nextHasNoMarginBottom
							label={ item.label }
							checked={ item.done }
							onChange={ ( checked ) => toggle( item.id, checked ) }
						/>
					</li>
				) ) }
			</ul>
		</div>
	);
}
