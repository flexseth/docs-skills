<?php
/**
 * Front-end AJAX submission handler.
 *
 * @package EventRsvp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles `event_rsvp_submit` for logged-in and logged-out visitors.
 */
class Event_RSVP_Ajax {

	const NONCE_ACTION = 'event_rsvp_submit';
	const MAX_GUESTS   = 5;
	const MAX_LENGTH   = 200;

	/**
	 * Hooks the handler.
	 */
	public static function init() {
		add_action( 'wp_ajax_event_rsvp_submit', array( __CLASS__, 'handle_submit' ) );
		add_action( 'wp_ajax_nopriv_event_rsvp_submit', array( __CLASS__, 'handle_submit' ) );
	}

	/**
	 * Validates and stores an RSVP, then replies with JSON.
	 */
	public static function handle_submit() {
		if ( false === check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Your session has expired. Please reload the page and try again.', 'event-rsvp' ) ),
				403
			);
		}

		// Honeypot: real visitors never fill this hidden field.
		if ( ! empty( $_POST['website'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Submission rejected.', 'event-rsvp' ) ), 400 );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$guests  = isset( $_POST['guests'] ) ? absint( wp_unslash( $_POST['guests'] ) ) : 0;

		$errors = array();

		$post = $post_id ? get_post( $post_id ) : null;
		if ( ! $post || 'publish' !== $post->post_status || ! is_post_publicly_viewable( $post ) || post_password_required( $post ) ) {
			$errors['post_id'] = __( 'This event is not accepting RSVPs.', 'event-rsvp' );
		}

		if ( '' === $name ) {
			$errors['name'] = __( 'Please enter your name.', 'event-rsvp' );
		} elseif ( mb_strlen( $name ) > self::MAX_LENGTH ) {
			$errors['name'] = __( 'Your name is too long.', 'event-rsvp' );
		}

		if ( '' === $email || ! is_email( $email ) ) {
			$errors['email'] = __( 'Please enter a valid email address.', 'event-rsvp' );
		} elseif ( strlen( $email ) > self::MAX_LENGTH ) {
			$errors['email'] = __( 'Your email address is too long.', 'event-rsvp' );
		}

		if ( $guests < 1 || $guests > self::MAX_GUESTS ) {
			$errors['guests'] = sprintf(
				/* translators: %d: maximum number of guests. */
				__( 'Number of guests must be between 1 and %d.', 'event-rsvp' ),
				self::MAX_GUESTS
			);
		}

		if ( ! empty( $errors ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please correct the highlighted fields.', 'event-rsvp' ),
					'errors'  => $errors,
				),
				422
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

		if ( false === $id ) {
			wp_send_json_error( array( 'message' => __( 'Your RSVP could not be saved. Please try again.', 'event-rsvp' ) ), 500 );
		}

		/**
		 * Fires after an RSVP has been stored.
		 *
		 * @param int    $id      RSVP row ID.
		 * @param int    $post_id Post ID.
		 * @param string $name    Name.
		 * @param string $email   Email.
		 * @param int    $guests  Number of guests.
		 */
		do_action( 'event_rsvp_submitted', $id, $post_id, $name, $email, $guests );

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
