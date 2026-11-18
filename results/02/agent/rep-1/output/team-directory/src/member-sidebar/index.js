/**
 * Document sidebar panel for team member role and start year.
 */
import { __, sprintf } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { TextControl } from '@wordpress/components';

const POST_TYPE = 'td_member';
const ROLE_META = 'td_role';
const START_YEAR_META = 'td_start_year';

const { minYear = 1900, maxYear = new Date().getFullYear() + 1 } =
	window.teamDirectoryMember || {};

function MemberFields() {
	const [ meta, setMeta ] = useEntityProp( 'postType', POST_TYPE, 'meta' );

	const role = meta?.[ ROLE_META ] ?? '';
	const startYear = meta?.[ START_YEAR_META ] || '';

	const onChangeYear = ( value ) => {
		const year = parseInt( value, 10 );
		setMeta( {
			...meta,
			[ START_YEAR_META ]: Number.isNaN( year ) ? 0 : year,
		} );
	};

	const yearIsValid =
		'' === startYear || ( startYear >= minYear && startYear <= maxYear );

	return (
		<>
			<TextControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ __( 'Role', 'team-directory' ) }
				help={ __( 'For example “Designer” or “CTO”.', 'team-directory' ) }
				value={ role }
				maxLength={ 100 }
				onChange={ ( value ) =>
					setMeta( { ...meta, [ ROLE_META ]: value } )
				}
			/>
			<div style={ { marginTop: '16px' } }>
				<TextControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					type="number"
					label={ __( 'Start year', 'team-directory' ) }
					help={
						yearIsValid
							? __( 'Year the member joined.', 'team-directory' )
							: sprintfRange()
					}
					min={ minYear }
					max={ maxYear }
					step={ 1 }
					value={ startYear }
					onChange={ onChangeYear }
				/>
			</div>
		</>
	);
}

function sprintfRange() {
	return sprintf(
		/* translators: 1: earliest year, 2: latest year. */
		__(
			'Enter a year between %1$d and %2$d. Other values are not saved.',
			'team-directory'
		),
		minYear,
		maxYear
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
		>
			<MemberFields />
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'team-directory-member-details', {
	render: MemberDetailsPanel,
	icon: 'groups',
} );
