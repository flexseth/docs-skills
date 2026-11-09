<?php
/**
 * Front-end markup for the event-rsvp/form block.
 *
 * @package EventRsvp
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner content (unused).
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event_rsvp_post_id = ! empty( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();

if ( ! $event_rsvp_post_id ) {
	return;
}

event_rsvp_add_view_script_data();

$event_rsvp_heading = ! empty( $attributes['heading'] ) ? $attributes['heading'] : __( 'RSVP', 'event-rsvp' );
$event_rsvp_button  = ! empty( $attributes['buttonText'] ) ? $attributes['buttonText'] : __( 'Send RSVP', 'event-rsvp' );
$event_rsvp_id      = wp_unique_id( 'event-rsvp-' );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'event-rsvp-form' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<h3 class="event-rsvp-form__heading" id="<?php echo esc_attr( $event_rsvp_id ); ?>-title"><?php echo esc_html( $event_rsvp_heading ); ?></h3>

	<form class="event-rsvp-form__form" method="post" novalidate aria-labelledby="<?php echo esc_attr( $event_rsvp_id ); ?>-title">
		<input type="hidden" name="post_id" value="<?php echo esc_attr( $event_rsvp_post_id ); ?>" />

		<p class="event-rsvp-form__field">
			<label for="<?php echo esc_attr( $event_rsvp_id ); ?>-name"><?php esc_html_e( 'Name', 'event-rsvp' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $event_rsvp_id ); ?>-name" name="name" required maxlength="200" autocomplete="name" />
			<span class="event-rsvp-form__error" data-error-for="name" aria-live="polite"></span>
		</p>

		<p class="event-rsvp-form__field">
			<label for="<?php echo esc_attr( $event_rsvp_id ); ?>-email"><?php esc_html_e( 'Email', 'event-rsvp' ); ?></label>
			<input type="email" id="<?php echo esc_attr( $event_rsvp_id ); ?>-email" name="email" required maxlength="200" autocomplete="email" />
			<span class="event-rsvp-form__error" data-error-for="email" aria-live="polite"></span>
		</p>

		<p class="event-rsvp-form__field">
			<label for="<?php echo esc_attr( $event_rsvp_id ); ?>-guests"><?php esc_html_e( 'Number of guests', 'event-rsvp' ); ?></label>
			<select id="<?php echo esc_attr( $event_rsvp_id ); ?>-guests" name="guests" required>
				<?php for ( $event_rsvp_i = 1; $event_rsvp_i <= Event_RSVP_Ajax::MAX_GUESTS; $event_rsvp_i++ ) : ?>
					<option value="<?php echo esc_attr( $event_rsvp_i ); ?>"><?php echo esc_html( number_format_i18n( $event_rsvp_i ) ); ?></option>
				<?php endfor; ?>
			</select>
			<span class="event-rsvp-form__error" data-error-for="guests" aria-live="polite"></span>
		</p>

		<p class="event-rsvp-form__hp" aria-hidden="true">
			<label for="<?php echo esc_attr( $event_rsvp_id ); ?>-website"><?php esc_html_e( 'Leave this field empty', 'event-rsvp' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $event_rsvp_id ); ?>-website" name="website" tabindex="-1" autocomplete="off" />
		</p>

		<p class="event-rsvp-form__actions">
			<button type="submit" class="wp-element-button event-rsvp-form__submit"><?php echo esc_html( $event_rsvp_button ); ?></button>
		</p>

		<div class="event-rsvp-form__message" role="status" aria-live="polite"></div>
	</form>
</div>
