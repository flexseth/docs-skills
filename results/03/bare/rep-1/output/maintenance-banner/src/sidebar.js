/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import {
	Button,
	PanelBody,
	PanelRow,
	Spinner,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as coreStore, useEntityRecord } from '@wordpress/core-data';
import { store as noticesStore } from '@wordpress/notices';
import { addQueryArgs } from '@wordpress/url';

const OPTION_NAME = 'maintenance_banner_options';
const SIDEBAR_NAME = 'maintenance-banner-sidebar';

const DEFAULTS = {
	enabled: false,
	message: '',
	background_color: '#ffcc00',
	start_datetime: '',
	end_datetime: '',
	hidden_roles: [],
};

/**
 * Sidebar body. Only mounted for users who can manage site settings, so the
 * /wp/v2/settings request is never made for anyone else.
 */
function SidebarContent() {
	const { record, editedRecord, edit, hasEdits } = useEntityRecord(
		'root',
		'site'
	);

	const isSaving = useSelect(
		( select ) => select( coreStore ).isSavingEntityRecord( 'root', 'site' ),
		[]
	);

	const { saveEditedEntityRecord } = useDispatch( coreStore );
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	if ( ! record ) {
		return (
			<PanelBody>
				<Spinner />
			</PanelBody>
		);
	}

	const options = {
		...DEFAULTS,
		...( editedRecord?.[ OPTION_NAME ] ?? {} ),
	};

	const update = ( changes ) =>
		edit( { [ OPTION_NAME ]: { ...options, ...changes } } );

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
		<PanelBody>
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Show banner', 'maintenance-banner' ) }
				help={ __(
					'Schedule and role visibility still apply.',
					'maintenance-banner'
				) }
				checked={ !! options.enabled }
				onChange={ ( enabled ) => update( { enabled } ) }
			/>
			<TextareaControl
				__nextHasNoMarginBottom
				label={ __( 'Message', 'maintenance-banner' ) }
				help={ __(
					'Limited HTML (links, bold, italic, line breaks) is allowed.',
					'maintenance-banner'
				) }
				value={ options.message }
				onChange={ ( message ) => update( { message } ) }
				rows={ 4 }
			/>
			<PanelRow>
				<Button
					__next40pxDefaultSize
					variant="primary"
					onClick={ onSave }
					isBusy={ isSaving }
					disabled={ ! hasEdits || isSaving }
					accessibleWhenDisabled
				>
					{ __( 'Save banner', 'maintenance-banner' ) }
				</Button>
			</PanelRow>
			<p>
				<a
					href={ addQueryArgs( 'options-general.php', {
						page: 'maintenance-banner',
					} ) }
				>
					{ __(
						'Colour, schedule and role settings',
						'maintenance-banner'
					) }
				</a>
			</p>
		</PanelBody>
	);
}

/**
 * Plugin render component.
 */
export default function MaintenanceBannerSidebar() {
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

	const title = __( 'Maintenance Banner', 'maintenance-banner' );

	return (
		<>
			<PluginSidebarMoreMenuItem target={ SIDEBAR_NAME } icon="megaphone">
				{ title }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar name={ SIDEBAR_NAME } title={ title } icon="megaphone">
				<SidebarContent />
			</PluginSidebar>
		</>
	);
}
