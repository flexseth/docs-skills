<?php
/**
 * Database layer for RSVPs.
 *
 * @package EventRSVP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the table and wraps all queries against it.
 */
class Event_RSVP_DB {

	/**
	 * Cache group for counts.
	 */
	const CACHE_GROUP = 'event_rsvp';

	/**
	 * Returns the full table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'event_rsvps';
	}

	/**
	 * Creates or upgrades the table with dbDelta().
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = self::table();
		$charset_collate = $wpdb->get_charset_collate();

		// dbDelta() requires two spaces after PRIMARY KEY and one field per line.
		$sql = "CREATE TABLE {$table} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  post_id bigint(20) unsigned NOT NULL DEFAULT 0,
  name varchar(191) NOT NULL DEFAULT '',
  email varchar(100) NOT NULL DEFAULT '',
  guests tinyint(3) unsigned NOT NULL DEFAULT 1,
  created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY post_id (post_id),
  KEY email (email)
) {$charset_collate};";

		dbDelta( $sql );

		update_option( 'event_rsvp_db_version', EVENT_RSVP_DB_VERSION, false );
	}

	/**
	 * Inserts an RSVP.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $name    Sanitized name.
	 * @param string $email   Sanitized email.
	 * @param int    $guests  Number of guests.
	 * @return int|false Inserted ID or false.
	 */
	public static function insert( $post_id, $name, $email, $guests ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			self::table(),
			array(
				'post_id'    => $post_id,
				'name'       => $name,
				'email'      => $email,
				'guests'     => $guests,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%d', '%s' )
		);

		if ( false === $result ) {
			return false;
		}

		wp_cache_delete( 'count_' . $post_id, self::CACHE_GROUP );

		return (int) $wpdb->insert_id;
	}

	/**
	 * Whether an email already RSVP'd for a post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $email   Email.
	 * @return bool
	 */
	public static function exists( $post_id, $email ) {
		global $wpdb;

		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE post_id = %d AND email = %s LIMIT 1", $post_id, $email ) );

		return ! empty( $id );
	}

	/**
	 * Number of RSVPs for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return int
	 */
	public static function count_for_post( $post_id ) {
		global $wpdb;

		$post_id = absint( $post_id );
		$count   = wp_cache_get( 'count_' . $post_id, self::CACHE_GROUP );

		if ( false === $count ) {
			$table = self::table();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE post_id = %d", $post_id ) );
			wp_cache_set( 'count_' . $post_id, $count, self::CACHE_GROUP );
		}

		return (int) $count;
	}

	/**
	 * Fetches a page of RSVPs.
	 *
	 * @param array $args {
	 *     @type int    $per_page Rows per page.
	 *     @type int    $offset   Offset.
	 *     @type string $orderby  Whitelisted column.
	 *     @type string $order    ASC|DESC.
	 *     @type string $search   Search term.
	 * }
	 * @return array[]
	 */
	public static function get_rows( array $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'per_page' => 20,
				'offset'   => 0,
				'orderby'  => 'created_at',
				'order'    => 'DESC',
				'search'   => '',
			)
		);

		$allowed_orderby = array( 'id', 'name', 'email', 'guests', 'post_id', 'created_at' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
		$table           = self::table();

		list( $where, $params ) = self::search_clause( $args['search'] );

		$params[] = (int) $args['per_page'];
		$params[] = (int) $args['offset'];

		// $orderby and $order are whitelisted above.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
				$params
			),
			ARRAY_A
		);
		// phpcs:enable

		return $rows ? $rows : array();
	}

	/**
	 * Fetches all RSVPs (for CSV export).
	 *
	 * @return array[]
	 */
	public static function get_all() {
		global $wpdb;

		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A );

		return $rows ? $rows : array();
	}

	/**
	 * Total row count, optionally filtered by search.
	 *
	 * @param string $search Search term.
	 * @return int
	 */
	public static function count_all( $search = '' ) {
		global $wpdb;

		$table                  = self::table();
		list( $where, $params ) = self::search_clause( $search );
		$sql                    = "SELECT COUNT(*) FROM {$table} {$where}";

		if ( $params ) {
			$sql = $wpdb->prepare( $sql, $params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Deletes RSVPs by ID.
	 *
	 * @param int[] $ids IDs.
	 * @return int Number of deleted rows.
	 */
	public static function delete( array $ids ) {
		global $wpdb;

		$ids = array_filter( array_map( 'absint', $ids ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		$table        = self::table();
		$placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_id FROM {$table} WHERE id IN ( {$placeholders} )", $ids ) );
		$deleted  = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ( {$placeholders} )", $ids ) );
		// phpcs:enable

		foreach ( (array) $post_ids as $post_id ) {
			wp_cache_delete( 'count_' . (int) $post_id, self::CACHE_GROUP );
		}

		return (int) $deleted;
	}

	/**
	 * Builds a WHERE clause for a search term.
	 *
	 * @param string $search Search term.
	 * @return array{0:string,1:array}
	 */
	private static function search_clause( $search ) {
		global $wpdb;

		$search = trim( (string) $search );
		if ( '' === $search ) {
			return array( '', array() );
		}

		$like = '%' . $wpdb->esc_like( $search ) . '%';

		return array( 'WHERE name LIKE %s OR email LIKE %s', array( $like, $like ) );
	}
}
