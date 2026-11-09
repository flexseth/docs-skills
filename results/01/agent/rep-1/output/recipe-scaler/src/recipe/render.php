<?php
/**
 * Server-side rendering of the `recipe-scaler/recipe` block.
 *
 * @package RecipeScaler
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused).
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recipe_scaler_title         = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$recipe_scaler_base_servings = isset( $attributes['servings'] ) ? max( 1, (int) $attributes['servings'] ) : 4;
$recipe_scaler_ingredients   = recipe_scaler_sanitize_ingredients( $attributes['ingredients'] ?? array() );

/*
 * Global state for the store. Derived values are closures so the server
 * directive processor can compute them per block / per ingredient from the
 * local context, matching the getters defined in view.js.
 */
wp_interactivity_state(
	'recipe-scaler',
	array(
		'minServings'    => 1,
		'isMinimum'      => static function () {
			$context = wp_interactivity_get_context();
			return isset( $context['servings'] ) && (int) $context['servings'] <= 1;
		},
		'scaledQuantity' => static function () {
			$context = wp_interactivity_get_context();
			$index   = isset( $context['index'] ) ? (int) $context['index'] : -1;

			if ( empty( $context['ingredients'][ $index ] ) || empty( $context['baseServings'] ) ) {
				return '';
			}

			$quantity = (float) $context['ingredients'][ $index ]['quantity'];

			return recipe_scaler_format_quantity(
				$quantity * (int) $context['servings'] / (int) $context['baseServings']
			);
		},
	)
);

$recipe_scaler_context = array(
	'baseServings' => $recipe_scaler_base_servings,
	'servings'     => $recipe_scaler_base_servings,
	'ingredients'  => $recipe_scaler_ingredients,
);

$recipe_scaler_wrapper = get_block_wrapper_attributes( array( 'class' => 'recipe-scaler' ) );
?>
<div
	<?php echo $recipe_scaler_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes(). ?>
	data-wp-interactive="recipe-scaler"
	<?php echo wp_interactivity_data_wp_context( $recipe_scaler_context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_interactivity_data_wp_context(). ?>
>
	<?php if ( '' !== trim( wp_strip_all_tags( $recipe_scaler_title ) ) ) : ?>
		<h3 class="recipe-scaler__title"><?php echo wp_kses_post( $recipe_scaler_title ); ?></h3>
	<?php endif; ?>

	<div class="recipe-scaler__controls" role="group" aria-label="<?php esc_attr_e( 'Adjust servings', 'recipe-scaler' ); ?>">
		<button
			type="button"
			class="recipe-scaler__button recipe-scaler__button--decrease"
			aria-label="<?php esc_attr_e( 'Decrease servings', 'recipe-scaler' ); ?>"
			data-wp-on--click="actions.decrease"
			data-wp-bind--disabled="state.isMinimum"
			<?php disabled( $recipe_scaler_base_servings <= 1 ); ?>
		>&minus;</button>

		<span class="recipe-scaler__servings">
			<output class="recipe-scaler__servings-count" aria-live="polite" data-wp-text="context.servings"><?php echo esc_html( (string) $recipe_scaler_base_servings ); ?></output>
			<span class="recipe-scaler__servings-label"><?php esc_html_e( 'servings', 'recipe-scaler' ); ?></span>
		</span>

		<button
			type="button"
			class="recipe-scaler__button recipe-scaler__button--increase"
			aria-label="<?php esc_attr_e( 'Increase servings', 'recipe-scaler' ); ?>"
			data-wp-on--click="actions.increase"
		>+</button>
	</div>

	<?php if ( ! empty( $recipe_scaler_ingredients ) ) : ?>
		<ul class="recipe-scaler__ingredients">
			<?php foreach ( $recipe_scaler_ingredients as $recipe_scaler_index => $recipe_scaler_ingredient ) : ?>
				<li
					class="recipe-scaler__ingredient"
					<?php echo wp_interactivity_data_wp_context( array( 'index' => $recipe_scaler_index ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_interactivity_data_wp_context(). ?>
				>
					<span class="recipe-scaler__quantity" data-wp-text="state.scaledQuantity"><?php echo esc_html( recipe_scaler_format_quantity( $recipe_scaler_ingredient['quantity'] ) ); ?></span>
					<?php if ( '' !== $recipe_scaler_ingredient['unit'] ) : ?>
						<span class="recipe-scaler__unit"><?php echo esc_html( $recipe_scaler_ingredient['unit'] ); ?></span>
					<?php endif; ?>
					<span class="recipe-scaler__name"><?php echo esc_html( $recipe_scaler_ingredient['name'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
