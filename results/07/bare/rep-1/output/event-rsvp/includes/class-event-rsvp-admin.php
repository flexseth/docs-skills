<?php
/**
 * Admin page: Tools → Event RSVPs.
 *
 * @package EventRsvp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin screen, bulk delete and CSV export.
 */
class Event_RSVP_Admin {

	const PAGE_SLUG         = 'event-rsvps';
	const CAPABILITY        = 'manage_options';
	const BULK_NONCE_ACTION = 'event_rsvp_bulk_action';
	const BULK_NONCE_NAME   = 'event_rsvp_bulk_nonce';
	const CSV_NONCE_ACTION  = 'event_rsvp_export_csv';
	const CSV_NONCE_NAME    = 'event_rsvp_csv_nonce';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_post_event_rsvp_export_csv', array( __CLASS__, 'export_csv' ) );
	}

	/**
	 * Registers the Tools submenu page.
	 */
	public static function add_menu() {
		$hook = add_management_page(
			__( 'Event RSVPs', 'event-rsvp' ),
			__( 'Event RSVPs', 'event-rsvp' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);

		if ( $hook ) {
			add_action( "load-{$hook}", array( __CLASS__, 'handle_bulk_action' ) );
		}
	}

	/**
	 * Returns the selected bulk action from either the top or bottom dropdown.
	 *
	 * @return string
	 */
	private static function current_bulk_action() {
		// phpcs:disable WordPress.Security.NonceVerification -- Nonce is verified by the caller before acting.
		foreach ( array( 'action', 'action2' ) as $key ) {
			if ( isset( $_REQUEST[ $key ] ) ) {
				$value = sanitize_key( wp_unslash( $_REQUEST[ $key ] ) );
				if ( '' !== $value && '-1' !== $value ) {
					return $value;
				}
			}
		}
		// phpcs:enable

		return '';
	}

	/**
	 * Processes bulk delete before any output so we can redirect.
	 */
	public static function handle_bulk_action() {
		if ( 'delete' !== self::current_bulk_action() ) {
			return;
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to delete RSVPs.', 'event-rsvp' ), 403 );
		}

		check_admin_referer( self::BULK_NONCE_ACTION, self::BULK_NONCE_NAME );

		$ids = isset( $_REQUEST['rsvp'] ) ? array_map( 'absint', (array) wp_unslash( $_REQUEST['rsvp'] ) ) : array();

		$deleted = Event_RSVP_DB::delete( $ids );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => self::PAGE_SLUG,
					'deleted' => $deleted,
				),
				admin_url( 'tools.php' )
			)
		);
		exit;
	}

	/**
	 * Renders the admin page.
	 */
	public static function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'event-rsvp' ), 403 );
		}

		require_once EVENT_RSVP_DIR . 'includes/class-event-rsvp-list-table.php';

		$table = new Event_RSVP_List_Table();
		$table->prepare_items();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only notice.
		$deleted = isset( $_GET['deleted'] ) ? absint( $_GET['deleted'] ) : null;
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Event RSVPs', 'event-rsvp' ); ?></h1>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-left:8px;">
				<input type="hidden" name="action" value="event_rsvp_export_csv" />
				<?php wp_nonce_field( self::CSV_NONCE_ACTION, self::CSV_NONCE_NAME ); ?>
				<?php submit_button( __( 'Download CSV', 'event-rsvp' ), 'secondary page-title-action', 'submit', false ); ?>
			</form>
			<hr class="wp-header-end" />

			<?php if ( null !== $deleted ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: number of deleted RSVPs. */
								_n( '%s RSVP deleted.', '%s RSVPs deleted.', $deleted, 'event-rsvp' ),
								number_format_i18n( $deleted )
							)
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( add_query_arg( 'page', self::PAGE_SLUG, admin_url( 'tools.php' ) ) ); ?>">
				<?php wp_nonce_field( self::BULK_NONCE_ACTION, self::BULK_NONCE_NAME ); ?>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Streams all RSVPs as CSV via admin-post.php.
	 */
	public static function export_csv() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to export RSVPs.', 'event-rsvp' ), 403 );
		}

		check_admin_referer( self::CSV_NONCE_ACTION, self::CSV_NONCE_NAME );

		$rows     = Event_RSVP_DB::get_rows(
			array(
				'orderby'  => 'created_at',
				'order'    => 'ASC',
				'per_page' => 0,
			)
		);
		$filename = 'event-rsvps-' . gmdate( 'Y-m-d' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'X-Content-Type-Options: nosniff' );

		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		// UTF-8 BOM so spreadsheet apps detect the encoding.
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

		fputcsv( $out, array( 'ID', 'Event ID', 'Event', 'Name', 'Email', 'Guests', 'Submitted (UTC)' ) );

		foreach ( $rows as $row ) {
			fputcsv(
				$out,
				array(
					(int) $row['id'],
					(int) $row['post_id'],
					self::csv_safe( get_the_title( (int) $row['post_id'] ) ),
					self::csv_safe( $row['name'] ),
					self::csv_safe( $row['email'] ),
					(int) $row['guests'],
					$row['created_at'],
				)
			);
		}

		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/**
	 * Neutralises spreadsheet formula injection in user-supplied values.
	 *
	 * @param string $value Cell value.
	 * @return string
	 */
	private static function csv_safe( $value ) {
		$value = wp_specialchars_decode( (string) $value, ENT_QUOTES );

		if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			$value = "'" . $value;
		}

		return $value;
	}
}
