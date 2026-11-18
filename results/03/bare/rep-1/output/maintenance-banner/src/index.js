/**
 * Block editor panel for quickly toggling the maintenance banner and editing its message.
 */

/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { store as noticesStore } from '@wordpress/notices';
import { addQueryArgs } from '@wordpress/url';
import {
	Button,
	Flex,
	FlexItem,
	Spinner,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';

const OPTION = 'maintenance_banner_settings';

function BannerControls() {
	const [ settings, setSettings ] = useEntityProp( 'root', 'site', OPTION );

	const { isSaving, hasEdits } = useSelect( ( select ) => {
		const { isSavingEntityRecord, getEntityRecordEdits } =
			select( coreStore );
		const edits = getEntityRecordEdits( 'root', 'site' );

		return {
			isSaving: isSavingEntityRecord( 'root', 'site' ),
			hasEdits: !! edits && OPTION in edits,
		};
	}, [] );

	const { saveEditedEntityRecord } = useDispatch( coreStore );
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	if ( ! settings ) {
		return <Spinner />;
	}

	const update = ( changes ) => setSettings( { ...settings, ...changes } );

	const onSave = async () => {
		try {
			await saveEditedEntityRecord( 'root', 'site', undefined, {
				throwOnError: true,
			} );
			createSuccessNotice(
				__( 'Maintenance banner saved.', 'maintenance-banner' ),
				{ type: 'snackbar' }
			);
		} catch ( error ) {
			createErrorNotice(
				error?.message ||
					__(
						'The maintenance banner could not be saved.',
						'maintenance-banner'
					),
				{ type: 'snackbar' }
			);
		}
	};

	return (
		<>
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Show banner', 'maintenance-banner' ) }
				checked={ !! settings.enabled }
				onChange={ ( enabled ) => update( { enabled } ) }
			/>
			<TextareaControl
				__nextHasNoMarginBottom
				label={ __( 'Message', 'maintenance-banner' ) }
				help={ __(
					'Basic HTML is allowed: <strong>, <em>, <a href="…">.',
					'maintenance-banner'
				) }
				value={ settings.message ?? '' }
				onChange={ ( message ) => update( { message } ) }
				rows={ 4 }
			/>
			<Flex justify="space-between" align="center">
				<FlexItem>
					<Button
						__next40pxDefaultSize
						variant="primary"
						onClick={ onSave }
						isBusy={ isSaving }
						disabled={ isSaving || ! hasEdits }
						accessibleWhenDisabled
					>
						{ __( 'Save banner', 'maintenance-banner' ) }
					</Button>
				</FlexItem>
				<FlexItem>
					<Button
						variant="link"
						href={ addQueryArgs( 'options-general.php', {
							page: 'maintenance-banner',
						} ) }
					>
						{ __( 'All settings', 'maintenance-banner' ) }
					</Button>
				</FlexItem>
			</Flex>
		</>
	);
}

function MaintenanceBannerPanel() {
	const canManage = useSelect(
		( select ) =>
			select( coreStore ).canUser( 'update', {
				kind: 'root',
				name: 'site',
			} ),
		[]
	);

	// Render nothing until we know the user may manage site options.
	if ( ! canManage ) {
		return null;
	}

	return (
		<PluginDocumentSettingPanel
			name="maintenance-banner-panel"
			title={ __( 'Maintenance Banner', 'maintenance-banner' ) }
		>
			<BannerControls />
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'maintenance-banner', {
	render: MaintenanceBannerPanel,
	icon: 'megaphone',
} );
