<?php
/**
 * Server-side render for the recipe-scaler/recipe block.
 *
 * Available variables:
 * - $attributes (array):    Block attributes.
 * - $content    (string):   Block inner content (unused).
 * - $block      (WP_Block): Block instance.
 *
 * @package RecipeScaler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recipe_scaler_title    = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$recipe_scaler_servings = isset( $attributes['servings'] ) ? max( 1, absint( $attributes['servings'] ) ) : 4;
$recipe_scaler_raw      = isset( $attributes['ingredients'] ) && is_array( $attributes['ingredients'] ) ? $attributes['ingredients'] : array();

// Normalize ingredients into the shape used by the client-side context.
$recipe_scaler_ingredients = array();
foreach ( $recipe_scaler_raw as $recipe_scaler_item ) {
	if ( ! is_array( $recipe_scaler_item ) ) {
		continue;
	}

	$recipe_scaler_quantity = isset( $recipe_scaler_item['quantity'] ) ? sanitize_text_field( $recipe_scaler_item['quantity'] ) : '';
	$recipe_scaler_unit     = isset( $recipe_scaler_item['unit'] ) ? sanitize_text_field( $recipe_scaler_item['unit'] ) : '';
	$recipe_scaler_name     = isset( $recipe_scaler_item['name'] ) ? sanitize_text_field( $recipe_scaler_item['name'] ) : '';

	if ( '' === $recipe_scaler_quantity && '' === $recipe_scaler_unit && '' === $recipe_scaler_name ) {
		continue;
	}

	$recipe_scaler_ingredients[] = array(
		// Numeric base amount, or null if the quantity can't be scaled (e.g. "a pinch").
		'amount'   => recipe_scaler_parse_quantity( $recipe_scaler_quantity ),
		'original' => $recipe_scaler_quantity,
		'unit'     => $recipe_scaler_unit,
		'name'     => $recipe_scaler_name,
	);
}

/*
 * Global state for the store. The closures are derived state: they are
 * evaluated during server-side directive processing using the current
 * element's context, so the HTML is correct before JavaScript loads.
 * view.js defines getters with the same names for the client.
 */
wp_interactivity_state(
	'recipe-scaler',
	array(
		'scaledQuantity' => static function () {
			$context = wp_interactivity_get_context( 'recipe-scaler' );
			$index   = isset( $context['index'] ) ? (int) $context['index'] : -1;
			$items   = isset( $context['ingredients'] ) ? $context['ingredients'] : array();

			if ( ! isset( $items[ $index ] ) ) {
				return '';
			}

			$item = $items[ $index ];
			if ( null === $item['amount'] ) {
				return $item['original'];
			}

			$base     = max( 1, (int) $context['baseServings'] );
			$servings = max( 1, (int) $context['servings'] );

			return recipe_scaler_format_quantity( $item['amount'] * $servings / $base );
		},
		'isMinServings'  => static function () {
			$context = wp_interactivity_get_context( 'recipe-scaler' );
			return isset( $context['servings'] ) && (int) $context['servings'] <= 1;
		},
	)
);

// Per-block context: each block instance scales independently.
$recipe_scaler_context = array(
	'baseServings' => $recipe_scaler_servings,
	'servings'     => $recipe_scaler_servings,
	'ingredients'  => $recipe_scaler_ingredients,
);
?>
<div
	<?php echo get_block_wrapper_attributes( array( 'class' => 'recipe-scaler' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
	data-wp-interactive="recipe-scaler"
	<?php echo wp_interactivity_data_wp_context( $recipe_scaler_context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
>
	<?php if ( '' !== $recipe_scaler_title ) : ?>
		<h3 class="recipe-scaler__title"><?php echo wp_kses_post( $recipe_scaler_title ); ?></h3>
	<?php endif; ?>

	<div class="recipe-scaler__controls" role="group" aria-label="<?php esc_attr_e( 'Adjust servings', 'recipe-scaler' ); ?>">
		<button
			type="button"
			class="recipe-scaler__button recipe-scaler__button--decrease"
			aria-label="<?php esc_attr_e( 'Decrease servings', 'recipe-scaler' ); ?>"
			data-wp-on--click="actions.decrement"
			data-wp-bind--disabled="state.isMinServings"
		>&minus;</button>

		<span class="recipe-scaler__servings" aria-live="polite">
			<span class="recipe-scaler__servings-count" data-wp-text="context.servings"><?php echo esc_html( (string) $recipe_scaler_servings ); ?></span>
			<span class="recipe-scaler__servings-label"><?php esc_html_e( 'servings', 'recipe-scaler' ); ?></span>
		</span>

		<button
			type="button"
			class="recipe-scaler__button recipe-scaler__button--increase"
			aria-label="<?php esc_attr_e( 'Increase servings', 'recipe-scaler' ); ?>"
			data-wp-on--click="actions.increment"
		>+</button>
	</div>

	<?php if ( ! empty( $recipe_scaler_ingredients ) ) : ?>
		<ul class="recipe-scaler__ingredients">
			<?php foreach ( $recipe_scaler_ingredients as $recipe_scaler_index => $recipe_scaler_ingredient ) : ?>
				<li
					class="recipe-scaler__ingredient"
					<?php echo wp_interactivity_data_wp_context( array( 'index' => $recipe_scaler_index ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
				>
					<span class="recipe-scaler__quantity" data-wp-text="state.scaledQuantity">
						<?php
						echo esc_html(
							null === $recipe_scaler_ingredient['amount']
								? $recipe_scaler_ingredient['original']
								: recipe_scaler_format_quantity( $recipe_scaler_ingredient['amount'] )
						);
						?>
					</span>
					<?php if ( '' !== $recipe_scaler_ingredient['unit'] ) : ?>
						<span class="recipe-scaler__unit"><?php echo esc_html( $recipe_scaler_ingredient['unit'] ); ?></span>
					<?php endif; ?>
					<span class="recipe-scaler__name"><?php echo esc_html( $recipe_scaler_ingredient['name'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
