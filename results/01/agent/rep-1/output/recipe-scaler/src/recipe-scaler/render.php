<?php
/**
 * Server-side render for the recipe block.
 *
 * Quantities are computed here so the page is correct before JavaScript
 * loads. The Interactivity API then takes over on the client. Each block
 * has its own context, so multiple recipes scale independently.
 *
 * @package RecipeScaler
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recipe_scaler_title       = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$recipe_scaler_servings    = isset( $attributes['servings'] ) ? max( 1, (int) $attributes['servings'] ) : 4;
$recipe_scaler_ingredients = isset( $attributes['ingredients'] ) && is_array( $attributes['ingredients'] ) ? $attributes['ingredients'] : array();

// Server-side derived state, evaluated during directive processing.
// Client-side getters with the same names in view.js take over after hydration.
wp_interactivity_state(
	'recipe-scaler',
	array(
		'scaledQuantity' => function () {
			$context = wp_interactivity_get_context();
			return recipe_scaler_format_quantity(
				isset( $context['quantity'] ) ? $context['quantity'] : null,
				isset( $context['servings'] ) ? $context['servings'] : 1,
				isset( $context['baseServings'] ) ? $context['baseServings'] : 1
			);
		},
		'isMinimum'      => function () {
			$context = wp_interactivity_get_context();
			return ! isset( $context['servings'] ) || (int) $context['servings'] <= 1;
		},
	)
);

$recipe_scaler_wrapper = get_block_wrapper_attributes(
	array(
		'class'               => 'recipe-scaler',
		'data-wp-interactive' => 'recipe-scaler',
	)
);
?>
<div
	<?php echo $recipe_scaler_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes(). ?>
	<?php
	echo wp_interactivity_data_wp_context( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_interactivity_data_wp_context().
		array(
			'servings'     => $recipe_scaler_servings,
			'baseServings' => $recipe_scaler_servings,
		)
	);
	?>
>
	<?php if ( '' !== trim( wp_strip_all_tags( $recipe_scaler_title ) ) ) : ?>
		<h3 class="recipe-scaler__title"><?php echo wp_kses_post( $recipe_scaler_title ); ?></h3>
	<?php endif; ?>

	<div class="recipe-scaler__servings">
		<span class="recipe-scaler__servings-label"><?php esc_html_e( 'Servings:', 'recipe-scaler' ); ?></span>
		<button
			type="button"
			class="recipe-scaler__button recipe-scaler__button--decrease"
			aria-label="<?php esc_attr_e( 'Decrease servings', 'recipe-scaler' ); ?>"
			data-wp-on--click="actions.decrement"
			data-wp-bind--disabled="state.isMinimum"
			<?php disabled( $recipe_scaler_servings <= 1 ); ?>
		>&minus;</button>
		<span
			class="recipe-scaler__servings-value"
			aria-live="polite"
			data-wp-text="context.servings"
		><?php echo esc_html( (string) $recipe_scaler_servings ); ?></span>
		<button
			type="button"
			class="recipe-scaler__button recipe-scaler__button--increase"
			aria-label="<?php esc_attr_e( 'Increase servings', 'recipe-scaler' ); ?>"
			data-wp-on--click="actions.increment"
		>+</button>
	</div>

	<?php if ( ! empty( $recipe_scaler_ingredients ) ) : ?>
		<ul class="recipe-scaler__ingredients">
			<?php
			foreach ( $recipe_scaler_ingredients as $recipe_scaler_ingredient ) :
				if ( ! is_array( $recipe_scaler_ingredient ) ) {
					continue;
				}

				$recipe_scaler_quantity = isset( $recipe_scaler_ingredient['quantity'] ) && is_numeric( $recipe_scaler_ingredient['quantity'] )
					? (float) $recipe_scaler_ingredient['quantity']
					: null;
				$recipe_scaler_unit     = isset( $recipe_scaler_ingredient['unit'] ) ? sanitize_text_field( $recipe_scaler_ingredient['unit'] ) : '';
				$recipe_scaler_name     = isset( $recipe_scaler_ingredient['name'] ) ? sanitize_text_field( $recipe_scaler_ingredient['name'] ) : '';

				if ( null === $recipe_scaler_quantity && '' === $recipe_scaler_unit && '' === $recipe_scaler_name ) {
					continue;
				}
				?>
				<li
					class="recipe-scaler__ingredient"
					<?php
					echo wp_interactivity_data_wp_context( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_interactivity_data_wp_context().
						array( 'quantity' => $recipe_scaler_quantity )
					);
					?>
				>
					<span class="recipe-scaler__quantity" data-wp-text="state.scaledQuantity"><?php echo esc_html( recipe_scaler_format_quantity( $recipe_scaler_quantity, $recipe_scaler_servings, $recipe_scaler_servings ) ); ?></span>
					<?php if ( '' !== $recipe_scaler_unit ) : ?>
						<span class="recipe-scaler__unit"><?php echo esc_html( $recipe_scaler_unit ); ?></span>
					<?php endif; ?>
					<span class="recipe-scaler__name"><?php echo esc_html( $recipe_scaler_name ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
