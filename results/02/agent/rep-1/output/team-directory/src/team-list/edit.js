/**
 * Editor component for the Team List block.
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	Notice,
	PanelBody,
	RangeControl,
	SelectControl,
	Spinner,
	ToggleControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const API_BASE = '/team-directory/v1';
const MAX_PER_PAGE = 50;

/**
 * Load the list of roles in use.
 *
 * @return {string[]} Roles.
 */
function useRoles() {
	const [ roles, setRoles ] = useState( [] );

	useEffect( () => {
		const controller = new AbortController();

		apiFetch( { path: `${ API_BASE }/roles`, signal: controller.signal } )
			.then( ( result ) => setRoles( Array.isArray( result ) ? result : [] ) )
			.catch( () => {} );

		return () => controller.abort();
	}, [] );

	return roles;
}

/**
 * Load members for the preview.
 *
 * @param {string} role    Role filter.
 * @param {number} perPage Items per page.
 * @return {Object} { members, total, isLoading, error }
 */
function useMembers( role, perPage ) {
	const [ state, setState ] = useState( {
		members: [],
		total: 0,
		isLoading: true,
		error: null,
	} );

	useEffect( () => {
		const controller = new AbortController();

		setState( ( prev ) => ( { ...prev, isLoading: true, error: null } ) );

		apiFetch( {
			path: addQueryArgs( `${ API_BASE }/members`, {
				per_page: perPage,
				...( role ? { role } : {} ),
			} ),
			parse: false,
			signal: controller.signal,
		} )
			.then( async ( response ) => {
				const members = await response.json();
				const total = parseInt(
					response.headers.get( 'X-WP-Total' ),
					10
				);

				setState( {
					members: Array.isArray( members ) ? members : [],
					total: Number.isNaN( total ) ? 0 : total,
					isLoading: false,
					error: null,
				} );
			} )
			.catch( async ( error ) => {
				if ( error?.name === 'AbortError' ) {
					return;
				}

				let message = __(
					'Team members could not be loaded.',
					'team-directory'
				);

				// With parse: false, apiFetch rejects with the raw Response.
				if ( error && typeof error.json === 'function' ) {
					try {
						const body = await error.json();
						message = body?.message || message;
					} catch ( e ) {
						// Body was not JSON; keep the generic message.
					}
				} else if ( error?.message ) {
					message = error.message;
				}

				setState( {
					members: [],
					total: 0,
					isLoading: false,
					error: message,
				} );
			} );

		return () => controller.abort();
	}, [ role, perPage ] );

	return state;
}

function MemberItem( { member, showPhoto, showBio } ) {
	return (
		<li className="wp-block-team-directory-team-list__item">
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
					{ member.name || __( '(no name)', 'team-directory' ) }
				</h3>
				{ member.role && (
					<p className="wp-block-team-directory-team-list__role">
						{ member.role }
					</p>
				) }
				{ member.start_year && (
					<p className="wp-block-team-directory-team-list__since">
						{ sprintf(
							/* translators: %d: start year. */
							__( 'Since %d', 'team-directory' ),
							member.start_year
						) }
					</p>
				) }
				{ showBio && member.bio && (
					<p className="wp-block-team-directory-team-list__bio">
						{ member.bio }
					</p>
				) }
			</div>
		</li>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const { role, perPage, showPhoto, showBio } = attributes;
	const roles = useRoles();
	const { members, total, isLoading, error } = useMembers( role, perPage );
	const blockProps = useBlockProps();

	const roleOptions = [
		{ label: __( 'All roles', 'team-directory' ), value: '' },
		...roles.map( ( item ) => ( { label: item, value: item } ) ),
	];
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
						<MemberItem
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
							/* translators: 1: number shown, 2: total number of members. */
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
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Filter by role', 'team-directory' ) }
						value={ role }
						options={ roleOptions }
						onChange={ ( value ) =>
							setAttributes( { role: value } )
						}
					/>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Number of members', 'team-directory' ) }
						value={ perPage }
						min={ 1 }
						max={ MAX_PER_PAGE }
						onChange={ ( value ) =>
							setAttributes( { perPage: value || 1 } )
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
