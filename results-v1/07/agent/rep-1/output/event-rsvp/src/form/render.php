<?php
/**
 * Front-end markup for the Event RSVP form block.
 *
 * @package EventRSVP
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event_rsvp_post_id = isset( $block->context['postId'] ) ? absint( $block->context['postId'] ) : (int) get_the_ID();

if ( ! $event_rsvp_post_id ) {
	return;
}

$event_rsvp_uid     = wp_unique_id( 'event-rsvp-' );
$event_rsvp_heading = isset( $attributes['heading'] ) ? $attributes['heading'] : '';
$event_rsvp_button  = ! empty( $attributes['buttonText'] ) ? $attributes['buttonText'] : __( 'Send RSVP', 'event-rsvp' );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'event-rsvp' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<?php if ( '' !== $event_rsvp_heading ) : ?>
		<h3 class="event-rsvp__heading"><?php echo wp_kses( $event_rsvp_heading, array( 'strong' => array(), 'em' => array() ) ); ?></h3>
	<?php endif; ?>

	<form class="event-rsvp__form" method="post" novalidate>
		<input type="hidden" name="post_id" value="<?php echo esc_attr( $event_rsvp_post_id ); ?>" />

		<p class="event-rsvp__field">
			<label for="<?php echo esc_attr( $event_rsvp_uid . '-name' ); ?>"><?php esc_html_e( 'Name', 'event-rsvp' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $event_rsvp_uid . '-name' ); ?>" name="name" maxlength="191" autocomplete="name" required aria-describedby="<?php echo esc_attr( $event_rsvp_uid . '-name-error' ); ?>" />
			<span class="event-rsvp__error" id="<?php echo esc_attr( $event_rsvp_uid . '-name-error' ); ?>" data-error-for="name"></span>
		</p>

		<p class="event-rsvp__field">
			<label for="<?php echo esc_attr( $event_rsvp_uid . '-email' ); ?>"><?php esc_html_e( 'Email', 'event-rsvp' ); ?></label>
			<input type="email" id="<?php echo esc_attr( $event_rsvp_uid . '-email' ); ?>" name="email" maxlength="100" autocomplete="email" required aria-describedby="<?php echo esc_attr( $event_rsvp_uid . '-email-error' ); ?>" />
			<span class="event-rsvp__error" id="<?php echo esc_attr( $event_rsvp_uid . '-email-error' ); ?>" data-error-for="email"></span>
		</p>

		<p class="event-rsvp__field">
			<label for="<?php echo esc_attr( $event_rsvp_uid . '-guests' ); ?>"><?php esc_html_e( 'Number of guests', 'event-rsvp' ); ?></label>
			<select id="<?php echo esc_attr( $event_rsvp_uid . '-guests' ); ?>" name="guests" required aria-describedby="<?php echo esc_attr( $event_rsvp_uid . '-guests-error' ); ?>">
				<?php for ( $event_rsvp_i = 1; $event_rsvp_i <= EVENT_RSVP_MAX_GUESTS; $event_rsvp_i++ ) : ?>
					<option value="<?php echo esc_attr( $event_rsvp_i ); ?>"><?php echo esc_html( number_format_i18n( $event_rsvp_i ) ); ?></option>
				<?php endfor; ?>
			</select>
			<span class="event-rsvp__error" id="<?php echo esc_attr( $event_rsvp_uid . '-guests-error' ); ?>" data-error-for="guests"></span>
		</p>

		<p>
			<button type="submit" class="wp-element-button event-rsvp__submit"><?php echo esc_html( $event_rsvp_button ); ?></button>
		</p>

		<p class="event-rsvp__message" role="status" aria-live="polite"></p>
	</form>
</div>
