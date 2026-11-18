/**
 * Maintenance Banner panel for the block editor.
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import {
	Button,
	ExternalLink,
	Spinner,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { store as noticesStore } from '@wordpress/notices';
import { __ } from '@wordpress/i18n';

const SETTING = 'maintenance_banner';

function MaintenanceBannerPanel() {
	const { canManage, isSaving, hasEdits } = useSelect( ( select ) => {
		const core = select( coreStore );
		return {
			canManage: core.canUser( 'update', {
				kind: 'root',
				name: 'site',
			} ),
			isSaving: core.isSavingEntityRecord( 'root', 'site' ),
			hasEdits: core.hasEditsForEntityRecord( 'root', 'site' ),
		};
	}, [] );

	const [ settings, setSettings ] = useEntityProp( 'root', 'site', SETTING );
	const { saveEditedEntityRecord } = useDispatch( coreStore );
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	if ( ! canManage ) {
		return null;
	}

	const update = ( key, value ) =>
		setSettings( { ...settings, [ key ]: value } );

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

	const settingsUrl = window.maintenanceBannerEditor?.settingsUrl;

	return (
		<PluginDocumentSettingPanel
			name="maintenance-banner-panel"
			title={ __( 'Maintenance banner', 'maintenance-banner' ) }
		>
			{ ! settings ? (
				<Spinner />
			) : (
				<>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show banner', 'maintenance-banner' ) }
						checked={ !! settings.enabled }
						onChange={ ( value ) => update( 'enabled', value ) }
					/>
					<TextareaControl
						__nextHasNoMarginBottom
						label={ __( 'Message', 'maintenance-banner' ) }
						help={ __(
							'Allowed HTML: <strong>, <em>, <a href="…">, <br>.',
							'maintenance-banner'
						) }
						value={ settings.message || '' }
						onChange={ ( value ) => update( 'message', value ) }
						rows={ 4 }
					/>
					<p>
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
					</p>
					{ settingsUrl && (
						<ExternalLink href={ settingsUrl }>
							{ __(
								'Schedule, colour and roles',
								'maintenance-banner'
							) }
						</ExternalLink>
					) }
				</>
			) }
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'maintenance-banner', {
	render: MaintenanceBannerPanel,
} );
