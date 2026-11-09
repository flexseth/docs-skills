<?php
/**
 * Admin page: Tools → Event RSVPs.
 *
 * @package EventRSVP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the admin screen, bulk delete and CSV export.
 */
class Event_RSVP_Admin {

	const PAGE_SLUG    = 'event-rsvps';
	const CAPABILITY   = 'manage_options';
	const BULK_NONCE   = 'event_rsvp_bulk';
	const BULK_FIELD   = '_event_rsvp_nonce';
	const EXPORT_NONCE = 'event_rsvp_export';

	/**
	 * Page hook suffix.
	 *
	 * @var string
	 */
	private static $hook = '';

	/**
	 * Hooks everything.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_event_rsvp_export', array( __CLASS__, 'export_csv' ) );
		add_filter( 'set_screen_option_event_rsvp_per_page', array( __CLASS__, 'save_screen_option' ), 10, 3 );
	}

	/**
	 * Adds the Tools submenu.
	 *
	 * @return void
	 */
	public static function menu() {
		$hook = add_management_page(
			__( 'Event RSVPs', 'event-rsvp' ),
			__( 'Event RSVPs', 'event-rsvp' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);

		if ( $hook ) {
			self::$hook = $hook;
			add_action( 'load-' . $hook, array( __CLASS__, 'on_load' ) );
		}
	}

	/**
	 * Runs before output: screen options and action handling.
	 *
	 * @return void
	 */
	public static function on_load() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'event-rsvp' ), 403 );
		}

		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'RSVPs per page', 'event-rsvp' ),
				'default' => 20,
				'option'  => 'event_rsvp_per_page',
			)
		);

		self::process_actions();
	}

	/**
	 * Persists the per-page screen option.
	 *
	 * @param mixed  $screen_option Default value (false).
	 * @param string $option        Option name.
	 * @param int    $value         Submitted value.
	 * @return int|mixed
	 */
	public static function save_screen_option( $screen_option, $option, $value ) {
		if ( 'event_rsvp_per_page' === $option ) {
			return max( 1, min( 999, absint( $value ) ) );
		}
		return $screen_option;
	}

	/**
	 * Handles bulk and single delete, then redirects.
	 *
	 * @return void
	 */
	private static function process_actions() {
		// phpcs:disable WordPress.Security.NonceVerification -- Nonces are verified below before any action.
		$action = '';
		if ( isset( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] ) {
			$action = sanitize_key( wp_unslash( $_REQUEST['action'] ) );
		} elseif ( isset( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] ) {
			$action = sanitize_key( wp_unslash( $_REQUEST['action2'] ) );
		}
		// phpcs:enable

		if ( 'delete' === $action ) {
			check_admin_referer( self::BULK_NONCE, self::BULK_FIELD );

			if ( ! current_user_can( self::CAPABILITY ) ) {
				wp_die( esc_html__( 'You do not have permission to delete RSVPs.', 'event-rsvp' ), 403 );
			}

			$ids     = isset( $_POST['rsvp'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['rsvp'] ) ) : array();
			$deleted = Event_RSVP_DB::delete( $ids );

			self::redirect_with_notice( $deleted );
		}

		if ( 'delete_single' === $action ) {
			$id = isset( $_GET['rsvp'] ) ? absint( $_GET['rsvp'] ) : 0;

			check_admin_referer( 'event_rsvp_delete_' . $id );

			if ( ! current_user_can( self::CAPABILITY ) ) {
				wp_die( esc_html__( 'You do not have permission to delete RSVPs.', 'event-rsvp' ), 403 );
			}

			$deleted = Event_RSVP_DB::delete( array( $id ) );

			self::redirect_with_notice( $deleted );
		}
	}

	/**
	 * Redirects back to the list with a deleted count.
	 *
	 * @param int $deleted Rows deleted.
	 * @return void
	 */
	private static function redirect_with_notice( $deleted ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => self::PAGE_SLUG,
					'deleted' => absint( $deleted ),
				),
				admin_url( 'tools.php' )
			)
		);
		exit;
	}

	/**
	 * Renders the page.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		require_once EVENT_RSVP_DIR . 'includes/class-event-rsvp-list-table.php';

		$table = new Event_RSVP_List_Table();
		$table->prepare_items();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag set by our own redirect.
		$deleted = isset( $_GET['deleted'] ) ? absint( $_GET['deleted'] ) : null;
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Event RSVPs', 'event-rsvp' ); ?></h1>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-left:8px;vertical-align:top;">
				<input type="hidden" name="action" value="event_rsvp_export" />
				<?php wp_nonce_field( self::EXPORT_NONCE, '_event_rsvp_export_nonce' ); ?>
				<?php submit_button( __( 'Download CSV', 'event-rsvp' ), 'page-title-action', 'submit', false ); ?>
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

			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>" />
				<?php $table->search_box( __( 'Search RSVPs', 'event-rsvp' ), 'event-rsvp-search' ); ?>
			</form>

			<form method="post" action="<?php echo esc_url( add_query_arg( 'page', self::PAGE_SLUG, admin_url( 'tools.php' ) ) ); ?>">
				<?php wp_nonce_field( self::BULK_NONCE, self::BULK_FIELD ); ?>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Streams all RSVPs as CSV via admin-post.php.
	 *
	 * @return void
	 */
	public static function export_csv() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to export RSVPs.', 'event-rsvp' ), 403 );
		}

		check_admin_referer( self::EXPORT_NONCE, '_event_rsvp_export_nonce' );

		$rows     = Event_RSVP_DB::get_all();
		$filename = 'event-rsvps-' . gmdate( 'Y-m-d' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'X-Content-Type-Options: nosniff' );

		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		// UTF-8 BOM so spreadsheet apps detect the encoding.
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

		fputcsv( $out, array( 'ID', 'Event ID', 'Event', 'Name', 'Email', 'Guests', 'Date (UTC)' ) );

		foreach ( $rows as $row ) {
			fputcsv(
				$out,
				array(
					(int) $row['id'],
					(int) $row['post_id'],
					self::csv_cell( html_entity_decode( get_the_title( (int) $row['post_id'] ), ENT_QUOTES, 'UTF-8' ) ),
					self::csv_cell( $row['name'] ),
					self::csv_cell( $row['email'] ),
					(int) $row['guests'],
					$row['created_at'],
				)
			);
		}

		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/**
	 * Neutralizes spreadsheet formula injection.
	 *
	 * @param string $value Cell value.
	 * @return string
	 */
	private static function csv_cell( $value ) {
		$value = (string) $value;
		if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			$value = "'" . $value;
		}
		return $value;
	}
}

Event_RSVP_Admin::init();
