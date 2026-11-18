<?php
/**
 * Server-side rendering of the `recipe-scaler/recipe` block.
 *
 * Directives are processed on the server, so the initial HTML already
 * contains the correct quantities before any JavaScript runs. Each block
 * gets its own `data-wp-context`, so several recipes on one page scale
 * independently.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 *
 * @package RecipeScaler
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recipe_scaler_title         = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$recipe_scaler_base_servings = isset( $attributes['servings'] ) ? max( 1, (int) $attributes['servings'] ) : 4;
$recipe_scaler_ingredients   = isset( $attributes['ingredients'] ) && is_array( $attributes['ingredients'] ) ? $attributes['ingredients'] : array();
$recipe_scaler_servings_id   = wp_unique_id( 'recipe-scaler-servings-' );

/*
 * Derived state, computed on the server from the current context so the
 * first render matches what the client-side getters in view.js return.
 */
wp_interactivity_state(
	'recipe-scaler',
	array(
		'scaledQuantity' => function () {
			$context = wp_interactivity_get_context();

			if ( ! isset( $context['quantity'] ) || ! is_numeric( $context['quantity'] ) ) {
				return '';
			}

			return recipe_scaler_format_quantity(
				$context['quantity'],
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

$recipe_scaler_context = array(
	'servings'     => $recipe_scaler_base_servings,
	'baseServings' => $recipe_scaler_base_servings,
);
?>
<div
	<?php echo get_block_wrapper_attributes( array( 'class' => 'wp-block-recipe-scaler' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
	data-wp-interactive="recipe-scaler"
	<?php echo wp_interactivity_data_wp_context( $recipe_scaler_context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
>
	<?php if ( '' !== trim( wp_strip_all_tags( $recipe_scaler_title ) ) ) : ?>
		<h3 class="wp-block-recipe-scaler__title"><?php echo wp_kses_post( $recipe_scaler_title ); ?></h3>
	<?php endif; ?>

	<div class="wp-block-recipe-scaler__servings">
		<span id="<?php echo esc_attr( $recipe_scaler_servings_id ); ?>" class="wp-block-recipe-scaler__servings-label">
			<?php esc_html_e( 'Servings', 'recipe-scaler' ); ?>
		</span>
		<button
			type="button"
			class="wp-block-recipe-scaler__button wp-block-recipe-scaler__button--decrease"
			aria-label="<?php esc_attr_e( 'Decrease servings', 'recipe-scaler' ); ?>"
			data-wp-on--click="actions.decrement"
			data-wp-bind--disabled="state.isMinimum"
		>&minus;</button>
		<output
			class="wp-block-recipe-scaler__servings-count"
			aria-live="polite"
			aria-labelledby="<?php echo esc_attr( $recipe_scaler_servings_id ); ?>"
			data-wp-text="context.servings"
		><?php echo esc_html( (string) $recipe_scaler_base_servings ); ?></output>
		<button
			type="button"
			class="wp-block-recipe-scaler__button wp-block-recipe-scaler__button--increase"
			aria-label="<?php esc_attr_e( 'Increase servings', 'recipe-scaler' ); ?>"
			data-wp-on--click="actions.increment"
		>+</button>
	</div>

	<?php if ( ! empty( $recipe_scaler_ingredients ) ) : ?>
		<ul class="wp-block-recipe-scaler__ingredients">
			<?php
			foreach ( $recipe_scaler_ingredients as $recipe_scaler_ingredient ) :
				if ( ! is_array( $recipe_scaler_ingredient ) ) {
					continue;
				}

				$recipe_scaler_raw_quantity = isset( $recipe_scaler_ingredient['quantity'] ) ? (string) $recipe_scaler_ingredient['quantity'] : '';
				$recipe_scaler_unit         = isset( $recipe_scaler_ingredient['unit'] ) ? (string) $recipe_scaler_ingredient['unit'] : '';
				$recipe_scaler_name         = isset( $recipe_scaler_ingredient['name'] ) ? (string) $recipe_scaler_ingredient['name'] : '';
				$recipe_scaler_quantity     = recipe_scaler_parse_quantity( $recipe_scaler_raw_quantity );

				if ( '' === trim( $recipe_scaler_raw_quantity . $recipe_scaler_unit . $recipe_scaler_name ) ) {
					continue;
				}
				?>
				<li class="wp-block-recipe-scaler__ingredient">
					<?php if ( null !== $recipe_scaler_quantity ) : ?>
						<span
							class="wp-block-recipe-scaler__quantity"
							<?php echo wp_interactivity_data_wp_context( array( 'quantity' => $recipe_scaler_quantity ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
							data-wp-text="state.scaledQuantity"
						><?php echo esc_html( recipe_scaler_format_quantity( $recipe_scaler_quantity, $recipe_scaler_base_servings, $recipe_scaler_base_servings ) ); ?></span>
					<?php elseif ( '' !== trim( $recipe_scaler_raw_quantity ) ) : ?>
						<span class="wp-block-recipe-scaler__quantity"><?php echo esc_html( $recipe_scaler_raw_quantity ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== trim( $recipe_scaler_unit ) ) : ?>
						<span class="wp-block-recipe-scaler__unit"><?php echo esc_html( $recipe_scaler_unit ); ?></span>
					<?php endif; ?>
					<span class="wp-block-recipe-scaler__name"><?php echo esc_html( $recipe_scaler_name ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
