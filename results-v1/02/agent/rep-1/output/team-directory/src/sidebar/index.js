/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel, store as editorStore } from '@wordpress/editor';
import { TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';

function TeamMemberPanel() {
	const postType = useSelect(
		( select ) => select( editorStore ).getCurrentPostType(),
		[]
	);

	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	if ( 'team_member' !== postType || ! meta ) {
		return null;
	}

	const updateMeta = ( key, value ) => setMeta( { ...meta, [ key ]: value } );

	return (
		<PluginDocumentSettingPanel
			name="team-directory-member"
			title={ __( 'Team Member Details', 'team-directory' ) }
		>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Role', 'team-directory' ) }
				value={ meta.role ?? '' }
				onChange={ ( value ) => updateMeta( 'role', value ) }
			/>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="number"
				label={ __( 'Start year', 'team-directory' ) }
				value={ meta.start_year || '' }
				min={ 0 }
				max={ 9999 }
				onChange={ ( value ) => {
					const year = parseInt( value, 10 );
					updateMeta( 'start_year', Number.isNaN( year ) ? 0 : year );
				} }
			/>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'team-directory-member-panel', {
	render: TeamMemberPanel,
	icon: 'groups',
} );
