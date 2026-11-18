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
	TextControl,
	Button,
	Flex,
	FlexItem,
	FlexBlock,
} from '@wordpress/components';

/**
 * Editor component for the recipe block.
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
		const next = ingredients.map( ( item, i ) =>
			i === index ? { ...item, [ key ]: value } : item
		);
		setAttributes( { ingredients: next } );
	};

	const updateQuantity = ( index, value ) => {
		if ( '' === value ) {
			updateIngredient( index, 'quantity', null );
			return;
		}
		const parsed = parseFloat( value );
		updateIngredient(
			index,
			'quantity',
			Number.isFinite( parsed ) && parsed >= 0 ? parsed : null
		);
	};

	const addIngredient = () => {
		setAttributes( {
			ingredients: [
				...ingredients,
				{ quantity: null, unit: '', name: '' },
			],
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
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="number"
						min={ 1 }
						step={ 1 }
						label={ __( 'Base servings', 'recipe-scaler' ) }
						help={ __(
							'Number of servings the ingredient quantities are written for.',
							'recipe-scaler'
						) }
						value={ servings }
						onChange={ ( value ) => {
							const parsed = parseInt( value, 10 );
							setAttributes( {
								servings:
									Number.isFinite( parsed ) && parsed >= 1
										? parsed
										: 1,
							} );
						} }
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
					allowedFormats={ [] }
					disableLineBreaks
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
							align="flex-end"
							className="recipe-scaler__editor-row"
						>
							<FlexItem className="recipe-scaler__editor-quantity">
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									type="number"
									min={ 0 }
									step="any"
									label={ __( 'Quantity', 'recipe-scaler' ) }
									value={
										null === ingredient.quantity ||
										undefined === ingredient.quantity
											? ''
											: ingredient.quantity
									}
									onChange={ ( value ) =>
										updateQuantity( index, value )
									}
								/>
							</FlexItem>
							<FlexItem className="recipe-scaler__editor-unit">
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									label={ __( 'Unit', 'recipe-scaler' ) }
									value={ ingredient.unit || '' }
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
									value={ ingredient.name || '' }
									onChange={ ( value ) =>
										updateIngredient( index, 'name', value )
									}
								/>
							</FlexBlock>
							<FlexItem>
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
							</FlexItem>
						</Flex>
					) ) }

					<Button
						__next40pxDefaultSize
						variant="secondary"
						onClick={ addIngredient }
					>
						{ __( 'Add ingredient', 'recipe-scaler' ) }
					</Button>
				</div>
			</div>
		</>
	);
}
