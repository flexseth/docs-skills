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
	RangeControl,
	TextControl,
} from '@wordpress/components';

const EMPTY_INGREDIENT = { quantity: '', unit: '', name: '' };

/**
 * Editor UI for the recipe block.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Attribute setter.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { title, servings, ingredients } = attributes;
	const blockProps = useBlockProps( { className: 'wp-block-recipe-scaler' } );

	const updateIngredient = ( index, key, value ) => {
		setAttributes( {
			ingredients: ingredients.map( ( ingredient, i ) =>
				i === index ? { ...ingredient, [ key ]: value } : ingredient
			),
		} );
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
				<PanelBody title={ __( 'Recipe settings', 'recipe-scaler' ) }>
					<RangeControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Base servings', 'recipe-scaler' ) }
						help={ __(
							'The number of servings the ingredient quantities are written for.',
							'recipe-scaler'
						) }
						value={ servings }
						onChange={ ( value ) =>
							setAttributes( {
								servings: Math.max(
									1,
									parseInt( value, 10 ) || 1
								),
							} )
						}
						min={ 1 }
						max={ 100 }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<RichText
					tagName="h3"
					className="wp-block-recipe-scaler__title"
					value={ title }
					onChange={ ( value ) => setAttributes( { title: value } ) }
					placeholder={ __( 'Recipe title…', 'recipe-scaler' ) }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
				/>

				<p className="wp-block-recipe-scaler__servings">
					{ sprintf(
						/* translators: %d: number of servings. */
						__( 'Servings: %d', 'recipe-scaler' ),
						servings
					) }
				</p>

				<div className="wp-block-recipe-scaler__editor-ingredients">
					{ ingredients.map( ( ingredient, index ) => (
						<div
							className="wp-block-recipe-scaler__editor-row"
							key={ index }
						>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								className="wp-block-recipe-scaler__editor-quantity"
								label={ __( 'Quantity', 'recipe-scaler' ) }
								help={
									index === 0
										? __(
												'e.g. 2, 1.5 or 1/2',
												'recipe-scaler'
										  )
										: undefined
								}
								value={ ingredient.quantity ?? '' }
								onChange={ ( value ) =>
									updateIngredient( index, 'quantity', value )
								}
							/>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								className="wp-block-recipe-scaler__editor-unit"
								label={ __( 'Unit', 'recipe-scaler' ) }
								value={ ingredient.unit ?? '' }
								onChange={ ( value ) =>
									updateIngredient( index, 'unit', value )
								}
							/>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								className="wp-block-recipe-scaler__editor-name"
								label={ __( 'Ingredient', 'recipe-scaler' ) }
								value={ ingredient.name ?? '' }
								onChange={ ( value ) =>
									updateIngredient( index, 'name', value )
								}
							/>
							<Button
								__next40pxDefaultSize
								icon="trash"
								isDestructive
								label={ __(
									'Remove ingredient',
									'recipe-scaler'
								) }
								onClick={ () => removeIngredient( index ) }
							/>
						</div>
					) ) }

					<Button
						__next40pxDefaultSize
						variant="secondary"
						icon="plus"
						onClick={ addIngredient }
					>
						{ __( 'Add ingredient', 'recipe-scaler' ) }
					</Button>
				</div>
			</div>
		</>
	);
}
