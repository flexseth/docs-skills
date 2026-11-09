<?php
/**
 * Block Bindings source: event-rsvp/count.
 *
 * Usage (Code editor):
 * <!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"event-rsvp/count"}}}} -->
 * <p></p>
 * <!-- /wp:paragraph -->
 *
 * Pass {"args":{"key":"guests"}} to get the total number of guests instead.
 *
 * @package EventRsvp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the bindings source.
 */
class Event_RSVP_Bindings {

	/**
	 * Hooks registration.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Registers `event-rsvp/count` (WordPress 6.5+).
	 */
	public static function register() {
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}

		register_block_bindings_source(
			'event-rsvp/count',
			array(
				'label'              => __( 'Event RSVP count', 'event-rsvp' ),
				'get_value_callback' => array( __CLASS__, 'get_value' ),
				'uses_context'       => array( 'postId' ),
			)
		);
	}

	/**
	 * Returns the RSVP count for the current post.
	 *
	 * @param array    $source_args    Binding args. Optional `key` of `count` (default) or `guests`.
	 * @param WP_Block $block_instance Block being rendered.
	 * @param string   $attribute_name Bound attribute name.
	 * @return string
	 */
	public static function get_value( array $source_args, $block_instance, string $attribute_name ) {
		unset( $attribute_name );

		$post_id = ! empty( $block_instance->context['postId'] )
			? (int) $block_instance->context['postId']
			: (int) get_the_ID();

		if ( ! $post_id ) {
			return '0';
		}

		$sum_guests = isset( $source_args['key'] ) && 'guests' === $source_args['key'];

		return number_format_i18n( Event_RSVP_DB::count_for_post( $post_id, $sum_guests ) );
	}
}
