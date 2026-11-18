/**
 * Document sidebar panel for editing a team member's role and start year.
 */
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { TextControl, __experimentalVStack as VStack } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const POST_TYPE = 'tdir_member';
const META_ROLE = '_tdir_role';
const META_START_YEAR = '_tdir_start_year';

function MemberDetailsPanel() {
	const { postType, postId } = useSelect( ( select ) => {
		const { getCurrentPostType, getCurrentPostId } = select( editorStore );
		return {
			postType: getCurrentPostType(),
			postId: getCurrentPostId(),
		};
	}, [] );

	const [ meta, setMeta ] = useEntityProp(
		'postType',
		POST_TYPE,
		'meta',
		postId
	);

	if ( postType !== POST_TYPE || ! meta ) {
		return null;
	}

	const startYear = meta[ META_START_YEAR ];
	const maxYear = new Date().getFullYear() + 1;

	return (
		<PluginDocumentSettingPanel
			name="team-directory-member-details"
			title={ __( 'Member details', 'team-directory' ) }
		>
			<VStack spacing={ 4 }>
				<TextControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label={ __( 'Role', 'team-directory' ) }
					help={ __( 'For example “Designer” or “Engineering Lead”.', 'team-directory' ) }
					value={ meta[ META_ROLE ] || '' }
					maxLength={ 100 }
					onChange={ ( value ) =>
						setMeta( { ...meta, [ META_ROLE ]: value } )
					}
				/>
				<TextControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					type="number"
					label={ __( 'Start year', 'team-directory' ) }
					min={ 1900 }
					max={ maxYear }
					value={ startYear ? String( startYear ) : '' }
					onChange={ ( value ) => {
						const year = parseInt( value, 10 );
						setMeta( {
							...meta,
							[ META_START_YEAR ]: Number.isNaN( year ) ? 0 : year,
						} );
					} }
				/>
			</VStack>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'team-directory-member-details', {
	render: MemberDetailsPanel,
	icon: 'groups',
} );
