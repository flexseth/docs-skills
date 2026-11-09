/**
 * Block Visibility Rules – editor integration.
 *
 * Adds a `visibilityRule` attribute and a "Visibility" inspector panel to
 * every eligible block without registering any new block.
 *
 * Extensibility:
 * - Filter `blockVisibilityRules.ruleTypes`: receives the array of rule type
 *   options (`{ label, value }`) and the block name; return a modified array
 *   to add or remove rule types.
 * - Action `blockVisibilityRules.ruleChanged`: fires with
 *   `( newRule, previousRule, { clientId, name } )` whenever a rule changes.
 */

/**
 * WordPress dependencies
 */
import { addFilter, applyFilters, doAction } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { getBlockType, hasBlockSupport } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import {
	Button,
	DateTimePicker,
	Notice,
	PanelBody,
	RadioControl,
} from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import { getSettings as getDateSettings } from '@wordpress/date';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './editor.scss';

const ATTRIBUTE = 'visibilityRule';
const EXCLUDED_BLOCKS = [ 'core/freeform' ];

/**
 * Whether a block type should receive the visibility attribute.
 *
 * @param {Object} settings Block type settings.
 * @param {string} name     Block type name.
 * @return {boolean} True when supported.
 */
const isSupported = ( settings, name ) =>
	! EXCLUDED_BLOCKS.includes( name ) &&
	hasBlockSupport( settings, 'customClassName', true );

/**
 * Returns the type of a rule, defaulting to "all".
 *
 * @param {Object|undefined} rule Visibility rule.
 * @return {string} Rule type.
 */
const getRuleType = ( rule ) => ( rule && rule.type ) || 'all';

/**
 * Default rule types. Other plugins can extend them via the
 * `blockVisibilityRules.ruleTypes` filter.
 *
 * @return {Array<{label: string, value: string}>} Rule type options.
 */
const getDefaultRuleTypes = () => [
	{ label: __( 'Everyone', 'block-visibility-rules' ), value: 'all' },
	{
		label: __( 'Logged-in users only', 'block-visibility-rules' ),
		value: 'logged-in',
	},
	{
		label: __( 'Logged-out visitors only', 'block-visibility-rules' ),
		value: 'logged-out',
	},
	{
		label: __( 'Within a date range', 'block-visibility-rules' ),
		value: 'date-range',
	},
];

/**
 * 1. Register the attribute on every eligible block.
 *
 * @param {Object} settings Block type settings.
 * @param {string} name     Block type name.
 * @return {Object} Filtered settings.
 */
function addVisibilityAttribute( settings, name ) {
	if ( ! isSupported( settings, name ) ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			[ ATTRIBUTE ]: {
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
 * @param {Object}   props               Component props.
 * @param {string}   props.name          Block name.
 * @param {string}   props.clientId      Block client ID.
 * @param {Object}   props.rule          Current rule.
 * @param {Function} props.setAttributes Block attribute setter.
 * @return {Element} Panel element.
 */
function VisibilityPanel( { name, clientId, rule, setAttributes } ) {
	const ruleTypes = useMemo( () => {
		const filtered = applyFilters(
			'blockVisibilityRules.ruleTypes',
			getDefaultRuleTypes(),
			name
		);
		return Array.isArray( filtered ) ? filtered : getDefaultRuleTypes();
	}, [ name ] );

	// Respect the site time format (12h vs 24h) like core's post scheduler.
	const is12Hour = useMemo( () => {
		const { formats } = getDateSettings();
		return /a(?!\\)/i.test(
			( formats?.time || '' )
				.toLowerCase()
				.replace( /\\\\/g, '' )
				.split( '' )
				.reverse()
				.join( '' )
		);
	}, [] );

	const type = getRuleType( rule );

	const updateRule = ( changes ) => {
		const previousRule = rule;
		const merged = { ...( rule || {} ), ...changes };
		// Store nothing for the default rule so markup stays clean.
		const nextRule = 'all' === merged.type ? undefined : merged;

		setAttributes( { [ ATTRIBUTE ]: nextRule } );

		doAction(
			'blockVisibilityRules.ruleChanged',
			nextRule || { type: 'all' },
			previousRule || { type: 'all' },
			{ clientId, name }
		);
	};

	const start = rule?.start || '';
	const end = rule?.end || '';
	const isInvalidRange =
		!! start && !! end && new Date( start ) > new Date( end );

	return (
		<InspectorControls>
			<PanelBody
				title={ __( 'Visibility', 'block-visibility-rules' ) }
				initialOpen={ 'all' !== type }
				className="block-visibility-rules-panel"
			>
				<RadioControl
					label={ __( 'Show this block to', 'block-visibility-rules' ) }
					selected={ type }
					options={ ruleTypes }
					onChange={ ( value ) => updateRule( { type: value } ) }
				/>

				{ 'date-range' === type && (
					<>
						<p>
							{ __(
								'Dates use the site timezone. Leave a date empty for an open-ended range.',
								'block-visibility-rules'
							) }
						</p>

						<fieldset className="block-visibility-rules-panel__date">
							<legend>
								{ __( 'Start', 'block-visibility-rules' ) }
							</legend>
							<DateTimePicker
								currentDate={ start || null }
								onChange={ ( value ) =>
									updateRule( { start: value || '' } )
								}
								is12Hour={ is12Hour }
							/>
							{ !! start && (
								<Button
									className="block-visibility-rules-panel__clear"
									variant="secondary"
									size="small"
									onClick={ () => updateRule( { start: '' } ) }
								>
									{ __(
										'Clear start date',
										'block-visibility-rules'
									) }
								</Button>
							) }
						</fieldset>

						<fieldset className="block-visibility-rules-panel__date">
							<legend>
								{ __( 'End', 'block-visibility-rules' ) }
							</legend>
							<DateTimePicker
								currentDate={ end || null }
								onChange={ ( value ) =>
									updateRule( { end: value || '' } )
								}
								is12Hour={ is12Hour }
							/>
							{ !! end && (
								<Button
									className="block-visibility-rules-panel__clear"
									variant="secondary"
									size="small"
									onClick={ () => updateRule( { end: '' } ) }
								>
									{ __(
										'Clear end date',
										'block-visibility-rules'
									) }
								</Button>
							) }
						</fieldset>

						{ isInvalidRange && (
							<Notice status="warning" isDismissible={ false }>
								{ __(
									'The end date is before the start date, so this block will never be shown.',
									'block-visibility-rules'
								) }
							</Notice>
						) }
					</>
				) }
			</PanelBody>
		</InspectorControls>
	);
}

/**
 * 2. Add the inspector panel to the block edit UI.
 */
const withVisibilityControls = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		const { name, clientId, attributes, setAttributes, isSelected } =
			props;

		// The attribute only exists on supported blocks.
		if ( ! getBlockType( name )?.attributes?.[ ATTRIBUTE ] ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<>
				<BlockEdit { ...props } />
				{ isSelected && (
					<VisibilityPanel
						name={ name }
						clientId={ clientId }
						rule={ attributes[ ATTRIBUTE ] }
						setAttributes={ setAttributes }
					/>
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
 * 3. Flag restricted blocks in the editor canvas.
 */
const withVisibilityClassName = createHigherOrderComponent(
	( BlockListBlock ) => ( props ) => {
		const rule = props.attributes?.[ ATTRIBUTE ];

		if ( 'all' === getRuleType( rule ) ) {
			return <BlockListBlock { ...props } />;
		}

		const className = [ props.className, 'has-visibility-rule' ]
			.filter( Boolean )
			.join( ' ' );

		return <BlockListBlock { ...props } className={ className } />;
	},
	'withVisibilityClassName'
);

addFilter(
	'editor.BlockListBlock',
	'block-visibility-rules/with-class-name',
	withVisibilityClassName
);
