<?php
/**
 * List table for RSVPs.
 *
 * @package EventRSVP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Lists RSVP submissions.
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
	 * Columns.
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
			'created_at' => __( 'Date', 'event-rsvp' ),
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
	 * Message when there are no rows.
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No RSVPs found.', 'event-rsvp' );
	}

	/**
	 * Loads items.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->get_items_per_page( 'event_rsvp_per_page', 20 );
		$paged    = $this->get_pagenum();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only sorting/search parameters.
		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_at';
		$order   = isset( $_REQUEST['order'] ) ? sanitize_key( wp_unslash( $_REQUEST['order'] ) ) : 'desc';
		$search  = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		// phpcs:enable

		$total = Event_RSVP_DB::count_all( $search );

		$this->items = Event_RSVP_DB::get_rows(
			array(
				'per_page' => $per_page,
				'offset'   => ( $paged - 1 ) * $per_page,
				'orderby'  => $orderby,
				'order'    => $order,
				'search'   => $search,
			)
		);

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
			'name',
		);

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total / $per_page ),
			)
		);
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
	 * Name column with row actions.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_name( $item ) {
		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'page'   => Event_RSVP_Admin::PAGE_SLUG,
					'action' => 'delete_single',
					'rsvp'   => absint( $item['id'] ),
				),
				admin_url( 'tools.php' )
			),
			'event_rsvp_delete_' . absint( $item['id'] )
		);

		$actions = array(
			'delete' => sprintf(
				'<a href="%s" class="submitdelete" onclick="return confirm( this.dataset.confirm );" data-confirm="%s">%s</a>',
				esc_url( $delete_url ),
				esc_attr__( 'Delete this RSVP?', 'event-rsvp' ),
				esc_html__( 'Delete', 'event-rsvp' )
			),
		);

		return '<strong>' . esc_html( $item['name'] ) . '</strong>' . $this->row_actions( $actions );
	}

	/**
	 * Email column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_email( $item ) {
		return sprintf(
			'<a href="%s">%s</a>',
			esc_url( 'mailto:' . antispambot( $item['email'] ) ),
			esc_html( antispambot( $item['email'] ) )
		);
	}

	/**
	 * Guests column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_guests( $item ) {
		return esc_html( number_format_i18n( absint( $item['guests'] ) ) );
	}

	/**
	 * Event column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_post_id( $item ) {
		$post_id = absint( $item['post_id'] );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return esc_html__( '(deleted)', 'event-rsvp' );
		}

		$title = get_the_title( $post );
		if ( '' === $title ) {
			$title = __( '(no title)', 'event-rsvp' );
		}

		$link = current_user_can( 'edit_post', $post_id ) ? get_edit_post_link( $post_id ) : get_permalink( $post );

		return $link
			? sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html( $title ) )
			: esc_html( $title );
	}

	/**
	 * Date column.
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
	 * Fallback column renderer.
	 *
	 * @param array  $item        Row.
	 * @param string $column_name Column.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( (string) $item[ $column_name ] ) : '';
	}
}
