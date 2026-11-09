/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	ToggleControl,
	Spinner,
	Notice,
} from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const MAX_PER_PAGE = 50;

export default function Edit( { attributes, setAttributes } ) {
	const { role, perPage, showStartYear } = attributes;

	const [ members, setMembers ] = useState( [] );
	const [ total, setTotal ] = useState( 0 );
	const [ roles, setRoles ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ error, setError ] = useState( null );

	// Load the list of roles once for the filter control.
	useEffect( () => {
		const controller = new AbortController();

		apiFetch( {
			path: '/team-directory/v1/roles',
			signal: controller.signal,
		} )
			.then( ( result ) => setRoles( Array.isArray( result ) ? result : [] ) )
			.catch( () => {
				if ( ! controller.signal.aborted ) {
					setRoles( [] );
				}
			} );

		return () => controller.abort();
	}, [] );

	// Load members whenever the filter or page size changes.
	useEffect( () => {
		const controller = new AbortController();
		const query = { per_page: perPage };
		if ( role ) {
			query.role = role;
		}

		setIsLoading( true );
		setError( null );

		apiFetch( {
			path: addQueryArgs( '/team-directory/v1/members', query ),
			parse: false,
			signal: controller.signal,
		} )
			.then( ( response ) => {
				setTotal(
					parseInt( response.headers.get( 'X-WP-Total' ), 10 ) || 0
				);
				return response.json();
			} )
			.then( ( result ) => {
				setMembers( Array.isArray( result ) ? result : [] );
				setIsLoading( false );
			} )
			.catch( ( fetchError ) => {
				if ( controller.signal.aborted ) {
					return;
				}
				setMembers( [] );
				setError(
					fetchError?.message ||
						__( 'Could not load team members.', 'team-directory' )
				);
				setIsLoading( false );
			} );

		return () => controller.abort();
	}, [ role, perPage ] );

	const roleOptions = useMemo( () => {
		const values = role && ! roles.includes( role ) ? [ role, ...roles ] : roles;
		return [
			{ label: __( 'All roles', 'team-directory' ), value: '' },
			...values.map( ( value ) => ( { label: value, value } ) ),
		];
	}, [ roles, role ] );

	const blockProps = useBlockProps( { className: 'team-directory-list' } );

	let content;
	if ( isLoading ) {
		content = (
			<div className="team-directory-list__loading">
				<Spinner />
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
			<p className="team-directory-list__empty">
				{ __( 'No team members found.', 'team-directory' ) }
			</p>
		);
	} else {
		content = (
			<>
				<ul className="team-directory-list__items">
					{ members.map( ( member ) => (
						<li
							key={ member.id }
							className="team-directory-list__item"
						>
							<span className="team-directory-list__name">
								{ member.name ||
									__( '(no name)', 'team-directory' ) }
							</span>
							{ member.role && (
								<span className="team-directory-list__role">
									{ member.role }
								</span>
							) }
							{ showStartYear && member.start_year > 0 && (
								<span className="team-directory-list__since">
									{ sprintf(
										/* translators: %d: Year the member started. */
										__( 'Since %d', 'team-directory' ),
										member.start_year
									) }
								</span>
							) }
						</li>
					) ) }
				</ul>
				{ total > members.length && (
					<p className="team-directory-list__count">
						{ sprintf(
							/* translators: 1: Number shown, 2: Total number of members. */
							__( 'Showing %1$d of %2$d members', 'team-directory' ),
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
						label={ __( 'Members to show', 'team-directory' ) }
						value={ perPage }
						min={ 1 }
						max={ MAX_PER_PAGE }
						onChange={ ( value ) =>
							setAttributes( { perPage: value || 1 } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show start year', 'team-directory' ) }
						checked={ showStartYear }
						onChange={ ( value ) =>
							setAttributes( { showStartYear: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>{ content }</div>
		</>
	);
}
