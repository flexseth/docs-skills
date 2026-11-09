<?php
/**
 * Block Bindings source: event-rsvp/count.
 *
 * Usage in post content:
 * <!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"event-rsvp/count"}}}} -->
 * <p></p>
 * <!-- /wp:paragraph -->
 *
 * @package EventRSVP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the binding source.
 */
class Event_RSVP_Bindings {

	/**
	 * Hooks registration.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Registers `event-rsvp/count`.
	 *
	 * @return void
	 */
	public static function register() {
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}

		register_block_bindings_source(
			'event-rsvp/count',
			array(
				'label'              => __( 'RSVP count', 'event-rsvp' ),
				'get_value_callback' => array( __CLASS__, 'get_value' ),
				'uses_context'       => array( 'postId' ),
			)
		);
	}

	/**
	 * Returns the number of RSVPs for the current post.
	 *
	 * @param array    $source_args    Binding args. Optional `postId` overrides the context.
	 * @param WP_Block $block_instance Bound block.
	 * @param string   $attribute_name Bound attribute.
	 * @return string
	 */
	public static function get_value( array $source_args, $block_instance, $attribute_name ) {
		unset( $attribute_name );

		if ( ! empty( $source_args['postId'] ) ) {
			$post_id = absint( $source_args['postId'] );
		} elseif ( isset( $block_instance->context['postId'] ) ) {
			$post_id = absint( $block_instance->context['postId'] );
		} else {
			$post_id = (int) get_the_ID();
		}

		if ( ! $post_id ) {
			return '0';
		}

		return (string) number_format_i18n( Event_RSVP_DB::count_for_post( $post_id ) );
	}
}
