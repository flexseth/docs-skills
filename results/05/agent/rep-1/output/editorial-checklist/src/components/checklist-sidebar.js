/**
 * WordPress dependencies
 */
import {
	PluginSidebar,
	PluginSidebarMoreMenuItem,
} from '@wordpress/editor';
import {
	Button,
	CheckboxControl,
	Notice,
	PanelBody,
	Spinner,
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import useChecklist from '../hooks/use-checklist';
import ProgressIndicator from './progress-indicator';

export const SIDEBAR_NAME = 'editorial-checklist-sidebar';

/**
 * Sidebar listing every checklist item.
 *
 * @return {Element} Sidebar.
 */
export default function ChecklistSidebar() {
	const { items, progress, hasLoaded, error, toggle, reset } =
		useChecklist();

	const title = __( 'Editorial Checklist', 'editorial-checklist' );

	return (
		<>
			<PluginSidebarMoreMenuItem target={ SIDEBAR_NAME } icon="yes-alt">
				{ title }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar name={ SIDEBAR_NAME } title={ title } icon="yes-alt">
				<PanelBody>
					{ ! hasLoaded && <Spinner /> }

					{ hasLoaded && error && (
						<Notice status="error" isDismissible={ false }>
							{ error }
						</Notice>
					) }

					{ hasLoaded && ! error && items.length === 0 && (
						<p>
							{ __(
								'No checklist items are defined.',
								'editorial-checklist'
							) }
						</p>
					) }

					{ hasLoaded && items.length > 0 && (
						<VStack spacing={ 4 }>
							<ProgressIndicator value={ progress } />
							<VStack spacing={ 3 }>
								{ items.map( ( item ) => (
									<CheckboxControl
										key={ item.id }
										__nextHasNoMarginBottom
										label={
											item.required
												? sprintf(
														/* translators: %s: checklist item label. */
														__(
															'%s (required)',
															'editorial-checklist'
														),
														item.label
												  )
												: item.label
										}
										checked={ item.done }
										onChange={ ( checked ) =>
											toggle( item.id, checked )
										}
									/>
								) ) }
							</VStack>
							<div>
								<Button
									__next40pxDefaultSize
									variant="secondary"
									onClick={ reset }
									disabled={ progress === 0 }
								>
									{ __(
										'Reset checklist',
										'editorial-checklist'
									) }
								</Button>
							</div>
						</VStack>
					) }
				</PanelBody>
			</PluginSidebar>
		</>
	);
}
