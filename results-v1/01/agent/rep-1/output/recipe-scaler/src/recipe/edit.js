/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	TextControl,
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';

const EMPTY_INGREDIENT = { quantity: 0, unit: '', name: '' };

/**
 * Parses a NumberControl value into a non-negative number.
 *
 * @param {string|number} value    Raw value.
 * @param {number}        fallback Value used when the input is not a number.
 * @return {number} Parsed number.
 */
const toNumber = ( value, fallback = 0 ) => {
	const parsed = parseFloat( value );
	return Number.isFinite( parsed ) && parsed >= 0 ? parsed : fallback;
};

/**
 * Editor UI for the Recipe Scaler block.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Attribute setter.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { title, servings, ingredients } = attributes;
	const blockProps = useBlockProps( { className: 'recipe-scaler' } );

	const updateIngredient = ( index, key, value ) => {
		const next = ingredients.map( ( ingredient, i ) =>
			i === index ? { ...ingredient, [ key ]: value } : ingredient
		);
		setAttributes( { ingredients: next } );
	};

	const addIngredient = () => {
		setAttributes( {
			ingredients: [ ...ingredients, { ...EMPTY_INGREDIENT } ],
		} );
	};

	const removeIngredient = ( index ) => {
		setAttributes( {
			ingredients: ingredients.filter( ( _, i ) => i !== index ),
		} );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Servings', 'recipe-scaler' ) }>
					<NumberControl
						__next40pxDefaultSize
						label={ __( 'Base servings', 'recipe-scaler' ) }
						help={ __(
							'The number of servings the ingredient quantities are written for.',
							'recipe-scaler'
						) }
						min={ 1 }
						step={ 1 }
						value={ servings }
						onChange={ ( value ) =>
							setAttributes( {
								servings: Math.max(
									1,
									Math.round( toNumber( value, 1 ) )
								),
							} )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<RichText
					tagName="h3"
					className="recipe-scaler__title"
					value={ title }
					onChange={ ( value ) => setAttributes( { title: value } ) }
					placeholder={ __( 'Recipe title…', 'recipe-scaler' ) }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
				/>

				<p className="recipe-scaler__servings-note">
					{ sprintf(
						/* translators: %d: number of servings. */
						__( 'Quantities for %d servings', 'recipe-scaler' ),
						servings
					) }
				</p>

				<ul className="recipe-scaler__editor-list">
					{ ingredients.map( ( ingredient, index ) => (
						<li key={ index } className="recipe-scaler__editor-row">
							<NumberControl
								__next40pxDefaultSize
								label={ __( 'Quantity', 'recipe-scaler' ) }
								min={ 0 }
								step={ 0.01 }
								value={ ingredient.quantity || '' }
								onChange={ ( value ) =>
									updateIngredient(
										index,
										'quantity',
										toNumber( value )
									)
								}
							/>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __( 'Unit', 'recipe-scaler' ) }
								value={ ingredient.unit || '' }
								onChange={ ( value ) =>
									updateIngredient( index, 'unit', value )
								}
							/>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __( 'Ingredient', 'recipe-scaler' ) }
								value={ ingredient.name || '' }
								onChange={ ( value ) =>
									updateIngredient( index, 'name', value )
								}
							/>
							<Button
								__next40pxDefaultSize
								variant="tertiary"
								isDestructive
								className="recipe-scaler__remove"
								onClick={ () => removeIngredient( index ) }
								label={ sprintf(
									/* translators: %d: ingredient position. */
									__( 'Remove ingredient %d', 'recipe-scaler' ),
									index + 1
								) }
							>
								{ __( 'Remove', 'recipe-scaler' ) }
							</Button>
						</li>
					) ) }
				</ul>

				<Button
					__next40pxDefaultSize
					variant="secondary"
					onClick={ addIngredient }
				>
					{ __( 'Add ingredient', 'recipe-scaler' ) }
				</Button>
			</div>
		</>
	);
}
