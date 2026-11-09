/**
 * Block Visibility Rules – editor integration.
 *
 * Adds a `visibilityRule` attribute and an Inspector panel to every block
 * that supports custom class names.
 */

/**
 * WordPress dependencies
 */
import { addFilter, applyFilters, doAction } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { getBlockType, hasBlockSupport } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RadioControl,
	DateTimePicker,
	Button,
	Notice,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './editor.scss';

const EXCLUDED_BLOCKS = [ 'core/freeform' ];

/**
 * Whether a block type should receive the visibility setting.
 *
 * @param {string} name     Block name.
 * @param {Object} settings Block settings.
 * @return {boolean} True when supported.
 */
const isSupported = ( name, settings ) =>
	!! settings &&
	! EXCLUDED_BLOCKS.includes( name ) &&
	hasBlockSupport( settings, 'customClassName', true );

/**
 * Returns the list of selectable rule types, filterable by other plugins.
 *
 * Each entry is `{ label: string, value: string }`. Rule types added here
 * must be evaluated server-side via the `block_visibility_rules_match`
 * PHP filter.
 *
 * @return {Array<{label: string, value: string}>} Rule types.
 */
const getRuleTypes = () => {
	const defaults = [
		{ label: __( 'Everyone', 'block-visibility-rules' ), value: 'all' },
		{
			label: __( 'Logged-in users', 'block-visibility-rules' ),
			value: 'logged-in',
		},
		{
			label: __( 'Logged-out visitors', 'block-visibility-rules' ),
			value: 'logged-out',
		},
		{
			label: __( 'Date range', 'block-visibility-rules' ),
			value: 'date-range',
		},
	];

	const types = applyFilters( 'blockVisibilityRules.ruleTypes', defaults );

	if ( ! Array.isArray( types ) ) {
		return defaults;
	}

	return types.filter(
		( type ) =>
			type &&
			typeof type.value === 'string' &&
			typeof type.label === 'string'
	);
};

/**
 * Whether a rule actually restricts visibility.
 *
 * @param {Object|undefined} rule Rule object.
 * @return {boolean} True when restricted.
 */
const isRestricted = ( rule ) => !! rule?.type && rule.type !== 'all';

/**
 * 1. Register the attribute on every supported block.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Filtered settings.
 */
function addVisibilityAttribute( settings, name ) {
	if ( ! isSupported( name, settings ) ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			visibilityRule: {
				type: 'object',
			},
		},
	};
}

addFilter(
	'blocks.registerBlockType',
	'block-visibility-rules/add-attribute',
	addVisibilityAttribute
);

/**
 * Inspector panel component.
 *
 * @param {Object}   props          Component props.
 * @param {Object}   props.rule     Current rule.
 * @param {Function} props.onChange Change handler.
 * @return {Element} Panel.
 */
function VisibilityPanel( { rule, onChange } ) {
	const ruleTypes = useMemo( getRuleTypes, [] );
	const type = rule?.type || 'all';

	const startDate = rule?.start ? new Date( rule.start ) : null;
	const endDate = rule?.end ? new Date( rule.end ) : null;
	const invalidRange =
		startDate && endDate && startDate.getTime() > endDate.getTime();

	return (
		<PanelBody
			title={ __( 'Visibility', 'block-visibility-rules' ) }
			initialOpen={ isRestricted( rule ) }
		>
			<RadioControl
				label={ __( 'Show this block to', 'block-visibility-rules' ) }
				selected={ type }
				options={ ruleTypes }
				onChange={ ( newType ) => {
					if ( newType === 'all' ) {
						onChange( undefined );
						return;
					}
					if ( newType === 'date-range' ) {
						onChange( {
							type: newType,
							start: rule?.start || null,
							end: rule?.end || null,
						} );
						return;
					}
					onChange( { type: newType } );
				} }
			/>

			{ type === 'date-range' && (
				<>
					<div className="block-visibility-rules__date-field">
						<span className="block-visibility-rules__date-label">
							{ __( 'Start', 'block-visibility-rules' ) }
						</span>
						<DateTimePicker
							currentDate={ rule?.start || null }
							onChange={ ( start ) =>
								onChange( { ...rule, start: start || null } )
							}
							is12Hour={ false }
						/>
						{ rule?.start && (
							<Button
								variant="link"
								isDestructive
								onClick={ () =>
									onChange( { ...rule, start: null } )
								}
							>
								{ __( 'Clear start', 'block-visibility-rules' ) }
							</Button>
						) }
					</div>
					<div className="block-visibility-rules__date-field">
						<span className="block-visibility-rules__date-label">
							{ __( 'End', 'block-visibility-rules' ) }
						</span>
						<DateTimePicker
							currentDate={ rule?.end || null }
							onChange={ ( end ) =>
								onChange( { ...rule, end: end || null } )
							}
							is12Hour={ false }
						/>
						{ rule?.end && (
							<Button
								variant="link"
								isDestructive
								onClick={ () =>
									onChange( { ...rule, end: null } )
								}
							>
								{ __( 'Clear end', 'block-visibility-rules' ) }
							</Button>
						) }
					</div>
					{ invalidRange && (
						<Notice status="warning" isDismissible={ false }>
							{ __(
								'The end date is before the start date, so this block will never be shown.',
								'block-visibility-rules'
							) }
						</Notice>
					) }
					<p className="components-base-control__help">
						{ __(
							'Dates use the site timezone. Leave a field empty for an open-ended range.',
							'block-visibility-rules'
						) }
					</p>
				</>
			) }
		</PanelBody>
	);
}

/**
 * 2. Add the Inspector panel.
 */
const withVisibilityControls = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		const { name, attributes, setAttributes, clientId, isSelected } =
			props;

		if ( ! isSupported( name, getBlockType( name ) ) ) {
			return <BlockEdit { ...props } />;
		}

		const rule = attributes.visibilityRule;

		const onChange = ( newRule ) => {
			setAttributes( { visibilityRule: newRule } );

			/**
			 * Fires whenever a block's visibility rule changes.
			 *
			 * @param {Object|undefined} newRule  New rule (undefined = everyone).
			 * @param {Object|undefined} oldRule  Previous rule.
			 * @param {string}           clientId Block client ID.
			 * @param {string}           name     Block name.
			 */
			doAction(
				'blockVisibilityRules.ruleChanged',
				newRule,
				rule,
				clientId,
				name
			);
		};

		return (
			<>
				<BlockEdit { ...props } />
				{ isSelected && (
					<InspectorControls>
						<VisibilityPanel rule={ rule } onChange={ onChange } />
					</InspectorControls>
				) }
			</>
		);
	},
	'withVisibilityControls'
);

addFilter(
	'editor.BlockEdit',
	'block-visibility-rules/with-controls',
	withVisibilityControls
);

/**
 * 3. Add an editor class so restricted blocks are outlined.
 */
const withVisibilityClass = createHigherOrderComponent(
	( BlockListBlock ) => ( props ) => {
		const rule = props.attributes?.visibilityRule;

		if ( ! isRestricted( rule ) ) {
			return <BlockListBlock { ...props } />;
		}

		const className = [ props.className, 'has-visibility-rule' ]
			.filter( Boolean )
			.join( ' ' );

		return <BlockListBlock { ...props } className={ className } />;
	},
	'withVisibilityClass'
);

addFilter(
	'editor.BlockListBlock',
	'block-visibility-rules/with-class',
	withVisibilityClass
);
