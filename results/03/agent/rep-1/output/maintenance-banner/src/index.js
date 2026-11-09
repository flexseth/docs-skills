/**
 * Maintenance Banner editor sidebar.
 */

/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as coreStore, useEntityRecord } from '@wordpress/core-data';
import { store as noticesStore } from '@wordpress/notices';
import {
	PanelBody,
	ToggleControl,
	TextareaControl,
	Button,
	Spinner,
	Notice,
	ExternalLink,
} from '@wordpress/components';

const OPTION = 'maintenance_banner_options';
const SIDEBAR_NAME = 'maintenance-banner-sidebar';

function MaintenanceBannerPanel() {
	const { editedRecord, hasResolved, hasEdits, edit } = useEntityRecord(
		'root',
		'site'
	);
	const isSaving = useSelect(
		( select ) =>
			select( coreStore ).isSavingEntityRecord( 'root', 'site' ),
		[]
	);
	const { saveEditedEntityRecord } = useDispatch( coreStore );
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	if ( ! hasResolved ) {
		return <Spinner />;
	}

	const options = editedRecord?.[ OPTION ];

	if ( ! options ) {
		return (
			<Notice status="error" isDismissible={ false }>
				{ __(
					'The maintenance banner settings could not be loaded.',
					'maintenance-banner'
				) }
			</Notice>
		);
	}

	const update = ( changes ) =>
		edit( { [ OPTION ]: { ...options, ...changes } } );

	const onSave = async () => {
		try {
			await saveEditedEntityRecord( 'root', 'site', undefined, {
				throwOnError: true,
			} );
			createSuccessNotice(
				__( 'Maintenance banner settings saved.', 'maintenance-banner' ),
				{ type: 'snackbar' }
			);
		} catch ( error ) {
			createErrorNotice(
				error?.message ||
					__(
						'Could not save maintenance banner settings.',
						'maintenance-banner'
					),
				{ type: 'snackbar' }
			);
		}
	};

	return (
		<PanelBody>
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Show banner', 'maintenance-banner' ) }
				checked={ !! options.enabled }
				onChange={ ( enabled ) => update( { enabled } ) }
			/>
			<TextareaControl
				__nextHasNoMarginBottom
				label={ __( 'Message', 'maintenance-banner' ) }
				help={ __(
					'Limited HTML is allowed (links, bold, italic, line breaks).',
					'maintenance-banner'
				) }
				value={ options.message || '' }
				onChange={ ( message ) => update( { message } ) }
				rows={ 4 }
			/>
			<p>
				<Button
					__next40pxDefaultSize
					variant="primary"
					onClick={ onSave }
					isBusy={ isSaving }
					disabled={ ! hasEdits || isSaving }
					accessibleWhenDisabled
				>
					{ __( 'Save banner settings', 'maintenance-banner' ) }
				</Button>
			</p>
			<p>
				<ExternalLink href="options-general.php?page=maintenance-banner">
					{ __(
						'Schedule, colour and roles settings',
						'maintenance-banner'
					) }
				</ExternalLink>
			</p>
		</PanelBody>
	);
}

function MaintenanceBannerSidebar() {
	// The settings endpoint requires manage_options, so this maps to that capability.
	const canManageOptions = useSelect(
		( select ) =>
			select( coreStore ).canUser( 'update', {
				kind: 'root',
				name: 'site',
			} ),
		[]
	);

	if ( ! canManageOptions ) {
		return null;
	}

	return (
		<PluginSidebar
			name={ SIDEBAR_NAME }
			title={ __( 'Maintenance Banner', 'maintenance-banner' ) }
			icon="megaphone"
		>
			<MaintenanceBannerPanel />
		</PluginSidebar>
	);
}

registerPlugin( 'maintenance-banner', {
	render: MaintenanceBannerSidebar,
} );
