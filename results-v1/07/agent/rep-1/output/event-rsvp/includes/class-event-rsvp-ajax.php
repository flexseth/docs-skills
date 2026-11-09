<?php
/**
 * AJAX handler for RSVP submissions.
 *
 * @package EventRSVP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles `event_rsvp_submit` for logged-in and logged-out users.
 */
class Event_RSVP_Ajax {

	/**
	 * Hooks the handler.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_ajax_event_rsvp_submit', array( __CLASS__, 'submit' ) );
		add_action( 'wp_ajax_nopriv_event_rsvp_submit', array( __CLASS__, 'submit' ) );
	}

	/**
	 * Validates and stores a submission.
	 *
	 * @return void
	 */
	public static function submit() {
		// Dies with -1 / 403 on an invalid nonce.
		check_ajax_referer( 'event_rsvp_submit', 'nonce' );

		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$guests  = isset( $_POST['guests'] ) ? absint( wp_unslash( $_POST['guests'] ) ) : 0;
		$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;

		$errors = array();

		if ( '' === $name ) {
			$errors['name'] = __( 'Please enter your name.', 'event-rsvp' );
		} elseif ( mb_strlen( $name ) > 191 ) {
			$errors['name'] = __( 'Your name is too long.', 'event-rsvp' );
		}

		if ( '' === $email || ! is_email( $email ) ) {
			$errors['email'] = __( 'Please enter a valid email address.', 'event-rsvp' );
		}

		if ( $guests < 1 || $guests > EVENT_RSVP_MAX_GUESTS ) {
			$errors['guests'] = sprintf(
				/* translators: %d: maximum number of guests. */
				__( 'Number of guests must be between 1 and %d.', 'event-rsvp' ),
				EVENT_RSVP_MAX_GUESTS
			);
		}

		$post = $post_id ? get_post( $post_id ) : null;
		if ( ! $post || 'publish' !== get_post_status( $post ) || ! is_post_type_viewable( $post->post_type ) ) {
			$errors['post_id'] = __( 'This event is not accepting RSVPs.', 'event-rsvp' );
		}

		if ( $errors ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please correct the errors below.', 'event-rsvp' ),
					'errors'  => $errors,
				),
				400
			);
		}

		if ( Event_RSVP_DB::exists( $post_id, $email ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'This email address has already RSVP’d for this event.', 'event-rsvp' ),
					'errors'  => array( 'email' => __( 'Already registered.', 'event-rsvp' ) ),
				),
				409
			);
		}

		$id = Event_RSVP_DB::insert( $post_id, $name, $email, $guests );

		if ( ! $id ) {
			wp_send_json_error(
				array( 'message' => __( 'Your RSVP could not be saved. Please try again.', 'event-rsvp' ) ),
				500
			);
		}

		/**
		 * Fires after an RSVP has been stored.
		 *
		 * @param int    $id      RSVP ID.
		 * @param int    $post_id Post ID.
		 * @param string $email   Email address.
		 * @param int    $guests  Number of guests.
		 */
		do_action( 'event_rsvp_submitted', $id, $post_id, $email, $guests );

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %s: attendee name. */
					__( 'Thanks, %s! Your RSVP has been received.', 'event-rsvp' ),
					$name
				),
				'count'   => Event_RSVP_DB::count_for_post( $post_id ),
			)
		);
	}
}
