/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	Button,
	TextControl,
	Flex,
	FlexItem,
	FlexBlock,
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';
import { trash, plus } from '@wordpress/icons';

const EMPTY_INGREDIENT = { quantity: '', unit: '', name: '' };

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

	const onChangeServings = ( value ) => {
		const parsed = parseInt( value, 10 );
		setAttributes( {
			servings: Number.isNaN( parsed ) ? 1 : Math.max( 1, parsed ),
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
							'Number of servings the ingredient quantities are written for.',
							'recipe-scaler'
						) }
						min={ 1 }
						step={ 1 }
						value={ servings }
						onChange={ onChangeServings }
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

				<p className="recipe-scaler__servings">
					{ sprintf(
						/* translators: %d: number of servings. */
						__( 'Servings: %d', 'recipe-scaler' ),
						servings
					) }
				</p>

				<div className="recipe-scaler__editor-ingredients">
					{ ingredients.map( ( ingredient, index ) => (
						<Flex
							key={ index }
							className="recipe-scaler__editor-row"
							align="flex-end"
						>
							<FlexItem className="recipe-scaler__editor-quantity">
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									label={ __( 'Quantity', 'recipe-scaler' ) }
									hideLabelFromVision={ index > 0 }
									placeholder={ __(
										'e.g. 1.5 or 1/2',
										'recipe-scaler'
									) }
									value={ ingredient.quantity }
									onChange={ ( value ) =>
										updateIngredient(
											index,
											'quantity',
											value
										)
									}
								/>
							</FlexItem>
							<FlexItem className="recipe-scaler__editor-unit">
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									label={ __( 'Unit', 'recipe-scaler' ) }
									hideLabelFromVision={ index > 0 }
									placeholder={ __( 'g', 'recipe-scaler' ) }
									value={ ingredient.unit }
									onChange={ ( value ) =>
										updateIngredient( index, 'unit', value )
									}
								/>
							</FlexItem>
							<FlexBlock>
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									label={ __( 'Ingredient', 'recipe-scaler' ) }
									hideLabelFromVision={ index > 0 }
									placeholder={ __( 'flour', 'recipe-scaler' ) }
									value={ ingredient.name }
									onChange={ ( value ) =>
										updateIngredient( index, 'name', value )
									}
								/>
							</FlexBlock>
							<FlexItem>
								<Button
									__next40pxDefaultSize
									icon={ trash }
									label={ __(
										'Remove ingredient',
										'recipe-scaler'
									) }
									isDestructive
									onClick={ () => removeIngredient( index ) }
								/>
							</FlexItem>
						</Flex>
					) ) }

					<Button
						__next40pxDefaultSize
						variant="secondary"
						icon={ plus }
						onClick={ addIngredient }
					>
						{ __( 'Add ingredient', 'recipe-scaler' ) }
					</Button>
				</div>
			</div>
		</>
	);
}
