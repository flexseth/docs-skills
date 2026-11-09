/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	Spinner,
	Notice,
} from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const PATH = '/team-directory/v1/members';

export default function Edit( { attributes, setAttributes } ) {
	const { role, perPage } = attributes;
	const [ members, setMembers ] = useState( [] );
	const [ allRoles, setAllRoles ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ error, setError ] = useState( null );

	// Collect available roles once, from an unfiltered request.
	useEffect( () => {
		let isMounted = true;
		apiFetch( { path: addQueryArgs( PATH, { per_page: 50 } ) } )
			.then( ( items ) => {
				if ( ! isMounted ) {
					return;
				}
				const roles = [
					...new Set(
						items.map( ( item ) => item.role ).filter( Boolean )
					),
				].sort();
				setAllRoles( roles );
			} )
			.catch( () => {} );
		return () => {
			isMounted = false;
		};
	}, [] );

	// Fetch the members shown in the preview.
	useEffect( () => {
		let isMounted = true;
		setIsLoading( true );
		setError( null );

		const query = { per_page: perPage };
		if ( role ) {
			query.role = role;
		}

		apiFetch( { path: addQueryArgs( PATH, query ) } )
			.then( ( items ) => {
				if ( isMounted ) {
					setMembers( items );
				}
			} )
			.catch( ( err ) => {
				if ( isMounted ) {
					setMembers( [] );
					setError(
						err?.message ||
							__( 'Could not load team members.', 'team-directory' )
					);
				}
			} )
			.finally( () => {
				if ( isMounted ) {
					setIsLoading( false );
				}
			} );

		return () => {
			isMounted = false;
		};
	}, [ role, perPage ] );

	const roleOptions = useMemo( () => {
		const roles = role && ! allRoles.includes( role ) ? [ ...allRoles, role ] : allRoles;
		return [
			{ label: __( 'All roles', 'team-directory' ), value: '' },
			...roles.map( ( value ) => ( { label: value, value } ) ),
		];
	}, [ allRoles, role ] );

	const blockProps = useBlockProps();

	let content;
	if ( isLoading ) {
		content = <Spinner />;
	} else if ( error ) {
		content = (
			<Notice status="error" isDismissible={ false }>
				{ error }
			</Notice>
		);
	} else if ( ! members.length ) {
		content = (
			<p className="wp-block-team-directory-list__empty">
				{ __( 'No team members found.', 'team-directory' ) }
			</p>
		);
	} else {
		content = (
			<ul className="wp-block-team-directory-list__items">
				{ members.map( ( member ) => (
					<li
						key={ member.id }
						className="wp-block-team-directory-list__item"
					>
						<span className="wp-block-team-directory-list__name">
							{ member.title ||
								__( '(no name)', 'team-directory' ) }
						</span>
						{ member.role && (
							<span className="wp-block-team-directory-list__role">
								{ member.role }
							</span>
						) }
						{ member.start_year > 0 && (
							<span className="wp-block-team-directory-list__since">
								{ sprintf(
									/* translators: %d: start year. */
									__( 'Since %d', 'team-directory' ),
									member.start_year
								) }
							</span>
						) }
					</li>
				) ) }
			</ul>
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
						onChange={ ( value ) =>
							setAttributes( { role: value } )
						}
					/>
					<RangeControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Members to show', 'team-directory' ) }
						value={ perPage }
						min={ 1 }
						max={ 50 }
						onChange={ ( value ) =>
							setAttributes( { perPage: value ?? 10 } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>{ content }</div>
		</>
	);
}
