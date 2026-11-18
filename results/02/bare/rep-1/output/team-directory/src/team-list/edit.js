/**
 * Editor component for the Team List block. Shows a live preview loaded from
 * the public team-directory/v1/members endpoint.
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	ToggleControl,
	Spinner,
	Notice,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const API_PATH = '/team-directory/v1';
const MAX_PER_PAGE = 50;

/**
 * Loads the list of roles in use, for the role filter.
 *
 * @return {string[]} Roles.
 */
function useRoles() {
	const [ roles, setRoles ] = useState( [] );

	useEffect( () => {
		const controller = new AbortController();

		apiFetch( { path: `${ API_PATH }/roles`, signal: controller.signal } )
			.then( ( result ) => setRoles( Array.isArray( result ) ? result : [] ) )
			.catch( () => {} );

		return () => controller.abort();
	}, [] );

	return roles;
}

/**
 * Loads members for the preview.
 *
 * @param {string} role    Role filter.
 * @param {number} perPage Members per page.
 * @return {{members: Array, total: number, isLoading: boolean, error: string}} State.
 */
function useMembers( role, perPage ) {
	const [ state, setState ] = useState( {
		members: [],
		total: 0,
		isLoading: true,
		error: '',
	} );

	useEffect( () => {
		const controller = new AbortController();

		setState( ( prev ) => ( { ...prev, isLoading: true, error: '' } ) );

		apiFetch( {
			path: addQueryArgs( `${ API_PATH }/members`, {
				per_page: perPage,
				...( role ? { role } : {} ),
			} ),
			parse: false,
			signal: controller.signal,
		} )
			.then( async ( response ) => {
				const members = await response.json();
				setState( {
					members: Array.isArray( members ) ? members : [],
					total: parseInt(
						response.headers.get( 'X-WP-Total' ) || '0',
						10
					),
					isLoading: false,
					error: '',
				} );
			} )
			.catch( async ( error ) => {
				if ( error?.name === 'AbortError' ) {
					return;
				}

				let message = error?.message;
				// With parse: false, failed requests reject with the raw Response.
				if ( ! message && typeof error?.json === 'function' ) {
					message = ( await error.json().catch( () => ( {} ) ) )
						.message;
				}

				setState( {
					members: [],
					total: 0,
					isLoading: false,
					error:
						message ||
						__( 'Could not load team members.', 'team-directory' ),
				} );
			} );

		return () => controller.abort();
	}, [ role, perPage ] );

	return state;
}

function MemberPreview( { member, showPhoto, showBio } ) {
	return (
		<li className="wp-block-team-directory-team-list__member">
			{ showPhoto && member.photo?.url && (
				<img
					className="wp-block-team-directory-team-list__photo"
					src={ member.photo.url }
					alt={ member.photo.alt || '' }
					width={ member.photo.width }
					height={ member.photo.height }
				/>
			) }
			<div className="wp-block-team-directory-team-list__details">
				<h3 className="wp-block-team-directory-team-list__name">
					{ member.name }
				</h3>
				{ ( member.role || member.start_year ) && (
					<p className="wp-block-team-directory-team-list__meta">
						{ member.role && (
							<span className="wp-block-team-directory-team-list__role">
								{ member.role }
							</span>
						) }
						{ member.start_year && (
							<span className="wp-block-team-directory-team-list__since">
								{ sprintf(
									/* translators: %d: Year the member started. */
									__( 'Since %d', 'team-directory' ),
									member.start_year
								) }
							</span>
						) }
					</p>
				) }
				{ showBio && member.bio && (
					<div className="wp-block-team-directory-team-list__bio">
						{ member.bio
							.split( /\n{2,}/ )
							.map( ( paragraph, index ) => (
								<p key={ index }>{ paragraph }</p>
							) ) }
					</div>
				) }
			</div>
		</li>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const { role, perPage, showPhoto, showBio } = attributes;
	const blockProps = useBlockProps();
	const roles = useRoles();
	const { members, total, isLoading, error } = useMembers( role, perPage );

	const roleOptions = [
		{ label: __( 'All roles', 'team-directory' ), value: '' },
		...roles.map( ( item ) => ( { label: item, value: item } ) ),
	];

	// Keep a saved role selectable even if no published member uses it anymore.
	if ( role && ! roles.includes( role ) ) {
		roleOptions.push( { label: role, value: role } );
	}

	let content;
	if ( isLoading ) {
		content = (
			<div className="wp-block-team-directory-team-list__loading">
				<Spinner />
				<span>{ __( 'Loading team members…', 'team-directory' ) }</span>
			</div>
		);
	} else if ( error ) {
		content = (
			<Notice status="error" isDismissible={ false }>
				{ error }
			</Notice>
		);
	} else if ( ! members.length ) {
		content = (
			<p className="wp-block-team-directory-team-list__empty">
				{ __( 'No team members found.', 'team-directory' ) }
			</p>
		);
	} else {
		content = (
			<>
				<ul className="wp-block-team-directory-team-list__list">
					{ members.map( ( member ) => (
						<MemberPreview
							key={ member.id }
							member={ member }
							showPhoto={ showPhoto }
							showBio={ showBio }
						/>
					) ) }
				</ul>
				{ total > members.length && (
					<p className="wp-block-team-directory-team-list__count">
						{ sprintf(
							/* translators: 1: Number of members shown, 2: Total number of members. */
							_n(
								'Showing %1$d of %2$d member.',
								'Showing %1$d of %2$d members.',
								total,
								'team-directory'
							),
							members.length,
							total
						) }
					</p>
				) }
			</>
		);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'team-directory' ) }>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Filter by role', 'team-directory' ) }
						value={ role }
						options={ roleOptions }
						onChange={ ( value ) => setAttributes( { role: value } ) }
					/>
					<RangeControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Number of members', 'team-directory' ) }
						value={ perPage }
						min={ 1 }
						max={ MAX_PER_PAGE }
						onChange={ ( value ) =>
							setAttributes( {
								perPage: Math.min(
									Math.max( parseInt( value, 10 ) || 1, 1 ),
									MAX_PER_PAGE
								),
							} )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show photo', 'team-directory' ) }
						checked={ showPhoto }
						onChange={ ( value ) =>
							setAttributes( { showPhoto: value } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show bio', 'team-directory' ) }
						checked={ showBio }
						onChange={ ( value ) =>
							setAttributes( { showBio: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>{ content }</div>
		</>
	);
}
