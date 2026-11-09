/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel, store as editorStore } from '@wordpress/editor';
import { Flex, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';

const POST_TYPE = 'team_member';

function MemberDetailsFields() {
	const [ meta, setMeta ] = useEntityProp( 'postType', POST_TYPE, 'meta' );

	const role = meta?.role ?? '';
	const startYear = meta?.start_year ?? 0;

	const updateMeta = ( key, value ) => setMeta( { ...meta, [ key ]: value } );

	return (
		<Flex direction="column" align="stretch" gap={ 4 }>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Role', 'team-directory' ) }
				value={ role }
				onChange={ ( value ) => updateMeta( 'role', value ) }
			/>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="number"
				min={ 0 }
				max={ 9999 }
				label={ __( 'Start year', 'team-directory' ) }
				value={ startYear ? String( startYear ) : '' }
				onChange={ ( value ) => {
					const year = parseInt( value, 10 );
					updateMeta(
						'start_year',
						Number.isNaN( year ) ? 0 : Math.min( Math.max( year, 0 ), 9999 )
					);
				} }
			/>
		</Flex>
	);
}

function MemberDetailsPanel() {
	const postType = useSelect(
		( select ) => select( editorStore ).getCurrentPostType(),
		[]
	);

	if ( POST_TYPE !== postType ) {
		return null;
	}

	return (
		<PluginDocumentSettingPanel
			name="team-directory-member-details"
			title={ __( 'Member details', 'team-directory' ) }
			className="team-directory-member-details"
		>
			<MemberDetailsFields />
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'team-directory-member-details', {
	render: MemberDetailsPanel,
	icon: 'groups',
} );
