<?php
/**
 * List table for RSVPs.
 *
 * @package EventRsvp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Displays RSVP submissions under Tools → Event RSVPs.
 */
class Event_RSVP_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'rsvp',
				'plural'   => 'rsvps',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Column headers.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'name'       => __( 'Name', 'event-rsvp' ),
			'email'      => __( 'Email', 'event-rsvp' ),
			'guests'     => __( 'Guests', 'event-rsvp' ),
			'post_id'    => __( 'Event', 'event-rsvp' ),
			'created_at' => __( 'Submitted', 'event-rsvp' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'name'       => array( 'name', false ),
			'email'      => array( 'email', false ),
			'guests'     => array( 'guests', false ),
			'post_id'    => array( 'post_id', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * Bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'event-rsvp' ),
		);
	}

	/**
	 * Loads the current page of rows.
	 */
	public function prepare_items() {
		$per_page = $this->get_items_per_page( 'event_rsvp_per_page', 20 );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only sorting parameters.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'created_at';
		$order   = isset( $_GET['order'] ) ? sanitize_key( wp_unslash( $_GET['order'] ) ) : 'desc';
		// phpcs:enable

		$total = Event_RSVP_DB::count_all();

		$this->items = Event_RSVP_DB::get_rows(
			array(
				'orderby'  => $orderby,
				'order'    => $order,
				'per_page' => $per_page,
				'page'     => $this->get_pagenum(),
			)
		);

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns(), 'name' );

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total / $per_page ),
			)
		);
	}

	/**
	 * Message when there are no rows.
	 */
	public function no_items() {
		esc_html_e( 'No RSVPs yet.', 'event-rsvp' );
	}

	/**
	 * Checkbox column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<label class="screen-reader-text" for="rsvp-%1$d">%2$s</label><input type="checkbox" id="rsvp-%1$d" name="rsvp[]" value="%1$d" />',
			absint( $item['id'] ),
			/* translators: %s: attendee name. */
			esc_html( sprintf( __( 'Select %s', 'event-rsvp' ), $item['name'] ) )
		);
	}

	/**
	 * Name column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_name( $item ) {
		return '<strong>' . esc_html( $item['name'] ) . '</strong>';
	}

	/**
	 * Email column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_email( $item ) {
		return sprintf( '<a href="%s">%s</a>', esc_url( 'mailto:' . $item['email'] ), esc_html( $item['email'] ) );
	}

	/**
	 * Event column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_post_id( $item ) {
		$post = get_post( (int) $item['post_id'] );

		if ( ! $post ) {
			/* translators: %d: post ID. */
			return esc_html( sprintf( __( '(deleted post #%d)', 'event-rsvp' ), (int) $item['post_id'] ) );
		}

		$title = get_the_title( $post );
		if ( '' === $title ) {
			$title = __( '(no title)', 'event-rsvp' );
		}

		$link = current_user_can( 'edit_post', $post->ID ) ? get_edit_post_link( $post->ID ) : get_permalink( $post );

		return sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html( $title ) );
	}

	/**
	 * Submitted column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_created_at( $item ) {
		$timestamp = strtotime( $item['created_at'] . ' UTC' );

		if ( ! $timestamp ) {
			return '&mdash;';
		}

		return esc_html(
			wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp )
		);
	}

	/**
	 * Fallback for other columns.
	 *
	 * @param array  $item        Row.
	 * @param string $column_name Column key.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( (string) $item[ $column_name ] ) : '';
	}
}
